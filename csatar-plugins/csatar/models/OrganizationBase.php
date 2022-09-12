<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Classes\RightsMatrix;
use Csatar\Csatar\Models\MandateType;
use DateTime;
use Db;
use Input;
use Lang;
use Model;
use October\Rain\Database\Collection;
use Session;
use ValidationException;
use Yaml;

/**
 * Model
 */
class OrganizationBase extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    public $ignoreValidation = false;

    protected $dates = ['deleted_at'];

    protected static $translatedAttributeNames = null;

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate()
    {
        // check that the required mandates are set for now
        $this->validateRequiredMandates($this->attributes);
    }

    public function beforeValidateFromForm($data)
    {
        // check that the required mandates are set for now
        $this->validateRequiredMandates($data);
    }

    public function validateRequiredMandates($data)
    {
        if (!array_key_exists('id', $data) || Input::get('recordKeyValue') == 'new' || $this->ignoreValidation) {
            return;
        }

        $mandateTypes = MandateType::where('association_id', $this->getAssociationId())->where('organization_type_model_name', $this->getOrganizationTypeModelName())->where('required', true)->get();
        $mandates = $this->mandates;
        $now = new \DateTime();

        foreach ($mandateTypes as $mandateType) {
            $validMandate = false;
            for ($i = 0; $i < count($mandates) && !$validMandate; ++$i) {
                $mandate = $mandates[$i];

                if ($mandate->mandate_type_id == $mandateType->id && new DateTime($mandate->start_date) < $now && (!$mandate->end_date || new DateTime($mandate->end_date) > $now)) {
                    $validMandate = true;
                    break;
                }
            }
            if (!$validMandate) {
                throw new ValidationException(['logo' => str_replace('%name', $mandateType->name, Lang::get('csatar.csatar::lang.plugin.admin.mandate.requiredMandateError'))]);
            }
        }
    }

    /**
     * Relations
     */

    public $hasMany = [
        'mandates' => [
            '\Csatar\Csatar\Models\Mandate',
            'table' => 'csatar_csatar_mandates',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
        ],
    ];

    /**
     * Returns the name of the organization
     */
    public function getExtendedNameAttribute()
    {
        return $this->attributes['name'];
    }

    public static function getOrganizationTypeModelName()
    {
        return '\\' . static::class;
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.csatar::lang.plugin.admin.organizationBase.organizationBase');
    }

    public function filterNameForWords($name, $filterWords){
        $filterWords = array_map('trim',$filterWords);
        $nameExploded = explode(' ', $name);
        $nameFiltered = array_map(function($word) use ($filterWords){
            if(in_array(mb_strtolower($word), $filterWords)){
                return '';
            }

            return $word;
        }, $nameExploded);

        return trim(implode(' ', $nameFiltered));
    }

    public function getGuestRightsForModel() {
        $associationId = $this->getAssociationId();
        $modelName = $this::getOrganizationTypeModelName();
        $key = $associationId . $modelName;

        $sessionRecord = Session::get('guest.rightsForModels');
        $sessionRecordForModel = $sessionRecord ? $sessionRecord->get($key) : null;

        if (!empty($sessionRecordForModel) && $sessionRecordForModel['savedToSession'] >= RightsMatrix::getRightsMatrixLastUpdateTime() && $sessionRecordForModel['rights']->count() != 0) {
            return $sessionRecordForModel['rights'];
        }

        if(empty($sessionRecord)){
            $sessionRecord = new Collection([]);
        }

        $rights = $this->getRightsForMandateTypes();
        $sessionRecord = $sessionRecord->replace([
            $key => [
                'associationId' => $associationId,
                'model' => $modelName,
                'savedToSession' => date('Y-m-d H:i'),
                'rights'=> $rights,
            ],
        ]);

        Session::put('guest.rightsForModels', $sessionRecord);

        return $rights;
    }

    public function getRightsForMandateTypes(array $mandateTypeIds = [], bool $own = false, bool $is2fa = false){

        $associationId = $this->getAssociationId();

        if(empty($associationId)) {
            return;
        }

        if(empty($mandateTypeIds) && $guestMandateTypeId = MandateType::getGuestMandateTypeIdInAssociation($associationId)) {
            $mandateTypeIds = [ $guestMandateTypeId ];
        }

        if(empty($mandateTypeIds)) {
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
            ->where('model', self::getOrganizationTypeModelName())
            ->get();

        $rights = $rights->groupBy('field');

        return $rights->map(function ($item, $key) use ($is2fa){
            return [
                'obligatory' => $is2fa ? $item->min('obligatory') : $item->min('obligatory') - 1,
                'create' => $is2fa ? $item->max('create') : $item->max('create') -1,
                'read' => $is2fa ? $item->max('read') : $item->max('read') -1,
                'update' => $is2fa ? $item->max('update') : $item->max('update') -1,
                'delete' => $is2fa ? $item->max('delete') : $item->max('delete') -1,
                // in the permissions table we have 0 - if user has no permission, 1 - if has permission only with 2fa, 2 - if has permission without 2fa
                // if logged in user has NO 2fa, we substract 1 from every value, so where 2fa permission is needed, value will be 0, and no permission is granted
            ];
        });
    }

    public function isOwnOrganization($scout){

        $mandates = $scout->getMandatesForOrganization($this);
        return count($mandates) > 0;
    }

    public function getAssociationId(){
        return null;
    }

    public static function getTranslatedAttributeNames(string $organizationTypeModelName = null): array
    {
        if ((is_array(self::$translatedAttributeNames) && !array_key_exists($organizationTypeModelName, self::$translatedAttributeNames)) ||
            !is_array(self::$translatedAttributeNames)
        ) {
            $attributes = Yaml::parseFile(plugins_path() . $organizationTypeModelName . '\\fields.yaml');
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
        }

        return self::$translatedAttributeNames[$organizationTypeModelName];
    }
}
