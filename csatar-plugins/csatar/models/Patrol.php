<?php
namespace Csatar\Csatar\Models;

use Cache;
use Csatar\Csatar\Classes\Enums\Gender;
use Csatar\Csatar\Classes\Enums\Status;
use Csatar\Csatar\Classes\StructureTree;
use Lang;
use DB;
use Csatar\Csatar\Models\AgeGroup;
use Csatar\Csatar\Models\OrganizationBase;
use Csatar\Csatar\Models\Troop;

/**
 * Model
 */
class Patrol extends OrganizationBase
{
    use \October\Rain\Database\Traits\Nullable;

    use \Csatar\Csatar\Traits\History;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_patrols';

    /**
     * @var array The columns that should be searchable by ContentPageSearchProvider
     */
    protected static $searchable = ['name'];

    protected $appends = ['extended_name'];

    public $customAttributes = ['active_members_count'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'email' => 'email|nullable',
        'website' => 'url|nullable',
        'facebook_page' => 'url|regex:(facebook)|nullable',
        'logo' => 'image|nullable',
        'age_group' => 'required',
        'team' => 'required',
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate()
    {
        // if we don't have all the data for this validation, then return. The 'required' validation rules will be triggered
        if (!isset($this->team_id)) {
            return;
        }

        if ($this->troop_id === 'null') {
            $this->troop_id = null;
        }

        // if the selected troop does not belong to the selected team, then throw and exception
        if ($this->troop_id) {
            $this->validateTroopId($this->troop_id);
        }

        // check that the required mandates are set for now
        $this->validateRequiredMandates($this->attributes);
    }

    public function validateTroopId($id)
    {
        $troop = Troop::find($id);

        if (empty($troop)) {
            throw new \ValidationException(['troop' => Lang::get('csatar.csatar::lang.plugin.admin.troop.canNotFindTroopError', ['troopId' => $id])]);
        }

        if ($troop->team_id != $this->team_id) {
            throw new \ValidationException(['troop' => Lang::get('csatar.csatar::lang.plugin.admin.patrol.troopNotInTheTeamError')]);
        }
    }

    /**
     * Handle the team-troop dependency
     */
    public function filterFields($fields, $context = null) {
        // populate the Troop dropdown with troops that belong to the selected team
        if (isset($fields->troop)) {
            $fields->troop->options = [];
            $team_id = $this->team_id;
            if ($team_id) {
                $fields->troop->options += ['null' => e(trans('csatar.csatar::lang.plugin.admin.general.select'))];
                foreach (\Csatar\Csatar\Models\Troop::teamId($team_id)->get() as $troop) {
                    $fields->troop->options += [$troop['id'] => $troop['extendedName']];
                }
            }
        }

    }

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'email',
        'website',
        'facebook_page',
        'age_group_id',
        'team_id',
        'troop_id',
        'logo',
        'slug',
        'gender',
        'status'
    ];

    public $additionalFieldsForPermissionMatrix = [
        'weeklyWorkPlans',
    ];

    protected $nullable = [
        'email',
        'website',
        'facebook_page',
        'troop_id',
        'slug',
        'gender',
        'status'
    ];

    /**
     * Relations
     */

    public $belongsTo = [
        'team' => [
            '\Csatar\Csatar\Models\Team',
            'formBuilder' => [
                'requiredBeforeRender' => true,
            ],
        ],
        'troop' => '\Csatar\Csatar\Models\Troop',
        'age_group' => '\Csatar\Csatar\Models\AgeGroup',
        'trial_system_trial_type' => '\Csatar\KnowledgeRepository\Models\TrialSystemTrialType',
    ];

    public $hasMany = [
        'scouts' => [
            '\Csatar\Csatar\Models\Scout',
            'label' => 'csatar.csatar::lang.plugin.admin.scout.scouts',
        ],
        'scoutsActive' => [
            '\Csatar\Csatar\Models\Scout',
            'scope' => 'active',
            'ignoreInPermissionsMatrix' => true,
        ],
        'mandates' => [
            '\Csatar\Csatar\Models\Mandate',
            'key' => 'mandate_model_id',
            'scope' => 'mandateModelType',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
            'renderableOnCreateForm' => true,
            'renderableOnUpdateForm' => true,
        ],
        'mandatesInactive' => [
            '\Csatar\Csatar\Models\Mandate',
            'key' => 'mandate_model_id',
            'scope' => 'inactiveMandatesInOrganization',
            'ignoreInPermissionsMatrix' => true,
        ],
        'ovamtvWorkPlans' => [
            '\Csatar\KnowledgeRepository\Models\OvamtvWorkPlan',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.ovamtvWorkPlan.ovamtvWorkPlan',
        ],
    ];

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];

    public $attachMany = [
        'richTextUploads' => [
            'System\Models\File',
            'ignoreInPermissionsMatrix' => true,
        ],
    ];

    public static function getEagerLoadSettings(string $useCase = null): array
    {
        $eagerLoadSettings = parent::getEagerLoadSettings($useCase);
        if ($useCase === 'formBuilder') {
            // Important to extend the eager load settings, not to overwrite them!
            $eagerLoadSettings['mandates.mandate_patrol']      = function($query) {
                return $query->select(
                    'csatar_csatar_patrols.id',
                    'csatar_csatar_patrols.team_id',
                    'csatar_csatar_patrols.troop_id'
                );
            };
            $eagerLoadSettings['mandates.mandate_patrol.team'] = function($query) {
                return $query->select(
                    'csatar_csatar_teams.id',
                    'csatar_csatar_teams.name',
                    'csatar_csatar_teams.team_number',
                    'csatar_csatar_teams.district_id'
                );
            };
            $eagerLoadSettings = array_merge_recursive($eagerLoadSettings, [
                'team.district.association', 'troop'
            ]);
        }

        if ($useCase == 'inactiveMandatesPatrol') {
            $eagerLoadSettings = [
                'mandatesInactive.mandate_patrol.team' => function($query) {
                    return $query->select(
                        'csatar_csatar_teams.id',
                        'csatar_csatar_teams.name',
                        'csatar_csatar_teams.team_number'
                    )->withTrashed();
                },
            ];
            $eagerLoadSettings = array_merge($eagerLoadSettings, parent::getEagerLoadSettings('inactiveMandates'));
        }

        return $eagerLoadSettings;
    }

    public function beforeSave()
    {
        $filterWords    = explode(',', Lang::get('csatar.csatar::lang.plugin.admin.patrol.filterOrganizationUnitNameForWords'));
        $this->name     = $this->filterNameForWords($this->name, $filterWords);
        $this->troop_id = $this->troop_id != 0 ? $this->troop_id : null;

        $this->generateSlugIfEmpty();
    }

    public function afterSave() {
        if (isset($this->original['status']) && $this->status != $this->original['status'] && $this->original['status'] == Status::ACTIVE) {
            // it would be more efficient to use mass update here, but in that case model events are not fired
            foreach (Scout::where('patrol_id', $this->id)->whereNull('inactivated_at')->get() as $scout) {
                $scout->inactivated_at   = date('Y-m-d H:i:s');
                $scout->ignoreValidation = true;
                $scout->forceSave();
            }

            Mandate::setAllMandatesExpiredInOrganization($this);
        }

        $this->updateScoutsTroopId(); // this should be called before updateCache()

        $this->updateCache();
    }

    public function afterDelete()
    {
        $this->updateCache();
    }

    public function updateCache(): void
    {
        if ($this->wasRecentlyCreated && $this->status == Status::ACTIVE) {
            StructureTree::updateTeamTree($this->team_id);
        }

        if (empty($this->original)) {
            return;
        }

        if ($this->getOriginalValue('status') != $this->status || $this->getOriginalValue('deleted_at') != $this->deleted_at) {
            StructureTree::updateTeamTree($this->team_id);
        }

        if ($this->getOriginalValue('team_id') != $this->team_id || $this->getOriginalValue('troop_id') != $this->troop_id) {
            StructureTree::updateTeamTree($this->team_id);
            if (!empty($this->original['team_id'])) {
                StructureTree::updateTeamTree($this->original['team_id']);
            }
        }

        if ($this->getOriginalValue('name') != $this->name) {
            $structureTree = Cache::pull('structureTree');
            if (empty($structureTree)) {
                StructureTree::getStructureTree();
                return;
            }

            $associationId = $this->team->district->association_id;
            $districtId    = $this->team->district_id;
            $teamId        = $this->team->id;
            $teamsActive   = $structureTree[$associationId]['districtsActive'][$districtId]['teamsActive'];

            $teamsActive[$teamId]['patrolsActive'][$this->id]['name']          = $this->name;
            $teamsActive[$teamId]['patrolsActive'][$this->id]['extended_name'] = $this->extended_name;

            if (isset($this->troop_id)) {
                $teamsActive[$teamId]['troopsActive'][$this->troop_id]['patrolsActive'][$this->id]['name']          = $this->name;
                $teamsActive[$teamId]['troopsActive'][$this->troop_id]['patrolsActive'][$this->id]['extended_name'] = $this->extended_name;
            }

            $structureTree[$associationId]['districtsActive'][$districtId]['teamsActive'] = $teamsActive;
            Cache::forever('structureTree', $structureTree);
        }
    }

    public function updateScoutsTroopId() {
        if ($this->getOriginalValue('troop_id') != $this->troop_id) {
            foreach ($this->scouts as $scout) {
                $scout->troop_id         = $this->troop_id;
                $scout->ignoreValidation = true;
                $scout->skipCacheRefresh = true;
                $scout->forceSave();
            }
        }
    }

    public function generateSlugIfEmpty() {
        if (empty($this->slug)) {
            $this->slug  = str_slug($this->team->district->association->name_abbreviation) ;
            $this->slug .= '/' . str_slug($this->team->team_number) . '/' . str_slug($this->name);
            $this->slug .= '-ors';
        }
    }

    /**
     * Override the getExtendedNameAttribute function
     */
    public function getExtendedNameAttribute()
    {
        return isset($this->attributes['name']) ? $this->attributes['name'] . ' ' . Lang::get('csatar.csatar::lang.plugin.admin.patrol.nameSuffix') : null;
    }

    public $morphOne = [
        'content_page' => [
            '\Csatar\Csatar\Models\ContentPage',
            'name' => 'model',
            'label' => 'csatar.csatar::lang.plugin.admin.general.contentPage',
        ],
    ];

    public $morphMany = [
        'galleryPivot' => [
            \Csatar\Csatar\Models\GalleryModelPivot::class,
            'table' => 'csatar_csatar_gallery_model',
            'name' => 'model',
        ],
    ];

    /**
     * Scope a query to only include patrols with a given team id.
     */
    public function scopeTeamId($query, $id)
    {
        return $query->where('team_id', $id);
    }

    /**
     * Scope a query to only include patrols with a given troop id.
     */
    public function scopeTroopId($query, $id)
    {
        return $query->where('troop_id', $id);
    }

    public function getAgeGroupOptions(){
        if ($this->team_id) {
            $team = $this->team;
            return AgeGroup::select(
                DB::raw("CONCAT(NAME, IF(note, CONCAT(' (',note, ')'), '')) AS name"), 'id')
                ->where('association_id', $team->district->association->id)
                ->orderBy('sort_order')
                ->lists('name', 'id')
                ;
        }

        return [];
    }

    public static function getStatusOptions(){
        return [
            Status::ACTIVE => e(trans('csatar.csatar::lang.plugin.admin.general.active')),
            Status::INACTIVE => e(trans('csatar.csatar::lang.plugin.admin.general.inactive')),
        ];
    }

    public static function getGenderOptions(){
        return [
            Gender::MALE => e(trans('csatar.csatar::lang.plugin.admin.scout.gender.male')),
            Gender::FEMALE => e(trans('csatar.csatar::lang.plugin.admin.scout.gender.female')),
            Gender::MIXED => e(trans('csatar.csatar::lang.plugin.admin.patrol.gender.mixed')),
        ];
    }

    /**
     * Return all patrols, which belong to the given team
     */
    public static function getAllByAssociationId($associationId, $teamId)
    {
        $options = [];
        foreach (self::where('team_id', $teamId)->get() as $item) {
            $options[$item->id] = $item->extendedName;
        }

        asort($options);
        return $options;
    }

    /**
     * Returns the id of the association to which the item belongs to.
     */
    public function getAssociationId()
    {
        return $this->team->district->association->id;
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.csatar::lang.plugin.admin.patrol.patrol');
    }

    public function getAssociation() {
        return $this->team->district->association ?? null;
    }

    public function getDistrict() {
        return $this->team->district ?? null;
    }

    public function getTeam() {
        return $this->team_id ? $this->team : null;
    }

    public function getTroop() {
        return $this->troop_id ? $this->troop : null;
    }

    public function getPatrol() {
        return $this;
    }

    public function getActiveScouts() {
        return $this->scoutsActive;
    }

    public function getActiveScoutsCount() {
        return $this->scoutsActive->count();
    }

    public function scopeInTeam($query, $teamId) {
        return $query->where('team_id', $teamId);
    }

    public function scopeInTroop($query, $troopId) {
        return $query->where('troop_id', $troopId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::ACTIVE);
    }

    public function getActiveMembersCountAttribute() {
        return StructureTree::getPatrolScoutsCount($this->id);
    }

    public function getTextForSearchResultsTreeAttribute() {
        return $this->extended_name;
    }

    public function getParentTree() {
        $tree = [
            $this->team->district->association->text_for_search_results_tree ?? null,
            $this->team->district->text_for_search_results_tree ?? null,
            $this->team->text_for_search_results_tree ?? null,
        ];

        if (isset($this->troop_id)) {
            $tree[] = $this->troop->text_for_search_results_tree ?? null;
        }

        return '(' . implode(' - ', $tree) . ')';
    }

}
