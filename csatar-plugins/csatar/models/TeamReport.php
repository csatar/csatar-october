<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class TeamReport extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_team_reports';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'team_id' => 'required',
        'number_of_adult_patrols' => 'required|numeric|min:0',
        'number_of_explorer_patrols' => 'required|numeric|min:0',
        'number_of_scout_patrols' => 'required|numeric|min:0',
        'number_of_cub_scout_patrols' => 'required|numeric|min:0',
        'number_of_mixed_patrols' => 'required|numeric|min:0',
        'scouting_year_report_team_camp' => 'required',
        'scouting_year_report_homesteading' => 'required',
        'scouting_year_report_programs' => 'required',
        'scouting_year_team_applications' => 'required',
        'spiritual_leader_name' => 'required',
        'spiritual_leader_religion_id' => 'required',
        'spiritual_leader_occupation' => 'required',
    ];

    protected $fillable = [
        'team_id',
        'year',
        'number_of_adult_patrols',
        'number_of_explorer_patrols',
        'number_of_scout_patrols',
        'number_of_cub_scout_patrols',
        'number_of_mixed_patrols',
        'scouting_year_report_team_camp',
        'scouting_year_report_homesteading',
        'scouting_year_report_programs',
        'scouting_year_team_applications',
        'spiritual_leader_name',
        'spiritual_leader_religion_id',
        'spiritual_leader_occupation',
        'team_fee',
        'total_amount',
        'currency_id',
    ];
    
    /**
     * Relations
     */
    public $belongsTo = [
        'team' => '\Csatar\Csatar\Models\Team',
        'spiritual_leader_religion' => '\Csatar\Csatar\Models\Religion',
        'currency' => '\Csatar\Csatar\Models\Currency',
    ];

    /**
     * Set additional data
     */
    public function beforeCreate()
    {
        $this->team = Team::find($this->team_id);
        $association = $this->team->district->association;

        $this->year = date('n') == 1 ? date('Y') - 1 : date('Y');
        $this->team_fee = $association->team_fee;
        $this->total_amount = $this->team_fee;
        $this->currency_id = $association->currency_id;
    }
}
