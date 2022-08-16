<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\Troop;
use Input;
use Lang;
use Model;

/**
 * Model
 */
class MandateType extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\NestedTree;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    const MODEL_NAME_GUEST = 'GUEST';

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_mandate_types';

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
        'overlap_allowed',
        'parent_id',
    ];

    /**
     * Relations
     */
    public $belongsTo = [
        'association' => '\Csatar\Csatar\Models\Association',
        'parent' => [
            '\Csatar\Csatar\Models\MandateType',
            'key' => 'parent_id',
            'otherKey' => 'id',
        ],
    ];

    public $belongsToMany = [
        'mandates' => [
            '\Csatar\Csatar\Models\Mandate',
            'table' => 'csatar_csatar_mandates',
            'label' => 'csatar.csatar::lang.plugin.admin.mandate.mandates',
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

    /**
     * Scope a query to only include mandates from a given association.
     */
    function scopeAssociation($query, $model = null)
    {
        $organizationTypes = [
            '\Csatar\Csatar\Models\Association',
            '\Csatar\Csatar\Models\District',
            '\Csatar\Csatar\Models\Patrol',
            '\Csatar\Csatar\Models\Team',
            '\Csatar\Csatar\Models\Troop',
        ];

        // the model is null when this method is being triggered from the form; set the data needed to do the filtering on the organization form pages
        $mandate_model_type = null;
        $mandate_model_id = null;
        $association_id = null;
        if (!$model || !$mandate_model_type || !$association_id) {
            $inputData = Input::get('data');
            if ($inputData && array_key_exists('association', $inputData) && !empty($inputData['association'])) {
                $mandate_model_type = '\Csatar\Csatar\Models\District';
                $association_id = $inputData['association'];
            }
            else if ($inputData && array_key_exists('district', $inputData) && !empty($inputData['district'])) {
                $mandate_model_type = '\Csatar\Csatar\Models\Team';
                $association_id = District::find($inputData['district'])->association_id;
            }
            else if ($inputData && array_key_exists('team', $inputData) && !empty($inputData['team'])) {
                $mandate_model_type = (array_key_exists('troop', $inputData) && !empty($inputData['troop'])) ? '\Csatar\Csatar\Models\Patrol' : '\Csatar\Csatar\Models\Troop';
                $association_id = Team::find($inputData['team'])->district->association_id;
            }
        }
        else {
            $mandate_model_type = $model->mandate_model_type ?? ($model && array_key_exists('mandate_model', $model->belongsTo) ? $model->belongsTo['mandate_model'] : null);
            $mandate_model_id = $model->mandate_model_id;
        }

        return $model && $model->scout_id ?
            $query->where('association_id', Scout::find($model->scout_id)->getAssociationId())->whereIn('organization_type_model_name', $organizationTypes) :
            ($model && $mandate_model_id && $mandate_model_type ?
                $query->where('association_id', ($mandate_model_type)::find($mandate_model_id)->getAssociationId())->where('organization_type_model_name', $mandate_model_type) :
                (!empty(Input::get('data')['team']) ?
                    $query->where('association_id', Team::find(Input::get('data')['team'])->district->association->id)->whereIn('organization_type_model_name', $organizationTypes) :
                        ($association_id && $mandate_model_type ?
                            $query->where('association_id', $association_id)->where('organization_type_model_name', $mandate_model_type) :
                            $query->whereNull('id'))));
    }

    function scopeMandateTypeIdsInAssociation($query, $associationId) {
        return self::where('association_id', $associationId)->get()->pluck('id');
    }

    function scopeScoutMandateTypeIdInAssociation($query, $associationId): array {
        $scoutMandateType = self::where('association_id', $associationId)
            ->where('organization_type_model_name', '\Csatar\Csatar\Models\Scout')
            ->first();
        return $scoutMandateType ? [ $scoutMandateType->id ] : [];
    }

    function scopeGuestMandateTypeInAssociation($query, $associationId): array {
        $guestMandateType = self::where('association_id', $associationId)
            ->where('organization_type_model_name', self::MODEL_NAME_GUEST)
            ->first();
        return $guestMandateType ? [ $guestMandateType->id ] : [];
    }
}
