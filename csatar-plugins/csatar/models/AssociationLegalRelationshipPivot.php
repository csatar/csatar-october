<?php namespace Csatar\Csatar\Models;

use October\Rain\Database\Pivot;

/**
 * Pivot Model
 */
class AssociationLegalRelationshipPivot extends Pivot
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_associations_legal_relationships';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'membership_fee' => 'required|digits_between:1,20',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'membership_fee',
    ];
}
