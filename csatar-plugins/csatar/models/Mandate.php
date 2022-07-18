<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\Troop;
use Lang;
use Model;

/**
 * Model
 */
class Mandate extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\NestedTree;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_mandates';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'association' => 'required',
        'organization_type_model_name' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'association_id',
        'organization_type_model_name',
        'required',
        'parent_id',
    ];

    /**
     * Relations
     */
    public $belongsTo = [
        'association' => '\Csatar\Csatar\Models\Association',
        'parent' => [
            '\Csatar\Csatar\Models\Mandate',
            'key' => 'parent_id',
            'otherKey' => 'id',
        ],
    ];

    public $belongsToMany = [
        'scouts' => [
            '\Csatar\Csatar\Models\Scout',
            'table' => 'csatar_csatar_scouts_mandates',
            'pivot' => ['startDate', 'endDate', 'comment'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutMandatePivot',
            'label' => 'csatar.csatar::lang.plugin.admin.scout.scouts',
        ],
        'mandate_models' => [
            '\Csatar\Csatar\Models\OrganizationBase',
            'table' => 'csatar_csatar_scouts_mandates',
            'pivot' => ['startDate', 'endDate', 'comment'],
            'pivotModel' => '\Csatar\Csatar\Models\ScoutMandatePivot',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandateModels',
        ],
    ];

    public $hasMany = [
        'children' => [
            '\Csatar\Csatar\Models\Mandate', 
            'key' => 'parent_id',
            'order' => 'weight asc',
        ],
        'child_count' => [
            '\Csatar\Csatar\Models\Mandate', 
            'key' => 'parent_id',
            'count' => true,
        ],
    ];

    function getOrganizationTypeModelNameOptions()
    {
        return [
            Association::getOrganizationTypeModelName() => Lang::get('csatar.csatar::lang.plugin.admin.association.association'),
            District::getOrganizationTypeModelName() => Lang::get('csatar.csatar::lang.plugin.admin.district.district'),
            Patrol::getOrganizationTypeModelName() => Lang::get('csatar.csatar::lang.plugin.admin.patrol.patrol'),
            Team::getOrganizationTypeModelName() => Lang::get('csatar.csatar::lang.plugin.admin.team.team'),
            Troop::getOrganizationTypeModelName() => Lang::get('csatar.csatar::lang.plugin.admin.troop.troop'),
        ];
    }
}
