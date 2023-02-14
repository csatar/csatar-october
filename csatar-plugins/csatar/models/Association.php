<?php namespace Csatar\Csatar\Models;

use Cache;
use Csatar\Csatar\Models\OrganizationBase;
use DateTime;
use Lang;
use ValidationException;

/**
 * Model
 */
class Association extends OrganizationBase
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_associations';

    /**
     * @var array The columns that should be searchable by ContentPageSearchProvider
     */
    protected static $searchable = ['name'];

    protected $appends = ['extended_name'];

    protected $jsonable = ['personal_identification_number_validator'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'contact_name' => 'min:5|nullable',
        'contact_email' => 'email|nullable',
        'address' => 'min:5|nullable',
        'bank_account' => 'min:5|nullable',
        'leadership_presentation' => 'nullable',
        'logo' => 'image|nullable',
        'ecset_code_suffix' => 'max:2|alpha|nullable',
        'team_fee' => 'required|digits_between:1,20',
        'currency' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'name_abbreviation',
        'coordinates',
        'contact_name',
        'contact_email',
        'address',
        'bank_account',
        'leadership_presentation',
        'logo',
        'ecset_code_suffix',
        'team_fee',
        'currency_id',
        'personal_identification_number_validator',
        'team_report_submit_start_date',
        'team_report_submit_end_date',
    ];

    /**
     * Relations
     */
    public $belongsTo = [
        'currency' => '\Csatar\Csatar\Models\Currency',
    ];

    public $belongsToMany = [
        'legal_relationships' => [
            '\Csatar\Csatar\Models\LegalRelationship',
            'table' => 'csatar_csatar_associations_legal_relationships',
            'pivot' => ['membership_fee'],
            'pivotModel' => '\Csatar\Csatar\Models\AssociationLegalRelationshipPivot',
            'label' => 'csatar.csatar::lang.plugin.admin.legalRelationship.legalRelationships',
        ]
    ];

    public $hasMany = [
        'ageGroups' => [
            '\Csatar\Csatar\Models\AgeGroup',
            'label' => 'csatar.csatar::lang.plugin.admin.ageGroups.ageGroups',
        ],
        'districts' => [
            '\Csatar\Csatar\Models\District',
            'label' => 'csatar.csatar::lang.plugin.admin.district.districts',
        ],
        'districtsActive' => [
            '\Csatar\Csatar\Models\District',
            'scope' => 'active',
            'ignoreInPermissionsMatrix' => true,
        ],
        'mandates' => [
            '\Csatar\Csatar\Models\Mandate',
            'key' => 'mandate_model_id',
            'scope' => 'mandateModelType',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
            'renderableOnCreateForm' => false,
            'renderableOnUpdateForm' => true,
        ],
    ];

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];

    public $morphOne = [
        'content_page' => [
            '\Csatar\Csatar\Models\ContentPage',
            'name' => 'model',
            'label' => 'csatar.csatar::lang.plugin.admin.general.contentPage',
        ]
    ];

    public function beforeValidate()
    {
        if (!empty($this->team_report_submit_start_date)
            && (is_null($this->team_report_submit_end_date) || new DateTime($this->team_report_submit_start_date) > new DateTime($this->team_report_submit_end_date))
        ) {
            throw new ValidationException(['team_report_submit_end_date' => Lang::get('csatar.csatar::lang.plugin.admin.association.validationExceptions.invalidTeamReportSubmissionPeriod')]);
        }
    }

    public function afterSave()
    {
        if (empty($this->original)) {
            return;
        }

        if (
            (isset($this->original['name']) && $this->original['name'] != $this->name)
            || ($this->original['name_abbreviation'] && $this->original['name_abbreviation'] != $this->name_abbreviation)
        ) {
            $structureTree = Cache::pull('structureTree');
            if (empty($structureTree)) {
                StructureTree::getStructureTree();
                return;
            }
            if (empty($structureTree)) {
                StructureTree::getStructureTree();
                return;
            }
            $structureTree[$this->id]['name'] = $this->name;
            $structureTree[$this->id]['name_abbreviation'] = $this->name_abbreviation;
            $structureTree[$this->id]['extended_name'] = $this->extended_name;
            Cache::forever('structureTree', $structureTree);
        }
    }

    /**
     * Return the association with the given id
     */
    public static function getAllByAssociationId($associationId, $teamId)
    {
        $item = self::find($associationId);
        return [$item->id => $item->extendedName];
    }

    /**
     * Returns the id of the association to which the item belongs to.
     */
    public function getAssociationId()
    {
        return $this->id;
    }

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.csatar::lang.plugin.admin.association.association');
    }

    public function getPersonalIdentificationNumberValidatorOptions() {
        return [
            'unique:csatar_csatar_scouts'   => Lang::get('csatar.csatar::lang.plugin.admin.association.unique'),
            'required'                      => Lang::get('csatar.csatar::lang.plugin.admin.association.required'),
            'cnp'                           => Lang::get('csatar.csatar::lang.plugin.admin.association.cnp'),
        ];
    }

    public function getAssociation() {
        return $this;
    }

    public function getActiveDistricts() {
        return $this->districtsActive;
    }
}
