<?php namespace Csatar\Csatar\Models;

use DateTime;
use Lang;
use ValidationException;
use October\Rain\Database\Pivot;

/**
 * Pivot Model
 */
class ScoutTrainingQualificationPivot extends Pivot
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_scouts_training_qualifications';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'date' => 'required',
        'location' => 'required',
        'qualification_certificate_number' => 'required',
        'qualification' => 'required',
        'qualification_leader' => 'required',
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate() {
        // check that the date is not in the future
        if (isset($this->date) && (new DateTime($this->date) > new DateTime())) {
            throw new ValidationException(['date' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.dateInTheFuture')]);
        }
    }

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'date',
        'location',
        'qualification_certificate_number',
        'qualification',
        'qualification_leader',
    ];
}
