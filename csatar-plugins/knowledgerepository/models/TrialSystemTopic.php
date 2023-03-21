<?php namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class TrialSystemTopic extends Model
{
    use \October\Rain\Database\Traits\SoftDelete;

    use \October\Rain\Database\Traits\Validation;

    protected $dates = ['deleted_at'];
    
    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_trial_system_topics';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
    ];

    public $fillable = [
        'name',
    ];
}
