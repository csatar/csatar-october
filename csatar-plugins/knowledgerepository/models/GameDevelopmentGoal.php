<?php
namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class GameDevelopmentGoal extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\Sortable;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_game_development_goals';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'name',
        'note',
        'sort_order',
    ];

    public $nullable = [
        'note',
        'sort_order',
    ];

    public function beforeSave()
    {
        if (empty($this->sort_order)) {
            $this->sort_order = static::max('sort_order') + 1;
        }
    }

}
