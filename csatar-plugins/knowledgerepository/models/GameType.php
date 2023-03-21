<?php namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class GameType extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\Sortable;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_game_types';

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
}
