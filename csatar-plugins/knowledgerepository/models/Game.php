<?php
namespace Csatar\KnowledgeRepository\Models;

use Auth;
use Model;
use Csatar\Csatar\Models\PermissionBasedAccess;
use \Csatar\Csatar\Models\Scout;
use Lang;
/**
 * Model
 */
class Game extends PermissionBasedAccess
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\Nullable;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_games';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $fillable = [
        'association_id',
        'name',
        'note',
        'other_tools',
        'link',
        'description',
        'uploader_csatar_code',
        'approver_csatar_code',
        'approved_at',
    ];

    public $additionalFieldsForPermissionMatrix = [
        'created_at',
    ];

    public $nullable = [
        'description',
        'uploader_csatar_code',
        'approver_csatar_code',
        'note',
        'other_tools',
        'link',
        'description',
        'created_at',
        'updated_at',
        'approved_at',
    ];

    public $belongsTo = [
        'uploader' => [
            '\Csatar\Csatar\Models\Scout',
            'key' => 'uploader_csatar_code',
            'otherKey' => 'ecset_code',
            'keyType' => 'string',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.game.uploader',
        ],
        'approver' => [
            '\Csatar\Csatar\Models\Scout',
            'key' => 'approver_csatar_code',
            'otherKey' => 'ecset_code',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.game.approver',
            'ignoreInPermissionsMatrix' => true,
        ],
        'association' => [
            '\Csatar\Csatar\Models\Association',
            'formBuilder' => [
                'requiredBeforeRender' => true,
            ],
        ]
    ];

    public $belongsToMany = [
        'game_development_goals' => [
            '\Csatar\KnowledgeRepository\Models\GameDevelopmentGoal',
            'table' => 'csatar_knowledgerepository_game_development_goal_game',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.gameDevelopmentGoals',
        ],
        'tools' => [
            '\Csatar\KnowledgeRepository\Models\Tool',
            'table' => 'csatar_knowledgerepository_game_tool',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.tools',
        ],
        'headcounts' => [
            '\Csatar\KnowledgeRepository\Models\Headcount',
            'table' => 'csatar_knowledgerepository_game_headcount',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.headCounts',
        ],
        'durations' => [
            '\Csatar\KnowledgeRepository\Models\Duration',
            'table' => 'csatar_knowledgerepository_game_duration',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.durations',
        ],
        'age_groups' => [
            '\Csatar\Csatar\Models\AgeGroup',
            'table' => 'csatar_knowledgerepository_age_group_game',
            'label' => 'csatar.csatar::lang.plugin.admin.ageGroups.ageGroups',
            'scope' => [self::class, 'filterAgeGroupByAssociation']
        ],
        'locations' => [
            '\Csatar\KnowledgeRepository\Models\Location',
            'table' => 'csatar_knowledgerepository_game_location',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.locations',
        ],
        'game_types' => [
            '\Csatar\KnowledgeRepository\Models\GameType',
            'table' => 'csatar_knowledgerepository_game_game_type',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.menu.knowledgeRepositoryParameters.gameTypes',
        ],
        'trial_systems' => [
            '\Csatar\KnowledgeRepository\Models\TrialSystem',
            'table' => 'csatar_knowledgerepository_game_trial_system',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystems',
        ],
    ];

    public $attachMany = [
        'attachements' => ['System\Models\File'],
    ];


    public static function filterAgeGroupByAssociation($query, $related)
    {
        if (!isset($related->association_id)) {
            return $query;
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
        return Lang::get('csatar.knowledgerepository::lang.plugin.admin.game.game');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopeWaitingForApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function beforeCreate()
    {
        if (empty($this->uploader_csatar_code)) {
            $scout = Auth::user()->scout;

            $this->uploader_csatar_code = $scout->ecset_code;
        }
    }

    public function getUploaderScout() {
        return $this->uploader_csatar_code ? $this->uploader : null;
    }

    public function getApproverScout() {
        return $this->approver_csatar_code ? $this->approver : null;
    }
}
