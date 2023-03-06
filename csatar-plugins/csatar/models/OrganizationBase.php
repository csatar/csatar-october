<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Classes\Enums\Status;
use Csatar\Csatar\Classes\RightsMatrix;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\Mandate;
use Csatar\Csatar\Models\PermissionBasedAccess;
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
class OrganizationBase extends PermissionBasedAccess
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    public $ignoreValidation = false;

    protected $dates = ['deleted_at'];

    protected static $searchable = null;

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
        if ($this->ignoreValidation) {
            return;
        }

        if (!array_key_exists('id', $data) || Input::get('recordKeyValue') == 'new' || $this->ignoreValidation) {
            return;
        }

        $mandateTypes = MandateType::where('association_id', $this->getAssociationId())->where('organization_type_model_name', $this->getModelName())->where('required', true)->get();
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

    public static function getEagerLoadSettings(string $useCase = null): array
    {
        $eagerLoadSettings = [];

        if ($useCase == 'formBuilder') {
            $eagerLoadSettings = [
                'mandates',
                'mandates.mandate_type' => function($query) {
                    return $query->select(
                        'csatar_csatar_mandate_types.id',
                        'csatar_csatar_mandate_types.name'
                    );
                },
                'mandates.scout' => function($query) {
                    return $query->select(
                        'csatar_csatar_scouts.id',
                        'csatar_csatar_scouts.ecset_code',
                        'csatar_csatar_scouts.family_name',
                        'csatar_csatar_scouts.given_name',
                        'csatar_csatar_scouts.team_id'
                    );
                },
                'mandates.scout.team'  => function($query) {
                    return $query->select(
                        'csatar_csatar_teams.id',
                        'csatar_csatar_teams.name',
                        'csatar_csatar_teams.team_number'
                    );
                },
            ];
        }

        if ($useCase == 'inactiveMandates') {
            $eagerLoadSettings = [
                'mandatesInactive',
                'mandatesInactive.mandate_type' => function($query) {
                    return $query->select(
                        'csatar_csatar_mandate_types.id',
                        'csatar_csatar_mandate_types.name'
                    )->withTrashed();
                },
                'mandatesInactive.scout' => function($query) {
                    return $query->select(
                        'csatar_csatar_scouts.id',
                        'csatar_csatar_scouts.ecset_code',
                        'csatar_csatar_scouts.family_name',
                        'csatar_csatar_scouts.given_name',
                        'csatar_csatar_scouts.team_id'
                    );
                },
                'mandatesInactive.scout.team'  => function($query) {
                    return $query->select(
                        'csatar_csatar_teams.id',
                        'csatar_csatar_teams.name',
                        'csatar_csatar_teams.team_number'
                    );
                },
            ];
        }

        return $eagerLoadSettings;
    }

    function afterUpdate()
    {
        $now = new DateTime();
        if (isset($this->original['name']) && $this->name !== $this->original['name']) {
            $mandates = Mandate::where('mandate_model_type', '\\' . static::class)->where('mandate_model_id', $this->id)->get();
            foreach ($mandates as $mandate) {
                if ($mandate->start_date < $now) {
                    $mandate->mandate_model_name = $this->name;
                    $mandate->save();
                }
            }
        }
    }

    /**
     * Returns the name of the organization
     */
    public function getExtendedNameAttribute()
    {
        return isset($this->attributes['name']) ? $this->attributes['name'] : '';
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

    public static function getSearchableColumns () {
        return (self::getModelName())::$searchable;
    }

    public function scopeActive($query) {
        return $query->where('status', Status::ACTIVE);
    }

    public function scopeInactive($query) {
        return $query->where('status', Status::INACTIVE);
    }

    public function getInactiveMandatesInOrganization() {
        return Mandate::inactiveMandatesInOrganizations($this)->get();
    }

    public function getOriginalValue(string $attribute) {
        if (!isset($this->original[$attribute])) {
            return null;
        }
        return $this->original[$attribute];
    }
}
