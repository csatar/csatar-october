<?php namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class Region extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_region';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'name',
        'big_parent_id',
        'mid_parent_id',
        'small_parent_id'
    ];

    public $belongsTo = [
        'big_parent' => [
            '\Csatar\KnowledgeRepository\Models\Region',
            'key' => 'big_parent_id',
            'otherKey' => 'id',
        ],
        'mid_parent' => [
            '\Csatar\KnowledgeRepository\Models\Region',
            'key' => 'mid_parent_id',
            'otherKey' => 'id',
        ],
        'small_parent' => [
            '\Csatar\KnowledgeRepository\Models\Region',
            'key' => 'small_parent_id',
            'otherKey' => 'id',
        ],
    ];

    public $hasMany = [
        'big_children' => [
            '\Csatar\KnowledgeRepository\Models\Region',
            'key' => 'big_parent_id',
        ],
        'mid_children' => [
            '\Csatar\KnowledgeRepository\Models\Region',
            'key' => 'mid_parent_id',
        ],
        'small_children' => [
            '\Csatar\KnowledgeRepository\Models\Region',
            'key' => 'small_parent_id',
        ],
    ];

    public function getExtendedNameAttribute()
    {
        $name = '';

        if (!empty($this->big_parent_id)) {
            $name.= $this->big_parent->name . ' - ';
        }

        if (!empty($this->mid_parent_id)) {
            $name.= $this->mid_parent->name . ' - ';
        }

        if (!empty($this->small_parent_id)) {
            $name.= $this->small_parent->name . ' - ';
        }

        $name.= $this->name;

        return $name;
    }
}
