<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class Scout extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_scouts';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'family_name' => 'required',
        'given_name' => 'required',
        'personal_identification_number' => 'required',
        'email' => 'email'
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
            throw new \ValidationException(['troop' => \Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.troopNotInTheTeam')]);
        }

        // if the selected patrol does not belong to the selected team or to the selected troop, then throw and exception
        if ($this->patrol_id &&                                             // a Patrol is set
                ($this->patrol->team->id != $this->team_id ||               // the Patrol does not belong to the selected Team
                    ($this->troop_id &&                                     // a Troop is set as well
                        (!$this->patrol->troop ||                           // the Patrol does not belong to any Troop
                        $this->patrol->troop->id != $this->troop_id)))) {   // the Patrol belongs to a different Troop than the one selected
            throw new \ValidationException(['troop' => \Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.troopNotInTheTeamOrTroop')]);
        }
    }

    /**
     * Handle the team-troop-patrol dependencies
     */
    public function filterFields($fields, $context = null) {
        // populate the Troop and Patrol dropdowns with troops and patrols that belong to the selected team
        $team_id = $this->team_id;
        $fields->troop->options = $team_id ? \Csatar\Csatar\Models\Troop::teamId($team_id)->lists('name', 'id') : [];

        // populate the Patrol dropdown with patrols that belong to the selected team and to the selected troop
        $troop_id = $this->troop_id;
        $fields->patrol->options = $troop_id ? \Csatar\Csatar\Models\Patrol::troopId($troop_id)->lists('name', 'id') : ($team_id ? \Csatar\Csatar\Models\Patrol::teamId($team_id)->lists('name', 'id') : []);
    }

    protected $fillable = [
        'user_id',
        'family_name',
        'given_name',
        'email',
        'gender',
        'personal_identification_number',
        'is_active',
        'legal_relationship_id',
        'special_diet_id',
        'religion_id',
        'tshirt_size_id',
        'team_id',
        'troop_id',
        'patrol_id',
    ];

    /**
     * Relations
     */
    public $belongsTo = [
        'user' => '\Rainlab\User\Models\User',
        'legal_relationship' => '\Csatar\Csatar\Models\LegalRelationship',
        'special_diet' => '\Csatar\Csatar\Models\SpecialDiet',
        'religion' => '\Csatar\Csatar\Models\Religion',
        'tshirt_size' => '\Csatar\Csatar\Models\TShirtSize',
        'team' => '\Csatar\Csatar\Models\Team',
        'troop' => '\Csatar\Csatar\Models\Troop',
        'patrol' => '\Csatar\Csatar\Models\Patrol',
    ];

    public $belongsToMany = [
        'chronic_illnesses' => [
            '\Csatar\Csatar\Models\ChronicIllness',
            'table' => 'csatar_csatar_scouts_chronic_illnesses'
        ],
        'allergies' => [
            '\Csatar\Csatar\Models\Allergy',
            'table' => 'csatar_csatar_scouts_allergies',
            'pivot' => ['details']
        ]
    ];

    public function beforeCreate()
    {
        $this->ecset_code = strtoupper($this->generateEcsetCode());
    }

    private function generateEcsetCode(){

        $team = Team::find($this->team_id);

        if(empty($team)){
            throw new \ValidationException(['team_id' => \Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.noTeamSelected')]);
        }

        $sufix = $team->district->association->ecset_code_suffix ?? substr($team->district->association->name, 0, 2);

        $ecset_code = strtoupper(substr(uniqid(), 0, -3) . '-' . $sufix);

        if(Scout::where('ecset_code', $ecset_code)->exists()){
            return $this->generateEcsetCode();
        }

        return $ecset_code;
    }
}
