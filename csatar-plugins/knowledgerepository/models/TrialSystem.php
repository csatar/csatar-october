<?php namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class TrialSystem extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_trial_systems';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'trialSystemCategory' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystemCategory',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.knowledgeRepository.trialSystems.trialSystemCategory',
        ],
        'trialSystemTopic' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystemTopic',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.knowledgeRepository.trialSystems.trialSystemTopic',
        ],
        'trialSystemSubTopic' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystemSubTopic',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.knowledgeRepository.trialSystems.trialSystemSubTopic',
        ],
        'trialSystemType' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystemType',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.knowledgeRepository.trialSystems.trialSystemType',
        ],
        'trialSystemTrialType' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystemTrialType',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.knowledgeRepository.trialSystems.trialSystemTrialType',
        ],
        'ageGroup' => [
            '\Csatar\Csatar\Models\AgeGroup',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.knowledgeRepository.trialSystems.ageGroup',
        ],
    ];
}
