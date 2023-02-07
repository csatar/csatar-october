<?php namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class AccidentRiskLevel extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_accident_risk_levels';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'name',
        'sort_order',
        'note'
    ];

    public $nullable = [
        'note',
        'sort_order'
    ];

    public function beforeValidate()
    {
        if (empty($this->sort_order))
        {
            $this->sort_order = static::max('sort_order') + 1;
        }
    }
}
