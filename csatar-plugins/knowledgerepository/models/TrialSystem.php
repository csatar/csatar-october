<?php namespace Csatar\KnowledgeRepository\Models;

use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\PermissionBasedAccess;
use Lang;

/**
 * Model
 */
class TrialSystem extends PermissionBasedAccess
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

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

    public $fillable = [
        'id_string',
        'name',
        'for_patrols',
        'individual',
        'task',
        'obligatory',
        'note',
    ];

    public $belongsTo = [
        'trialSystemCategory' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystemCategory',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystemCategory',
        ],
        'trialSystemTopic' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystemTopic',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystemTopic',
        ],
        'trialSystemSubTopic' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystemSubTopic',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystemSubTopic',
        ],
        'trialSystemType' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystemType',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystemType',
        ],
        'trialSystemTrialType' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystemTrialType',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystemTrialType',
        ],
        'ageGroup' => [
            '\Csatar\Csatar\Models\AgeGroup',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.trialSystem.ageGroup',
            'scope' => [self::class, 'filterAgeGroupByAssociation']
        ],
        'association' => [
            '\Csatar\Csatar\Models\Association',
        ]
    ];


    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystem');
    }

    public function getAssociationId()
    {
        return $this->association_id;
    }

    public function getAssociation()
    {
        return $this->association ?? null;
    }

    public static function filterAgeGroupByAssociation($query, $related)
    {
        if (!isset($related->association_id)) {
            return $query->where('id', 0);
        }
        return $query->where('association_id', $related->association_id);
    }
}
