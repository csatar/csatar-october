<?php namespace Csatar\Csatar\Models;

use Model;
use ValidationException;

/**
 * Model
 */
class Test extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_tests';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'comment',
        'sort_order',
    ];

    /**
     * Relations
     */

    public $belongsToMany = [
        'scouts' => [
            '\Csatar\Csatar\Models\Scout',
            'table' => 'csatar_csatar_scouts_tests',
            'pivot' => ['date', 'location'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutTestPivot',
        ]
    ];

    public function beforeValidate()
    {
        if ($this->sort_order == null) {
            throw new ValidationException(['sort_order' => 'Sort order is required']);
        }
    }
}
