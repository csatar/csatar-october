<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class Promise extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_promises';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate() {
        // if we don't have all the data for this validation, then return. The 'required' validation rules will be triggered
        if (!isset($this->promises)) {
            return;
        }

        // check that the date is not in the future
        if (isset($this->date) && (new \DateTime($this->date) > new \DateTime())) {
            throw new \ValidationException(['date' => \Lang::get('csatar.csatar::lang.plugin.admin.scout.dateInTheFutureError')]);
        }
    }

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
    ];

    /**
     * Relations
     */

    public $belongsToMany = [
        'scouts' => [
            '\Csatar\Csatar\Models\Scout',
            'table' => 'csatar_csatar_scouts_promises',
            'order' => 'user_id'
        ]
    ];
}
