<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class SpecialDiet extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_special_diets';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'title'
    ];

    /** 
     * Relations 
     */
    public $belongsToMany = [
        'scouts' => '\Csatar\Csatar\Models\Scouts'
    ];
}
