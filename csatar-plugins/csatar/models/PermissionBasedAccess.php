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

    public function getRightsForMandateTypes(array $mandateTypeIds = [], bool $own = false, bool $is2fa = false, bool $ignoreCache = false)
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

        if (!$ignoreCache) {
            $rights = $this->getRightsForMandateTypesFromSession($mandateTypeIds, self::getModelName());
        }

        if ($ignoreCache || empty($rights) || $rights->count() == 0) {
            $rights = MandatePermission::whereIn('mandate_type_id', $mandateTypeIds)
               ->where('model', self::getModelName())
               ->get();
            $this->saveRightsForMandateTypesToSession($rights);
        }

        $rights = $rights->when(!$own, function ($collection) {
            return $collection->reject(function ($item) {
                return $item->own == 1;
            });
        });
        $rights = $rights->groupBy('field');

        $rights = $rights->map(function ($item, $key) use ($is2fa) {
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

        $rights->put( 'is2fa', $is2fa );

        return $rights;
    }

    public function getRightsForMandateTypesFromSession(array $mandateTypeIds, string $model){
        $sessionRecord = Session::get('scout.rightsForMandateTypes');
        return $sessionRecord ? $sessionRecord->whereIn('mandate_type_id', $mandateTypeIds)->where('model', $model) : null;
    }

    public function saveRightsForMandateTypesToSession($rights){
        Session::put('scout.rightsForMandateTypes', $rights);
    }

    public function getGuestRightsForModel()
    {
        $associationId = $this->getAssociationId();
        $modelName = $this::getModelName();
        $key = $associationId . $modelName;

        $sessionRecord = Session::get('guest.rightsForModels');
        $sessionRecordForModel = $sessionRecord ? $sessionRecord->get($key) : null;

        if (!empty($sessionRecordForModel)
            && $sessionRecordForModel['savedToSession'] >= RightsMatrix::getRightsMatrixLastUpdateTime()
            && $sessionRecordForModel['rights']->count() != 0)
        {
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
            $fields = strtolower(str_replace('\\', DIRECTORY_SEPARATOR, plugins_path() . $organizationTypeModelName . '\\fields.yaml'));
            $columns = strtolower(str_replace('\\', DIRECTORY_SEPARATOR, plugins_path() . $organizationTypeModelName . '\\columns.yaml'));
            $attributes = array_merge(Yaml::parseFile($fields)['fields'], Yaml::parseFile($columns)['columns']);
            foreach ($attributes as $key => $attribute) {
                if (is_array($attribute) && array_key_exists('label', $attribute)) {
                    self::$translatedAttributeNames[$organizationTypeModelName][$key] = Lang::get($attribute['label']);
                }
            }

            // add labels from belongsTo->label
            $model = new $organizationTypeModelName();
            foreach ($model->belongsTo as $realtionName => $relationData) {
                if (is_array($relationData) && array_key_exists('label', $relationData)) {
                    self::$translatedAttributeNames[$organizationTypeModelName][$realtionName] = Lang::get($relationData['label']);
                }
            }

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
            $pluginCodes = ['Csatar.Csatar', 'Csatar.KnowledgeRepository'];

            foreach ($pluginCodes as $pluginCode) {
                $pluginCodeObj = new PluginCode($pluginCode);
                $models = ModelModel::listPluginModels($pluginCodeObj);
                $pluginModelsNamespace = $pluginCodeObj->toPluginNamespace() . '\\Models\\';
                foreach ($models as $model) {
                    $fullClassName = $pluginModelsNamespace . $model->className;
                    if(!is_subclass_of($fullClassName, self::getModelName())){
                        continue;
                    };
                    $result[$fullClassName] = '\\' . $fullClassName;
                }
            }
        } catch (Exception $ex) {
            // Ignore invalid plugins and models
        }

        return $result;
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return '';
    }

    public static function getEagerLoadSettings(string $useCase = null): array
    {
        return [];
    }

    public function getAssociation() {
        return null;
    }

    public function getDistrict() {
        return null;
    }

    public function getTeam() {
        return null;
    }

    public function getTroop() {
        return null;
    }

    public function getPatrol() {
        return null;
    }
}
