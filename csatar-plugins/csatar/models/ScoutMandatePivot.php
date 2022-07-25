<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\Mandate;
use DateTime;
use Input;
use Lang;
use ValidationException;
use October\Rain\Database\Pivot;

/**
 * Pivot Model
 */
class ScoutMandatePivot extends Pivot
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_scouts_mandates';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'mandate_model_id' => 'required',
        'start_date' => 'required',
        'end_date' => 'nullable',
    ];

    public $attributeNames = [];

    function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->attributeNames['mandate_model_id'] = e(trans('csatar.csatar::lang.plugin.admin.mandate.organizationTypeModelName'));
        $this->attributeNames['start_date'] = e(trans('csatar.csatar::lang.plugin.admin.mandate.startDate'));
    }

    /**
     * Add custom validation
     */
    public function beforeValidate()
    {
        // if the validation is called from backend: check that the end date is not after the start date
        if (isset($this->start_date) && isset($this->end_date) && (new DateTime($this->end_date) < new DateTime($this->start_date))) {
            throw new ValidationException(['end_date' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.endDateBeforeStartDate')]);
        }
    }

    public function beforeValidateFromForm($pivotData)
    {
        // if the validation is called from the form: check that the end date is not after the start date
        if (isset($pivotData) && !empty($pivotData['start_date']) && !empty($pivotData['end_date']) && (new DateTime($pivotData['end_date']) < new DateTime($pivotData['start_date']))) {
            throw new ValidationException(['end_date' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.endDateBeforeStartDate')]);
        }
    }

    public function beforeSaveFromForm(&$pivotData)
    {
        $pivotData['end_date'] = !empty($pivotData['end_date']) ? $pivotData['end_date'] : null;
    }

    /**
     * @var array Fillable values
     */
    public $fillable = [
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
    public $morphTo = [
        'mandate_model' => [],
    ];

    function getMandateModelIdOptions()
    {
        // when assigning a mandate to the scout, the 'foreign_id' is set; when updating an assigned mandate, then the 'manage_id' is set
        $mandate_id = Input::get('foreign_id') ?? Input::get('manage_id');
        $mandate = ($this->parent->belongsToMany[Input::get('_relation_field')][0])::find($mandate_id);
        return ($mandate->organization_type_model_name)::getAllByAssociationId($mandate->association_id, $this->parent->team_id);
    }
}
