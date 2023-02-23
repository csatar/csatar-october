<?php namespace Csatar\KnowledgeRepository\Models;

use Csatar\Csatar\Models\Scout;
use Model;

/**
 * Model
 */
class Methodology extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_methodologies';

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
            'other_key' => 'ecset_code',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.general.proposerCsatarCode'
        ],
        'approverscout' => [
            '\Csatar\Csatar\Models\Scout',
            'key' => 'approver_csatar_code',
            'other_key' => 'ecset_code',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.general.approverCsatarCode'
        ]
    ];

    public $belongsToMany = [
        'headcounts' => [
            '\Csatar\KnowledgeRepository\Models\HeadCount',
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
        'attachment' => 'System\Models\File'
    ];

    public function beforeCreate()
    {
        $uploaderScout = Scout::find($this->uploader_csatar_code);
        $approverScout = Scout::find($this->approver_csatar_code);

        $this->uploader_csatar_code = $uploaderScout->ecset_code;
        $this->approver_csatar_code = $approverScout->ecset_code;
    }

    public function beforeSave()
    {
        $uploaderScout = Scout::find($this->uploader_csatar_code);
        $approverScout = Scout::find($this->approver_csatar_code);

        $this->uploader_csatar_code = $uploaderScout->ecset_code;
        $this->approver_csatar_code = $approverScout->ecset_code;
    }


}
