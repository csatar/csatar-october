<?php
namespace Csatar\Csatar\Models;

use Cache;
use Csatar\Csatar\Classes\StructureTree;
use Csatar\Csatar\Models\Scout;
use Lang;
use Csatar\Csatar\Models\OrganizationBase;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Classes\Enums\Status;

/**
 * Model
 */
class Team extends OrganizationBase
{
    use \October\Rain\Database\Traits\Nullable;

    use \Csatar\Csatar\Traits\History;

    const HISTORY_RELATION_NAME = 'change_history';

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_teams';

    protected $with = ['district', 'district.association'];

    /**
     * @var array The columns that should be searchable by ContentPageSearchProvider
     */
    protected static $searchable = ['name'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'team_number' => 'required|numeric|min:1|max:9999',
        'address' => 'min:5|nullable',
        'phone' => 'nullable|regex:(^[0-9+-.()]{10,}$)',
        'email' => 'email|nullable',
        'website' => 'url|nullable',
        'facebook_page' => 'url|regex:(facebook)|nullable',
        'contact_name' => 'min:5|nullable',
        'contact_email' => 'email|nullable',
        'juridical_person_address' => 'min:5|nullable',
        'juridical_person_bank_account' => 'min:5|nullable',
        'district' => 'required',
        'logo' => 'image|nullable',
    ];

    protected $appends = ['extended_name'];

    public $customAttributes = ['active_members_count'];

    /**
     * Add custom validation
     */
    public function beforeValidate() {
        // if we don't have all the data for this validation, then return. The 'required' validation rules will be triggered
        if (!isset($this->district) || !isset($this->team_number)) {
            return;
        }

        // get all district ids, which cannot contain a team with the same team number
        $districts_ids = $this->district->association->districts->map(function ($district) {
            return $district['id'];
        });

        // get the id and the team_number team attributes for all teams that belong to the same organization
        $teams = $this::select('id', 'team_number')->whereIn('district_id', $districts_ids)->get();

        // iterate through the teams and if there is another team with the same team number, then throw an exception
        foreach ($teams as $team) {
            if ($team->id != $this->id && $team->team_number == $this->team_number) {
                throw new \ValidationException(['team_number' => Lang::get('csatar.csatar::lang.plugin.admin.team.teamNumberTakenError', ['teamNumber' => $this->team_number])]);
            }
        }

        // check that the foundation date is not in the future
        if (isset($this->foundation_date) && (new \DateTime($this->foundation_date) > new \DateTime())) {
            throw new \ValidationException(['foundation_date' => Lang::get('csatar.csatar::lang.plugin.admin.team.dateInTheFutureError')]);
        }

        // check that the required mandates are set for now
        $this->validateRequiredMandates($this->attributes);
    }

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'status',
        'team_number',
        'address',
        'foundation_date',
        'phone',
        'email',
        'website',
        'facebook_page',
        'contact_name',
        'contact_email',
        'history',
        'coordinates',
        'leadership_presentation',
        'description',
        'juridical_person_name',
        'juridical_person_address',
        'juridical_person_tax_number',
        'juridical_person_bank_account',
        'home_supplier_name',
        'district_id',
        'logo',
        'slug',
        'google_calendar_id',
    ];

    protected $nullable = [
        'status',
        'address',
        'foundation_date',
        'phone',
        'email',
        'website',
        'facebook_page',
        'contact_name',
        'contact_email',
        'history',
        'coordinates',
        'leadership_presentation',
        'description',
        'juridical_person_name',
        'juridical_person_address',
        'juridical_person_tax_number',
        'juridical_person_bank_account',
        'home_supplier_name',
        'district_id',
        'slug',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Relations
     */

    public $morphOne = [
        'content_page' => [
            '\Csatar\Csatar\Models\ContentPage',
            'name' => 'model',
            'label' => 'csatar.csatar::lang.plugin.admin.general.contentPage',
        ],
    ];

    public $belongsTo = [
        'district' => [
            '\Csatar\Csatar\Models\District',
            'formBuilder' => [
                'requiredBeforeRender' => true,
            ],
            'label' => 'csatar.csatar::lang.plugin.admin.district.district',
        ],
    ];

    public $morphMany = [
        'galleryPivot' => [
            \Csatar\Csatar\Models\GalleryModelPivot::class,
            'table' => 'csatar_csatar_gallery_model',
            'name' => 'model',
        ],
    ];

    public $hasMany = [
        'troops' => [
            '\Csatar\Csatar\Models\Troop',
            'label' => 'csatar.csatar::lang.plugin.admin.troop.troops',
        ],
        'troopsActive' => [
            '\Csatar\Csatar\Models\Troop',
            'scope' => 'active',
            'ignoreInPermissionsMatrix' => true,
        ],
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
        'scoutsInactive' => [
            '\Csatar\Csatar\Models\Scout',
            'scope' => 'inactive',
            'ignoreInPermissionsMatrix' => true,
        ],
        'teamReports' => [
            '\Csatar\Csatar\Models\TeamReport',
            'label' => 'csatar.csatar::lang.plugin.admin.teamReport.teamReports',
        ],
        'mandates' => [
            '\Csatar\Csatar\Models\Mandate',
            'key' => 'mandate_model_id',
            'scope' => 'mandateModelType',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
            'renderableOnCreateForm' => false,
            'renderableOnUpdateForm' => true,
        ],
        'mandatesInactive' => [
            '\Csatar\Csatar\Models\Mandate',
            'key' => 'mandate_model_id',
            'scope' => 'inactiveMandatesInOrganization',
            'ignoreInPermissionsMatrix' => true,
        ],
        'workPlans' => [
            '\Csatar\KnowledgeRepository\Models\WorkPlan',
            'label' => 'csatar.knowledgerepository::lang.plugin.admin.workPlan.workPlans',
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

    public function beforeSave()
    {
        $filterWords = explode(',', Lang::get('csatar.csatar::lang.plugin.admin.team.filterOrganizationUnitNameForWords'));
        $this->name  = $this->filterNameForWords($this->name, $filterWords);

        $this->generateSlugIfEmpty();
    }

    public function generateSlugIfEmpty() {
        if (empty($this->slug)) {
            $this->slug = str_slug($this->district->association->name_abbreviation) . '/' . str_slug($this->team_number);
        }
    }

    public function afterSave() {
        if (isset($this->original['status']) && $this->status != $this->original['status'] && $this->original['status'] == Status::ACTIVE) {
            // it would be more efficient to use mass update here, but in that case model events are not fired
            foreach (Troop::where(['team_id' => $this->id, 'status' => Status::ACTIVE])->get() as $troop) {
                $troop->status           = Status::INACTIVE;
                $troop->ignoreValidation = true;
                $troop->forceSave();
            }

            foreach (Patrol::where(['team_id' => $this->id, 'status' => Status::ACTIVE])->get() as $patrol) {
                $patrol->status           = Status::INACTIVE;
                $patrol->ignoreValidation = true;
                $patrol->forceSave();
            }

            foreach (Scout::where('team_id', $this->id)->whereNull('inactivated_at')->get() as $scout) {
                $scout->inactivated_at   = date('Y-m-d H:i:s');
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
            $structureTree = Cache::pull('structureTree');
            if (empty($structureTree)) {
                StructureTree::getStructureTree();
                return;
            }

            $structureTree[$this->district->association_id]['districtsActive'][$this->district_id]['teamsActive'][$this->id]['id']            = $this->name;
            $structureTree[$this->district->association_id]['districtsActive'][$this->district_id]['teamsActive'][$this->id]['name']          = $this->name;
            $structureTree[$this->district->association_id]['districtsActive'][$this->district_id]['teamsActive'][$this->id]['extended_name'] = $this->extended_name;
            $structureTree[$this->district->association_id]['districtsActive'][$this->district_id]['teamsActive'][$this->id]['team_number']   = $this->team_number;
            $structureTree[$this->district->association_id]['districtsActive'][$this->district_id]['teamsActive'][$this->id]['district_id']   = $this->district_id;
            $structureTree[$this->district->association_id]['districtsActive'][$this->district_id]['teamsActive'][$this->id]['status']        = $this->status;
            Cache::forever('structureTree', $structureTree);
        }

        if (empty($this->original)) {
            return;
        }

        if ($this->getOriginalValue('status') != $this->status) {
            StructureTree::updateDistrictTree($this->district_id);
        }

        if ($this->getOriginalValue('district_id') != $this->district_id) {
            StructureTree::updateDistrictTree($this->district_id);
            if (!empty($this->original['district_id'])) {
                StructureTree::updateDistrictTree($this->original['district_id']);
            }
        }

        if (($this->getOriginalValue('name') != $this->name)
            || ($this->getOriginalValue('team_number') != $this->team_number)
        ) {
            $structureTree = Cache::pull('structureTree');
            if (empty($structureTree)) {
                StructureTree::getStructureTree();
                return;
            }

            $structureTree[$this->district->association_id]['districtsActive'][$this->district_id]['teamsActive'][$this->id]['name']          = $this->name;
            $structureTree[$this->district->association_id]['districtsActive'][$this->district_id]['teamsActive'][$this->id]['extended_name'] = $this->extended_name;
            $structureTree[$this->district->association_id]['districtsActive'][$this->district_id]['teamsActive'][$this->id]['team_number']   = $this->team_number;
            Cache::forever('structureTree', $structureTree);
        }
    }

    public static function getStatusOptions(){
        return [
            Status::ACTIVE => e(trans('csatar.csatar::lang.plugin.admin.team.active')),
            Status::INACTIVE => e(trans('csatar.csatar::lang.plugin.admin.team.inactive')),
            Status::SUSPENDED => e(trans('csatar.csatar::lang.plugin.admin.team.suspended')),
            Status::FORMING => e(trans('csatar.csatar::lang.plugin.admin.team.forming')),
        ];
    }

    /**
     * Override the getExtendedNameAttribute function
     */
    public function getExtendedNameAttribute()
    {
        return isset($this->attributes['team_number']) && isset($this->attributes['name']) ? str_pad($this->attributes['team_number'], 3, '0', STR_PAD_LEFT) . ' - ' . $this->attributes['name'] . ' ' . Lang::get('csatar.csatar::lang.plugin.admin.team.nameSuffix') : null;
    }

    public function getExtendedNameWithAssociationAttribute()
    {
        $associationAbbreviation = isset($this->district->association->name_abbreviation) ? $this->district->association->name_abbreviation . ' - ' : '';

        return $associationAbbreviation . $this->getExtendedNameAttribute();
    }

    /**
     * Retrieve the team by Id.
     */
    public static function getById($id)
    {
        return Team::find($id);
    }

    /**
     * Scope a query to only include teams with a given district id.
     */
    public function scopeDistrictId($query, $id)
    {
        return $query->where('district_id', $id);
    }

    /**
     * Return the team, which the given id
     */
    public static function getAllByAssociationId($associationId, $teamId)
    {
        $item = self::find($teamId);
        return [$item->id => $item->extendedName];
    }

    /**
     * Return the team, which the given id
     */
    public static function getTeamIdByAssociationAndTeamNumber($associationId, $teamNumber)
    {
        $items = self::where('team_number', $teamNumber)->get();
        foreach ($items as $item) {
            if ($item->district->association->id == $associationId) {
                return $item->id;
            }
        }

        return null;
    }

    /**
     * Returns the id of the association to which the item belongs to.
     */
    public function getAssociationId()
    {
        return $this->district->association->id;
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.csatar::lang.plugin.admin.team.team');
    }

    public function getAssociation() {
        return $this->district->association ?? null;
    }

    public function getDistrict() {
        return $this->district ?? null;
    }

    public function getTeam() {
        return $this;
    }

    public function getActiveScoutsCount() {
        return $this->scoutsActive->count();
    }

    public function getActiveScouts() {
        return $this->scoutsInactive->count();
    }

    public function scopeInDistrict($query, $districtId) {
        $query->where('district_id', $districtId);
    }

    public function scopeActive($query)
    {
        $query->where('status', Status::ACTIVE)->whereHas('scoutsActive')->orderByRaw('CONVERT(team_number, UNSIGNED) asc');
    }

    public function scopeActiveInDistrict($query, $districtId) {
        $query->where('district_id', $districtId)->active()->orderByRaw('CONVERT(team_number, UNSIGNED) asc');
    }

    public function scopeActiveInAssociation($query, $associationId) {
        $districtIds = District::where('association_id', $associationId)->get()->pluck('id')->toArray();
        return $query->whereIn('district_id', $districtIds)->active();
    }

    public function scopeForDropdown($query) {
        return $query->with(
            [
                'district' => function($query) {
                    $query->select('id', 'name', 'association_id');
                },
                'district.association' => function($query) {
                    $query->select('id', 'name', 'name_abbreviation');
                }
            ]
        )
        ->select('id', 'name', 'team_number', 'district_id');
    }

    public function getTroops() {
        return $this->troops;
    }

    public function getPatrols() {
        return $this->patrols;
    }

    public function getPatrolsWithoutTroop() {
        return $this->patrols->where('troop_id', null);
    }

    public function getActiveMembersCountAttribute() {
        return StructureTree::getTeamScoutsCount($this->id);
    }

    public function getTextForSearchResultsTreeAttribute() {
        return $this->team_number;
    }

    public function getParentTree() {
        $tree = [
            $this->district->association->text_for_search_results_tree ?? null,
            $this->district->text_for_search_results_tree ?? null,
        ];

        return '(' . implode(' - ', $tree) . ')';
    }

}
