<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\MandateType;
use DateTime;
use Db;
use Input;
use Lang;
use Model;
use Session;
use ValidationException;

/**
 * Model
 */
class OrganizationBase extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    public $ignoreValidation = false;

    protected $dates = ['deleted_at'];

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
    // !!!DELETE!!!    $mandates = Mandate::where('mandate_model_type', $this->getOrganizationTypeModelName())->where('mandate_model_id', $this->id)->get();
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

    public function getRightsForMandateTypes(array $mandateTypeIds = [], bool $own = false, bool $twoFA = false){

        $associationId = $this->getAssociationId();

        if(empty($associationId)) {
            return;
        }

        if(empty($mandateTypeIds) && $guestMandateTypeId = MandateType::guestMandateTypeInAssociation($associationId)) {
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
            ->when(!$twoFA, function ($query) {
                return $query->where(
                    function ($query) {
                        return $query->where('2fa', '<>', 1)->orWhereNull('2fa');
                    });
            })
            ->whereIn('mandate_type_id', $mandateTypeIds)
            ->where('model', self::getOrganizationTypeModelName())
            ->get();

        $rights = $rights->groupBy('field');

        return $rights->map(function ($item, $key){
            return [
                'obligatory' => $item->min('obligatory'),
                'create' => $item->max('create'),
                'read' => $item->max('read'),
                'update' => $item->max('update'),
                'delete' => $item->max('delete'),
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
}
