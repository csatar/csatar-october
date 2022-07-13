<?php namespace Csatar\Csatar\Models;

use DateTime;
use Lang;
use Model;
use Csatar\Csatar\Models\Scout;

/**
 * Model
 */
class TeamReport extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_team_reports';

    /**
     * @var array Validation rules
     */
    public $rules = [
        //Validation //'team' => 'required',
        'number_of_adult_patrols' => 'required|numeric|min:0',
        'number_of_explorer_patrols' => 'required|numeric|min:0',
        'number_of_scout_patrols' => 'required|numeric|min:0',
        'number_of_cub_scout_patrols' => 'required|numeric|min:0',
        'number_of_mixed_patrols' => 'required|numeric|min:0',
        'scouting_year_report_team_camp' => 'required',
        'scouting_year_report_homesteading' => 'required',
        'scouting_year_report_programs' => 'required',
        'scouting_year_team_applications' => 'required',
        'spiritual_leader_name' => 'required',
        //Validation //'spiritual_leader_religion' => 'required',
        'spiritual_leader_occupation' => 'required',
    ];

    /**
     * Add custom validation
     */    
    public function beforeValidate()
    {
        // check that the team report for this team and this team doesn't already exist
        $this->year = date('n') == 1 ? date('Y') - 1 : date('Y');
        if (TeamReport::where('team_id', $this->team_id)->where('year', $this->year)->where('deleted_at', null)->where('id', '<>', $this->id)->exists()) {
            throw new \ValidationException(['team_id' => Lang::get('csatar.csatar::lang.plugin.component.teamReport.validationExceptions.teamReportAlreadyExists')]);
        }

        // check that the submission date is not in the future
        if (isset($this->submitted_at) && (new DateTime($this->submitted_at) > new DateTime())) {
            throw new \ValidationException(['submitted_at' => Lang::get('csatar.csatar::lang.plugin.admin.teamReport.validationExceptions.dateInTheFuture')]);
        }
        
        // check that the approval date is not in the future
        if (isset($this->approved_at) && (new DateTime($this->approved_at) > new DateTime())) {
            throw new \ValidationException(['approved_at' => Lang::get('csatar.csatar::lang.plugin.admin.teamReport.validationExceptions.dateInTheFuture')]);
        }
        
        // check that the submission date is not after the approval date
        if (isset($this->submitted_at) && isset($this->approved_at) && (new DateTime($this->submitted_at) > new DateTime($this->approved_at))) {
            throw new \ValidationException(['approved_at' => Lang::get('csatar.csatar::lang.plugin.admin.teamReport.validationExceptions.submissionDateAfterApprovalDate')]);
        }
    }

    protected $fillable = [
        'team_id',
        'year',
        'number_of_adult_patrols',
        'number_of_explorer_patrols',
        'number_of_scout_patrols',
        'number_of_cub_scout_patrols',
        'number_of_mixed_patrols',
        'scouting_year_report_team_camp',
        'scouting_year_report_homesteading',
        'scouting_year_report_programs',
        'scouting_year_team_applications',
        'spiritual_leader_name',
        'spiritual_leader_religion_id',
        'spiritual_leader_occupation',
        'team_fee',
        'total_amount',
        'currency_id',
        'submitted_at',
        'approved_at',
    ];
    
    /**
     * Relations
     */
    public $belongsTo = [
        'team' => '\Csatar\Csatar\Models\Team',
        'spiritual_leader_religion' => '\Csatar\Csatar\Models\Religion',
        'currency' => '\Csatar\Csatar\Models\Currency',
    ];

    public $belongsToMany = [
        'scouts' => [
            '\Csatar\Csatar\Models\Scout',
            'table' => 'csatar_csatar_team_reports_scouts',
            'pivot' => ['name', 'legal_relationship_id', 'leadership_qualification_id', 'ecset_code', 'membership_fee'],
            'pivotModel' => '\Csatar\Csatar\Models\TeamReportScoutPivot',
        ],
    ];

    /**
     * Handle the team-currency dependency
     */
    public function filterFields($fields, $context = null)
    {
        // set the currency that corresponds to the selected team
        if (isset($fields->currency)) {
            $fields->currency->value = $this->team ? $this->team->district->association->currency->code : '';
        }
    }

    /**
     * Set additional data
     */
    public function beforeCreate()
    {
        $this->team = Team::find($this->team_id);
        $association = $this->team->district->association;

        // save additional data
        $this->year = date('n') == 1 ? date('Y') - 1 : date('Y');
        $this->team_fee = $association->team_fee;
        $this->total_amount = $this->team_fee;
        $this->currency_id = $association->currency_id;
    }

    public function afterCreate()
    {
        // save the scouts (the pivot data can be saved only after the team report has been created)
        $scouts = Scout::where('team_id', $this->team_id)->where('is_active', true)->get();
        foreach ($scouts as $scout) {
            $leadershipQualification = $scout->leadership_qualifications->sortByDesc(function ($item, $key) {
                return $item['pivot']['date'];
            })->values()->first();
            $membership_fee = $this->team->district->association->legal_relationships->where('id', $scout->legal_relationship_id)->first()->pivot->membership_fee;

            $this->scouts()->attach($scout, [
                'name' => $scout->family_name . ' ' . $scout->given_name,
                'legal_relationship_id' => $scout->legal_relationship_id,
                'leadership_qualification_id' => isset($leadershipQualification) ? $leadershipQualification->id : null,
                'ecset_code' => $scout->ecset_code,
                'membership_fee' => $membership_fee,
            ]);

            $this->total_amount += $membership_fee;
        }
        $this->save();
    }

    /**
     * Determine the team report's status
     */
    public function getStatus()
    {
        if (isset($this->approved_at)) {
            return Lang::get('csatar.csatar::lang.plugin.admin.teamReport.statuses.approved');
        }
        else if (isset($this->submitted_at)) {
            return Lang::get('csatar.csatar::lang.plugin.admin.teamReport.statuses.submitted');
        }
        else {
            return Lang::get('csatar.csatar::lang.plugin.admin.teamReport.statuses.created');
        }
    }
}
