<?php namespace Csatar\Csatar\Models;

use Lang;
use Csatar\Csatar\Models\OrganizationBase;

/**
 * Model
 */
class Team extends OrganizationBase
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_teams';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'team_number' => 'required|numeric|min:1|max:9999',
        'address' => 'required|min:5',
        'foundation_date' => 'required',
        'phone' => 'required|regex:(^[0-9+-.()]{5,}$)',
        'email' => 'required|email',
        'website' => 'url|nullable',
        'facebook_page' => 'url|regex:(facebook)|nullable',
        'contact_name' => 'required|min:5',
        'contact_email' => 'required|email',
        'leadership_presentation' => 'required',
        'description' => 'required',
        'juridical_person_name' => 'required',
        'juridical_person_address' => 'required|min:5',
        'juridical_person_tax_number' => 'required',
        'juridical_person_bank_account' => 'required|min:5',
        'district' => 'required',
        'logo' => 'image|nullable',
    ];

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
        foreach($teams as $team) {
            if ($team->id != $this->id && $team->team_number == $this->team_number) {
                throw new \ValidationException(['team_number' => Lang::get('csatar.csatar::lang.plugin.admin.team.teamNumberTakenError')]);
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
    ];

    /**
     * Relations
     */

    public $belongsTo = [
        'district' => [
            '\Csatar\Csatar\Models\District',
            'formBuilder' => [
                'requiredBeforeRender' => true,
            ],
        ],
    ];

    public $hasMany = [
        'troops' => '\Csatar\Csatar\Models\Troop',
        'patrols' => '\Csatar\Csatar\Models\Patrol',
        'scouts' => '\Csatar\Csatar\Models\Scout',
        'teamReports' => '\Csatar\Csatar\Models\TeamReport',
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
        'logo' => 'System\Models\File'
    ];

    public function beforeSave()
    {
        $filterWords = explode(',', Lang::get('csatar.csatar::lang.plugin.admin.team.filterOrganizationUnitNameForWords'));
        $this->name = $this->filterNameForWords($this->name, $filterWords);
    }

    /**
     * Override the getExtendedNameAttribute function
     */
    public function getExtendedNameAttribute()
    {
        return isset($this->attributes['team_number']) && isset($this->attributes['name']) ? str_pad($this->attributes['team_number'], 3, '0', STR_PAD_LEFT) . ' - ' . $this->attributes['name'] . ' ' . Lang::get('csatar.csatar::lang.plugin.admin.team.nameSuffix') : null;
    }

    /**
     * Retrieve the team by Id.
     */
    public static function getById($id)
    {
        return Team::find($id);
    }

    public $morphOne = [
        'content_page' => ['\Csatar\Csatar\Models\ContentPage', 'name' => 'model']
    ];

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
}
