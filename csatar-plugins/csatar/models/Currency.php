<?php 
namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class Currency extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \Csatar\Csatar\Traits\History;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_currencies';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'code' => 'required|alpha|max:3|unique:csatar_csatar_currencies,code',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'code',
    ];
    
    /**
     * Relations
     */

    public $hasMany = [
        'associations' => '\Csatar\Csatar\Models\Association',
        'teamReports' => '\Csatar\Csatar\Models\TeamReport',
    ];

}
