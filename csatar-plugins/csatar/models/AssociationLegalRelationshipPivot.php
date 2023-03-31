<?php namespace Csatar\Csatar\Models;

use Lang;
use Csatar\Csatar\Classes\CsatarPivot;

/**
 * Pivot Model
 */
class AssociationLegalRelationshipPivot extends CsatarPivot
{
    use \October\Rain\Database\Traits\Validation;

    use \Csatar\Csatar\Traits\History;

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

    public function beforeValidate()
    {
        // set the attribute names
        $this->setValidationAttributeNames([
            'membership_fee' => Lang::get('csatar.csatar::lang.plugin.admin.association.membershipFee'),
        ]);
    }

}
