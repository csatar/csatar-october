<?php
namespace Csatar\Csatar\Models;

use Auth;
use Cache;
use Carbon\Carbon;
use Csatar\Csatar\Classes\Enums\Status;
use Csatar\Csatar\Classes\RightsMatrix;
use Csatar\Csatar\Classes\StructureTree;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\Mandate;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\MembershipCard;
use Csatar\Csatar\Classes\Validators\CnpValidator;
use DateTime;
use Db;
use Flash;
use Lang;
use Log;
use Model;
use October\Rain\Database\Collection;
use Session;
use ValidationException;
use Rainlab\Location\Models\Country;

/**
 * Model
 */
class Scout extends OrganizationBase
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \October\Rain\Database\Traits\Nullable;

    use \Csatar\Csatar\Traits\History;

    public const NAME_DELETED_INACTIVITY = 'Inaktivítás miatt törölt név';

    protected $dates = ['deleted_at'];

    protected static $relationLabels = null;
    public $active_mandates          = [];

    /**
     * @var bool skipCacheRefresh
     * If set to true, the cache will not be refreshed after save
     * Usefull after bulk import, in such cases cache refresh should be done after all records are imported
     */
    public bool $skipCacheRefresh = false;

    /**
     * @var array The columns that should be searchable by ContentPageSearchProvider
     */
    protected static $searchable = ['family_name', 'given_name'];

    protected $appends = ['full_name', 'legal_relationship_name'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_scouts';

    public $fillable = [
        'ecset_code',
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
        'citizenship_country_id',
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
        'chronic_illnesses',
        'is_approved',
    ];

    protected $nullable = [
        'user_id',
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
        'mothers_email',
        'fathers_name',
        'fathers_email',
        'legal_representative_name',
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
        'ecset_code',
        'is_approved',
    ];

    public $additionalFieldsForPermissionMatrix = [
        'leaderTrainingHtml',
    ];

    protected $jsonable = ['raw_import'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'team' => 'required',
        'troop' => 'nullable',
        'patrol' => 'nullable',
        'phone' => 'nullable|regex:(^[0-9+-.()]{10,}$)',
        'birthdate' => 'required',
        'citizenship_country' => 'required',
        'legal_representative_phone' => 'nullable|regex:(^[0-9+-.()]{10,}$)',
        'personal_identification_number' => 'nullable',
        'mothers_phone' => 'nullable|regex:(^[0-9+-.()]{10,}$)',
        'fathers_phone' => 'nullable|regex:(^[0-9+-.()]{10,}$)',
        'email' => 'email|nullable',
        'mothers_email' => 'email|nullable',
        'fathers_email' => 'email|nullable',
        'legal_representative_email' => 'email|nullable',
        'profile_image' => 'image|nullable|max:5120',
        'registration_form' => 'mimes:jpg,png,pdf|nullable|max:1536',
    ];

    public $customMessages = [];


    // this conditional rules work in form builder if there are functions defined with validationFunctionName name in class
    public $conditionalRules = [
        [
            'fields' => ['legal_representative_phone'],
            'rules' => 'required_without_all:mothers_phone,fathers_phone',
            'validationFunctionName' => 'legalRepresentativePhoneForUnderAge'
        ],
        [
            'fields' => ['mothers_phone'],
            'rules' => 'required_without_all:legal_representative_phone,fathers_phone',
            'validationFunctionName' => 'legalRepresentativePhoneForUnderAge'
        ],
        [
            'fields' => ['fathers_phone'],
            'rules' => 'required_without_all:legal_representative_phone,mothers_phone',
            'validationFunctionName' => 'legalRepresentativePhoneForUnderAge'
        ]
    ];

    public function legalRepresentativePhoneForUnderAge($input) {
        $birthdate    = strtotime($input->birthdate);
        $birthday18th = strtotime('+18 years', $birthdate);
        return $birthday18th > time();
    }

    public $attributeNames = [];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->attributeNames['phone'] = e(trans('csatar.csatar::lang.plugin.admin.general.phone'));
        $this->attributeNames['team']  = e(trans('csatar.csatar::lang.plugin.admin.scout.team'));
        $this->attributeNames['registration_form'] = e(trans('csatar.csatar::lang.plugin.admin.scout.registrationForm'));
        $this->attributeNames['profile_image']     = e(trans('csatar.csatar::lang.plugin.admin.scout.profile_image'));
        $this->attributeNames['personal_identification_number']     = e(trans('csatar.csatar::lang.plugin.admin.scout.personalIdentificationNumber'));
        $this->customMessages['mothers_phone.required_without_all'] = e(trans('csatar.csatar::lang.plugin.admin.scout.validationExceptions.legalRepresentativePhoneUnderAge'));
        $this->customMessages['fathers_phone.required_without_all'] = e(trans('csatar.csatar::lang.plugin.admin.scout.validationExceptions.legalRepresentativePhoneUnderAge'));
        $this->customMessages['legal_representative_phone.required_without_all'] = e(trans('csatar.csatar::lang.plugin.admin.scout.validationExceptions.legalRepresentativePhoneUnderAge'));
        $this->customMessages['personal_identification_number.unique']           = e(trans('csatar.csatar::lang.plugin.admin.scout.validationExceptions.uniquePersonalIdentificationNumber'));
    }

    /**
     * Add custom validation
     */
    public function beforeValidate() {
        if (isset($this->is_active) && $this->is_active == 0 && $this->getOriginalValue('inactivated_at') == null) {
            $this->inactivated_at = date('Y-m-d H:i:s');
        }

        if ($this->is_active == 1 && $this->getOriginalValue('inactivated_at') != null) {
            $this->inactivated_at = null;
        }

        unset($this->is_active);

        if (!$this->ignoreValidation) {
            // if we don't have all the data for this validation, then return. The 'required' validation rules will be triggered
            if (!isset($this->team_id)) {
                return;
            }

            // personal id number validations, for active scouts only
            if ($this->inactivated_at == null) {
                $personalIdentificationNumberValidators = $this->getPersonalIdentificationNumberValidators();

                if (in_array('cnp', $personalIdentificationNumberValidators) && $this->shouldNotValidateCnp()) {
                    unset($personalIdentificationNumberValidators[array_search('cnp', $personalIdentificationNumberValidators)]);
                }

                if (!empty($personalIdentificationNumberValidators)) {
                    $this->rules['personal_identification_number'] .= '|' . implode('|', $personalIdentificationNumberValidators);
                }

                if (in_array('cnp', $personalIdentificationNumberValidators)
                    && !empty($this->personal_identification_number)
                    && (new DateTime($this->birthdate))->format('Y-m-d') != $this->getBirthDateFromCNP($this->personal_identification_number)
                ) {
                    throw new \ValidationException(['birthdate' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.personalIdentificationNumberBirthdateMismatch')]);
                }
            }

            // if the selected troop does not belong to the selected team, then throw and exception
            if ($this->troop && $this->troop->team->id != $this->team_id) {
                throw new \ValidationException(['troop' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.troopNotInTheTeam')]);
            }

            // if troop_id is 0, it should be set to null and $this->troop should be set to null as well
            if ($this->troop_id === 0 || $this->troop_id === '0' || $this->troop_id === 'null') {
                $this->troop_id = null;
                $this->troop    = null;
            }

            // if the selected patrol does not belong to the selected team or to the selected troop, then throw and exception
            if ($this->patrol &&                                             // a Patrol is set
                ($this->patrol->team->id != $this->team_id ||               // the Patrol does not belong to the selected Team
                    ($this->troop &&                                     // a Troop is set as well
                        (!$this->patrol->troop ||                           // the Patrol does not belong to any Troop
                            $this->patrol->troop->id != $this->troop_id)))) {   // the Patrol belongs to a different Troop than the one selected
                throw new \ValidationException(['patrol' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.troopNotInTheTeamOrTroop')]);
            }

            // check that the birthdate is not in the future
            if (isset($this->birthdate) && (new \DateTime($this->birthdate) > new \DateTime())) {
                throw new \ValidationException(['birthdate' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.dateInTheFuture')]);
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
    }

    public function afterSave() {
        if (isset($this->original['inactivated_at'])
            && $this->inactivated_at != $this->original['inactivated_at']
            && $this->original['inactivated_at'] == null) {
            Mandate::where('scout_id', $this->id)->update(['end_date' => date('Y-m-d')]);

            if (!empty($this->membership_cards)) {
                MembershipCard::where('scout_id', $this->id)->where('active', Status::ACTIVE)->update(['active' => Status::INACTIVE]);
            }
        }

        if (!$this->skipCacheRefresh) {
            $this->updateCache();
        }

    }

    public function afterDelete() {
        $this->inactivated_at   = date('Y-m-d H:i:s');
        $this->ignoreValidation = true;
        $this->forceSave();

        if ($this->skipCacheRefresh) {
            return;
        }

        if (!empty($this->team_id)) {
            StructureTree::updateTeamTree($this->team_id);
        }
    }

    public function updateCache(): void
    {
        if ($this->wasRecentlyCreated && $this->inactivated_at == null) {
            StructureTree::updateTeamTree($this->team_id);
        }

        if (empty($this->original)) {
            return;
        }

        if (($this->getOriginalValue('inactivated_at') != $this->inactivated_at) || $this->deleted_at != null) {
            StructureTree::updateTeamTree($this->team_id);
        }

        if (($this->getOriginalValue('team_id') != $this->team_id)
            || ($this->getOriginalValue('troop_id') != $this->troop_id)
            || ($this->getOriginalValue('patrol_id') != $this->patrol_id)
        ) {
            StructureTree::updateTeamTree($this->team_id);
            if (!empty($this->original['team_id'])) {
                StructureTree::updateTeamTree($this->original['team_id']);
            }
        }

        if (($this->getOriginalValue('family_name') != $this->family_name)
            || ($this->getOriginalValue('given_name') != $this->given_name)
            || ($this->getOriginalValue('ecset_code') != $this->ecset_code)
            || ($this->getOriginalValue('legal_relationship_id') != $this->legal_relationship_id)
        ) {
            $structureTree = Cache::pull('structureTree');
            if (empty($structureTree)) {
                StructureTree::handleEmptyStructureTree();
                return;
            }

            $teamsActive = $structureTree[$this->team->district->association_id]['districtsActive'][$this->team->district_id]['teamsActive'];

            $teamsActive[$this->team->id]['scoutsActive'][$this->id]['family_name'] = $this->family_name;
            $teamsActive[$this->team->id]['scoutsActive'][$this->id]['given_name']  = $this->given_name;
            $teamsActive[$this->team->id]['scoutsActive'][$this->id]['full_name']   = $this->full_name;
            $teamsActive[$this->team->id]['scoutsActive'][$this->id]['ecset_code']  = $this->ecset_code;
            $teamsActive[$this->team->id]['scoutsActive'][$this->id]['legal_relationship_id']   = $this->legal_relationship_id;
            $teamsActive[$this->team->id]['scoutsActive'][$this->id]['legal_relationship_name'] = $this->legal_relationship_name;
            $teamsActive[$this->team->id]['scoutsActive'][$this->id]['legal_relationship']      = $this->legal_relationship ? $this->legal_relationship->toArray() : null;

            if (isset($this->patrol_id)) {
                $scoutsActive = $teamsActive[$this->team->id]['patrolsActive'][$this->patrol_id]['scoutsActive'];

                $scoutsActive[$this->id]['family_name'] = $this->family_name;
                $scoutsActive[$this->id]['given_name']  = $this->given_name;
                $scoutsActive[$this->id]['full_name']   = $this->full_name;
                $scoutsActive[$this->id]['ecset_code']  = $this->ecset_code;
                $scoutsActive[$this->id]['legal_relationship_id']   = $this->legal_relationship_id;
                $scoutsActive[$this->id]['legal_relationship_name'] = $this->legal_relationship_name;
                $scoutsActive[$this->id]['legal_relationship']      = $this->legal_relationship ? $this->legal_relationship->toArray() : null;

                $teamsActive[$this->team->id]['patrolsActive'][$this->patrol_id]['scoutsActive'] = $scoutsActive;
            }

            if (isset($this->troop_id)) {
                $scoutsActive = $teamsActive[$this->team->id]['troopsActive'][$this->troop_id]['scoutsActive'];

                $scoutsActive[$this->id]['family_name'] = $this->family_name;
                $scoutsActive[$this->id]['given_name']  = $this->given_name;
                $scoutsActive[$this->id]['full_name']   = $this->full_name;
                $scoutsActive[$this->id]['ecset_code']  = $this->ecset_code;
                $scoutsActive[$this->id]['legal_relationship_id']   = $this->legal_relationship_id;
                $scoutsActive[$this->id]['legal_relationship_name'] = $this->legal_relationship_name;
                $scoutsActive[$this->id]['legal_relationship']      = $this->legal_relationship ? $this->legal_relationship->toArray() : null;

                $teamsActive[$this->team->id]['troopsActive'][$this->troop_id]['scoutsActive'] = $scoutsActive;
            }

            if (isset($this->troop_id) && isset($this->patrol_id)) {
                $scoutsActive = $teamsActive[$this->team->id]['troopsActive'][$this->troop_id]['patrolsActive'][$this->patrol_id]['scoutsActive'];

                $scoutsActive[$this->id]['family_name'] = $this->family_name;
                $scoutsActive[$this->id]['given_name']  = $this->given_name;
                $scoutsActive[$this->id]['full_name']   = $this->full_name;
                $scoutsActive[$this->id]['ecset_code']  = $this->ecset_code;
                $scoutsActive[$this->id]['legal_relationship_id']   = $this->legal_relationship_id;
                $scoutsActive[$this->id]['legal_relationship_name'] = $this->legal_relationship_name;
                $scoutsActive[$this->id]['legal_relationship']      = $this->legal_relationship ? $this->legal_relationship->toArray() : null;

                $teamsActive[$this->team->id]['troopsActive'][$this->troop_id]['patrolsActive'][$this->patrol_id]['scoutsActive'] = $scoutsActive;
            }

            $structureTree[$this->team->district->association_id]['districtsActive'][$this->team->district_id]['teamsActive'] = $teamsActive;
            Cache::forever('structureTree', $structureTree);
        }
    }

    public function initFromForm()
    {
        // set the mandates of the user
        $mandates = Mandate::where('scout_id', $this->id)
            ->where('start_date', '<=', date('Y-m-d H:i'))
            ->where(function ($query) {
                $query->whereNull('end_date')
                  ->orWhere('end_date', '>=', date('Y-m-d H:i'));
            })
            ->with('mandate_type')
            ->get();

        // first add the team mandates in the mandates list
        foreach ($mandates as $key => $value) {
            if ($value->mandate_model_type == '\Csatar\Csatar\Models\Team') {
                array_push($this->active_mandates, [
                    'title' => '',
                    'value' => $value->mandate_type->name ?? '',
                ]);
            }
        }

        // add all other mandates to the mandates list
        foreach ($mandates as $key => $value) {
            if ($value->mandate_model_type != '\Csatar\Csatar\Models\Team') {
                array_push($this->active_mandates, [
                    'title' => $value->mandate_model_name,
                    'value' => $value->mandate_type->name ?? ''
                ]);
            }
        }

        $personalIdentificationNumberValidators = $this->getPersonalIdentificationNumberValidators();

        if (!empty($personalIdentificationNumberValidators)) {
            $this->rules['personal_identification_number'] .= '|' . implode('|', $personalIdentificationNumberValidators);
        }
    }

    /**
     * Handle the team-troop-patrol dependencies
     */
    public function filterFields($fields, $context = null) {
        $this->handleTroopDropdown($fields);
        $this->handlePatrolDopdown($fields);
        $this->handleLegalRelationshipDropdown($fields);
        $this->handlePersonalIdentificationNumberField($fields);
        $this->handleAddressFields($fields);
        $this->handleCitizenshipField($fields);
        $this->handleIsActiveField($fields);
    }

    /**
     * Relations
     */
    public $belongsTo = [
        'user' => [
            '\Rainlab\User\Models\User',
            'label' => 'csatar.csatar::lang.plugin.admin.scout.user',
        ],
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
        'citizenship_country' => [
            '\Rainlab\Location\Models\Country',
            'label' => 'csatar.csatar::lang.plugin.admin.scout.citizenship_country',
            'key' => 'citizenship_country_id',
        ],
    ];

    public $belongsToMany = [
        'chronic_illnesses' => [
            '\Csatar\Csatar\Models\ChronicIllness',
            'table' => 'csatar_csatar_scouts_chronic_illnesses',
            'pivot' => ['comment'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutChronicIllnessPivot',
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
            'label' => 'csatar.csatar::lang.plugin.admin.teamReport.teamReports',
        ],
        'methodologies' => [
            '\Csatar\Csatar\Models\Methodology',
            'key' => 'ecset_code'
        ]
    ];

    public $hasMany = [
        'mandates' => [
            '\Csatar\Csatar\Models\Mandate',
            'table' => 'csatar_csatar_mandates',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
        ],
        'mandatesInactive' => [
            '\Csatar\Csatar\Models\Mandate',
            'scope' => 'inactive',
            'table' => 'csatar_csatar_mandates',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
        ],
        'membership_cards' => \Csatar\Csatar\Models\MembershipCard::class
    ];

    public $attachOne = [
        'profile_image' => 'System\Models\File',
        'registration_form' => 'System\Models\File',
    ];

    public static function getEagerLoadSettings(string $useCase = null): array
    {
        $eagerLoadSettings = parent::getEagerLoadSettings($useCase);
        if ($useCase === 'formBuilder') {
            // Important to extend the eager load settings, not to overwrite them!
            $eagerLoadSettings = array_merge_recursive($eagerLoadSettings, [
                'allergies',
                'chronic_illnesses',
                'food_sensitivities',
                'promises',
                'tests',
                'special_tests',
                'professional_qualifications',
                'special_qualifications',
                'leadership_qualifications',
                'training_qualifications',
                'team',
                'team.district',
                'team.district.association',
                'troop',
                'patrol',
            ]);
        }

        return $eagerLoadSettings;
    }

    public function getTeamOptions() {
        $teams       = Team::forDropdown()->get();
        $teamOptions = [];
        foreach ($teams as $team) {
            $teamOptions[$team->id] = $team->extended_name_with_association;
        }

        return $teamOptions;
    }

    public function getCitizenshipCountryOptions() {
        $countries      = Country::all();
        $countryOptions = [];
        foreach ($countries as $country) {
            $countryOptions[$country->id] = $country->getAttributeTranslated('name', 'hu');
            // this is a hardcoded language setting, will should be solved with task CS-521
        }

        return $countryOptions;
    }

    public function beforeCreate()
    {
        $this->ecset_code = isset($this->ecset_code) && !empty($this->ecset_code) ? $this->ecset_code : strtoupper($this->generateEcsetCode());
    }

    public function beforeSave()
    {
        if (isset($this->original['team_id']) && $this->original['team_id'] != $this->team_id) {
            $team   = Team::find($this->original['team_id']);
            $troop  = Troop::find($this->original['troop_id']);
            $patrol = Patrol::find($this->original['patrol_id']);

            if (!empty($team)) {
                $this->setAllMandatesToExpiredInOrganization($team);
            }

            if (!empty($troop)) {
                $this->setAllMandatesToExpiredInOrganization($troop);
                $this->troop_id = $troop->id == $this->troop_id ? null : $this->troop_id;
            }

            if (!empty($patrol)) {
                $this->setAllMandatesToExpiredInOrganization($patrol);
                $this->patrol_id = $patrol->id == $this->patrol_id ? null : $this->patrol_id;
            }
        }

        $this->nameday   = $this->nameday != '' ? $this->nameday : null;
        $this->troop_id  = $this->troop_id != 0 ? $this->troop_id : null;
        $this->patrol_id = $this->patrol_id != 0 ? $this->patrol_id : null;

        // if troop is set to null and patrol is not changed, patrol should be set to null, but if patrols is changed as well, we keep the new patrol setting and change troop accordingly
        if ($this->getOriginalValue('troop_id') != $this->troop_id && empty($this->troop_id)) {
            if ($this->getOriginalValue('patrol_id') == $this->patrol_id) {
                $this->patrol_id = null;
            }
        }

        // when patrol is changed, troop should be changed to the troop of the patrol
        if ($this->getOriginalValue('patrol_id') != $this->patrol_id) {
            $patrol         = Patrol::find($this->patrol_id);
            $this->troop_id = $patrol ? $patrol->troop_id : null;
        }
    }

    public function beforeDelete()
    {
        $now      = new DateTime();
        $mandates = Mandate::where('scout_id', $this->id)->get();
        foreach ($mandates as $mandate) {
            if (new DateTime($mandate->start_date) < $now && (new DateTime($mandate->end_date) > $now || $mandate->end_date == null)) {
                $sessionKey = self::getModelName() . $this->id;
                Session::put($sessionKey, str_replace('%name', $this->getFullName(), Lang::get('csatar.csatar::lang.plugin.admin.scout.activeMandateDeleteError')));
                return false;
            }
        }
    }

    private function shouldNotValidateCnp() {
        return empty($this->citizenship_country_id) || (!empty($this->citizenship_country_id) && $this->citizenship_country_id != (new CnpValidator())->getRomaniaCountryId());
    }

    public function setAllMandatesToExpiredInOrganization(OrganizationBase $organization): void
    {
        if (!empty($organization) && $mandates = $this->getMandatesForOrganization($organization, true)) {
            foreach ($mandates as $mandate) {
                $mandate->ignoreValidation = true;
                $mandate->end_date         = (new DateTime($mandate->end_date) > new DateTime() || $mandate->end_date == null) ? date('Y-m-d') : $mandate->end_date;
                $mandate->save();
            }
        }
    }

    private function generateEcsetCode()
    {
        $team = Team::find($this->team_id);

        if (empty($team)) {
            throw new \ValidationException(['team_id' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.noTeamSelected')]);
        }

        $sufix = $team->district->association->ecset_code_suffix ?? substr($team->district->association->name, 0, 2);

        $uid        = uniqid();
        $ecset_code = strtoupper(substr($uid, 0, 6) . '-' . $sufix);

        if ($this->ecsetCodeExists($ecset_code)) {
            $ecset_code = strtoupper(substr($uid, 6, 6) . '-' . $sufix);
        }

        if ($this->ecsetCodeExists($ecset_code)) {
            return $this->generateEcsetCode();
        }

        return $ecset_code;
    }

    private function ecsetCodeExists(string $code): bool
    {
        return Scout::where('ecset_code', $code)->exists();
    }

    private function validatePivotDateAndLocationFields($fields, $category)
    {
        if ($fields) {
            foreach ($fields as $field) {
                if (!isset($field->pivot->date)) {
                    $error = Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.dateRequiredError');
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], $error)]);
                }

                if (!isset($field->pivot->location) || $field->pivot->location == '') {
                    $error = Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.locationRequiredError');
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], $error)]);
                }

                if (new \DateTime($field->pivot->date) > new \DateTime()) {
                    $error = Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.dateInTheFutureError');
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], $error)]);
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
                    $error = Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.qualificationCertificateNumberRequiredError');
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], $error)]);
                }

                if (!isset($field->pivot->training_id) || $field->pivot->training_id == '') {
                    $error = Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.qualificationRequiredError');
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], $error)]);
                }

                if (!isset($field->pivot->qualification_leader) || $field->pivot->qualification_leader == '') {
                    $error = Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.qualificationLeaderRequiredError');
                    throw new \ValidationException(['' => str_replace(['%name', '%category'], [$field->name, $category], $error)]);
                }
            }
        }
    }

    public function getFullName()
    {
        $fullName = $this->family_name . ' ' . $this->given_name;
        return $fullName != ' ' ? $fullName : '';
    }

    public function getFullNameAttribute()
    {
        $fullName = $this->family_name . ' ' . $this->given_name;
        return $fullName != ' ' ? $fullName : '';
    }

    public function getLeaderTrainingHtmlAttribute(): string
    {
        $linkText = Lang::get('csatar.csatar::lang.plugin.component.general.login');
        $vkUrl    = \Config::get('csatar.csatar::vkUrl');
        $vkData   = $this->getVkData();
        return "<a href='$vkUrl?data=$vkData' target='_blank'>$linkText <span class='bi bi-box-arrow-up-right'></span></a>";
    }

    private function getVkData(): string
    {
        $birthdate           = strtotime($this->birthdate);
        $legalRepresentative = $this->mothers_name ? "mothers" : ($this->fathers_name ? 'fathers' : ($this->legal_representative_name ? 'legal_representative' : false));
        $legalRepresentativeName  = $legalRepresentative ? $this->{$legalRepresentative . '_name'} : '';
        $legalRepresentativePhone = $legalRepresentative ? $this->{$legalRepresentative . '_phone'} : '';
        $scoutData = [
            'basic' => [
                "firstname" => $this->given_name ?? '',
                "lastname" => $this->family_name ?? '',
                "email" => $this->email ?? '',
            ],
            'profile' => [
                'ECSK' => $this->ecset_code,
                'mobil' => $this->phone ?? '',
                "szev" => date("Y", $birthdate),
                "szhonap" => (Carbon::parse($this->birthdate))->locale('hu')->monthName, // This is needed because vk expects month in this format.
                "sznap" => date("d", $birthdate),
                "gondviselonev" => $legalRepresentativeName,
                "gondviselotelefon" => $legalRepresentativePhone,
                "Szemlyiszm" => $this->personal_identification_number ?? '',
                "csszam" => $this->team->team_number ?? '',
                "csnev" => $this->team->name ?? '',
            ],
        ];

        $encryptionKey        = \Config::get('csatar.csatar::moddleEncryptionKey');
        $initializationVector = openssl_random_pseudo_bytes(16);
        $encryptedData        = openssl_encrypt(serialize($scoutData), 'aes-256-cbc', $encryptionKey, 0, $initializationVector);

        return json_encode(
            [
                base64_encode($encryptedData),
                base64_encode($initializationVector)
            ]
        );
    }

    public function getAssociation() {
        return $this->team->district->association ?? null;
    }

    public function getDistrict() {
        return $this->team->district ?? null;
    }

    public function getTeam() {
        return $this->team_id ? $this->team : null;
    }

    public function getTroop() {
        return $this->troop_id ? $this->troop : null;
    }

    public function getPatrol() {
        return $this->patrol_id ? $this->patrol : null;
    }

    public function getNameAttribute()
    {
        return $this->getFullName();
    }

    public function getNameWithIdNumberAttribute()
    {
        return $this->getFullName() . ' - ' . $this->ecset_code;
    }

    public function getScoutLinkAttribute()
    {
        return '<a href="' . url("/tag/$this->ecset_code") . '">' . $this->getFullName() . '</a>';
    }

    public function getLegalRelationshipName()
    {
        if (empty($this->legal_relationship_id)) {
            return '';
        }

        return $this->legal_relationship->name;
    }

    public function getLegalRelationshipNameAttribute()
    {
        if (empty($this->legal_relationship_id)) {
            return '';
        }

        return $this->legal_relationship->name;
    }

    public function scopeOrganization($query, $mandate_model_type, $mandate_model_id)
    {
        switch ($mandate_model_type) {
            case Association::getModelName():
                $districts = \Csatar\Csatar\Models\District::where('association_id', $mandate_model_id)->lists('id');
                $teams     = \Csatar\Csatar\Models\Team::whereIn('district_id', $districts)->lists('id');
                return $query->whereIn('team_id', $teams);

            case District::getModelName():
                $teams = \Csatar\Csatar\Models\Team::where('district_id', $mandate_model_id)->lists('id');
                return $query->whereIn('team_id', $teams);

            case Team::getModelName():
                return $query->where('team_id', $mandate_model_id);

            default:
                return $query->whereNull('id');
        }
    }

    public function scopeTeamId($query, $id)
    {
        return $query->where('team_id', $id);
    }

    public function scopeInTroop($query, $id) {
        return $query->where('troop_id', $id);
    }

    public function scopeInPatrol($query, $id) {
        return $query->where('patrol_id', $id);
    }

    public function scopeActive($query) {
        return $query->whereNull('inactivated_at');
    }

    public function scopeInactive($query) {
        return $query->whereNotNull('inactivated_at');
    }

    public function scopeActiveScoutsInTeam($query, $id)
    {
        return $query->teamId($id)->active()->orderBy('family_name');
    }

    public function scopeActiveScoutsInTroop($query, $id)
    {
        return $query->inTroop($id)->active()->orderBy('family_name');
    }

    public function scopeActiveScoutsInPatrol($query, $id)
    {
        return $query->inPatrol($id)->active()->orderBy('family_name');
    }

    public function scopeInactiveScoutsInTeam($query, $id)
    {
        return $query->teamId($id)->inactive()->orderBy('family_name');
    }

    public function scopeInactiveScoutsInTroop($query, $id)
    {
        return $query->inTroop($id)->inactive()->orderBy('family_name');
    }

    public function scopeInactiveScoutsInPatrol($query, $id)
    {
        return $query->inPatrol($id)->inactive()->orderBy('family_name');
    }

    public function scopeAssociations($query, array $associationIds)
    {
        return $query->whereHas('team', function ($query) use ($associationIds) {
            $query->whereHas('district', function ($query) use ($associationIds) {
                $query->whereIn('association_id', $associationIds);
            });
        });
    }

    /*
     * Returns all the mandates scout has in a specific association
     */
    public function getMandatesInAssociation($associationId, $savedAfterDate = null): Collection
    {
        $sessionRecord = Session::get('scout.mandates');

        if (!empty($sessionRecord) && $sessionRecordForAssociation = $sessionRecord->where('associationId', $associationId)->first()) {
            if ($sessionRecordForAssociation['savedToSession'] >= $savedAfterDate) {
                // implement touch scout when mandate is added or removed CS-288
                return new Collection($sessionRecordForAssociation['mandates']);
            }
        }

        // get all mandate type ids from association
        $mandateTypeIdsInAssociation = MandateType::getAllMandateTypeIdsInAssociation($associationId);

        // get scout's mandates with the above mandate types and pluck mandate_type_ids
        $scoutMandates = $this->mandates()
            ->whereIn('mandate_type_id', $mandateTypeIdsInAssociation)
            ->where('start_date', '<=', date('Y-m-d H:i'))
            ->where(function ($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', date('Y-m-d H:i'));
            })
            ->get();

        if (empty($sessionRecord)) {
            $sessionRecord = new Collection([]);
        }

        $sessionRecord = $sessionRecord->replace([ $associationId => [
            'associationId' => $associationId,
            'savedToSession' => date('Y-m-d H:i'),
            'mandates' => ($scoutMandates)->toArray(),
        ]
        ]);

        Session::put('scout.mandates', $sessionRecord);

        return $scoutMandates;
    }

    public function getMandateTypeIdsInAssociation($associationId, $savedAfterDate = null, $ignoreCache = false)
    {
        $sessionRecord = $ignoreCache ? null : Session::get('scout.mandateTypeIds');

        if (!empty($sessionRecord) && $sessionRecordForAssociation = $sessionRecord->where('associationId', $associationId)->first()) {
            if ($sessionRecordForAssociation['savedToSession'] >= $savedAfterDate) {
                // implement touch scout when mandate is added or removed CS-288
                return $sessionRecordForAssociation['mandateTypeIds'];
            }
        }

        if (empty($sessionRecord)) {
            $sessionRecord = new Collection([]);
        }

        $scoutMandateTypeIds = array_merge(
            $this->getMandatesInAssociation($associationId, $savedAfterDate)
                ->pluck('mandate_type_id')
                ->toArray(),
            MandateType::getScoutMandateTypeIdInAssociation($associationId)
        );

        $sessionRecord = $sessionRecord->replace([ $associationId => [
            'associationId' => $associationId,
            'savedToSession' => date('Y-m-d H:i'),
            'mandateTypeIds' => $scoutMandateTypeIds,
        ]
        ]);

        Session::put('scout.mandateTypeIds', $sessionRecord);
        return $scoutMandateTypeIds;
    }

    public function getMandateTypeIdsInOrganizationTree(PermissionBasedAccess $model): ?array
    {
        $modelAssociation = $model->getAssociation();
        $modelDistrict    = $model->getDistrict();
        $modelTeam        = $model->getTeam();
        $modelTroop       = $model->getTroop();
        $modelPatrol      = $model->getPatrol();

        if (!empty($modelAssociation)) {
            $mandateIdsForAssociation = $this->getMandatesForOrganization($modelAssociation)
                                             ->pluck('mandate_type_id')->toArray();
        }

        if (!empty($modelDistrict)) {
            $mandateIdsForDistrict = $this->getMandatesForOrganization($modelDistrict)
                                          ->pluck('mandate_type_id')->toArray();
        }

        if (!empty($modelTeam)) {
            $mandateIdsForTeam = $this->getMandatesForOrganization($modelTeam)
                                      ->pluck('mandate_type_id')->toArray();
        }

        if (!empty($modelTroop)) {
            $mandateIdsForTroop = $this->getMandatesForOrganization($modelTroop)
                                       ->pluck('mandate_type_id')->toArray();
        }

        if (!empty($modelPatrol)) {
            $mandateIdsForPatrol = $this->getMandatesForOrganization($modelPatrol)
                                        ->pluck('mandate_type_id')->toArray();
        }

        if (empty($modelAssociation)) {
            return [];
        }

        return array_merge(
            $mandateIdsForAssociation ?? [],
            $mandateIdsForDistrict ?? [],
            $mandateIdsForTeam ?? [],
            $mandateIdsForTroop ?? [],
            $mandateIdsForPatrol ?? [],
            MandateType::getScoutMandateTypeIdInAssociation($modelAssociation->id)
        );
    }

    public function getMandatesForOrganization(PermissionBasedAccess $organization, bool $withInactive = false) {
        return $this->mandates
            ->where('mandate_model_type', $organization->getModelName())
            ->where('mandate_model_id', $organization->id)
            ->when(!$withInactive, function ($collection){
                return $collection->where('start_date', '<=', date('Y-m-d H:i'))
                    ->filter(function ($item) {
                        return $item->end_date === null || $item->end_date >= date('Y-m-d H:i');
                    });
            });
    }

    public function saveMandateTypeIdsForEveryAssociationToSession(){
        $associationIds = Association::all()->pluck('id');

        if (empty($associationIds)) {
            return;
        }

        foreach ($associationIds as $associationId) {
            $this->getMandateTypeIdsInAssociation($associationId, false, true);
        }
    }

    public function is2FA(): bool
    {
        if (Auth::user() && Session::get('scout.twoFA', false)) {
            return true;
        }

        return false;
    }

    public function getRightsForModel($model, $ignoreCache = false){

        if (empty($model)) {
            return;
        }

        $this->load('mandates', 'mandates.mandate_type');

        $isOwn = false;
        if (Auth::user() && !empty(Auth::user()->scout)) {
            $isOwn = $model->isOwnModel(Auth::user()->scout);
        }

        $is2fa = $this->is2FA();

        $mandateTypeIds = $this->getMandateTypeIdsInOrganizationTree($model);

        return $model->getRightsForMandateTypes($mandateTypeIds, $isOwn, $is2fa, $ignoreCache);
    }

    public function isOwnModel($scout){
        return $this->id === $scout->id;
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.csatar::lang.plugin.admin.scout.scout');
    }

    public function getScoutOptions($scopes = null){
        if (!empty($scopes['association']->value)) {
            return self::associations(array_keys($scopes['association']->value))
                ->select(DB::raw("CONCAT(ifnull(family_name, ''), ' ', ifnull(given_name, '')) AS fullname, id"))
                ->lists('fullname', 'id');
        } else {
            return self::select(DB::raw("CONCAT(ifnull(family_name, ''), ' ', ifnull(given_name, '')) AS fullname, id"))
                ->lists('fullname', 'id');
        }
    }

    public function getScoutTeamOptions($scopes = null){
        if (!empty($scopes['association']->value)) {
            return self::associations(array_keys($scopes['association']->value))
                ->get()
                ->filter(function ($item) {
                    if (!empty($item->team)) {
                        return $item;
                    }
                })
                ->map(function ($item) {
                    return [ 'name' => $item->team->extended_Name, 'id' => $item->team->id ];
                })
                ->pluck('name', 'id')->toArray();
        } else {
            return self::all()
                ->filter(function ($item) {
                    if (!empty($item->team)) {
                        return $item;
        }
                })
                ->map(function ($item) {
                    return [ 'name' => $item->team->extended_Name, 'id' => $item->id ];
                })
                ->pluck('name', 'id')->toArray();
        }
    }

    public function getStaticMessages(): array
    {
        $messages = [];

        if (!$this->isPersonalDataAccepted()) {
            $messages['warning']['personalDataNotAccepted'] =
                [
                    'message' => Lang::get('csatar.csatar::lang.plugin.admin.scout.staticMessages.personalDataNotAccepted'),
                    'actionUrl' => 'tag/' . $this->ecset_code,
                ];
        }

        return $messages;
    }

    public function isPersonalDataAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function setPersonalDataAccepted(): bool
    {
        $this->accepted_at = new \DateTime();
        return $this->save();
    }

    /**
     * @return string
     */
    public function getBirthDateFromCNP($cnp)
    {
        if (empty($cnp)) {
            return e(trans('csatar.csatar::lang.plugin.admin.scout.validationExceptions.invalidPersonalIdentificationNumber'));
        }

        if (!(new CnpValidator())->validate(null, $cnp, null)) {
            throw new ValidationException([
                'personal_identification_number' => e(trans('csatar.csatar::lang.plugin.admin.scout.validationExceptions.invalidPersonalIdentificationNumber'))
            ]
            );
        }

        $sex   = substr($cnp, 0, 1);
        $year  = substr($cnp, 1, 2);
        $month = substr($cnp, 3, 2);
        $day   = substr($cnp, 5, 2);

        switch ($sex) {
            case 1:
            case 2:
            case 7:
            case 8:
                $year += 1900;
                break;
            case 5:
            case 6:
                $year += 2000;
                break;
            case 3:
            case 4:
                $year += 1800;
                break;
            default:
                return e(trans('csatar.csatar::lang.plugin.admin.scout.validationExceptions.invalidPersonalIdentificationNumber'));
        }

        return checkdate($month, $day, $year) ? $year . '-' . $month . '-' . $day : date("Y-m-d");
    }

    /**
     * @return array
     */
    public function getPersonalIdentificationNumberValidators(): array
    {
        if (!empty($this->team) && !empty($this->team->district->association->personal_identification_number_validator)) {
            return $this->team->district->association->personal_identification_number_validator;
        }

        return [];
    }

    public function setAddressCountyOptions(&$field)
    {
        $savedCounty = $this->original['address_county'] ?? null;
        $array       = [];
        if ($this->address_zipcode != null) {
            $array = Locations::where('country', '=', $this->address_country)->where('code', '=', $this->address_zipcode)->lists('county', 'county');
        }

        if (empty($array)) {
            if ($savedCounty != null) {
                $array[$savedCounty] = $savedCounty;
            }

            if ($this->address_county != $savedCounty) {
                $array[$this->address_county] = $this->address_county;
            }
        } else {
            $field->value = array_values($array)[0];
        }

        $field->options = $array;
    }

    public function setAddressLocationOptions(&$field)
    {
        $savedLocation = $this->original['address_location'] ?? null;
        $array         = [];

        if ($this->address_zipcode != null) {
            $array = Locations::where('country', '=', $this->address_country)->where('code', '=', $this->address_zipcode)->lists('city', 'city');
        }

        if (empty($array)) {
            if ($savedLocation != null) {
                $array[$savedLocation] = $savedLocation;
            }

            if ($this->address_location != $savedLocation) {
                $array[$this->address_location] = $this->address_location;
            }
        } else {
            $field->value           = array_values($array)[0];
            $this->address_location = array_values($array)[0];
        }

        $field->options = $array;
    }

    public function setAddressStreetOptions(&$field)
    {
        $savedStreet = $this->original['address_street'] ?? null;
        $array       = [];

        if ($this->address_zipcode != null) {
            $locationsArray = Locations::where('country', '=', $this->address_country)
                ->where('code', '=', $this->address_zipcode)
                ->where('city', '=', $this->address_location)
                ->where('street', '!=', '')
                ->get();
            if (!empty($locationsArray)) {
                foreach ($locationsArray as $location) {
                    $street         = $location['street_type'] . ' ' . $location['street'];
                    $array[$street] = $street;
                }
            }
        }

        if (empty($array)) {
            if ($savedStreet != null) {
                $array[$savedStreet] = $savedStreet;
            }

            if ($this->address_street != $savedStreet) {
                $array[$this->address_street] = $this->address_street;
            }
        } else {
            $field->value = array_values($array)[0];
        }

        $field->options = $array;
    }

    public function getAddressCountryAttribute()
    {
        $savedCountry = array_get($this->attributes, 'address_country');
        $team         = $this->team ?? Team::find($this->team_id);

        if (empty($team)) {
            return null;
        }

        return $savedCountry ?? $team->district->association->country;
    }

    public function deletePersonalInformation() {
        $this->family_name = '(Törölt';
        $this->given_name  = 'Tag)';
        $this->email       = null;
        $this->phone       = null;

        $this->address_country  = null;
        $this->address_county   = null;
        $this->address_zipcode  = null;
        $this->address_location = null;
        $this->address_street   = null;
        $this->address_number   = null;

        $this->ignoreValidation = true;
        $this->forceSave();
    }

    public function getTeamChangeHistory()
    {
        $teamChangeHistory = $this->history()->where('attribute', 'team_id')->get();
        $historyArray      = [];
        if (empty($teamChangeHistory)) {
            return [];
        }

        foreach ($teamChangeHistory as $history) {
            $date    = $history->created_at;
            $oldTeam = Team::find($history->old_value);
            $newTeam = Team::find($history->new_value);

            if (empty($oldTeam) || empty($newTeam)) {
                continue;
            }

            $oldTeam        = "<a href='/csapat/$oldTeam->id'>$oldTeam->name</a>";
            $newTeam        = "<a href='/csapat/$newTeam->id'>$newTeam->name</a>";
            $historyArray[] = Lang::get('csatar.csatar::lang.plugin.admin.scout.teamChangeHistoryMessage', ['date' => $date, 'oldTeam' => $oldTeam, 'newTeam' => $newTeam]);
        }

        return $historyArray;
    }

    public function getParentTree() {
        $tree = [
            $this->team->district->association->text_for_search_results_tree ?? null,
            $this->team->district->text_for_search_results_tree ?? null,
            $this->team->text_for_search_results_tree ?? null,
        ];

        if (isset($this->troop_id)) {
            $tree[] = $this->troop->text_for_search_results_tree ?? null;
        }

        if (isset($this->patrol_id)) {
            $tree[] = $this->patrol->text_for_search_results_tree ?? null;
        }

        return '(' . implode(' - ', $tree) . ')';
    }

    public function getBirthdateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    /**
     * @param  $fields
     * @return void
     */
    public function handleTroopDropdown(&$fields)
    {
        // populate the Troop and Patrol dropdowns with troops and patrols that belong to the selected team
        if (isset($fields->troop)) {
            $fields->troop->options = [];
            $team_id = $this->team_id;
            if ($team_id) {
                $fields->troop->options += ['null' => e(trans('csatar.csatar::lang.plugin.admin.general.select'))];
                foreach (\Csatar\Csatar\Models\Troop::teamId($team_id)->get() as $troop) {
                    $fields->troop->options += [$troop['id'] => $troop['extendedName']];
                }
            }
        }
    }

    /**
     * @param  $fields
     * @param  $team_id
     * @return void
     */
    public function handlePatrolDopdown(&$fields): void
    {
        // populate the Patrol dropdown with patrols that belong to the selected team and to the selected troop
        if (isset($fields->patrol)) {
            $team_id = $this->team_id;
            $fields->patrol->options = [];
            $troop_id = $this->troop_id;
            $fields->patrol->options += ['null' => e(trans('csatar.csatar::lang.plugin.admin.general.select'))];
            if ($troop_id && $troop_id != 'null') { // important, 'null' is string at this point
                foreach (\Csatar\Csatar\Models\Patrol::troopId($troop_id)->get() as $patrol) {
                    $fields->patrol->options += [$patrol['id'] => $patrol['extendedName']];
                }
            } else if ($team_id) {
                foreach (\Csatar\Csatar\Models\Patrol::teamId($team_id)->get() as $patrol) {
                    $fields->patrol->options += [$patrol['id'] => $patrol['extendedName']];
                }
            }
        }
    }

    /**
     * @param  $fields
     * @return void
     */
    public function handleLegalRelationshipDropdown(&$fields): void
    {
        // populate the Legal Relationships dropdown with legal relationships that belong to the selected team's association
        if (isset($fields->legal_relationship)) {
            $fields->legal_relationship->options = $this->team ? \Csatar\Csatar\Models\LegalRelationship::associationId($this->team->district->association->id)->lists('name', 'id') : [];
        }
    }

    /**
     * @param  $fields
     * @return void
     */
    public function handlePersonalIdentificationNumberField(&$fields): void
    {
        if (isset($fields->personal_identification_number)
            && !$this->shouldNotValidateCnp()
            && !empty($fields->personal_identification_number->value)
            && in_array('cnp', $this->getPersonalIdentificationNumberValidators())
            && ((isset($this->original['personal_identification_number'])
                    && $this->original['personal_identification_number'] != $fields->personal_identification_number->value)
                || empty($this->original)
            )
        ) {
            $fields->birthdate->value = $this->getBirthDateFromCNP($fields->personal_identification_number->value);
        }
    }

    /**
     * @param  $fields
     * @return void
     */
    public function handleAddressFields(&$fields): void
    {
        if (isset($fields->address_county)) {
            $this->setAddressCountyOptions($fields->address_county);
        }

        if (isset($fields->address_location)) {
            $this->setAddressLocationOptions($fields->address_location);
        }

        if (isset($fields->address_street)) {
            $this->setAddressStreetOptions($fields->address_street);
        }
    }

    /**
     * @param  $fields
     * @return void
     */
    public function handleCitizenshipField(&$fields): void
    {
        if (isset($fields->citizenship_country) && empty($fields->citizenship_country->value)) {
            $fields->citizenship_country->value = Country::where('code', 'RO')->first()->id ?? null;
        }
    }

    public function handleIsActiveField($fields) {
        if (isset($fields->citizenship_country)) {
            $fields->is_active->value = $this->inactivated_at == null;
        }
    }

    public static function getScoutOptionsForSelect(string $searchTerm): array
    {
        $queryResults = Db::table('csatar_csatar_scouts')->whereRaw("CONCAT(family_name, ' ', given_name, ' ', ecset_code) like ?", ['%' . $searchTerm . '%'])->paginate(15);
        $results      = [];
        foreach ($queryResults as $result) {
            $results[] = [
                'id' => $result->ecset_code,
                'text' => $result->family_name . ' ' . $result->given_name . ' - ' . $result->ecset_code,
            ];
        }

        return [
            'results' => $results,
            'pagination' => [
                'more' => true
            ],
        ];
    }

}
