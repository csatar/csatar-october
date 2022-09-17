<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Classes\RightsMatrix;
use Model;
use Db;
use RainLab\Builder\Classes\ModelModel;
use RainLab\Builder\Classes\PluginCode;
use Yaml;
use Lang;
use Session;
use October\Rain\Database\Collection;

class PermissionBasedAccess extends Model
{
    protected static $translatedAttributeNames = null;

    /**
     * @param $scout
     * @return bool
     * If scout has mandates for the specific record is considered own
     */
    public function isOwnModel($scout)
    {
        $mandates = $scout->getMandatesForOrganization($this);
        return count($mandates) > 0;
    }

    /**
     * Returns the id of the association to which the item belongs to.
     */
    public function getAssociationId()
    {
        if ($this->team_id) {
            return $this->team->district->association->id;
        }

        return null;
    }

    public static function getModelName()
    {
        return '\\' . static::class;
    }

    public function getRightsForMandateTypes(array $mandateTypeIds = [], bool $own = false, bool $is2fa = false)
    {

        $associationId = $this->getAssociationId();

        if (empty($associationId)) {
            return;
        }

        if (empty($mandateTypeIds) && $guestMandateTypeId = MandateType::getGuestMandateTypeIdInAssociation($associationId)) {
            $mandateTypeIds = [$guestMandateTypeId];
        }

        if (empty($mandateTypeIds)) {
            return;
        }

        $rights = Db::table('csatar_csatar_mandates_permissions')
            ->when(!$own, function ($query) {
                return $query->where(
                    function ($query) {
                        return $query->where('own', '<>', 1)->orWhereNull('own');
                    });
            })
            ->whereIn('mandate_type_id', $mandateTypeIds)
            ->where('model', self::getModelName())
            ->get();

        $rights = $rights->groupBy('field');

        return $rights->map(function ($item, $key) use ($is2fa) {
            return [
                'obligatory' => $is2fa ? $item->min('obligatory') : $item->min('obligatory') - 1,
                'create' => $is2fa ? $item->max('create') : $item->max('create') - 1,
                'read' => $is2fa ? $item->max('read') : $item->max('read') - 1,
                'update' => $is2fa ? $item->max('update') : $item->max('update') - 1,
                'delete' => $is2fa ? $item->max('delete') : $item->max('delete') - 1,
                // in the permissions table we have 0 - if user has no permission, 1 - if has permission only with 2fa, 2 - if has permission without 2fa
                // if logged in user has NO 2fa, we substract 1 from every value, so where 2fa permission is needed, value will be 0, and no permission is granted
            ];
        });
    }

    public function getGuestRightsForModel()
    {
        $associationId = $this->getAssociationId();
        $modelName = $this::getModelName();
        $key = $associationId . $modelName;

        $sessionRecord = Session::get('guest.rightsForModels');
        $sessionRecordForModel = $sessionRecord ? $sessionRecord->get($key) : null;

        if (!empty($sessionRecordForModel) && $sessionRecordForModel['savedToSession'] >= RightsMatrix::getRightsMatrixLastUpdateTime() && $sessionRecordForModel['rights']->count() != 0) {
            return $sessionRecordForModel['rights'];
        }

        if (empty($sessionRecord)) {
            $sessionRecord = new Collection([]);
        }

        $rights = $this->getRightsForMandateTypes();
        $sessionRecord = $sessionRecord->replace([
            $key => [
                'associationId' => $associationId,
                'model' => $modelName,
                'savedToSession' => date('Y-m-d H:i'),
                'rights' => $rights,
            ],
        ]);

        Session::put('guest.rightsForModels', $sessionRecord);

        return $rights;
    }

    public static function getTranslatedAttributeNames(string $organizationTypeModelName = null): array
    {
        if($organizationTypeModelName == '\Csatar\Csatar\Models\OrganizationBase'){
            return [];
        }

        if ((is_array(self::$translatedAttributeNames) && !array_key_exists($organizationTypeModelName, self::$translatedAttributeNames)) ||
            !is_array(self::$translatedAttributeNames)
        ) {
            $path = strtolower(str_replace('\\', DIRECTORY_SEPARATOR, plugins_path() . $organizationTypeModelName . '\\fields.yaml'));
            $attributes = Yaml::parseFile($path);
            foreach ($attributes['fields'] as $key => $attribute) {
                self::$translatedAttributeNames[$organizationTypeModelName][$key] = Lang::get($attribute['label']);
            }

            // add labels from belongsTo->label
            $model = new $organizationTypeModelName();
            foreach ($model->belongsToMany as $realtionName => $relationData) {
                if (is_array($relationData) && array_key_exists('label', $relationData)) {
                    self::$translatedAttributeNames[$organizationTypeModelName][$realtionName] = Lang::get($relationData['label']);
                }
            }

            foreach ($model->hasMany as $realtionName => $relationData) {
                if (is_array($relationData) && array_key_exists('label', $relationData)) {
                    self::$translatedAttributeNames[$organizationTypeModelName][$realtionName] = Lang::get($relationData['label']);
                }
            }

            foreach ($model->morphOne as $realtionName => $relationData) {
                if (is_array($relationData) && array_key_exists('label', $relationData)) {
                    self::$translatedAttributeNames[$organizationTypeModelName][$realtionName] = Lang::get($relationData['label']);
                }
            }
        }

        return self::$translatedAttributeNames[$organizationTypeModelName];
    }

    public static function getAllChildClasses(): array
    {
        $result = [];
        try {
            $pluginCodeObj = new PluginCode('Csatar.Csatar');

            $models = ModelModel::listPluginModels($pluginCodeObj);

            $pluginCodeStr = $pluginCodeObj->toCode();
            $pluginModelsNamespace = $pluginCodeObj->toPluginNamespace() . '\\Models\\';
            foreach ($models as $model) {
                $fullClassName = $pluginModelsNamespace . $model->className;

                if(!is_subclass_of($fullClassName, self::getModelName())){
                    continue;
                };

                $result[$fullClassName] = '\\' . $fullClassName;
            }
        } catch (Exception $ex) {
            // Ignore invalid plugins and models
        }

        return $result;
    }
}
