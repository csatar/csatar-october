<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\TeamReport;
use Model;

/**
 * Model
 */
class DynamicFields extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_dynamic_fields';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'association' => 'required',
        'start_date' => 'required',
        'model' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'association_id',
        'start_date',
        'end_date',
        'model',
        'extra_fields_definition',
    ];

    /**
     * Relations
     */

    public $belongsTo = [
        'association' => '\Csatar\Csatar\Models\Association',
    ];

    function getModelOptions()
    {
        return [
            TeamReport::getModelName() => TeamReport::getOrganizationTypeModelNameUserFriendly(),
        ];
    }

    function getOrganizationTypeModelNameUserFriendlyAttribute()
    {
        return $this->attributes['model']
            ? ($this->attributes['model'])::getOrganizationTypeModelNameUserFriendly()
                : '';
    }

    protected $jsonable = ['extra_fields_definition'];
}
