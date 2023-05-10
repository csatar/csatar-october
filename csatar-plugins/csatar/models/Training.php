<?php
namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\ModelExtended;

/**
 * Model
 */
class Training extends ModelExtended
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_trainings';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required'
    ];

    public $fillable = [
        'name',
        'comment',
    ];

}
