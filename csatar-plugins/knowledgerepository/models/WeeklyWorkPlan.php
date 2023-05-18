<?php
namespace Csatar\KnowledgeRepository\Models;

use Auth;
use Carbon\Carbon;
use Csatar\Csatar\Classes\Constants;
use Csatar\Csatar\Models\MandateType;
use Csatar\csatar\models\PatrolWorkPlanBase;
use Csatar\KnowledgeRepository\Models\OvamtvWorkPlan;
use Lang;

/**
 * Model
 */
class WeeklyWorkPlan extends PatrolWorkPlanBase
{
    use \October\Rain\Database\Traits\Validation;

    use \Csatar\Csatar\Traits\History;

    public const NO_TOOL_REQUIRED = 'nem szükséges kellék';
    public const SPECIAL_AGE_GROUP_ACTIVITIES = [
        'Nyitó szertartás' => [
            'sort_order' => 1,
            'default_duration' => 5,
        ],
        'Mozgós, erőkifejtős játék' => [
            'sort_order' => 2,
        ],
        'Mozgós játék' => [
            'sort_order' => 3,
        ],
        'Kevés mozgást igénylő játék, ügyességi, koncentrációs játék' => [
            'sort_order' => 4,
        ],
        'El nem mozdulós játék' => [
            'sort_order' => 5,
        ],
        'Új anyag' => [
            'sort_order' => 6,
        ],
        'Régi anyag' => [
            'sort_order' => 7,
        ],
        'Mozgós, szórakoztató játék' => [
            'sort_order' => 8,
        ],
        'Záró szertartás' => [
            'sort_order' => 9,
            'default_duration' => 5,
        ]
    ];
    public const DEFAULT_AGE_GROUP_ACTIVITIES = [
        'Nyitó szertartás' => [
            'sort_order' => 1,
            'default_duration' => 5,
        ],
        'Játék' => [
            'sort_order' => 2,
        ],
        'Régi gyakorlat' => [
            'sort_order' => 3,
        ],
        'Új elmélet' => [
            'sort_order' => 4,
        ],
        'Dal' => [
            'sort_order' => 5,
            'default_duration' => 10,
        ],
        'Új gyakorlat' => [
            'sort_order' => 6,
        ],
        'Régi elmélet' => [
            'sort_order' => 7,
        ],
        'Új játék' => [
            'sort_order' => 8,
        ],
        'Záró szertartás' => [
            'sort_order' => 9,
            'default_duration' => 5,
        ]
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_weekly_work_plans';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'patrol' => 'required',
        'start_date_time' => 'required',
        'ovamtvWorkPlan' => 'required',
        'newMaterials' => 'required',
        'oldMaterials' => 'required',
    ];

    public $fillable = [
        'patrol_id',
        'ovamtv_work_plan_id',
        'patrol_name',
        'patrol_leader',
        'deputy_patrol_leaders',
        'start_date_time',
        'advertisement',
        'extra_tools',
        'evaluation',
        'creator_csatar_code',
        'updater_csatar_code',
        'new_material_id',
        'old_material_id',
        'scouts_list',
    ];

    public $additionalFieldsForPermissionMatrix = [
        'tools',
        'new_material_effective_knowledge',
        'old_material_effective_knowledge',
        'attachments',
    ];

    public $belongsTo = [
        'ovamtvWorkPlan' => [
            '\Csatar\KnowledgeRepository\Models\OvamtvWorkPlan',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.weeklyWorkPlan.ovamtvWorkPlan',
            'key' => 'ovamtv_work_plan_id',
        ],
        'patrol' => [
            'Csatar\Csatar\Models\Patrol',
            'formBuilder' => [
                'requiredBeforeRender' => true,
            ],
        ],
        'creator' => ['Csatar\Csatar\Models\Scout', 'key' => 'creator_csatar_code', 'otherKey' => 'ecset_code'],
        'updater' => ['Csatar\Csatar\Models\Scout', 'key' => 'creator_csatar_code', 'otherKey' => 'ecset_code'],
    ];

    public $belongsToMany = [
        'activityTypes' => [
            'Csatar\KnowledgeRepository\Models\ActivityType',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.weeklyWorkPlan.activityTypes',
            'table' => 'csatar_knowledgerepository_weekly_work_plan_activity_type',
            'pivot' => ['programmable_type', 'programmable_id', 'description', 'sort_order', 'duration'],
            'pivotModel' => 'Csatar\KnowledgeRepository\Models\WeeklyWorkPlanActivityTypePivot',
            'renderableOnCreateForm' => false,
        ],
        'spareGames' => [
            'Csatar\KnowledgeRepository\Models\Game',
            'table' => 'csatar_knowledgerepository_weekly_work_plan_spare_game',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.weeklyWorkPlan.spareGames',
            'scope' => 'approved',
        ],
        'scouts' => [
            'Csatar\Csatar\Models\Scout',
            'table' => 'csatar_knowledgerepository_weekly_work_plan_scout',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.weeklyWorkPlan.scouts',
            'scope' => [self::class, 'filterScoutsByPatrolId']
        ],
        'newMaterials' => [
            'Csatar\KnowledgeRepository\Models\TrialSystem',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.weeklyWorkPlan.newMaterial',
            'table' => 'csatar_knowledgerepository_weekly_work_plan_material',
            'key' => 'weekly_work_plan_id',
            'otherKey' => 'new_material_id',
        ],
        'oldMaterials' => [
            'Csatar\KnowledgeRepository\Models\TrialSystem',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.weeklyWorkPlan.oldMaterial',
            'table' => 'csatar_knowledgerepository_weekly_work_plan_material',
            'key' => 'weekly_work_plan_id',
            'otherKey' => 'old_material_id',
        ],
    ];

    public static function filterScoutsByPatrolId($query, $related, $parent)
    {
        $query->where('patrol_id', $related->patrol_id)
            ->orderByRaw("CONCAT(family_name, ' ', given_name) ASC");
    }

    public function beforeCreate()
    {
        if (empty($this->creator_csatar_code)) {
            $scout = Auth::user()->scout;

            $this->creator_csatar_code = $scout->ecset_code;
        }
    }

    public function beforeSave() {
        $scout = Auth::user()->scout;
        if (!empty($scout)) {
            $this->updater_csatar_code = $scout->ecset_code;
        }

        $this->updateScoutsList();
    }

    public function afterCreate() {
        $this->attachDefaultActivities();
    }

    public function filterFields($fields, $context = null) {

        if (empty($fields)) {
            return;
        }

        $this->handlePatrolName($fields);
        $this->handlePatrolLeaderField($fields);
        $this->handleDeputyPatrolLeadersField($fields);
        $this->handleScoutsPivotField($fields);
        $this->handleScoutsListField($fields);
        $this->hideFieldsOnCreate($fields);
        $this->handleStartDateTimeField($fields);
    }

    public function getAssociationId()
    {
        if ($this->patrol) {
            return $this->patrol->team->district->association_id ?? null;
        }

        return null;
    }

    public function getAssociation() {
        return $this->patrol->team->district->association ?? null;
    }

    public function getDistrict() {
        return $this->patrol->team->district ?? null;
    }

    public function getTeam() {
        return $this->patrol->team ? $this->team : null;
    }

    public function getTroop() {
        return $this->patrol ? $this->patrol->troop : null;
    }

    public function getPatrol() {
        return $this->patrol ?? null;
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.knowledgerepository::lang.plugin.admin.weeklyWorkPlan.weeklyWorkPlan');
    }

    public function getActivitiesToAttach() {
        if (empty($this->patrol)) {
            return;
        }

        $specialAgeGroupId = $this->patrol->team->district->association->special_workplan_age_group_id ?? null;

        if ($this->patrol && $specialAgeGroupId && $this->patrol->age_group->id == $specialAgeGroupId) {
            $activitiesToAttach = self::SPECIAL_AGE_GROUP_ACTIVITIES;
        } else {
            $activitiesToAttach = self::DEFAULT_AGE_GROUP_ACTIVITIES;
        }

        return ActivityType::whereIn('name', array_keys($activitiesToAttach))
            ->get()
            ->mapWithKeys(function ($item, $key) use ($activitiesToAttach) {
                return [
                    $item->id => [
                        'sort_order' => $activitiesToAttach[$item->name]['sort_order'],
                        'duration' => $activitiesToAttach[$item->name]['default_duration'] ?? null,
                    ]
                ];
            })
            ->sortBy('sort_order');
    }

    public function attachDefaultActivities() {
        $activitiesToAttach = $this->getActivitiesToAttach();
        $this->activityTypes()->sync($activitiesToAttach);
    }

    public function handlePatrol(&$fields){
        if (empty($this->patrol_name)) {
            $fields->patrol_name->value = $this->patrol->name;
        }
    }

    public function handlePatrolName(&$fields){
        if (empty($this->patrol_name) && isset($fields->patrol_name)) {
            $fields->patrol_name->value = $this->patrol->name;
        }
    }

    public function handlePatrolLeaderField(&$fields){
        if (!empty($this->patrol_id) && isset($fields->patrol_leader)) {
            $fields->patrol_leader->value = $this->getPatrolLeader();
        }
    }

    public function handleDeputyPatrolLeadersField(&$fields){
        if (!empty($this->patrol_id) && isset($fields->deputy_patrol_leaders)) {
            $fields->deputy_patrol_leaders->value = $this->getDeputyPatrolLeaders();
        }
    }

    public function handleScoutsPivotField(&$fields){
        if (!isset($fields->scouts)) {
            return;
        }

        if (isset($fields->scouts->config['formBuilder']['preview']) && $fields->scouts->config['formBuilder']['preview'] === true) {
            $fields->scouts->hidden = true;
            return;
        }

        if ($this->scouts->isNotEmpty()) {
            return;
        }

        if (!empty($this->patrol_id) && !empty($this->id)) {
            $fields->scouts->value = $this->getScoutIds();
        } else {
            $fields->scouts->hidden = true;
        }
    }

    public function handleScoutsListField(&$fields){
        if (!isset($fields->scouts_list)) {
            return;
        }

        if (!isset($fields->scouts_list->config['formBuilder']['preview']) || empty($this->id) || $this->scouts->isEmpty()) {
            $fields->scouts_list->hidden = true;
        }
    }

    public function handleStartDateTimeField(&$fields){
        if (!isset($fields->start_date_time)) {
            return;
        }

        if (empty($this->start_date_time) && !empty($this->patrol_id)) {
            $previousWeeklyWorkPlans = WeeklyWorkPlan::where('patrol_id', $this->patrol_id)
                ->where('id', '!=', $this->id)
                ->orderBy('id', 'desc')
                ->get();

            if ($previousWeeklyWorkPlans->isEmpty()) {
                return;
            }

            // get the most frequent week of the day with hour and minute from start_date_time of previous weekly work plans
            $mostFrequentDayTime = $previousWeeklyWorkPlans->groupBy(function ($item, $key) {
                return Carbon::parse($item->start_date_time)->format('w H:i');
            })->sortByDesc(function ($item, $key) {
                return $item->count();
            })->keys()->first();

            $dayOfWeek = explode(' ', $mostFrequentDayTime)[0] - 1;
            // set week of the day with hour and minute to start_date_time based on current week
            $startDateTime = Carbon::now()->startOfWeek()->addDays($dayOfWeek)->format('Y-m-d') . ' ' . explode(' ', $mostFrequentDayTime)[1];
            // if start_date_time is in the past, add 7 days
            if (Carbon::parse($startDateTime)->isPast()) {
                $startDateTime = Carbon::parse($startDateTime)->addDays(7)->format('Y-m-d H:i');
            }

            $fields->start_date_time->value = $startDateTime ?? Carbon::now()->format('Y-m-d H:i');
        }
    }

    public function hideFieldsOnCreate(&$fields){
        if (empty($this->id)) {
            if (isset($fields->spareGames)) {
                $fields->spareGames->cssClass = 'd-none';
            }

            if (isset($fields->tools)) {
                $fields->tools->cssClass = 'd-none';
            }

            if (isset($fields->extra_tools)) {
                $fields->extra_tools->cssClass = 'd-none';
            }

            if (isset($fields->evaluation)) {
                $fields->evaluation->cssClass = 'd-none';
            }

            if (isset($fields->new_material_effective_knowledge)) {
                $fields->new_material_effective_knowledge->cssClass = 'd-none';
            }

            if (isset($fields->old_material_effective_knowledge)) {
                $fields->old_material_effective_knowledge->cssClass = 'd-none';
            }

            if (isset($fields->attachments)) {
                $fields->attachments->cssClass = 'd-none';
            }

            if (isset($fields->creator_csatar_code)) {
                $fields->creator_csatar_code->cssClass = 'd-none';
            }

            if (isset($fields->updater_csatar_code)) {
                $fields->updater_csatar_code->cssClass = 'd-none';
            }

            if (isset($fields->scouts)) {
                $fields->scouts->cssClass = 'd-none';
            }

            if (isset($fields->scouts_list)) {
                $fields->scouts_list->cssClass = 'd-none';
            }

            if (isset($fields->programNote)) {
                $fields->programNote->cssClass = 'd-none';
            }

            if (isset($fields->_ruler4)) {
                $fields->_ruler4->cssClass = 'd-none';
            }
        }
    }

    public function getScoutIds() {
        return $this->patrol->scouts->pluck('id')->toArray();
    }

    public function getCreator() {
        return $this->creator_csatar_code ? $this->creator : null;
    }

    public function getUpdater() {
        return $this->updater_csatar_code ? $this->updater : null;
    }

    public function getOvamtvWorkPlanOptions() {
        $options = OvamtvWorkPlan::where('patrol_id', $this->patrol_id)
            ->get()
            ->sortBy('start_date')
            ->mapWithKeys(function ($ovamtvWorkPlan) {
                return [
                    $ovamtvWorkPlan->id => $ovamtvWorkPlan->name,
                ];
            })
            ->toArray();

        return $options;
    }

    public function getNewMaterialsOptions() {
        if (empty($this->ovamtv_work_plan_id)) {
            return [];
        }

        $ovamtvWorkPlan = OvamtvWorkPlan::where('id', $this->ovamtv_work_plan_id)->with('newMaterial')->first();

        return $ovamtvWorkPlan->newMaterial->mapWithKeys(function ($material) {
            return [
                $material->id => $material->name,
            ];
        })
        ->prepend(e(trans('csatar.csatar::lang.plugin.admin.general.select')), 'null')
        ->toArray();

    }

    public function getOldMaterialsOptions() {
        if (empty($this->ovamtv_work_plan_id)) {
            return [];
        }

        $ovamtvWorkPlan = OvamtvWorkPlan::where('id', $this->ovamtv_work_plan_id)->with('oldMaterial')->first();

        return $ovamtvWorkPlan->oldMaterial->mapWithKeys(function ($material) {
            return [
                $material->id => $material->name,
            ];
        })
        ->prepend(e(trans('csatar.csatar::lang.plugin.admin.general.select')), 'null')
        ->toArray();

    }

    public function getActivityTypesOptions() {
        return ActivityType::all()->lists('name', 'id');
    }

    public function getActivityStartDateTimeBySortOrder($sortOrder) {
        $previousActivitiesDuration = $this->activityTypes->whereBetween('pivot.sort_order', [0, $sortOrder - 1])
            ->sum('pivot.duration');

        return date('H:i:s', strtotime($this->start_date_time) + ($previousActivitiesDuration * 60));

    }

    public function getToolsAttribute() {
        if (empty($this->activityTypes)) {
            return '';
        }

        $tools = [];

        foreach ($this->activityTypes as $activityType) {
            if (isset($activityType->pivot->programmable->tools)) {
                $toolsArray = $activityType->pivot->programmable->tools->pluck('name')->toArray();
            }

            if (!empty($toolsArray)) {
                $tools[] = $toolsArray;
            }

            if (!empty($activityType->other_tools)) {
                $tools[] = $activityType->other_tools;
            }
        }

        $tools = collect($tools)
            ->flatten()
            ->unique()
            ->map(function ($item, $key) {
                if ($item != self::NO_TOOL_REQUIRED) {
                    return ucfirst($item);
                }
            })
            ->filter(function ($item) {
                return $item != null;
            })
            ->toArray();
        return implode( ', ', $tools);
    }

    public function updateScoutsList() {
        if ($this->scouts->isNotEmpty()) {
            $this->scouts_list = $this->scouts->sortBy('name')->implode("name", ", ");
        }
    }

    public function getNewMaterialEffectiveKnowledgeAttribute() {
        return $this->getEffectiveKnowledgeConcatenated($this->newMaterials);
    }

    public function getOldMaterialEffectiveKnowledgeAttribute() {
        return $this->getEffectiveKnowledgeConcatenated($this->oldMaterials);
    }

    public function getEffectiveKnowledgeConcatenated($trialSystems) {
        $effectiveKnowledge = '';

        foreach ($trialSystems as $trialSystem) {
            $effectiveKnowledge .= '<h6>' . $trialSystem->name . '</h6>';
            $effectiveKnowledge .= $trialSystem->pivot->effective_knowledge . "<br>";
        }

        return $effectiveKnowledge;
    }

    public function getAttachmentsAttribute()
    {
        if (empty($this->activityTypes)) {
            return '';
        }

        $attachments = collect([]);

        foreach ($this->activityTypes as $activityType) {
            $activityAttachments = $activityType->pivot->programmable ? $activityType->pivot->programmable->attachements : collect([]);
            if (!empty($activityAttachments)) {
                $attachments = $attachments->concat($activityAttachments);
            }
        }

        return $attachments->map(function ($item, $key) {
                return [
                    'fileName' => $item->file_name,
                    'path' => $item->getPath()
                ];
            });
    }

}
