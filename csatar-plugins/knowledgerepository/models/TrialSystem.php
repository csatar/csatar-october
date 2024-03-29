<?php
namespace Csatar\KnowledgeRepository\Models;

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
        'effective_knowledge',
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

    public static function getOrganizationTypeModelNameUserFriendly($lang = null)
    {
        return Lang::get('csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystem', [], $lang);
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
            return $query;
        }

        return $query->where('association_id', $related->association_id);
    }

    public function getOEFKAttribute()
    {
        $oefk = [];
        if ($this->for_patrols) {
            $oefk[] = 'Ő';
        }

        if ($this->individual) {
            $oefk[] = 'E';
        }

        if ($this->task) {
            $oefk[] = 'F';
        }

        if ($this->obligatory) {
            $oefk[] = 'K';
        }

        return implode('-', $oefk);
    }

    public function getOEFKTooltipAttribute() {
        $oefk = [];
        if ($this->for_patrols) {
            $oefk[] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.trialSystem.forPatrols');
        }

        if ($this->individual) {
            $oefk[] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.trialSystem.individual');
        }

        if ($this->task) {
            $oefk[] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.trialSystem.task');
        }

        if ($this->obligatory) {
            $oefk[] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.trialSystem.obligatory');
        }

        return implode('-', $oefk);
    }

}
