<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class TrainingQualification extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_training_qualifications';

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
            'table' => 'csatar_csatar_scouts_training_qualifications',
            'pivot' => ['date', 'location', 'qualification_certificate_number', 'training_id', 'qualification_leader'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutTrainingQualificationPivot',
        ]
    ];

    public function getTrainingIdOptions(){
        return Training::lists('name', 'id');
    }
}
