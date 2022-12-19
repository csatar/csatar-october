<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Classes\Enums\Status;
use Lang;
use Csatar\Csatar\Models\OrganizationBase;

/**
 * Model
 */
class District extends OrganizationBase
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_districts';

    /**
     * @var array The columns that should be searchable by ContentPageSearchProvider
     */
    protected static $searchable = ['name'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
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
        'mandates' => [
            '\Csatar\Csatar\Models\Mandate',
            'key' => 'mandate_model_id',
            'scope' => 'mandateModelType',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
            'renderableOnCreateForm' => false,
            'renderableOnUpdateForm' => true,
        ],
    ];

    public $attachOne = [
        'logo' => 'System\Models\File',
    ];

    public $morphOne = [
        'content_page' => [
            '\Csatar\Csatar\Models\ContentPage',
            'name' => 'model',
            'label' => 'csatar.csatar::lang.plugin.admin.general.contentPage',
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
        $this->name = $this->filterNameForWords($this->name, $filterWords);

        $this->generateSlugIfEmpty();
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

    public function getActiveTeams(){
        return Team::inDistrict($this->id)->active()->get();
    }

    public function scopeInAssociation($query, $associationId) {
        return $query->where('association_id', $associationId);
    }
}
