<?php
namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class AgeGroup extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \October\Rain\Database\Traits\Sortable;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_age_groups';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'association' => \Csatar\Csatar\Models\Association::class
    ];

    public $fillable = [
        'name',
        'association_id'
    ];

    public $belongsToMany = [
        'methodologies' => [
            '\Csatar\KnowledgeRepository\Models\Methodology',
            'table' => 'csatar_knowledgerepository_age_group_methodology',
            'pivotModel' => '\Csatar\KnowledgeRepository\Models\AgeGroupMethodologyPivot',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.methodologies'
        ]
    ];

}
