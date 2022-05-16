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
