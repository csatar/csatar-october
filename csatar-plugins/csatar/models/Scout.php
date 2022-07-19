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
        'team' => 'required',
        'family_name' => 'required',
        'given_name' => 'required',
        'email' => 'email',
        'phone' => 'required|regex:(^[0-9+-.()]{5,}$)',
        'personal_identification_number' => 'required',
        'gender' => 'required',
        'is_active' => 'required',
        'legal_relationship' => 'required',
        'religion' => 'required',
        'tshirt_size' => 'required',
        'birthdate' => 'required',
        'birthplace' => 'required',
        'address_country' => 'required',
        'address_zipcode' => 'required',
        'address_county' => 'required',
        'address_location' => 'required',
        'address_street' => 'required',
        'address_number' => 'required',
        'mothers_phone' => 'regex:(^[0-9+-.()]{5,}$)',
        'mothers_email' => 'email',
        'fathers_phone' => 'regex:(^[0-9+-.()]{5,}$)',
        'fathers_email' => 'email',
        'legal_representative_phone' => 'regex:(^[0-9+-.()]{5,}$)',
        'legal_representative_email' => 'email',
        'profile_image' => 'image|nullable|max:5120',
        'registration_form' => 'mimes:jpg,png,pdf|nullable|max:1536',
        'chronic_illnesses' => 'required',
        'special_diet' => 'required',
    ];

    public $attributeNames = [];

    function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->attributeNames['registration_form'] = e(trans('csatar.csatar::lang.plugin.admin.scout.registrationForm'));
        $this->attributeNames['profile_image'] = e(trans('csatar.csatar::lang.plugin.admin.scout.profile_image'));
    }

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

        // check that the birthdate is not in the future
        if (isset($this->birthdate) && (new \DateTime($this->birthdate) > new \DateTime())) {
            throw new \ValidationException(['birthdate' => \Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.dateInTheFuture')]);
        }

        // the registration form is required
        $registration_form = $this->registration_form()->withDeferred($this->sessionKey)->first();
        if (!isset($registration_form)) {
            throw new \ValidationException(['registration_form' => \Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.registrationFormRequired')]);
        }

        // the Date and Location pivot fields are required and the Date cannot be in the future
        $this->validatePivotDateAndLocationFields($this->promises, \Lang::get('csatar.csatar::lang.plugin.admin.promise.promise'));
        $this->validatePivotDateAndLocationFields($this->tests, \Lang::get('csatar.csatar::lang.plugin.admin.test.test'));
        $this->validatePivotDateAndLocationFields($this->special_tests, \Lang::get('csatar.csatar::lang.plugin.admin.specialTest.specialTest'));
        $this->validatePivotDateAndLocationFields($this->professional_qualifications, \Lang::get('csatar.csatar::lang.plugin.admin.professionalQualification.professionalQualification'));
        $this->validatePivotDateAndLocationFields($this->special_qualifications, \Lang::get('csatar.csatar::lang.plugin.admin.specialQualification.specialQualification'));
        $this->validatePivotQualificationFields($this->leadership_qualifications, \Lang::get('csatar.csatar::lang.plugin.admin.leadershipQualification.leadershipQualification'));
        $this->validatePivotQualificationFields($this->training_qualifications, \Lang::get('csatar.csatar::lang.plugin.admin.trainingQualification.trainingQualification'));
    }

    /**
     * Handle the team-troop-patrol dependencies
     */
    public function filterFields($fields, $context = null) {
        // populate the Troop and Patrol dropdowns with troops and patrols that belong to the selected team
        $fields->troop->options = [];
        $team_id = $this->team_id;
        if ($team_id) {
            foreach (\Csatar\Csatar\Models\Troop::teamId($team_id)->get() as $troop) {
                $fields->troop->options += [$troop['id'] => $troop['extendedName']];
            }
        }

        // populate the Patrol dropdown with patrols that belong to the selected team and to the selected troop
        $fields->patrol->options = [];
        $troop_id = $this->troop_id;
        if ($troop_id) {
            foreach (\Csatar\Csatar\Models\Patrol::troopId($troop_id)->get() as $patrol) {
                $fields->patrol->options += [$patrol['id'] => $patrol['extendedName']];
            }
        }
        else if ($team_id) {
            foreach (\Csatar\Csatar\Models\Patrol::teamId($team_id)->get() as $patrol) {
                $fields->patrol->options += [$patrol['id'] => $patrol['extendedName']];
            }
        }

        // populate the Legal Relationships dropdown with legal relationships that belong to the selected teamás association
        $fields->legal_relationship->options = $this->team ? \Csatar\Csatar\Models\LegalRelationship::associationId($this->team->district->association->id)->lists('name', 'id') : [];
    }

    protected $fillable = [
        'user_id',
        'team_id',
        'troop_id',
        'patrol_id',
        'name_prefix',
        'family_name',
        'given_name',
        'nickname',
        'email',
        'phone',
        'personal_identification_number',
        'gender',
        'is_active',
        'legal_relationship_id',
        'special_diet_id',
        'religion_id',
        'nationality',
        'tshirt_size_id',
        'birthdate',
        'nameday',
        'maiden_name',
        'birthplace',
        'address_country',
        'address_zipcode',
        'address_county',
        'address_location',
        'address_street',
        'address_number',
        'mothers_name',
        'mothers_phone',
        'mothers_email',
        'fathers_name',
        'fathers_phone',
        'fathers_email',
        'legal_representative_name',
        'legal_representative_phone',
        'legal_representative_email',
        'elementary_school',
        'primary_school',
        'secondary_school',
        'post_secondary_school',
        'college',
        'university',
        'other_trainings',
        'foreign_language_knowledge',
        'occupation',
        'workplace',
        'comment',
        'profile_image',
        'registration_form',
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
            'table' => 'csatar_csatar_scouts_chronic_illnesses',
            'label' => 'csatar.csatar::lang.plugin.admin.chronicIllness.chronicIllnesses',
        ],
        'allergies' => [
            '\Csatar\Csatar\Models\Allergy',
            'table' => 'csatar_csatar_scouts_allergies',
            'pivot' => ['comment'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutAllergyPivot',
            'label' => 'csatar.csatar::lang.plugin.admin.allergy.allergies',
        ],
        'food_sensitivities' => [
            '\Csatar\Csatar\Models\FoodSensitivity',
            'table' => 'csatar_csatar_scouts_food_sensitivities',
            'pivot' => ['comment'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutFoodSensitivityPivot',
            'label' => 'csatar.csatar::lang.plugin.admin.foodSensitivity.foodSensitivities',
        ],
        'promises' => [
            '\Csatar\Csatar\Models\Promise',
            'table' => 'csatar_csatar_scouts_promises',
            'pivot' => ['date', 'location'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutPromisePivot',
            'label' => 'csatar.csatar::lang.plugin.admin.promise.promises',
        ],
        'tests' => [
            '\Csatar\Csatar\Models\Test',
            'table' => 'csatar_csatar_scouts_tests',
            'pivot' => ['date', 'location'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutTestPivot',
            'label' => 'csatar.csatar::lang.plugin.admin.test.tests',
        ],
        'special_tests' => [
            '\Csatar\Csatar\Models\SpecialTest',
            'table' => 'csatar_csatar_scouts_special_tests',
            'pivot' => ['date', 'location'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutSpecialTestPivot',
            'label' => 'csatar.csatar::lang.plugin.admin.specialTest.specialTests',
        ],
        'professional_qualifications' => [
            '\Csatar\Csatar\Models\ProfessionalQualification',
            'table' => 'csatar_csatar_scouts_professional_qualifications',
            'pivot' => ['date', 'location'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutProfessionalQualificationPivot',
            'label' => 'csatar.csatar::lang.plugin.admin.professionalQualification.professionalQualifications',
        ],
        'special_qualifications' => [
            '\Csatar\Csatar\Models\SpecialQualification',
            'table' => 'csatar_csatar_scouts_special_qualifications',
            'pivot' => ['date', 'location'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutSpecialQualificationPivot',
            'label' => 'csatar.csatar::lang.plugin.admin.specialQualification.specialQualifications',
        ],
        'leadership_qualifications' => [
            '\Csatar\Csatar\Models\LeadershipQualification',
            'table' => 'csatar_csatar_scouts_leadership_qualifications',
            'pivot' => ['date', 'location', 'qualification_certificate_number', 'training_id', 'qualification_leader', 'training_name'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutLeadershipQualificationPivot',
            'label' => 'csatar.csatar::lang.plugin.admin.leadershipQualification.leadershipQualifications',
        ],
        'training_qualifications' => [
            '\Csatar\Csatar\Models\TrainingQualification',
            'table' => 'csatar_csatar_scouts_training_qualifications',
            'pivot' => ['date', 'location', 'qualification_certificate_number', 'training_id', 'qualification_leader', 'training_name'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutTrainingQualificationPivot',
            'label' => 'csatar.csatar::lang.plugin.admin.trainingQualification.trainingQualifications',
        ],
        'mandates' => [
            '\Csatar\Csatar\Models\Mandate',
            'table' => 'csatar_csatar_scouts_mandates',
            'pivot' => ['start_date', 'end_date', 'comment'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutMandatePivot',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
        ],
        'mandate_models' => [
            '\Csatar\Csatar\Models\OrganizationBase',
            'table' => 'csatar_csatar_scouts_mandates',
            'pivot' => ['start_date', 'end_date', 'comment'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutMandatePivot',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandateModels',
        ],
        'team_reports' => [
            '\Csatar\Csatar\Models\TeamReport',
            'table' => 'csatar_csatar_team_reports_scouts',
            'pivot' => ['name', 'legal_relationship_id', 'leadership_qualification_id', 'ecset_code', 'membership_fee'],
            'pivotModel' => '\Csatar\Csatar\Models\TeamReportScoutPivot',
        ],
    ];

    public $attachOne = [
        'profile_image' => 'System\Models\File',
        'registration_form' => 'System\Models\File',
    ];

    public function beforeCreate()
    {
        $this->ecset_code = strtoupper($this->generateEcsetCode());
    }

    public function beforeSave()
    {
        $this->nameday = $this->nameday != '' ? $this->nameday : null;
    }

    private function generateEcsetCode()
    {
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

    private function validatePivotDateAndLocationFields($fields, $category)
    {
        if ($fields) {
            foreach ($fields as $field) {
                if (!isset($field->pivot->date)) {
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], \Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.dateRequiredError'))]);
                }
                if (!isset($field->pivot->location) || $field->pivot->location == '') {
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], \Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.locationRequiredError'))]);
                }
                if (new \DateTime($field->pivot->date) > new \DateTime()) {
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], \Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.dateInTheFutureError'))]);
                }
            }
        }
    }

    private function validatePivotQualificationFields($fields, $category)
    {
        if ($fields) {
            $this->validatePivotDateAndLocationFields($fields, $category);
            foreach ($fields as $field) {
                if (!isset($field->pivot->qualification_certificate_number) || $field->pivot->qualification_certificate_number == '') {
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], \Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.qualificationCertificateNumberRequiredError'))]);
                }
                if (!isset($field->pivot->training_id) || $field->pivot->training_id == '') {
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], \Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.qualificationRequiredError'))]);
                }
                if (!isset($field->pivot->qualification_leader) || $field->pivot->qualification_leader == '') {
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], \Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.qualificationLeaderRequiredError'))]);
                }
            }
        }
    }

    public function getFullName(){
        return $this->family_name . ' ' . $this->given_name;
    }
}
