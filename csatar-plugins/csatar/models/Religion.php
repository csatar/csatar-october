<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class Religion extends Model
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

    public $morphMany = [
        'history' => [
            \Csatar\Csatar\Models\History::class,
            'name' => 'history',
            'ignoreInPermissionsMatrix' => true,
        ],
    ];

    public static function getOtherReligionId()
    {
        $data = self::where('name', 'Más felekezethez tartozó')->first();
        return isset($data) ? $data->id : null;
    }
}
