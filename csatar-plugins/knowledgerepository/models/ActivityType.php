<?php
namespace Csatar\KnowledgeRepository\Models;

use Csatar\Csatar\Classes\Constants;
use Input;
use Model;
use Csatar\KnowledgeRepository\Models\WeeklyWorkPlan;

/**
 * Model
 */
class ActivityType extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_activity_types';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'name',
        'model',
        'categories',
    ];

    public $jsonable = [
        'categories',
    ];

    public $belongsToMany = [
        'weeklyWorkPlans' => [
            'Csatar\KnowledgeRepository\Models\WeeklyWorkPlan',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.weeklyWorkPlan.activityTypes',
            'table' => 'csatar_knowledgerepository_weekly_work_plan_activity_type',
            'pivot' => ['programmable_type', 'programmable_id', 'description', 'sort_order', 'duration'],
            'pivotModel' => 'Csatar\KnowledgeRepository\Models\WeeklyWorkPlanActivityTypePivot',
        ],
    ];

    public function filterFields($fields, $context = null) {
        $pivotData = Input::get('activityTypes')['pivot'] ?? null;
        if (isset($pivotData['programmable_id'])
            && (!isset($this->pivot['programmable_id']) || $pivotData['programmable_id'] != $this->pivot['programmable_id'])) {
            $fields->{'pivot[programmable_id]'}->value = $pivotData['programmable_id'];
            $fields->{'pivot[duration]'}->value        = $this->getDurationDefaultValue($pivotData['programmable_id']);
            $fields->{'pivot[description]'}->value     = $this->getDescriptionDefaultValue($pivotData['programmable_id']);
        }

        if (empty($this->model)) {
            $fields->{'pivot[programmable_id]'}->hidden = true;
        } else {
            $fields->{'pivot[programmable_id]'}->required = true;
        }

        if (empty($pivotData['sort_order']) && !isset($this->pivot['programmable_id'])) {
            $fields->{'pivot[sort_order]'}->value = $this->getDefaultSortOrderValue();
        }

        if (!empty($pivotData['sort_order'])) {
            $fields->{'pivot[sort_order]'}->value = $pivotData['sort_order'];
        }

    }

    public function getProgrammableTypeOptions() {
        return [
            $this->model => $this->model,
        ];
    }

    public function getProgrammableIdOptions() {
        $trialSystemIds = $this->getWeeklyWorkPlanTrialSystemIds();
        $ageGroupIds    = [$this->getWeeklyWorkPlanPatrolAgeGroupId(), $this->getMixedAgeGroupIdInAssociation()];

        $shouldFilterByAgeGroup    = $this->model != '\Csatar\KnowledgeRepository\Models\Methodology';
        $shouldFilterByTrialSystem = $this->model == '\Csatar\KnowledgeRepository\Models\Methodology';

        if (empty($this->model)) {
            return [];
        }

        $return = $this->model::whereNotNull('approved_at')
            ->when($shouldFilterByAgeGroup, function ($query) use ($ageGroupIds) {
                $query->whereHas('age_groups', function ($query) use ($ageGroupIds) {
                    $query->whereIn('id', $ageGroupIds);
                });
            })
            ->when(!empty($this->categories), function ($query) {
                foreach ($this->categories as $key => $category) {
                    $query->whereHas($key, function ($query) use ($category) {
                        $query->whereIn('name', $category);
                    });
                }
            })
            ->when($shouldFilterByTrialSystem, function ($query) use ($trialSystemIds) {
                $query->whereHas('trial_systems', function ($query) use ($trialSystemIds) {
                    $query->whereIn('id', $trialSystemIds);
                });
            })
            ->withCount([
                'trial_systems' => function ($query) use ($trialSystemIds) {
                    if (!empty($trialSystemIds)) {
                        $query->whereIn('id', $trialSystemIds);
                    }
                },
            ])
            ->orderBy('trial_systems_count', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->name ?? $item->title ?? $item->id];
            })
            ->prepend(e(trans('csatar.csatar::lang.plugin.admin.general.select')), 'null')
            ->toArray();

        return $return;
    }

    public function getWeeklyWorkPlan() {
        $weeklyWorkPlanId = post('recordKeyValue');
        if (empty($weeklyWorkPlanId)) {
            return;
        }

        return WeeklyWorkPlan::where('id', $weeklyWorkPlanId)
            ->with([
                'newMaterials',
                'oldMaterials',
            ])
            ->first();
    }

    public function getDefaultSortOrderValue() {
        $weeklyWorkPlan = $this->getWeeklyWorkPlan();
        if (empty($weeklyWorkPlan)) {
            return;
        }

        return $weeklyWorkPlan->activityTypes->max('pivot.sort_order') + 1;
;
    }

    public function getWeeklyWorkPlanTrialSystemIds() {
        $weeklyWorkPlan = $this->getWeeklyWorkPlan();
        if (empty($weeklyWorkPlan)) {
            return;
        }

        $ids = [];

        if ($weeklyWorkPlan->newMaterials) {
            $ids[] = $weeklyWorkPlan->newMaterials->pluck('id')->toArray();
        }

        if ($weeklyWorkPlan->oldMaterials) {
            $ids[] = $weeklyWorkPlan->oldMaterials->pluck('id')->toArray();
        }

        return array_flatten($ids);
    }

    public function getWeeklyWorkPlanPatrolAgeGroupId() {
        $weeklyWorkPlan = $this->getWeeklyWorkPlan();
        if (empty($weeklyWorkPlan)) {
            return;
        }

        return $weeklyWorkPlan->patrol->age_group_id;
    }

    public function getMixedAgeGroupIdInAssociation() {
        $weeklyWorkPlan = $this->getWeeklyWorkPlan();
        if (empty($weeklyWorkPlan)) {
            return;
        }

        return $weeklyWorkPlan
            ->patrol
            ->team
            ->district
            ->association
            ->ageGroups
            ->where('name', Constants::MIXED_AGE_GROUP_NAME)
            ->first()
            ->id ?? null;
    }

    public function getWhenAttribute()
    {
        return $this->pivot->getParent($this->pivot->weekly_work_plan_id)->getActivityStartDateTimeBySortOrder($this->pivot->sort_order);
    }

    public function getTypeNameAttribute()
    {
        $activityName = $this->pivot->programmable->name ?? $this->pivot->programmable->title ?? null;
        return $this->name . ($activityName ? ' / ' . $activityName : '');
    }

    public function getHowAttribute()
    {
        return $this->pivot->programmable->description ?? $this->pivot->programmable->text ?? '';
    }

    public function beforeAttach() {
        if (empty($this->sort_order)) {
            $this->sort_order = 22;
        }
    }

    public function getDurationDefaultValue($programmableId) {
        if (empty($this->model)) {
            return null;
        }

        $programmable = $this->model::find($programmableId);

        if (empty($programmable)) {
            return null;
        }

        if (isset($programmable->durations)) {
            $durationsMin = $programmable->durations->min('min');
            $durationsMax = $programmable->durations->max('max');
            return $this->calculateDuration($durationsMin, $durationsMax);
        }

        if (isset($programmable->timeframe)) {
            $durationsMin = $programmable->timeframe->min;
            $durationsMax = $programmable->timeframe->max;
            return $this->calculateDuration($durationsMin, $durationsMax);
        }
    }

    public function calculateDuration($durationsMin, $durationsMax) {
        $duration = (($durationsMin ?? $durationsMax) + ($durationsMax ?? $durationsMin)) / 2;
        return round($duration);
    }

    public function getDescriptionDefaultValue($programmableId) {
        if (empty($this->model)) {
            return null;
        }

        $programmable = $this->model::find($programmableId);

        if (empty($programmable)) {
            return null;
        }

        if (isset($programmable->description)) {
            return $programmable->description;
        }

        if (isset($programmable->text)) {
            return $programmable->text;
        }
    }

}
