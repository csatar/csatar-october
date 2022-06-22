<?php namespace Csatar\Csatar\Models;

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
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'email' => 'email|nullable',
        'website' => 'url|nullable',
        'facebook_page' => 'url|regex:(facebook)|nullable',
        'troop_leader_name' => 'required|min:5',
        'troop_leader_phone' => 'required|regex:(^[0-9+-.()]{5,}$)',
        'troop_leader_email' => 'required|email',
        'logo' => 'image|nullable',
        //Validation //'team' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'email',
        'website',
        'facebook_page',
        'troop_leader_name',
        'troop_leader_phone',
        'troop_leader_email',
        'team_id',
    ];

    /**
     * Relations
     */

    public $belongsTo = [
        'team' => '\Csatar\Csatar\Models\Team',
    ];

    public $hasMany = [
        'patrols' => '\Csatar\Csatar\Models\Patrol',
        'scouts' => '\Csatar\Csatar\Models\Scout',
    ];

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];
    
    /**
     * Override the getNameAttribute function
     */
    public function getNameAttribute()
    {
        return isset($this->attributes['name']) ? $this->attributes['name'] . ' ' . Lang::get('csatar.csatar::lang.plugin.admin.troop.nameSuffix') : null;
    }

    public $morphOne = [
        'content_page' => ['\Csatar\Csatar\Models\ContentPage', 'name' => 'model']
    ];

    /**
     * Scope a query to only include troops with a given team id.
     */
    public function scopeTeamId($query, $id)
    {
        return $query->where('team_id', $id);
    }
}
