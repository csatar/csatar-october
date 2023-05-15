<?php

namespace Csatar\KnowledgeRepository\Models;

use Auth;
use Carbon\Carbon;
use Csatar\Csatar\Classes\Constants;
use Csatar\Csatar\Classes\Enums\Gender;
use Csatar\Csatar\Classes\GoogleCalendar;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\PermissionBasedAccess;
use Csatar\Csatar\Models\Scout;
use Lang;
use Model;
use ValidationException;

/**
 * Model
 */
class OvamtvWorkPlan extends PermissionBasedAccess
{
    use \October\Rain\Database\Traits\Validation;

    use \Csatar\Csatar\Traits\History;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_ovamtv_work_plans';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'start_date' => 'required',
    ];

    public $fillable = [
        'team_id',
        'patrol_id',
        'creator_csatar_code',
        'patrol_name_gender',
        'patrol_leader',
        'deputy_patrol_leaders',
        'patrol_members',
        'troop',
        'age_group_test',
        'start_date',
        'notes',
        'goals',
        'tasks',
    ];

    public $additionalFieldsForPermissionMatrix = [
        'event_calendar',
    ];

    public $nullable = [
        'team_id',
        'patrol_id',
        'creator_csatar_code',
        'patrol_name_gender',
        'patrol_leader',
        'deputy_patrol_leaders',
        'patrol_members',
        'troop',
        'age_group_test',
        'start_date',
        'notes',
        'goals',
        'tasks',
    ];

    public $belongsTo = [
        'team' => ['Csatar\Csatar\Models\Team'],
        'patrol' => ['Csatar\Csatar\Models\Patrol'],
        'creator' => ['Csatar\Csatar\Models\Scout', 'key' => 'creator_csatar_code', 'otherKey' => 'ecset_code']
    ];

    public $belongsToMany = [
        'newMaterial' => [
            'Csatar\KnowledgeRepository\Models\TrialSystem',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.ovamtvWorkPlan.newMaterial',
            'table' => 'csatar_knowledgerepository_ovamtv_work_plan_material',
            'key' => 'ovamtv_work_plan_id',
            'otherKey' => 'new_material_id',
        ],
        'oldMaterial' => [
            'Csatar\KnowledgeRepository\Models\TrialSystem',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.ovamtvWorkPlan.oldMaterial',
            'table' => 'csatar_knowledgerepository_ovamtv_work_plan_material',
            'key' => 'ovamtv_work_plan_id',
            'otherKey' => 'old_material_id',
        ],
    ];

    public $hasMany = [
        'weeklyWorkPlans' => [
            'Csatar\KnowledgeRepository\Models\WeeklyWorkPlan',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.weeklyWorkPlan.weeklyWorkPlans',
        ],
    ];

    public function beforeCreate()
    {
        if (empty($this->creator_csatar_code)) {
            $scout = Auth::user()->scout;

            $this->creator_csatar_code = $scout->ecset_code;
        }
    }

    public function beforeValidate() {
        if (!empty($this->patrol_id) && empty($this->patrol_name_gender)) {
            $this->patrol_name_gender = $this->getPatrolGenderName(Patrol::where('id', $this->patrol_id)->first());
        }

        $this->validateStartDate();
    }

    public function validateStartDate() {
        if (empty($this->start_date) || empty($this->patrol_id)) {
            throw new ValidationException(['start_date' => '$error']);
        }

        $planExists = self::where('patrol_id', $this->patrol_id)
            ->where('id', '<>', $this->id)
            ->where('start_date', $this->start_date)->exists();

        if ($planExists) {
            throw new ValidationException(['start_date' => Lang::get('csatar.knowledgerepository::lang.plugin.admin.ovamtvWorkPlan.ovamtvWorkPlanExistsForPeriod')]);
        }
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

    public function getTroop() {
        return $this->patrol_id ? $this->patrol->troop : null;
    }

    public function getPatrol() {
        return $this->patrol_id ? $this->patrol : null;
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.knowledgerepository::lang.plugin.admin.ovamtvWorkPlan.ovamtvWorkPlan');
    }

    public function filterFields($fields, $context = null) {
        $this->handlePatrolNameGenderField($fields);
        $this->handlePatrolLeaderField($fields);
        $this->handleDeputyPatrolLeadersField($fields);
        $this->handlePatrolMembersField($fields);
        $this->handleTroopField($fields);
        $this->handleAgeGroupTestField($fields);
    }

    public function handlePatrolNameGenderField(&$fields){
        if (empty($fields->patrol_name_gender)) {
            return;
        }

        if (empty($this->patrol_name_gender)) {
            $fields->patrol_name_gender->span   = 'full';
            $fields->patrol_name_gender->hidden = true;
        }

        if (!empty($this->patrol_id) && !empty($this->id)) {
            $fields->patrol->span   = 'full';
            $fields->patrol->hidden = true;
        }

        if (empty($this->id)) {
            $fields->patrol->readOnly = false;
        }
    }

    public function handlePatrolLeaderField(&$fields){
        if (!empty($this->patrol_id)) {
            $fields->patrol_leader->readOnly = true;
            $fields->patrol_leader->value    = $this->getPatrolLeader();
        }
    }

    public function handleDeputyPatrolLeadersField(&$fields){
        if (!empty($this->patrol_id)) {
            $fields->deputy_patrol_leaders->readOnly = true;
            $fields->deputy_patrol_leaders->value    = $this->getDeputyPatrolLeaders();
        }
    }

    public function handlePatrolMembersField(&$fields){
        if (!empty($this->patrol_id) && empty($this->patrol_members)) {
            $fields->patrol_members->value = $this->getDefaultValueForPatrolMembers();
        }
    }

    public function handleTroopField(&$fields){
        if (!empty($this->patrol_id)) {
            $fields->troop->readOnly = true;
            $fields->troop->value    = $this->getDefaultValueForTroop();
        }
    }

    public function handleAgeGroupTestField(&$fields){
        if (!empty($this->patrol_id)) {
            $fields->age_group_test->readOnly = true;
            $fields->age_group_test->value    = $this->getDefaultValueForAgeGroupTest();
        }
    }

    public function getPatrolOptions($patrolId = null) {
        $patrols       = Patrol::where('team_id', $this->team_id)->get();
        $patrolOptions = [];
        foreach ($patrols as $patrol) {
            $patrolGenderName           = $this->getPatrolGenderName($patrol);
            $patrolOptions[$patrol->id] = $patrolGenderName;
        }

        return $patrolOptions;
    }

    public function getPatrolGenderName($patrol) {
        if (!$patrol) {
            return null;
        }

        $gender = $patrol->gender ? Gender::getOptionsWithLabels()[$patrol->gender] ?? null : null;
        return $patrol->extended_name . ($gender ? ' - ' . $gender : '');
    }

    public function getMandates($mandateTypeId) {
        $date = $this->created_at ?? date('Y-m-d');

        return $this->patrol->mandates()
            ->where('mandate_type_id', $mandateTypeId)
            ->where('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->where('end_date', '>=', $date)
                    ->orWhereNull('end_date');
            })
            ->get() ?? null;
    }

    public function getPatrolLeader() {
        $patrolLeaderMandateTypeId = MandateType::where('name', Constants::MANDATE_TYPE_PATROL_LEADER)
            ->where('association_id', $this->getAssociation()->id)
            ->first()->id;

        return $this->getMandates($patrolLeaderMandateTypeId)->first()->scout->full_name ?? null;
    }

    public function getDeputyPatrolLeaders() {
        $deputyPatrolLeaderMandateTypeId = MandateType::where('name', Constants::MANDATE_TYPE_DEPUTY_PATROL_LEADER)
            ->where('association_id', $this->getAssociation()->id)
            ->first()->id;

        $mandates            = $this->getMandates($deputyPatrolLeaderMandateTypeId);
        $deputyPatrolLeaders = [];

        foreach ($mandates as $mandate) {
            $deputyPatrolLeaders[] = $mandate->scout->full_name;
        }

        return implode(', ', $deputyPatrolLeaders);
    }

    public function getDefaultValueForPatrolMembers() {
        $scouts = $this->patrol->scouts ?? null;
        if (empty($scouts)) {
            return null;
        }

        $scoutsList = [];

        foreach ($scouts as $scout) {
            $scoutsList[] = $scout->full_name;
        }

        return implode(", \n", $scoutsList);
    }

    public function getDefaultValueForTroop() {
        if (empty($this->patrol->troop)) {
            return null;
        }

        return $this->patrol->troop->extended_name;
    }

    public function getDefaultValueForAgeGroupTest() {
        $ageGroupTest = '';
        if (!empty($this->patrol->age_group)) {
            $ageGroupTest .= $this->patrol->age_group->name;
        }

        if (!empty($this->patrol->trial_system_trial_type)) {
            $ageGroupTest .= ' - ' . $this->patrol->trial_system_trial_type->name;
        }

        return $ageGroupTest;
    }

    public function getStartDateOptions() {
        // if current month is september or later, start year is current year, else start year is last year
        $scoutYearStart = date('m') >= 9 ? date('Y') . '-09-01' : date('Y', strtotime('-1 year')) . '-09-01';

        $startDateOptions = [];
        // array start date options every second month from september to august as key, value month and next month name
        for ($i = 0; $i < 12; $i += 2) {
            $key   = date('Y-m-d', strtotime($scoutYearStart . ' +' . $i . ' month'));
            $value = date('F', strtotime($scoutYearStart . ' +' . $i . ' month')) . date('F', strtotime($scoutYearStart . ' +' . ($i + 1) . ' month'));
            $value = Lang::get('csatar.knowledgerepository::lang.plugin.admin.ovamtvWorkPlan.periods.' . $value);
            $startDateOptions[$key] = $value;
        }

        return $startDateOptions;
    }

    public function getMonthLabel($month) {
        if (empty($month)) {
            return null;
        }

        $months         = [];
        $scoutYearStart = date('m') >= 9 ? date('Y') . '-09-01' : date('Y', strtotime('-1 year')) . '-09-01';
        for ($i = 0; $i < 12; $i += 2) {
            $key          = date('m', strtotime($scoutYearStart . ' +' . $i . ' month'));
            $value        = date('F', strtotime($scoutYearStart . ' +' . $i . ' month')) . date('F', strtotime($scoutYearStart . ' +' . ($i + 1) . ' month'));
            $value        = Lang::get('csatar.knowledgerepository::lang.plugin.admin.ovamtvWorkPlan.periods.' . $value);
            $months[$key] = $value;
        }

        if (isset($months[$month])) {
            return $months[$month];
        }

        return null;

    }

    public function getEventCalendarAttribute() {
        if (empty($this->start_date)) {
            return null;
        }

        $endDate = date('Y-m-d', strtotime($this->start_date . ' +2 month'));

        $events = GoogleCalendar::getEvents($this->getCalnedarIds(), $this->start_date, $endDate)->sortBy('start');

        //format the date in the collection
        $events->transform(function ($item, $key) {
            $item['start'] = $item['start'] ? $this->formatDateTimeFromIso($item['start']) : null;
            $item['end']   = $item['end'] ? $this->formatDateTimeFromIso($item['end']) : null;
            return $item;
        });

        return $events;
    }

    public function formatDateTimeFromIso($dateTimeString) {
        if (empty($dateTimeString)) {
            return null;
        }

        if (strpos($dateTimeString, 'T') !== false) {
            $date = Carbon::createFromFormat('Y-m-d\TH:i:sP', $dateTimeString);
            // Set the timezone to the original timezone
            $date->setTimezone(new \DateTimeZone($date->getTimezone()->getName()));
        } else {
            return $dateTimeString;
        }

        return $date->format('Y-m-d H:i');
    }

    public function getCalnedarIds() {
        $calendarIds = [];
        if (!empty($this->patrol->google_calendar_id)) {
            $calendarIds[] = $this->patrol->google_calendar_id;
        }

        if (!empty($this->patrol->troop->google_calendar_id)) {
            $calendarIds[] = $this->patrol->troop->google_calendar_id;
        }

        if (!empty($this->patrol->team->google_calendar_id)) {
            $calendarIds[] = $this->patrol->troop->team->google_calendar_id;
        }

        if (!empty($this->patrol->team->district->google_calendar_id)) {
            $calendarIds[] = $this->patrol->team->district->google_calendar_id;
        }

        if (!empty($this->patrol->team->district->association->google_calendar_id)) {
            $calendarIds[] = $this->patrol->team->district->association->google_calendar_id;
        }

        return $calendarIds;
    }

    public function getNameAttribute() {
        return date("Y", strtotime($this->start_date)) . ' ' . $this->getMonthLabel(date("m", strtotime($this->start_date)));
    }

}
