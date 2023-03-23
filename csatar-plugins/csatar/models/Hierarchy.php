<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class Hierarchy extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\Sortable;

    use \October\Rain\Database\Traits\NestedTree;

    use \Csatar\Csatar\Traits\History;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_hierarchy';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required'
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'parent_id',
        'sort_order',
    ];

    /**
     * Relations
     */
    public $belongsTo = [
        'parent' => [
            '\Csatar\Csatar\Models\Hierarchy',
            'key' => 'parent_id',
            'otherKey' => 'id',
        ],
    ];

    public $hasMany = [
        'children' => [
            '\Csatar\Csatar\Models\Hierarchy', 
            'key' => 'parent_id',
            'order' => 'weight asc',
        ],
        'child_count' => [
            '\Csatar\Csatar\Models\Hierarchy', 
            'key' => 'parent_id',
            'count' => true,
        ],
    ];

    public $morphMany = [
        'history' => [
            \Csatar\Csatar\Models\History::class,
            'name' => 'history',
        ],
    ];
}
