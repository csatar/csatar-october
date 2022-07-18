<?php namespace Csatar\Csatar\Models;

use DateTime;
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
        'start_date' => 'required',
        'end_date' => 'required',
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate()
    {
        // set the attribute names
        $this->setValidationAttributeNames([
            'start_date' => Lang::get('csatar.csatar::lang.plugin.admin.mandate.startDate'),
            'end_date' => Lang::get('csatar.csatar::lang.plugin.admin.mandate.endDate'),
        ]);

        // check that end date is not after the start date
        if (isset($this->start_date) && isset($this->end_date) && (new DateTime($this->end_date) < new DateTime($this->start_date))) {
            throw new ValidationException(['end_date' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.endDateBeforeStartDate')]);
        }
    }

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'start_date',
        'end_date',
        'comment',
    ];

    public function beforeCreate()
    {
        $this->mandate_model_id = 0;
    }
}
