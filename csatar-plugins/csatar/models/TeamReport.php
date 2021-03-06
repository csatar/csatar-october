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
        'team' => 'required',
        'scouting_year_report_team_camp' => 'required',
        'scouting_year_report_homesteading' => 'required',
        'scouting_year_report_programs' => 'required',
        'scouting_year_team_applications' => 'required',
        'spiritual_leader_name' => 'required',
        'spiritual_leader_religion' => 'required',
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
        'ageGroups' => [
            '\Csatar\Csatar\Models\AgeGroup',
            'table' => 'csatar_csatar_age_group_team_report',
            'pivot' => ['number_of_patrols_in_age_group'],
            'label' => 'csatar.csatar::lang.plugin.admin.ageGroups.ageGroups',
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

        // save the number of patrols in different age groups
        $ageGroups = AgeGroup::where('association_id', $this->team->district->association_id )->get();
        foreach ($ageGroups as $ageGroup) {
            $count = Patrol::where('team_id', $this->team_id)->where('age_group_id', $ageGroup->id)->count();
            if($count>0) {
                $this->ageGroups()->attach(
                    $ageGroup,
                    ['number_of_patrols_in_age_group' => $count]
                );
            }
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

    public function getAgeGroupsOptions(){
        $team_id = $this->team_id ?? \Input::get('data.team');
        $attachedIds = [];
        if(!empty($this->team_id)) {
            $attachedIds = $this->ageGroups->pluck('id');
        }
        $team = Team::find($team_id);
        if(!empty($team_id)){
            $ageGroups = AgeGroup::select(
                \DB::raw("CONCAT(NAME, IF(note, CONCAT(' (',note, ')'), '')) AS name"),'id')
                ->where('association_id', $team->district->association->id)
                ->whereNotIn('id', $attachedIds)
                ->orderBy('sort_order')
                ->lists('name', 'id')
            ;
            return $ageGroups;
        }
        return [];
    }
}
