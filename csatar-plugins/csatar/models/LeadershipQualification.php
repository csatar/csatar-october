<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class LeadershipQualification extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_leadership_qualifications';

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
        'teamReportScouts' => '\Csatar\Csatar\Models\TeamReportScoutPivot',
        'scouts' => [
            '\Csatar\Csatar\Models\Scout',
            'table' => 'csatar_csatar_scouts_leadership_qualifications',
            'pivot' => ['date', 'location', 'qualification_certificate_number', 'qualification', 'qualification_leader'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutLeadershipQualificationPivot',
        ]
    ];
}
