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

    function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->setValidationAttributeNames([
            'mandate_type' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.mandateType')),
            'mandate_model' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.organizationTypeModelName')),
            'scout' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.scout')),
            'start_date' => e(trans('csatar.csatar::lang.plugin.admin.mandateType.startDate')),
        ]);
        // modify the mandate_model relation type, in order to the mandate_model relation to be set when creating a new mandate from backend
        $this->belongsTo['mandate_model'] = '\Csatar\Csatar\Models\\' . (Input::get('Association') !== null ?
            'Association' :
            (Input::get('District') !== null ?
                'District' :
                (Input::get('Team') !== null ?
                   'Team' :
                    (Input::get('Troop') !== null ?
                       'Troop' :
                        (Input::get('Patrol') !== null ?
                            'Patrol' :
                            'OrganizationBase')))));
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
        $data['mandate_model_id'] = isset($data['mandate_model']) ? $data['mandate_model'] : null;
        $data['scout_id'] = isset($data['scout']) ? $data['scout'] : null;

        // check that this mandate doesn't overlap with an already existing one
        $this->validateWithExistingMandates($data);
    }

    public function initFromForm($record)
    {
        // set different values depending on if we are on a Scout screen or on an Organization screen
        $modelName = $record::getOrganizationTypeModelName();
        if ($modelName == '\Csatar\Csatar\Models\Scout') {
            $this->scout = $record;
        }
        else {
            $this->mandate_model = $record;
            $this->mandate_model_type = $modelName;
            $this->mandate_model_name = $record->extendedName;
        }
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

    private function validateWithExistingMandates($data) {
        if ($data['mandate_type_id'] == null || $data['mandate_model_id'] == null || $data['start_date'] == null) {
            return;
        }

        $mandateType = MandateType::find($data['mandate_type_id']);
        $organizationUnit = ($mandateType->organization_type_model_name)::find($data['mandate_model_id']);
        $startDate = new DateTime($data['start_date']);
        $endDate = isset($data['end_date']) ? new DateTime($data['end_date']) : null;
        $scoutId = array_key_exists('scout_id', $data) ? ($data['scout_id']) : null;
        $mandates = Mandate::where('mandate_type_id', $data['mandate_type_id'])->where('mandate_model_id', $data['mandate_model_id'])->get();
        
        foreach ($mandates as $mandate) {
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
        // from the Scout screens: populate the Mandate Models dropdown on the basis of Mandate Type if the mandate_type is set and the team id is set
        if ($this->scout || $this->scout_id) {
            $team_id = $this->scout->team_id ?? Input::get('data')['team'];
            if (isset($fields->mandate_model)) {
                $fields->mandate_model->options = $this->mandate_type &&
                    (!empty($team_id) ||
                        $this->mandate_type->organization_type_model_name == '\Csatar\Csatar\Models\Association') ?
                        ($this->mandate_type->organization_type_model_name)::getAllByAssociationId($this->mandate_type->association_id, $team_id) :
                        [];
            }

            // set the scout and make the field read only
            $fields->scout->options = [$this->scout_id => $this->scout->name];
            $fields->scout->value = $this->scout_id;
            $fields->scout->readOnly = 1;
        }

        // from the Organization screens: populate the Scouts dropdown
        if ($this->mandate_model_id || $this->mandate_model_type || $this->mandate_model) {
            $this->mandate_model_type = !$this->mandate_model_type ? $this->belongsTo['mandate_model'] : $this->mandate_model_type;
            $this->mandate_model = !$this->mandate_model ? ($this->mandate_model_type)::find($this->mandate_model_id) : $this->mandate_model;
            $scouts = Scout::where('is_active', true)->organization($this->mandate_model_type, $this->mandate_model_id)->get();
            $options = [];
            foreach ($scouts as $item) {
                $options[$item->id] = $item->name;
            }
            asort($options);
            $fields->scout->options = $options;

            // set the mandate model and make the field read only
            $fields->mandate_model->options = $this->mandate_model ? [$this->mandate_model_id => $this->mandate_model->extendedName] : [];
            $fields->mandate_model->value = $this->mandate_model_id;
            $fields->mandate_model->readOnly = 1;
        }
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

    public function getMandateModelOptions()
    {
        return [];
    }

    public function getScoutOptions()
    {
        return [];
    }

    /**
     * Scope a query to only include mandates of a given type.
     */
    public function scopeMandateModelType($query, $model = null)
    {
        return $model ? $query->where('mandate_model_type', $model::getOrganizationTypeModelName()) : $query->whereNull('id');
    }
}
