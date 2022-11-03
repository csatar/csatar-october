<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\OrganizationBase;
use Lang;

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

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'contact_name' => 'required|min:5',
        'contact_email' => 'required|email',
        'address' => 'required|min:5',
        'bank_account' => 'min:5|nullable',
        'leadership_presentation' => 'required',
        'logo' => 'image|nullable',
        'ecset_code_suffix' => 'max:2|alpha',
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
        'personal_identification_number_validator'
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
        return ['cnp' => 'CNP'];
    }

    public function getAssociation() {
        return $this;
    }
}
