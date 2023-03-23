<?php namespace Csatar\Csatar\Models;

use DateTime;
use Lang;
use Model;
use Csatar\Csatar\Models\Religion;
use Csatar\Csatar\Models\Scout;

/**
 * Model
 */
class TeamReport extends PermissionBasedAccess
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];

    public $updateScoutsList = false;
    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_team_reports';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'team' => 'required',
    ];

    /**
     * @var bool skipAfterSave
     */
    public $skipAfterSave = false;

    /**
     * Add custom validation
     */
    public function beforeValidate()
    {
        // check that the team report for this team and this team doesn't already exist
        $this->year = date('n') <= 5 ? date('Y') - 1 : date('Y');
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

    public $fillable = [
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
        'extra_fields',
    ];

    protected $jsonable = ['extra_fields'];

    /**
     * Relations
     */
    public $belongsTo = [
        'team' => '\Csatar\Csatar\Models\Team',
        'spiritual_leader_religion' => '\Csatar\Csatar\Models\Religion',
        'currency' => [
            '\Csatar\Csatar\Models\Currency',
            'label' => 'csatar.csatar::lang.plugin.admin.teamReport.currency',
        ],
    ];

    public $belongsToMany = [
        'scouts' => [
            '\Csatar\Csatar\Models\Scout',
            'table' => 'csatar_csatar_team_reports_scouts',
            'pivot' => ['name', 'legal_relationship_id', 'leadership_qualification_id', 'ecset_code', 'membership_fee'],
            'pivotModel' => '\Csatar\Csatar\Models\TeamReportScoutPivot',
            'label' => 'csatar.csatar::lang.plugin.admin.scout.scouts',
        ],
        'ageGroups' => [
            '\Csatar\Csatar\Models\AgeGroup',
            'table' => 'csatar_csatar_age_group_team_report',
            'pivot' => ['number_of_patrols_in_age_group'],
            'pivotModel' => '\Csatar\Csatar\Models\TeamReportAgeGroupPivot',
            'label' => 'csatar.csatar::lang.plugin.admin.ageGroups.ageGroups',
        ],
    ];

    public $morphMany = [
        'history' => [
            \Csatar\Csatar\Models\History::class,
            'name' => 'history',
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

        // set the spiritual leader data
        $this->year = date('n') <= 5 ? date('Y') - 1 : date('Y');
        $lastYearTeamReport = $this::where('team_id', $this->team_id)->where('year', $this->year - 1)->first();
        if (isset($lastYearTeamReport)) {
            $fields->spiritual_leader_name->value = $lastYearTeamReport->spiritual_leader_name;
            $fields->spiritual_leader_religion->value = $lastYearTeamReport->spiritual_leader_religion_id;
            $fields->spiritual_leader_occupation->value = $lastYearTeamReport->spiritual_leader_occupation;
        }

        return $fields;
    }

    /**
     * Set additional data
     */
    public function beforeCreate()
    {
        $this->team = Team::find($this->team_id);
        $association = $this->team->district->association;

        // save additional data
        $this->team_fee = $association->team_fee;
        $this->total_amount = $this->team_fee;
        $this->currency_id = $association->currency_id;
    }

    public function afterSave()
    {
        if ($this->skipAfterSave || $this->submitted_at || $this->approved_at || (!$this->updateScoutsList && !$this->wasRecentlyCreated)) {
            return;
        }
        // save the scouts (the pivot data can be saved only after the team report has been created)
        $scouts = Scout::where('team_id', $this->team_id)->whereNull('inactivated_at')->get();
        $scoutsToSync = [];
        $this->total_amount = $this->team_fee;

        foreach ($scouts as $scout) {
            $leadershipQualification = $scout->leadership_qualifications->sortByDesc(function ($item, $key) {
                return $item['pivot']['date'];
            })->values()->first();

            if($legalRelationShip = $this->team->district->association->legal_relationships->where('id', $scout->legal_relationship_id)->first()) {
                $membership_fee = $legalRelationShip->pivot->membership_fee;
            } else {
                $membership_fee = 0;
            }

            $scoutsToSync[$scout->id] = [
                'name' => $scout->family_name . ' ' . $scout->given_name,
                'legal_relationship_id' => $scout->legal_relationship_id,
                'leadership_qualification_id' => isset($leadershipQualification) ? $leadershipQualification->id : null,
                'ecset_code' => $scout->ecset_code,
                'membership_fee' => $membership_fee,
            ];

            $this->total_amount += $membership_fee;
        }

        $this->scouts()->sync($scoutsToSync);

        // save the number of patrols in different age groups
        $ageGroups = AgeGroup::where('association_id', $this->team->district->association_id )->get();
        $ageGroupsToSync = [];

        foreach ($ageGroups as $ageGroup) {
            $count = Patrol::active()->where('team_id', $this->team_id)->where('age_group_id', $ageGroup->id)->count();
            if($count>0) {
                $ageGroupsToSync[$ageGroup->id] = ['number_of_patrols_in_age_group' => $count];
            }
        }

        $this->ageGroups()->sync($ageGroupsToSync);
        $this->skipAfterSave = true;
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

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.csatar::lang.plugin.admin.teamReport.teamReport');
    }

    public function getAssociation() {
        return $this->team->district->association ?? null;
    }

    public function getDistrict() {
        return $this->team->district ?? null;
    }

    public function getTeam() {
        return $this->team_id ? $this->team : null;
    }

    // method to get scouts without registration form
    public static function getScoutsWithoutRegistrationForm($scouts): array
    {
        foreach ($scouts as $scout) {
            if(!$scout->registration_form) {
                $scoutsWithoutRegistrationForm[] = [
                    'name' => $scout->name,
                    'ecset_code' => $scout->ecset_code,
                ];
            }
        }
        return $scoutsWithoutRegistrationForm ?? [];
    }
}
