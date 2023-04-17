<?php namespace Csatar\KnowledgeRepository\Models;

use Csatar\Csatar\Classes\Constants;
use Csatar\Csatar\Models\PermissionBasedAccess;
use Csatar\Csatar\Models\Troop;
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\Mandate;
use Csatar\Csatar\Models\MandateType;
use Lang;

/**
 * Model
 */
class WorkPlan extends PermissionBasedAccess
{
    use \October\Rain\Database\Traits\Validation;

    use \Csatar\Csatar\Traits\History;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_work_plans';

    protected $appends = ['name'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'team' => 'required',
        'year' => 'required',
    ];

    public $fillable = [
        'team_id',
        'year',
        'troops',
        'patrols',
        'frame_story',
        'team_goals',
        'team_notes',
    ];

    public $belongsTo = [
        'team' => [
            '\Csatar\Csatar\\Models\Team',
            'label' => 'csatar.csatar::lang.plugin.admin.team.team',
            'formBuilder' => [
                'requiredBeforeRender' => true,
            ],
        ],
    ];

    public function beforeValidate()
    {
        $existingWorkPlans = self::where('team_id', $this->team_id)->where('year', $this->year)->where('id', '<>', $this->id)->get();

        if ($existingWorkPlans->count() > 0) {
            throw new \ValidationException(['year' => Lang::get('csatar.knowledgerepository::lang.plugin.admin.workPlan.workPlanAlreadyExistsForYear', ['year' => $this->year])]);
        };
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.knowledgerepository::lang.plugin.admin.workPlan.workPlan');
    }

    public function getTeam() {
        return $this->team_id ? $this->team : null;
    }

    public function getAssociation() {
        return $this->team->district->association ?? null;
    }

    public function getTroopsAttribute() {
        return array_get($this->attributes, 'troops') ?? $this->getDefaultValueForTroops();
    }

    public function getPatrolsAttribute() {
        return array_get($this->attributes, 'patrols') ?? $this->getDefaultValueForPatrols();
    }

    public function getNameAttribute() {
        return 'CSMTV ' . $this->year . '-' . ($this->year + 1);
    }

    public function getTeamLeaderAttribute() {
        $teamLeaderMandateTypeId = MandateType::where('name', Constants::MANDATE_TYPE_TEAM_LEADER)
            ->where('association_id', $this->getAssociation()->id)
            ->first()->id;

        return $this->getMandates($teamLeaderMandateTypeId)->first()->scout->full_name ?? null;
    }

    public function getDeputyTeamLeadersAttribute() {
        $deputyTeamLeaderMandateTypeId = MandateType::where('name', Constants::MANDATE_TYPE_DEPUTY_TEAM_LEADER)
            ->where('association_id', $this->getAssociation()->id)
            ->first()->id;

        $mandates = $this->getMandates($deputyTeamLeaderMandateTypeId);
        $deputyTeamLeaders = [];

        foreach ($mandates as $mandate) {
            $deputyTeamLeaders[] = $mandate->scout->full_name;
        }
        return implode(', ', $deputyTeamLeaders);
    }

    public function getMandates($mandateTypeId) {
        $date = $this->created_at ?? date('Y-m-d');

        return $this->team->mandates()
            ->where('mandate_type_id', $mandateTypeId)
            ->where('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->where('end_date', '>=', $date)
                    ->orWhereNull('end_date');
            })
            ->get() ?? null;
    }

    public function getDefaultValueForTroops() {

        $teamId = $this->team_id ?? post('team');

        if (!empty($this->troops)) {
            return $this->troops;
        }

        $troops = Troop::where('team_id', $teamId)->withCount('patrolsActive')->get();
        if (empty($troops)) {
            return null;
        }

        $troopsHtml = '<ul>';

        foreach ($troops as $troop) {
            $troopsHtml .= '<li>';
            $troopsHtml .= $troop->extended_name;
            $troopsHtml .= $troop->patrols_active_count > 0 ? ': ' . $troop->patrols_active_count . ' ' . mb_strtolower(Lang::get('csatar.csatar::lang.plugin.admin.patrol.patrol')) : '';
            $troopsHtml .= '</li>';
        }

        $troopsHtml .= '</ul>';

        return $troopsHtml;
    }

    public function getDefaultValueForPatrols() {

        $teamId = $this->team_id ?? post('team');

        if (!empty($this->patrols)) {
            return $this->patrols;
        }

        $patrols = Patrol::active()->where('team_id', $teamId)->with('age_group')->get();
        if (empty($patrols)) {
            return null;
        }

        $patrolsHtml = '<ul>';

        foreach ($patrols as $patrol) {
            $patrolsHtml .= '<li>';
            $patrolsHtml .= $patrol->extended_name;
            $patrolsHtml .= $patrol->age_group ? ' - ' . $patrol->age_group->name : '';
            $patrolsHtml .= $patrol->trial_system_trial_type ? ' - ' . $patrol->trial_system_trial_type->name : '';
            $patrolsHtml .= '</li>';
        }

        $patrolsHtml .= '</ul>';

        return $patrolsHtml;
    }

    public function scopeForTeam($query, $teamId) {
        return $query->where('team_id', $teamId);
    }

}
