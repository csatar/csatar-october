<?php namespace Csatar\Csatar\Models;

use Lang;
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
        'patrol_leader_name' => 'required|min:5',
        'patrol_leader_phone' => 'required|regex:(^[0-9+-.()]{5,}$)',
        'patrol_leader_email' => 'required|email',
        'logo' => 'image|nullable',
        'age_group' => 'required',
        //Validation //'team' => 'required',
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate() {
        // if we don't have all the data for this validation, then return. The 'required' validation rules will be triggered
        if (!isset($this->team_id)) {
            return;
        }

        // if the selected troop does not belong to the selected team, then throw and exception
        if ($this->troop_id && $this->troop->team->id != $this->team_id) {
            throw new \ValidationException(['troop' => \Lang::get('csatar.csatar::lang.plugin.admin.patrol.troopNotInTheTeamError')]);
        }
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
        'patrol_leader_name',
        'patrol_leader_phone',
        'patrol_leader_email',
        'age_group',
        'team_id',
        'troop_id',
        'logo',
    ];

    /**
     * Relations
     */

    public $belongsTo = [
        'team' => '\Csatar\Csatar\Models\Team',
        'troop' => '\Csatar\Csatar\Models\Troop',
    ];

    public $hasMany = [
        'scouts' => '\Csatar\Csatar\Models\Scout',
    ];

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];

    public function beforeSave()
    {
        $filterWords = explode(',', Lang::get('csatar.csatar::lang.plugin.admin.patrol.filterOrganizationUnitNameForWords'));
        $this->name = str_replace($filterWords, '', $this->name);
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
}
