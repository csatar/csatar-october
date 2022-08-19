<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\MandateType;
use DateTime;
use Input;
use Lang;
use Model;
use ValidationException;

/**
 * Model
 */
class Mandate extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    protected $touches = ['scout'];

    function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->setValidationAttributeNames([
            'mandate_type' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.mandateType')),
            'mandate_model' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.organizationTypeModelName')),
            'scout' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.scout')),
            'start_date' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.startDate')),
        ]);

        // on the BE, when clicking the Mandate Create button: modify the mandate_model relation type, in order to the mandate_model relation to be set when creating a new mandate
        $this->belongsTo['mandate_model'] = Input::get('Association') !== null ?
            Association::getOrganizationTypeModelName() :
            (Input::get('District') !== null ?
                District::getOrganizationTypeModelName() :
                (Input::get('Team') !== null ?
                   Team::getOrganizationTypeModelName() :
                    (Input::get('Troop') !== null ?
                       Troop::getOrganizationTypeModelName() :
                        (Input::get('Patrol') !== null ?
                            Patrol::getOrganizationTypeModelName() :
                            OrganizationBase::getOrganizationTypeModelName()))));

        // on the BE, when changing the Mandate Type on the form, which is shown after the Mandate Create button has been clicked: modify the mandate_model relation type, in order to the mandate_model relation to be set
        if ($this->belongsTo['mandate_model'] == OrganizationBase::getOrganizationTypeModelName()) {
            $mandate = Input::get('Mandate');
            $mandate_type_id = $mandate ? $mandate['mandate_type'] : null;
            $mandate_type = $mandate_type_id ? MandateType::find($mandate_type_id) : null;
            $this->belongsTo['mandate_model'] = $mandate_type ? $mandate_type->organization_type_model_name : OrganizationBase::getOrganizationTypeModelName();
        }
    }

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_mandates';

    /**
     * @var string The model name, from which the items should be listed from on the FE form, when clicking the add relation button.
     */
    public static $relatedModelNameForFormBuilder = '\Csatar\Csatar\Models\MandateType';

    /**
     * @var string The field from the model, in which the selected item's value should be set on the FE form, when clicking Next, after adding the relation.
     */
    public static $relatedFieldForFormBuilder = 'mandate_type';

    /**
     * @var string Are duplicate records be allowed to be added on the FE form, when clicking the add relation button.
     */
    public static $relatedModelAllowDuplicates = true;

    /**
     * @var array Validation rules
     */
    public $rules = [
        'mandate_type' => 'required',
        'mandate_model' => 'required',
        'scout' => 'required',
        'start_date' => 'required',
        'end_date' => 'nullable',
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate()
    {
        // if the validation is called from backend: check that the end date is not after the start date
        if (isset($this->start_date) && isset($this->end_date) && (new DateTime($this->end_date) < new DateTime($this->start_date))) {
            throw new ValidationException(['end_date' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.endDateBeforeStartDate')]);
        }

        // check that this mandate doesn't overlap with an already existing one
        $this->validateWithExistingMandates($this->attributes);
    }

    public function beforeValidateFromForm(&$data)
    {
        // if the validation is called from the form: check that the end date is not after the start date
        if (isset($data) && !empty($data['start_date']) && !empty($data['end_date']) && (new DateTime($data['end_date']) < new DateTime($data['start_date']))) {
            throw new ValidationException(['end_date' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.endDateBeforeStartDate')]);
        }

        // set mandate_type_id and mandate_model_id
        $data['mandate_type_id'] = isset($data['mandate_type']) ? $data['mandate_type'] : null;
        $data['mandate_model'] = Input::get('recordKeyValue');
        $data['mandate_model_id'] = isset($data['mandate_model']) ? $data['mandate_model'] : null;
        $data['scout_id'] = isset($data['scout']) ? $data['scout'] : null;

        // check that this mandate doesn't overlap with an already existing one
        $this->validateWithExistingMandates($data);
    }

    public function initFromForm($record)
    {
        // from the Organization page
        $modelName = $record::getOrganizationTypeModelName();
        $this->mandate_model = $record;
        $this->mandate_model_type = $modelName;
        $this->mandate_model_name = $record->extendedName;
    }

    public function beforeSave()
    {
        // set further mandate model data
        $mandateType = MandateType::find($this->mandate_type_id);
        if (isset($mandateType)) {
            $this->mandate_model_type = $mandateType->organization_type_model_name;

            $organizationUnit = ($mandateType->organization_type_model_name)::find($this->mandate_model_id);
            $this->mandate_model_name = isset($organizationUnit) ? $organizationUnit->extendedName : '';
        }
    }

    public function beforeSaveFromForm(&$data)
    {
        // set further mandate model data
        $mandateType = MandateType::find($data['mandate_type_id']);
        if (isset($mandateType)) {
            $data['mandate_model_type'] = $mandateType->organization_type_model_name;

            $organizationUnit = ($mandateType->organization_type_model_name)::find($data['mandate_model_id']);
            $data['mandate_model_name'] = isset($organizationUnit) ? $organizationUnit->extendedName : '';
        }

        // if the end_date is an empty string, then set it to null
        $data['end_date'] = !empty($data['end_date']) ? $data['end_date'] : null;
    }

    private function validateWithExistingMandates($data)
    {
        if ($data['mandate_type_id'] == null || $data['mandate_model_id'] == null || $data['start_date'] == null) {
            return;
        }

        $id = array_key_exists('id', $data) ? $data['id'] : null;
        $mandateType = MandateType::find($data['mandate_type_id']);
        $organizationUnit = ($mandateType->organization_type_model_name)::find($data['mandate_model_id']);
        $startDate = new DateTime($data['start_date']);
        $endDate = isset($data['end_date']) ? new DateTime($data['end_date']) : null;
        $scoutId = array_key_exists('scout_id', $data) ? ($data['scout_id']) : null;
        $mandates = Mandate::where('mandate_type_id', $data['mandate_type_id'])->where('mandate_model_id', $data['mandate_model_id'])->get();

        foreach ($mandates as $mandate) {
            // if we are editing the mandate: the mandate shouldn't be compared to itself
            if ($id == $mandate->id) {
                continue;
            }

            // check that the date isn't (partially) overlapping with a different assignment for the same period: if the overlapping is not enabled or if it's the same user: overlap if max(start1, start2) < min(end1, end2)
            if (!$mandateType->overlap_enabled || $mandate->scout_id == $scout_id) {
                $mandateStartDate = new DateTime($mandate['start_date']);
                $mandateEndDate = isset($mandate['end_date']) ? new DateTime($mandate['end_date']) : null;

                if (($endDate !== null && $mandateEndDate !== null && max($startDate, $mandateStartDate) < min($endDate, $mandateEndDate)) ||
                    ($endDate == null && max($startDate, $mandateStartDate) < $mandateEndDate) ||
                    ($mandateEndDate == null && max($startDate, $mandateStartDate) < $endDate) ||
                    ($endDate == null && $mandateEndDate == null)) {
                        throw new ValidationException(['start_date' => Lang::get('csatar.csatar::lang.plugin.admin.mandate.overlappingMandateError')]);
                }
            }
        }
    }

    /**
     * Handle the mandate type - mandate model dependencies
     */
    public function filterFields($fields, $context = null)
    {
        $this->mandate_model_type = !$this->mandate_model_type ? $this->belongsTo['mandate_model'] : $this->mandate_model_type;
        $mandate_model_id = null;
        $mandate_model_type = null;
        
        // in case of troops and patrols, allow anyone from the team. In case of other organization units, allow only scouts from that organization unit
        if (!$this->mandate_model_id && !$this->mandate_model) {
            // we are on a create form on the FE
            $inputData = Input::get('data');
            switch ($this->mandate_model_type) {
                case Troop::getOrganizationTypeModelName():
                case Patrol::getOrganizationTypeModelName():
                    $mandate_model_id = $inputData['team'];
                    $mandate_model_type = Team::getOrganizationTypeModelName();
                    break;

                default:
                    break;
            }
        }
        else {
            // we are on an edit form
            if ($this->mandate_model_type == Troop::getOrganizationTypeModelName() || $this->mandate_model_type == Patrol::getOrganizationTypeModelName()) {
                $mandate_model_id = $this->mandate_model ? $this->mandate_model->team_id : null;
                $mandate_model_type = Team::getOrganizationTypeModelName();
            }
            else {
                $mandate_model_id = $this->mandate_model_id;
                $mandate_model_type = $this->mandate_model_type;
            }
        }

        // from the Organization pages: populate the Scouts dropdown
        $scouts = Scout::where('is_active', true)->organization($mandate_model_type, $mandate_model_id)->get();
        $options = [];
        foreach ($scouts as $item) {
            $options[$item->id] = $item->name;
        }
        asort($options);
        $fields->scout->options = $options;
    }

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'scout_id',
        'mandate_type_id',
        'mandate_model_id',
        'mandate_model_type',
        'mandate_model_name',
        'start_date',
        'end_date',
        'comment',
    ];

    /**
     * Relations
     */
    public $belongsTo = [
        'scout' => '\Csatar\Csatar\Models\Scout',
        'mandate_type' => '\Csatar\Csatar\Models\MandateType',
        'mandate_model' => '\Csatar\Csatar\Models\OrganizationBase',
    ];

    public function getMandateModelAttribute()
    {
        if ($this->mandate_model_type == '\Csatar\Csatar\Models\OrganizationBase') {
            $this->mandate_model_type = $this->mandate_type ? $this->mandate_type->organization_type_model_name : null;
            $this->belongsTo['mandate_model'] = $this->mandate_model_type;
        }

        return $this->mandate_model_type ? ($this->mandate_model_type)::find($this->mandate_model_id) : null;
    }

    /**
     * Scope a query to only include mandates of a given type.
     */
    public function scopeMandateModelType($query, $model = null)
    {
        return $model ? $query->where('mandate_model_type', $model::getOrganizationTypeModelName()) : $query->whereNull('id');
    }
}
