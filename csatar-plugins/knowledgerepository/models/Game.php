<?php namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class Game extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_games';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'uploader' => [
            '\Csatar\Csatar\Models\Scout',
            'key' => 'uploader_csatar_code',
            'otherKey' => 'ecset_code',
        ],
        'approver' => [
            '\Csatar\Csatar\Models\Scout',
            'key' => 'uploader_csatar_code',
            'otherKey' => 'ecset_code',
        ]
    ];

    public $belongsToMany = [
        'game_development_goals' => [
            '\Csatar\KnowledgeRepository\Models\GameDevelopmentGoal',
            'table' => 'csatar_knowledgerepository_game_development_goal_game',
            'label' => 'csatar.csatar::lang.plugin.admin.menu.knowledgeRepositoryParameters.gameDevelopmentGoals',
        ],
        'tools' => [
            '\Csatar\KnowledgeRepository\Models\Tool',
            'table' => 'csatar_knowledgerepository_game_tool',
            'label' => 'csatar.csatar::lang.plugin.admin.menu.knowledgeRepositoryParameters.tools',
        ],
        'headcounts' => [
            '\Csatar\KnowledgeRepository\Models\Headcount',
            'table' => 'csatar_knowledgerepository_game_headcount',
            'label' => 'csatar.csatar::lang.plugin.admin.menu.knowledgeRepositoryParameters.headCounts',
        ],
        'durations' => [
            '\Csatar\KnowledgeRepository\Models\Duration',
            'table' => 'csatar_knowledgerepository_game_duration',
            'label' => 'csatar.csatar::lang.plugin.admin.menu.knowledgeRepositoryParameters.durations',
        ],
        'age_groups' => [
            '\Csatar\Csatar\Models\AgeGroup',
            'table' => 'csatar_knowledgerepository_age_group_game',
            'label' => 'csatar.csatar::lang.plugin.admin.ageGroups.ageGroups',
        ],
        'locations' => [
            '\Csatar\KnowledgeRepository\Models\Location',
            'table' => 'csatar_knowledgerepository_game_location',
            'label' => 'csatar.csatar::lang.plugin.admin.menu.knowledgeRepositoryParameters.locations',
        ],
        'game_types' => [
            '\Csatar\KnowledgeRepository\Models\GameType',
            'table' => 'csatar_knowledgerepository_game_game_type',
            'label' => 'csatar.csatar::lang.plugin.admin.menu.knowledgeRepositoryParameters.gameTypes',
        ],
    ];

    public $attachMany = [
        'attachements' => ['System\Models\File'],
    ];
}
