<?php
namespace Csatar\Csatar\Models;

use Cache;
use Csatar\Csatar\Classes\Enums\Status;
use Csatar\Csatar\Classes\StructureTree;
use Lang;
use Csatar\Csatar\Models\OrganizationBase;

/**
 * Model
 */
class District extends OrganizationBase
{
    use \Csatar\Csatar\Traits\History;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_districts';

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
        'status' => 'required',
        'phone' => 'nullable|regex:(^[0-9+-.()]{10,}$)',
        'email' => 'email|nullable',
        'website' => 'url|nullable',
        'facebook_page' => 'url|regex:(facebook)|nullable',
        'contact_name' => 'min:5|nullable',
        'contact_email' => 'email|nullable',
        'address' => 'min:5|nullable',
        'bank_account' => 'min:5|nullable',
        'association' => 'required',
        'logo' => 'image|nullable',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'phone',
        'email',
        'website',
        'facebook_page',
        'coordinates',
        'contact_name',
        'contact_email',
        'address',
        'leadership_presentation',
        'description',
        'bank_account',
        'association_id',
        'logo',
        'slug',
        'status',
        'google_calendar_id',
    ];

    public $nullable = [
        'phone',
        'email',
        'website',
        'facebook_page',
        'coordinates',
        'contact_name',
        'contact_email',
        'address',
        'leadership_presentation',
        'description',
        'bank_account',
        'association_id',
        'logo',
        'slug',
    ];

    /**
     * Relations
     */

    public $belongsTo = [
        'association' => [
            '\Csatar\Csatar\Models\Association',
            'formBuilder' => [
                'requiredBeforeRender' => true,
            ],
        ],
    ];

    public $hasMany = [
        'teams' => [
            '\Csatar\Csatar\Models\Team',
            'label' => 'csatar.csatar::lang.plugin.admin.team.teams',
        ],
        'teamsActive' => [
            '\Csatar\Csatar\Models\Team',
            'scope' => 'active',
            'ignoreInPermissionsMatrix' => true,
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
    ];

    public $attachOne = [
        'logo' => 'System\Models\File',
    ];

    public $attachMany = [
        'richTextUploads' => [
            'System\Models\File',
            'ignoreInPermissionsMatrix' => true,
        ],
    ];

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
     * Override the getExtendedNameAttribute function
     */
    public function getExtendedNameAttribute()
    {
        return isset($this->attributes['name']) ? $this->attributes['name'] . ' ' . Lang::get('csatar.csatar::lang.plugin.admin.district.nameSuffix') : null;
    }

    /**
     * Scope a query to only include districts with a given association id.
     */
    public function scopeAssociationId($query, $id)
    {
        return $query->where('association_id', $id);
    }

    public function beforeSave()
    {
        $filterWords = explode(',', Lang::get('csatar.csatar::lang.plugin.admin.district.filterOrganizationUnitNameForWords'));
        $this->name  = $this->filterNameForWords($this->name, $filterWords);

        $this->generateSlugIfEmpty();
    }

    public function afterSave()
    {
        $this->updateCache();
    }

    public function afterDelete()
    {
        $this->updateCache();
    }

    public function updateCache(): void
    {
        if ($this->wasRecentlyCreated && $this->status == Status::ACTIVE) {
            $structureTree = Cache::pull('structureTree');
            if (empty($structureTree)) {
                StructureTree::handleEmptyStructureTree();
                return;
            }

            $structureTree[$this->association_id]['districtsActive'][$this->id]['id']            = $this->id;
            $structureTree[$this->association_id]['districtsActive'][$this->id]['name']          = $this->name;
            $structureTree[$this->association_id]['districtsActive'][$this->id]['extended_name'] = $this->extended_name;
            $structureTree[$this->association_id]['districtsActive'][$this->id]['status']        = $this->status;
            $structureTree[$this->association_id]['districtsActive'][$this->id]['association_id'] = $this->association_id;
            Cache::forever('structureTree', $structureTree);
        }

        if (empty($this->original)) {
            return;
        }

        if ($this->getOriginalValue('status') != $this->status || $this->getOriginalValue('deleted_at') != $this->deleted_at) {
            StructureTree::updateAssociationTree($this->association_id);
        }

        if ($this->getOriginalValue('association_id') != $this->association_id) {
            StructureTree::updateAssociationTree($this->association_id);
            if (!empty($this->original['association_id'])) {
                StructureTree::updateAssociationTree($this->original['association_id']);
            }
        }

        if ($this->getOriginalValue('name') != $this->name) {
            $structureTree = Cache::pull('structureTree');
            if (empty($structureTree)) {
                StructureTree::handleEmptyStructureTree();
                return;
            }

            $structureTree[$this->association_id]['districtsActive'][$this->id]['name']          = $this->name;
            $structureTree[$this->association_id]['districtsActive'][$this->id]['extended_name'] = $this->extended_name;
            Cache::forever('structureTree', $structureTree);
        }
    }

    public function generateSlugIfEmpty() {
        if (empty($this->slug)) {
            $this->slug = str_slug($this->association->name_abbreviation) . '/' . str_slug($this->name) . '-korzet';
        }
    }

    /**
     * Return the district, which belongs to the given association, and to which the given team belongs to
     */
    public static function getAllByAssociationId($associationId, $teamId)
    {
        $item = self::join('csatar_csatar_teams', 'csatar_csatar_districts.id', '=', 'csatar_csatar_teams.district_id')
            ->select('csatar_csatar_teams.district_id', 'csatar_csatar_districts.name', 'csatar_csatar_teams.id', 'csatar_csatar_districts.association_id')
            ->where('csatar_csatar_districts.association_id', $associationId)
            ->where('csatar_csatar_teams.id', $teamId)
            ->first();
        return [$item->district_id => $item->extendedName];
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
        return $this->association->id;
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.csatar::lang.plugin.admin.district.district');
    }

    public function getAssociation() {
        return $this->association ?? null;
    }

    public function getDistrict() {
        return $this;
    }

    public function getTeamsAttribute() {
        return Team::where('district_id', $this->id)
            ->orderByRaw('CONVERT(team_number, SIGNED) ASC')
            ->get();
    }

    public function getActiveTeams(){
        return $this->teamsActive;
    }

    public function scopeInAssociation($query, $associationId) {
        return $query->where('association_id', $associationId);
    }

    // scope to get only districts with active status and active teams
    public function scopeActive($query) {
        return $query->where('status', Status::ACTIVE)->whereHas('teamsActive');
    }

    public function getActiveMembersCountAttribute() {
        return StructureTree::getDistrictScoutsCount($this->id);
    }

    public function getTextForSearchResultsTreeAttribute() {
        return $this->name;
    }

    public function getParentTree() {
        if (empty($this->association)) {
            return null;
        }
        return '(' . $this->association->text_for_search_results_tree . ')';
    }

}
