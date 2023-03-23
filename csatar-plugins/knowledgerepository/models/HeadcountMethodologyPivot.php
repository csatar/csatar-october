<?php namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class HeadcountMethodologyPivot extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \Csatar\Csatar\Traits\History;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_headcount_methodology';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
