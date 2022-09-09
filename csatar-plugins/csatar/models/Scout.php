<?php namespace Csatar\Csatar\Models;

use Auth;
use Csatar\Csatar\Models\MandateType;
use Db;
use Lang;
use Session;
use Model;
use Csatar\Csatar\Classes\RightsMatrix;
use Csatar\Csatar\Models\Association;
use October\Rain\Database\Collection;

/**
 * Model
 */
class Scout extends OrganizationBase
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    protected static $relationLabels = null;

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
            throw new \ValidationException(['troop' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.troopNotInTheTeam')]);
        }

        // if the selected patrol does not belong to the selected team or to the selected troop, then throw and exception
        if ($this->patrol_id &&                                             // a Patrol is set
                ($this->patrol->team->id != $this->team_id ||               // the Patrol does not belong to the selected Team
                    ($this->troop_id &&                                     // a Troop is set as well
                        (!$this->patrol->troop ||                           // the Patrol does not belong to any Troop
                        $this->patrol->troop->id != $this->troop_id)))) {   // the Patrol belongs to a different Troop than the one selected
            throw new \ValidationException(['troop' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.troopNotInTheTeamOrTroop')]);
        }

        // check that the birthdate is not in the future
        if (isset($this->birthdate) && (new \DateTime($this->birthdate) > new \DateTime())) {
            throw new \ValidationException(['birthdate' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.dateInTheFuture')]);
        }

        // the registration form is required
        $registration_form = $this->registration_form()->withDeferred($this->sessionKey)->first();
        if (!isset($registration_form)) {
            throw new \ValidationException(['registration_form' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.registrationFormRequired')]);
        }

        // the Date and Location pivot fields are required and the Date cannot be in the future
        $this->validatePivotDateAndLocationFields($this->promises, Lang::get('csatar.csatar::lang.plugin.admin.promise.promise'));
        $this->validatePivotDateAndLocationFields($this->tests, Lang::get('csatar.csatar::lang.plugin.admin.test.test'));
        $this->validatePivotDateAndLocationFields($this->special_tests, Lang::get('csatar.csatar::lang.plugin.admin.specialTest.specialTest'));
        $this->validatePivotDateAndLocationFields($this->professional_qualifications, Lang::get('csatar.csatar::lang.plugin.admin.professionalQualification.professionalQualification'));
        $this->validatePivotDateAndLocationFields($this->special_qualifications, Lang::get('csatar.csatar::lang.plugin.admin.specialQualification.specialQualification'));
        $this->validatePivotQualificationFields($this->leadership_qualifications, Lang::get('csatar.csatar::lang.plugin.admin.leadershipQualification.leadershipQualification'));
        $this->validatePivotQualificationFields($this->training_qualifications, Lang::get('csatar.csatar::lang.plugin.admin.trainingQualification.trainingQualification'));

        // mandates: check that end date is not after the start date
        foreach ($this->mandates as $field) {
            if (isset($field->pivot->start_date) && isset($field->pivot->end_date) && (new \DateTime($field->pivot->end_date) < new \DateTime($field->pivot->start_date))) {
                throw new \ValidationException(['' => str_replace('%name', $field->name, Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.mandateEndDateBeforeStartDate'))]);
            }
        }
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

    public $fillable = [
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
        'team' => [
            '\Csatar\Csatar\Models\Team',
            'formBuilder' => [
                'requiredBeforeRender' => true,
            ],
        ],
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
        'team_reports' => [
            '\Csatar\Csatar\Models\TeamReport',
            'table' => 'csatar_csatar_team_reports_scouts',
            'pivot' => ['name', 'legal_relationship_id', 'leadership_qualification_id', 'ecset_code', 'membership_fee'],
            'pivotModel' => '\Csatar\Csatar\Models\TeamReportScoutPivot',
        ],
    ];

    public $hasMany = [
        'mandates' => [
            '\Csatar\Csatar\Models\Mandate',
            'table' => 'csatar_csatar_mandates',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
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
        $this->troop_id = $this->troop_id != 0 ? $this->troop_id : null;
        $this->patrol_id = $this->patrol_id != 0 ? $this->patrol_id : null;
    }

    private function generateEcsetCode()
    {
        $team = Team::find($this->team_id);

        if(empty($team)){
            throw new \ValidationException(['team_id' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.noTeamSelected')]);
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
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.dateRequiredError'))]);
                }
                if (!isset($field->pivot->location) || $field->pivot->location == '') {
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.locationRequiredError'))]);
                }
                if (new \DateTime($field->pivot->date) > new \DateTime()) {
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.dateInTheFutureError'))]);
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
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.qualificationCertificateNumberRequiredError'))]);
                }
                if (!isset($field->pivot->training_id) || $field->pivot->training_id == '') {
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.qualificationRequiredError'))]);
                }
                if (!isset($field->pivot->qualification_leader) || $field->pivot->qualification_leader == '') {
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.qualificationLeaderRequiredError'))]);
                }
            }
        }
    }

    public function getFullName()
    {
        return $this->family_name . ' ' . $this->given_name;
    }

    public function getNameAttribute()
    {
        return $this->getFullName();
    }

    /**
     * Returns the id of the association to which the item belongs to.
     */
    public function getAssociationId()
    {
        if ($this->team_id) {
            return $this->team->district->association->id;
        }

        return null;
    }

    public function scopeOrganization($query, $mandate_model_type, $mandate_model_id)
    {
        switch ($mandate_model_type) {
            case Association::getOrganizationTypeModelName():
                $districts = \Csatar\Csatar\Models\District::where('association_id', $mandate_model_id)->lists('id');
                $teams = \Csatar\Csatar\Models\Team::whereIn('district_id', $districts)->lists('id');
                return $query->whereIn('team_id', $teams);

            case District::getOrganizationTypeModelName():
                $teams = \Csatar\Csatar\Models\Team::where('district_id', $mandate_model_id)->lists('id');
                return $query->whereIn('team_id', $teams);

            case Team::getOrganizationTypeModelName():
                return $query->where('team_id', $mandate_model_id);

            default:
                return $query->whereNull('id');
        }
    }

    /*
     * Returns all the mandates scout has in a specific association
     */
    public function getMandatesInAssociation($associationId, $savedAfterDate = null): Collection
    {
        $sessionRecord = Session::get('scout.mandates');

        if(!empty($sessionRecord) && $sessionRecordForAssociation = $sessionRecord->where('associationId', $associationId)->first()) {
            if($sessionRecordForAssociation['savedToSession'] >= $savedAfterDate) {
                //TODO: implement touch scout when mandate is added or removed CS-288
                return new Collection($sessionRecordForAssociation['mandates']);
            }
        }

        //get all mandate type ids from association
        $mandateTypeIdsInAssociation = MandateType::getAllMandateTypeIdsInAssociation($associationId);

        //get scout's mandates with the above mandate types and pluck mandate_type_ids
        $scoutMandates = $this->mandates()
            ->whereIn('mandate_type_id', $mandateTypeIdsInAssociation)
            ->where('start_date', '<=', date('Y-m-d H:i'))
            ->where('end_date', '>=', date('Y-m-d H:i'))
            ->get();

        if(empty($sessionRecord)){
            $sessionRecord = new Collection([]);
        }

        $sessionRecord = $sessionRecord->replace([ $associationId => [
            'associationId' => $associationId,
            'savedToSession' => date('Y-m-d H:i'),
            'mandates'=> ($scoutMandates)->toArray(),
        ]]);

        Session::put('scout.mandates', $sessionRecord);

        return $scoutMandates;
    }

    public function getMandateTypeIdsInAssociation($associationId, $savedAfterDate = null, $ignoreCache = false)
    {
        $sessionRecord = $ignoreCache ? null : Session::get('scout.mandateTypeIds');

        if(!empty($sessionRecord) && $sessionRecordForAssociation = $sessionRecord->where('associationId', $associationId)->first()) {
            if($sessionRecordForAssociation['savedToSession'] >= $savedAfterDate) {
                //TODO: implement touch scout when mandate is added or removed CS-288
                return $sessionRecordForAssociation['mandateTypeIds'];
            }
        }

        if(empty($sessionRecord)){
            $sessionRecord = new Collection([]);
        }

        $scoutMandateTypeIds = array_merge($this->getMandatesInAssociation($associationId, $savedAfterDate)->pluck('mandate_type_id')->toArray(), MandateType::getScoutMandateTypeIdInAssociation($associationId));

        $sessionRecord = $sessionRecord->replace([ $associationId => [
            'associationId' => $associationId,
            'savedToSession' => date('Y-m-d H:i'),
            'mandateTypeIds'=> $scoutMandateTypeIds,
        ]]);

        Session::put('scout.mandateTypeIds', $sessionRecord);
        return $scoutMandateTypeIds;
    }

    public function getMandatesForOrganization(OrganizationBase $organization) {
        return $this->getMandatesInAssociation($organization->getAssociationId(), $this->updated_at);
    }

    public function saveMandateTypeIdsForEveryAssociationToSession(){
        $associationIds = Association::all()->pluck('id');

        if(empty($associationIds)){
            return;
        }

        foreach($associationIds as $associationId){
            $this->getMandateTypeIdsInAssociation($associationId, false, true);
        }
    }

    public function getRightsForModel($model, $ignoreCache = false){

        if (empty($model)) {
            return;
        }

        $associationId  = $model->getAssociationId();
        $mandateTypeIds = $this->getMandateTypeIdsInAssociation($associationId, $this->updated_at, $ignoreCache);

        $isOwn = false;
        if(Auth::user() && !empty(Auth::user()->scout)){
            $isOwn = $model->isOwnOrganization(Auth::user()->scout);
        }

        $is2fa = false;
        if(Auth::user() && !empty(Auth::user()->twoFA)){ //TODO: this will be implemented with task CS-287
            $is2fa = true;
        }

        $rightsForModel = $ignoreCache ? null : $this->getRightsForModelFromSession($model, $associationId, $isOwn, $is2fa);

        if(empty($rightsForModel) || $rightsForModel->count() == 0) {
            $rightsForModel = $model->getRightsForMandateTypes($mandateTypeIds, $isOwn, $is2fa, $ignoreCache);
            $this->saveRightsForModelToSession($model, $rightsForModel, $associationId, $isOwn, $is2fa);
//            dd('if', $rightsForModel);
        }

        return $rightsForModel;
    }

    public function getRightsForModelFromSession($model, $associationId, $own = false, $twoFA = false) {
        $sessionRecord = Session::get('scout.rightsForModels');

        if(empty($sessionRecord) || empty($model) || empty($associationId)) {
            return;
        }

        $key = $associationId . $model::getOrganizationTypeModelName() . ($own ? '_own' : '') . ($twoFA ? '_2fa' : '');

        $sessionRecordForModel = $sessionRecord->get($key);

        if (!empty($sessionRecordForModel) && $sessionRecordForModel['savedToSession'] >= RightsMatrix::getRightsMatrixLastUpdateTime() && $sessionRecordForModel['rights']->count() != 0) {
           return $sessionRecordForModel['rights'];
        }

        return null;
    }

    public function saveRightsForModelToSession($model, $rightsForModel, $associationId, $own, $twoFA) {
        $modelName = $model::getOrganizationTypeModelName();
        $key = $associationId . $modelName . ($own ? '_own' : '') . ($twoFA ? '_2fa' : '');
        $sessionRecord = Session::get('scout.rightsForModels');

        if(empty($sessionRecord)){
            $sessionRecord = new Collection([]);
        }

        $sessionRecord = $sessionRecord->replace([
            $key => [
                'associationId' => $associationId,
                'model' => $modelName,
                'own' => $own,
                'twoFA' => $twoFA,
                'savedToSession' => date('Y-m-d H:i'),
                'rights'=> $rightsForModel,
            ],
        ]);

        Session::put('scout.rightsForModels', $sessionRecord);
    }

    public function isOwnOrganization($scout){
        return $this->id === $scout->id;
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.csatar::lang.plugin.admin.scout.scout');
    }

}
