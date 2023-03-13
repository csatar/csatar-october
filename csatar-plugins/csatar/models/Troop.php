<?php namespace Csatar\Csatar\Models;

use Cache;
use Csatar\Csatar\Classes\Enums\Status;
use Csatar\Csatar\Classes\StructureTree;
use Lang;
use Csatar\Csatar\Models\OrganizationBase;

/**
 * Model
 */
class Troop extends OrganizationBase
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_troops';

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
        'team' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'email',
        'website',
        'facebook_page',
        'team_id',
        'slug',
        'status',
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
    ];

    public $hasMany = [
        'patrols' => [
            '\Csatar\Csatar\Models\Patrol',
            'label' => 'csatar.csatar::lang.plugin.admin.patrol.patrols',
        ],
        'patrolsActive' => [
            '\Csatar\Csatar\Models\Patrol',
            'scope' => 'active',
            'ignoreInPermissionsMatrix' => true,
        ],
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
    ];

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];

    public static function getEagerLoadSettings(string $useCase = null): array
    {
        $eagerLoadSettings = parent::getEagerLoadSettings($useCase);
        if ($useCase === 'formBuilder') {
            // Important to extend the eager load settings, not to overwrite them!
            $eagerLoadSettings['mandates.mandate_troop'] = function($query) {
                return $query->select(
                    'csatar_csatar_troops.id',
                    'csatar_csatar_troops.team_id'
                );
            };
            $eagerLoadSettings['mandates.mandate_troop.team'] = function($query) {
                return $query->select(
                    'csatar_csatar_teams.id',
                    'csatar_csatar_teams.name',
                    'csatar_csatar_teams.team_number',
                    'csatar_csatar_teams.district_id'
                );
            };
            $eagerLoadSettings = array_merge_recursive($eagerLoadSettings, [
                'team', 'team.district', 'team.district.association',
            ]);
        }
        if ($useCase == 'inactiveMandatesTroop') {
            $eagerLoadSettings = [
                'mandatesInactive.mandate_troop.team' => function($query) {
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
        $filterWords = explode(',', Lang::get('csatar.csatar::lang.plugin.admin.troop.filterOrganizationUnitNameForWords'));
        $this->name = $this->filterNameForWords($this->name, $filterWords);

        $this->generateSlugIfEmpty();
    }

    public function afterSave() {
        if (isset($this->original['status']) && $this->status != $this->original['status'] && $this->original['status'] == Status::ACTIVE) {
            // it would be more efficient to use mass update here, but in that case model events are not fired
            foreach (Patrol::where(['troop_id' => $this->id, 'status' => Status::ACTIVE])->get() as $patrol) {
                $patrol->status = Status::INACTIVE;
                $patrol->ignoreValidation = true;
                $patrol->forceSave();
            }
            foreach (Scout::where('troop_id', $this->id)->whereNull('inactivated_at')->get() as $scout) {
                $scout->inactivated_at = date('Y-m-d H:i:s');
                $scout->ignoreValidation = true;
                $scout->forceSave();
            }
            Mandate::setAllMandatesExpiredInOrganization($this);
        }

        $this->updateCache();
    }

    public function updateCache(): void
    {
        if ($this->wasRecentlyCreated && $this->status == Status::ACTIVE) {
            StructureTree::updateAssociationTree($this->association_id);
        }

        if (empty($this->original)) {
            return;
        }

        if ($this->getOriginalValue('status') != $this->status) {
            StructureTree::updateTeamTree($this->team_id);
        }

        if ($this->getOriginalValue('team_id') != $this->district_id) {
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
            $structureTree[$this->team->district->association_id]['districtsActive'][$this->team->district_id]['teamsActive'][$this->team->id]['troopsActive'][$this->id]['name'] = $this->name;
            $structureTree[$this->team->district->association_id]['districtsActive'][$this->team->district_id]['teamsActive'][$this->team->id]['troopsActive'][$this->id]['extended_name'] = $this->extended_name;
            Cache::forever('structureTree', $structureTree);
        }
    }

    public function generateSlugIfEmpty() {
        if (empty($this->slug)) {
            $this->slug = str_slug($this->team->district->association->name_abbreviation) ;
            $this->slug .= '/' . str_slug($this->team->team_number) . '/' . str_slug($this->name);
            $this->slug .= '-raj';
        }
    }

    /**
     * Override the getExtendedNameAttribute function
     */
    public function getExtendedNameAttribute()
    {
        return isset($this->attributes['name']) ? $this->attributes['name'] . ' ' . Lang::get('csatar.csatar::lang.plugin.admin.troop.nameSuffix') : null;
    }

    public $morphOne = [
        'content_page' => [
            '\Csatar\Csatar\Models\ContentPage',
            'name' => 'model',
            'label' => 'csatar.csatar::lang.plugin.admin.general.contentPage',
        ],
    ];

    /**
     * Scope a query to only include troops with a given team id.
     */
    public function scopeTeamId($query, $id)
    {
        return $query->where('team_id', $id);
    }

    /**
     * Return all troops, which belong to the given team
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

    public static function getStatusOptions(){
        return [
            Status::ACTIVE => e(trans('csatar.csatar::lang.plugin.admin.general.active')),
            Status::INACTIVE => e(trans('csatar.csatar::lang.plugin.admin.general.inactive')),
        ];
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
        return Lang::get('csatar.csatar::lang.plugin.admin.troop.troop');
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

    public function getPatrols() {
        return $this->patrols;
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::ACTIVE);
    }

    public function getActiveMembersCountAttribute() {
        return StructureTree::getTroopScoutsCount($this->id);
    }
}
