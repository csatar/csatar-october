<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\MandateType;
use DateTime;
use Lang;
use Model;
use ValidationException;

/**
 * Model
 */
class OrganizationBase extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

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
        if (!$this->id) {
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
}
