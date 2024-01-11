<?php

namespace Csatar\KnowledgeRepository\Models;

use Csatar\Csatar\Classes\CsatarPivot;
use Csatar\KnowledgeRepository\Models\ActivityType;
use Csatar\KnowledgeRepository\Models\WeeklyWorkPlan;

class WeeklyWorkPlanActivityTypePivot extends CsatarPivot
{
    use \Csatar\Csatar\Traits\History;

    use \October\Rain\Database\Traits\Nullable;

    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_weekly_work_plan_activity_type';

    public $otherKey = 'activity_type_id';

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'activity_type_id',
        'programmable_type',
        'programmable_id',
        'description',
        'sort_order',
        'duration',
    ];

    public $nullable = [
        'activity_type_id',
        'programmable_type',
        'programmable_id',
        'description',
        'sort_order',
        'duration',
    ];

    /**
     * @var array Validation rules
     */

    public $rules = [
//        'programmable_id' => 'required_unless:name,' . WeeklyWorkPlan::OPENING_CEREMONY . ',' . WeeklyWorkPlan::CLOSE_CEREMONY, //Temporary removed with CS-647. Search code fore this comment to reverse.
        'duration' => 'required|numeric|min:0|max:99',
    ];

    public $morphTo = [
        'programmable' => []
    ];

    public $customMessages = [
//        'programmable_id.required_unless' => 'csatar.knowledgerepository::lang.plugin.admin.weeklyWorkPlan.programNameRequired', //Temporary removed with CS-647. Search code fore this comment to reverse.
    ];

    public function beforeSave() {
        if (empty($this->sort_order)) {
            $this->sort_order = $this->getParent()->activityTypes->max('pivot.sort_order') + 1;
        }

        if ($this->programmable_id === 'null') {
            $this->programmable_id = null;
        }
    }
}
