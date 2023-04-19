<?php
namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class Headcount extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\Sortable;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_headcounts';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'description',
        'sort_order',
        'note',
        'min',
        'max',
    ];

    public $nullable = [
        'sort_order',
        'note',
        'min',
        'max',
    ];

    public $belongsToMany = [
        'methodologies' => [
            '\Csatar\KnowledgeRepository\Models\Methodology',
            'table' => 'csatar_knowledgerepository_headcount_methodology',
            'pivotModel' => '\Csatar\KnowledgeRepository\Models\HeadcountMethodologyPivot',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.methodologies'
        ]
    ];


    public function getNameAttribute()
    {
        return $this->description;
    }
}
