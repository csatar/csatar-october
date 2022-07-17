<?php namespace Csatar\Csatar\Models;

use DateTime;
use Lang;
use ValidationException;
use October\Rain\Database\Pivot;

/**
 * Pivot Model
 */
class ScoutTestPivot extends Pivot
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_scouts_tests';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'date' => 'required',
        'location' => 'required',
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate()
    {
        // set the attribute names
        $this->setValidationAttributeNames([
            'date' => Lang::get('csatar.csatar::lang.plugin.admin.general.date'),
            'location' => Lang::get('csatar.csatar::lang.plugin.admin.general.location'),
        ]);

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
    ];
}
