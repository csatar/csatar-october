<?php
namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\ModelExtended;

/**
 * Model
 */
class Religion extends ModelExtended
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_religions';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required'
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name'
    ];

    /**
     * Relations
     */
    public $belongsToMany = [
        'scouts' => '\Csatar\Csatar\Models\Scout',
        'team_reports' => '\Csatar\Csatar\Models\TeamReport',
    ];

    public static function getOtherReligionId()
    {
        $data = self::where('name', 'Más felekezethez tartozó')->first();
        return isset($data) ? $data->id : null;
    }

}
