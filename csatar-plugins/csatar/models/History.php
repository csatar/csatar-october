<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class History extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_history';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
