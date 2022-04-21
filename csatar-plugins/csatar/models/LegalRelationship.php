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
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'title',
        'sort_order'
    ];

    /** 
     * Relations 
     */
    public $hasMany = [
        'scouts' => '\Csatar\Csatar\Models\Scouts'
    ];
}
