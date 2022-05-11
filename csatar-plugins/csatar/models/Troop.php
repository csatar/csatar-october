<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class Troop extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_troops';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'email' => 'required|email',
        'website' => 'url',
        'facebook_page' => 'url|regex:(facebook)',
        'troop_leader_name' => 'required|min:5',
        'troop_leader_phone' => 'required|regex:(^[0-9+-.()]{5,}$)',
        'troop_leader_email' => 'required|email',
        'logo' => 'image|nullable',
        'team_id' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'email',
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

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];
    
    /**
     * Scope a query to only include troops with a given team id.
     */
    public function scopeTeamId($query, $id)
    {
        return $query->where('team_id', $id);
    }
}
