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
            Association::getOrganizationTypeModelName() => Association::getOrganizationTypeModelNameUserFriendly(),
            District::getOrganizationTypeModelName() => District::getOrganizationTypeModelNameUserFriendly(),
            Patrol::getOrganizationTypeModelName() => Patrol::getOrganizationTypeModelNameUserFriendly(),
            Team::getOrganizationTypeModelName() => Team::getOrganizationTypeModelNameUserFriendly(),
            Troop::getOrganizationTypeModelName() => Troop::getOrganizationTypeModelNameUserFriendly(),
        ];
    }

    function getOrganizationTypeModelNameUserFriendlyAttribute()
    {
        return $this->attributes['organization_type_model_name']
            && $this->attributes['organization_type_model_name'] != self::MODEL_NAME_GUEST
            ? ($this->attributes['organization_type_model_name'])::getOrganizationTypeModelNameUserFriendly()
                : '';
    }

    /**
     * Scope a query to only include mandates from a given association.
     */
    function scopeAssociation($query, $model = null)
    {
        $mandate_model_type = null;
        $association_id = null;

        // the model is null when this method is being triggered from the form; set the data needed to do the filtering on the organization form pages
        if ($model) {
            // when this is triggered from BE, then the mandate_model_id is set; on FE, on the pivot form, the mandate_type_id is set
            $mandate_model_type = $model->mandate_model_type ?? ($model && array_key_exists('mandate_model', $model->belongsTo) ? $model->belongsTo['mandate_model'] : null);
            $mandate_model_id = $model->mandate_model_id;
            $mandate_type_id = $model->mandate_type_id;
            if ($mandate_model_id) {
                $association_id = $mandate_model_type && $mandate_model_id ? ($mandate_model_type)::find($mandate_model_id)->getAssociationId() : null;
            }
            else if ($mandate_type_id) {
                $mandate_type = MandateType::find($mandate_type_id);
                $association_id = $mandate_type ? $mandate_type->association_id : null;
            }
        }
        else {
            $inputData = Input::get('data');
            if ($inputData && array_key_exists('association', $inputData) && !empty($inputData['association'])) {
                $mandate_model_type = District::getOrganizationTypeModelName();
                $association_id = $inputData['association'];
            }
            else if ($inputData && array_key_exists('district', $inputData) && !empty($inputData['district'])) {
                $mandate_model_type = Team::getOrganizationTypeModelName();
                $association_id = District::find($inputData['district'])->getAssociationId();
            }
            else if ($inputData && array_key_exists('team', $inputData) && !empty($inputData['team'])) {
                $mandate_model_type = array_key_exists('troop', $inputData) ? Patrol::getOrganizationTypeModelName() : Troop::getOrganizationTypeModelName();
                $association_id = Team::find($inputData['team'])->getAssociationId();
            }
        }

        return $association_id && $mandate_model_type ? $query->where('association_id', $association_id)->where('organization_type_model_name', $mandate_model_type) : $query->whereNull('id');
    }

    public static function getAllMandateTypeIdsInAssociation($associationId)
    {
        return self::where('association_id', $associationId)->get()->pluck('id');
    }

    public static function getScoutMandateTypeIdInAssociation($associationId): array
    {
        $scoutMandateType = self::where('association_id', $associationId)
            ->where('organization_type_model_name', '\Csatar\Csatar\Models\Scout')
            ->first();
        return $scoutMandateType ? [ $scoutMandateType->id ] : [];
    }

    public static function getGuestMandateTypeInAssociation($associationId): array
    {
        $guestMandateType = self::where('association_id', $associationId)
            ->where('organization_type_model_name', self::MODEL_NAME_GUEST)
            ->first();
        return $guestMandateType ? [ $guestMandateType->id ] : [];
    }
}
