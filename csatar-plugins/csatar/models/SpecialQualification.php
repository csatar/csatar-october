<?php
namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\ModelExtended;

/**
 * Model
 */
class SpecialQualification extends ModelExtended
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_special_qualifications';

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
    ];

    /**
     * Relations
     */

    public $belongsToMany = [
        'scouts' => [
            '\Csatar\Csatar\Models\Scout',
            'table' => 'csatar_csatar_scouts_special_qualifications',
            'pivot' => ['date', 'location'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutSpecialQualificationPivot',
        ]
    ];

}
