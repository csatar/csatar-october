<?php

namespace Csatar\KnowledgeRepository\Models;

use Csatar\Csatar\Classes\CsatarPivot;

class WeeklyWorkPlanActivityTypePivot extends CsatarPivot
{
    use \Csatar\Csatar\Traits\History;

    use \October\Rain\Database\Traits\Nullable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_weekly_work_plan_activity_type';

    public function filterFields($fields, $context = null) {
        $fields->programmable_type->options = [];
    }
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
        'duration' => 'required|numeric|min:0|max:99',
    ];

    public $morphTo = [
        'programmable' => []
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
