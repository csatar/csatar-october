<?php 
namespace Csatar\Csatar\Models;

use Csatar\Csatar\Classes\CsatarPivot;

/**
 * Model
 */
class TeamReportAgeGroupPivot extends CsatarPivot
{
    use \October\Rain\Database\Traits\Validation;

    use \Csatar\Csatar\Traits\History;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_age_group_team_report';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'number_of_patrols_in_age_group' => 'required|numeric',
    ];

    public $fillable = [
        'number_of_patrols_in_age_group',
    ];

}
