<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class Association extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_associations';

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
        'currency_id' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
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
        ],
    ];

    public $hasMany = [
        'districts' => '\Csatar\Csatar\Models\District',
    ];

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];
}
