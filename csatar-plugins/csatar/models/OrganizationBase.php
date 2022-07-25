<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class OrganizationBase extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * Relations
     */
    public $belongsToMany = [
        'scouts' => [
            '\Csatar\Csatar\Models\Scout',
            'table' => 'csatar_csatar_scouts_mandates',
            'pivot' => ['mandate_model_id', 'start_date', 'end_date', 'comment'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutMandatePivot',
            'label' => 'csatar.csatar::lang.plugin.admin.scout.scouts',
        ],
        'mandates' => [
            '\Csatar\Csatar\Models\Mandate',
            'table' => 'csatar_csatar_scouts_mandates',
            'pivot' => ['mandate_model_id', 'start_date', 'end_date', 'comment'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutMandatePivot',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
        ],
    ];

    public $morphOne = [
        'mandate' => [\Csatar\Csatar\Models\ScoutMandatePivot::class, 'name' => 'mandate_model'],
    ];

    /**
     * Returns the name of the organization
     */
    public function getExtendedNameAttribute()
    {
        return $this->attributes['name'];
    }

    public static function getOrganizationTypeModelName()
    {
        return '\\' . static::class;
    }
}
