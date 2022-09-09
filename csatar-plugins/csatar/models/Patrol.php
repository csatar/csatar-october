<?php namespace Csatar\Csatar\Models;

use Lang;
use DB;
use Csatar\Csatar\Models\AgeGroup;
use Csatar\Csatar\Models\OrganizationBase;

/**
 * Model
 */
class Patrol extends OrganizationBase
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_patrols';

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

        // if the selected troop does not belong to the selected team, then throw and exception
        if ($this->troop_id && $this->troop->team->id != $this->team_id) {
            throw new \ValidationException(['troop' => Lang::get('csatar.csatar::lang.plugin.admin.patrol.troopNotInTheTeamError')]);
        }

        // check that the required mandates are set for now
        $this->validateRequiredMandates($this->attributes);
    }

    /**
     * Handle the team-troop dependency
     */
    public function filterFields($fields, $context = null) {
        // populate the Troop dropdown with troops that belong to the selected team
        $fields->troop->options = [];
        $team_id = $this->team_id;
        if ($team_id) {
            foreach (\Csatar\Csatar\Models\Troop::teamId($team_id)->get() as $troop) {
                $fields->troop->options += [$troop['id'] => $troop['extendedName']];
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
        'age_group_id'
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
    ];

    public $hasMany = [
        'scouts' => '\Csatar\Csatar\Models\Scout',
        'mandates' => [
            '\Csatar\Csatar\Models\Mandate',
            'key' => 'mandate_model_id',
            'scope' => 'mandateModelType',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
            'renderableOnCreateForm' => true,
            'renderableOnUpdateForm' => true,
        ],
    ];

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];

    public function beforeSave()
    {
        $filterWords = explode(',', Lang::get('csatar.csatar::lang.plugin.admin.patrol.filterOrganizationUnitNameForWords'));
        $this->name = $this->filterNameForWords($this->name, $filterWords);
        $this->troop_id = $this->troop_id != 0 ? $this->troop_id : null;
    }

    /**
     * Override the getExtendedNameAttribute function
     */
    public function getExtendedNameAttribute()
    {
        return isset($this->attributes['name']) ? $this->attributes['name'] . ' ' . Lang::get('csatar.csatar::lang.plugin.admin.patrol.nameSuffix') : null;
    }

    public $morphOne = [
        'content_page' => ['\Csatar\Csatar\Models\ContentPage', 'name' => 'model']
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
        if($this->team_id){
            $team = Team::find($this->team_id);
            return AgeGroup::select(
                DB::raw("CONCAT(NAME, IF(note, CONCAT(' (',note, ')'), '')) AS name"),'id')
                ->where('association_id', $team->district->association->id)
                ->orderBy('sort_order')
                ->lists('name', 'id')
                ;
        }
        return [];
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
}
