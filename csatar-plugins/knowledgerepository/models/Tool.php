<?php namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class Tool extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_tools';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'name',
        'note',
        'is_approved',
        'approver_csatar_code',
        'proposer_csatar_code'
    ];

    public $nullable = [
        'note',
        'is_approved',
        'approver_csatar_code',
        'proposer_csatar_code'
    ];

    public $belongsTo = [
        'proposer' => [
            '\Csatar\Csatar\Models\Scout',
            'key' => 'proposer_csatar_code',
            'otherKey' => 'ecset_code',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.game.uploader',
        ],
        'approver' => [
            '\Csatar\Csatar\Models\Scout',
            'key' => 'approver_csatar_code',
            'otherKey' => 'ecset_code',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.game.approver',
        ],
    ];

    public $belongsToMany = [
        'methodologies' => [
            '\Csatar\KnowledgeRepository\Models\Methodology',
            'table' => 'csatar_knowledgerepository_methodology_tool',
            'pivotModel' => '\Csatar\KnowledgeRepository\Models\MethodologyToolPivot',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.methodologies'
        ]
    ];

    public $morphMany = [
        'history' => [
            \Csatar\Csatar\Models\History::class,
            'name' => 'history',
        ],
    ];
}
