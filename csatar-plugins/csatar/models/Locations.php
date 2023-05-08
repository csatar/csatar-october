<?php
namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\ModelExtended;

/**
 * Model
 */
class Locations extends ModelExtended
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
    public $table = 'csatar_csatar_locations';

    protected $fillable = [
        'country',
        'county',
        'city',
        'street_type',
        'street',
        'number',
        'code',
    ];

    protected $primaryKey = 'code';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

}
