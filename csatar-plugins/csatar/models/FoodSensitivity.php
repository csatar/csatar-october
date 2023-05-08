<?php
namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\ModelExtended;

/**
 * Model
 */
class FoodSensitivity extends ModelExtended
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_food_sensitivities';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required'
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name'
    ];

    /**
     * Relations
     */

    public $belongsToMany = [
        'scouts' => [
            '\Csatar\Csatar\Models\Scout',
            'table' => 'csatar_csatar_scouts_food_sensitivities',
            'pivot' => ['comment'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutFoodSensitivityPivot',
        ]
    ];

}
