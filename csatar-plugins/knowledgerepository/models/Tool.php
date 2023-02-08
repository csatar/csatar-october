<?php namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class Tool extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_tools';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'name',
        'note',
        'is_approved',
        'approver_csatar_code',
        'proposer_csatar_code'
    ];

    public $nullable = [
        'note',
        'is_approved',
        'approver_csatar_code',
        'proposer_csatar_code'
    ];
}
