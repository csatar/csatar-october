<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class Patrol extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_patrols';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'email' => 'email',
        'website' => 'url',
        'facebook_page' => 'url|regex:(facebook)',
        'patrol_leader_name' => 'required|min:5',
        'patrol_leader_phone' => 'required|regex:(^[0-9+-.()]{5,}$)',
        'patrol_leader_email' => 'required|email',
        'logo' => 'image|nullable',
        'age_group' => 'required',
        'team_id' => 'required',
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate() {
        // if we don't have all the data for this validation, then return. The 'required' validation rules will be triggered
        if (!$this->team_id) {
            return;
        }
        
        // if the selected troop does not belong to the selected team, then throw and exception
        if ($this->troop_id && $this->troop->team->id != $this->team_id) {
            throw new \ValidationException(['troop' => \Lang::get('csatar.csatar::lang.plugin.admin.patrol.troopNotInTheTeamError')]);
        }
    }

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'patrol_leader_name',
        'patrol_leader_phone',
        'patrol_leader_email',
        'age_group',
        'team_id',
    ];
    
    /**
     * Relations
     */
    
    public $belongsTo = [
        'team' => '\Csatar\Csatar\Models\Team',
        'troop' => '\Csatar\Csatar\Models\Troop',
    ];

    public $attachOne = [
        'logo' => 'System\Models\File'
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
