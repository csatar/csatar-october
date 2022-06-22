<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class SpecialTest extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_special_tests';

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
            'table' => 'csatar_csatar_scouts_special_tests',
            'pivot' => ['date', 'location'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutSpecialTestPivot',
        ]
    ];
}
