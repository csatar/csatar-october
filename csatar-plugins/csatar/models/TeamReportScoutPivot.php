<?php namespace Csatar\Csatar\Models;

use October\Rain\Database\Pivot;

/**
 * Pivot Model
 */
class TeamReportScoutPivot extends Pivot
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_team_reports_scouts';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'legal_relationship_id' => 'required',
        'membership_fee' => 'required|digits_between:1,20',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'legal_relationship_id',
        'leadership_qualification_id',
        'ecset_code',
        'membership_fee',
    ];

    /**
     * Relations
     */
    public $belongsTo = [
        'legal_relationship' => '\Csatar\Csatar\Models\LegalRelationship',
        'leadership_qualification' => '\Csatar\Csatar\Models\LeadershipQualification',
    ];
}
