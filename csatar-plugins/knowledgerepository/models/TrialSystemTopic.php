<?php
namespace Csatar\KnowledgeRepository\Models;

use Csatar\Csatar\Models\ModelExtended;

/**
 * Model
 */
class TrialSystemTopic extends ModelExtended
{
    use \October\Rain\Database\Traits\SoftDelete;

    use \October\Rain\Database\Traits\Validation;

    use \Csatar\Csatar\Traits\History;

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
