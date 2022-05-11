<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class Team extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_teams';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'team_number' => 'required|numeric|max:4',
        'address' => 'required|min:5',
        'foundation_date' => 'required',
        'phone' => 'required|regex:(^[0-9+-.()]{5,}$)',
        'email' => 'required|email',
        'website' => 'url',
        'facebook_page' => 'url|regex:(facebook)',
        'contact_name' => 'required|min:5',
        'contact_email' => 'required|email',
        'leadership_presentation' => 'required',
        'description' => 'required',
        'juridical_person_name' => 'required',
        'juridical_person_address' => 'required|min:5',
        'juridical_person_tax_number' => 'required',
        'juridical_person_bank_account' => 'required|min:5',
        'district_id' => 'required',
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate() {
        // if we don't have all the data for this validation, then return. The 'required' validation rules will be triggered
        if (!$this->district || !$this->team_number) {
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
                throw new \ValidationException(['team_number' => \Lang::get('csatar.csatar::lang.plugin.admin.team.teamNumberTakenError')]);
            }
        }
    }

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'team_number',
        'foundation_date',
        'phone',
        'email',
        'contact_name',
        'contact_email',
        'address',
        'leadership_presentation',
        'description',
        'juridical_person_name',
        'juridical_person_address',
        'juridical_person_tax_number',
        'juridical_person_bank_account',
        'district_id',
    ];
    
    /**
     * Relations
     */
    
    public $belongsTo = [
        'district' => '\Csatar\Csatar\Models\District',
    ];

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];
    
    /**
     * Scope a query to only include teams with a given district id.
     */
    public function scopeDistrictId($query, $id)
    {
        return $query->where('district_id', $id);
    }
}
