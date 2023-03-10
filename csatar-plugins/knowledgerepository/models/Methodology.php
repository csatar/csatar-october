<?php namespace Csatar\KnowledgeRepository\Models;

use Csatar\Csatar\Models\PermissionBasedAccess;
use Csatar\Csatar\Models\Scout;
use Model;
use Lang;

/**
 * Model
 */
class Methodology extends PermissionBasedAccess
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_methodologies';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public $fillable = [
        'association_id',
        'name',
        'description',
        'timeframe_id',
        'methodology_type_id',
        'link',
        'other_tools',
        'uploader_csatar_code',
        'approver_csatar_code',
        'note',
        'sort_order',
        'version',
        'created_at'
    ];

    public $nullable = [
        'name',
        'description',
        'timeframe_id',
        'methodology_type_id',
        'link',
        'other_tools',
        'uploader_csatar_code',
        'approver_csatar_code',
        'approved_at',
        'note',
        'sort_order',
        'version',
        'created_at'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'timeframe' => [
            '\Csatar\KnowledgeRepository\Models\Duration',
            'key' => 'timeframe_id',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.duration'
        ],
        'methodologytype' => [
            '\Csatar\KnowledgeRepository\Models\MethodologyType',
            'key' => 'methodology_type_id',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.methodologyType'
        ],
        'uploaderscout' => [
            '\Csatar\Csatar\Models\Scout',
            'key' => 'uploader_csatar_code',
            'otherKey' => 'ecset_code',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.general.proposerCsatarCode'
        ],
        'approverscout' => [
            '\Csatar\Csatar\Models\Scout',
            'key' => 'approver_csatar_code',
            'otherKey' => 'ecset_code',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.general.approverCsatarCode'
        ],
        'association' => [
            '\Csatar\Csatar\Models\Association',
            'formBuilder' => [
                'requiredBeforeRender' => true,
            ],
        ]
    ];

    public $belongsToMany = [
        'headcounts' => [
            '\Csatar\KnowledgeRepository\Models\Headcount',
            'table' => 'csatar_knowledgerepository_headcount_methodology',
            'pivotModel' => '\Csatar\KnowledgeRepository\Models\HeadcountMethodologyPivot',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.headCounts'
        ],
        'tools' => [
            '\Csatar\KnowledgeRepository\Models\Tool',
            'table' => 'csatar_knowledgerepository_methodology_tool',
            'pivotModel' => '\Csatar\KnowledgeRepository\Models\MethodologyToolPivot',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.tools'
        ],
        'agegroups' => [
            '\Csatar\Csatar\Models\AgeGroup',
            'table' => 'csatar_knowledgerepository_age_group_methodology',
            'pivotModel' => '\Csatar\KnowledgeRepository\Models\AgeGroupMethodologyPivot',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.ageGroup'
        ],
        'locations' => [
            '\Csatar\KnowledgeRepository\Models\Location',
            'table' => 'csatar_knowledgerepository_location_methodology',
            'pivotModel' => '\Csatar\KnowledgeRepository\Models\LocationMethodologyPivot',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.locations'
        ],
    ];

    public $attachMany = [
        'attachements' => ['System\Models\File'],
    ];

    public static function filterAgeGroupByAssociation($query, $related)
    {
        if (!isset($related->association_id)) {
            return $query->where('id', 0);
        }
        return $query->where('association_id', $related->association_id);
    }

    public function getAssociationId()
    {
        return $this->association_id;
    }

    public function getAssociation()
    {
        return $this->association ?? null;
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.methodology');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function getUploaderScoutOptions() {
        if (empty($this->uploader_csatar_code)) {
            return [];
        }
        return Scout::where('ecset_code', $this->uploader_csatar_code)->get()->pluck('name', 'ecset_code');
    }

    public function getApproverScoutOptions() {
        if (empty($this->approver_csatar_code)) {
            return [];
        }
        return Scout::where('ecset_code', $this->approver_csatar_code)->get()->pluck('name', 'ecset_code');
    }
}
