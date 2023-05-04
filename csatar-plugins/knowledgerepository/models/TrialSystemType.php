<?php
namespace Csatar\KnowledgeRepository\Models;

use Csatar\Csatar\Models\ModelExtended;

/**
 * Model
 */
class TrialSystemType extends ModelExtended
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_trial_system_types';

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
