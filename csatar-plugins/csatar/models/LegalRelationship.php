<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class LegalRelationship extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    use \October\Rain\Database\Traits\Sortable;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_legal_relationships';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'sort_order'
    ];

    /** 
     * Relations 
     */
    public $belongsToMany = [
        'scouts' => '\Csatar\Csatar\Models\Scout',
        'teamReportScouts' => '\Csatar\Csatar\Models\TeamReportScoutPivot',
        'associations' => [
            '\Csatar\Csatar\Models\Association',
            'table' => 'csatar_csatar_associations_legal_relationships',
            'pivot' => ['membership_fee'],
            'pivotModel' => '\Csatar\Csatar\Models\AssociationLegalRelationshipPivot',
        ],
    ];

    /**
     * Scope a query to only include legal relationships that are defined in the pivot table.
     */
    public function scopeAssociation($query)
    {
        return $query->join('csatar_csatar_associations_legal_relationships', 'csatar_csatar_legal_relationships.id', '=', 'csatar_csatar_associations_legal_relationships.legal_relationship_id');
    }
}
