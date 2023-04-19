<?php
namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class AccidentRiskLevel extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\Sortable;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];


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


    public function beforeSave()
    {
        if (empty($this->sort_order)) {
            $this->sort_order = static::max('sort_order') + 1;
        }
    }
}
