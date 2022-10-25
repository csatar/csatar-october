<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\Troop;
use Csatar\Csatar\Models\MandatePermission;
use DateTime;
use Flash;
use Input;
use Lang;
use Model;
use October\Rain\Database\Collection;
use Session;

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

    public static function boot() {

        parent::boot();

        static::deleting(function($item) {

            MandatePermission::where('mandate_type_id', $item->id)->delete();

        });
    }

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
        'mandatePermissions' => [
            '\Csatar\Csatar\Models\MandatePermission',
            'delete' => true,
        ]
    ];

    function beforeDelete()
    {
        $now = new DateTime();
        $mandates = Mandate::where('mandate_type_id', $this->id)->get();
        foreach ($mandates as $mandate) {
            if ($mandate->start_date < $now && ($mandate->end_date > $now || $mandate->end_date == null)) {
                Flash::error(str_replace('%name', $this->name, Lang::get('csatar.csatar::lang.plugin.admin.mandateType.activeMandateDeleteError')));
                return false;
            }
        }
    }

    function getOrganizationTypeModelNameOptions()
    {
        return [
            Association::getModelName() => Association::getOrganizationTypeModelNameUserFriendly(),
            District::getModelName() => District::getOrganizationTypeModelNameUserFriendly(),
            Patrol::getModelName() => Patrol::getOrganizationTypeModelNameUserFriendly(),
            Team::getModelName() => Team::getOrganizationTypeModelNameUserFriendly(),
            Troop::getModelName() => Troop::getOrganizationTypeModelNameUserFriendly(),
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
                $mandate_model_type = District::getModelName();
                $association_id = $inputData['association'];
            }
            else if ($inputData && array_key_exists('district', $inputData) && !empty($inputData['district'])) {
                $mandate_model_type = Team::getModelName();
                $association_id = District::find($inputData['district'])->getAssociationId();
            }
            else if ($inputData && array_key_exists('team', $inputData) && !empty($inputData['team'])) {
                $mandate_model_type = array_key_exists('troop', $inputData) ? Patrol::getModelName() : Troop::getModelName();
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

    public static function getGuestMandateTypeIdInAssociation($associationId): ?int
    {
        $sessionRecord = Session::get('guest.mandateTypeIds');

        if(!empty($sessionRecord) && $sessionRecordForAssociation = $sessionRecord->where('associationId', $associationId)->first()) {
            return $sessionRecordForAssociation['guestMandateTypeId'];
        }

        if(empty($sessionRecord)){
            $sessionRecord = new Collection([]);
        }

        $guestMandateType = self::where('association_id', $associationId)
            ->where('organization_type_model_name', self::MODEL_NAME_GUEST)
            ->first();

        $sessionRecord = $sessionRecord->replace([ $associationId => [
            'associationId' => $associationId,
            'savedToSession' => date('Y-m-d H:i'),
            'guestMandateTypeId'=> $guestMandateType ? $guestMandateType->id : null,
        ]]);

        Session::put('guest.mandateTypeIds', $sessionRecord);

        return $guestMandateType ? $guestMandateType->id : null;
    }

    public function getMandateTypeOptions($scopes = null){
        if (!empty($scopes['association']->value)) {
            return MandateType::whereIn('association_id', array_keys($scopes['association']->value))
                ->lists('name', 'id')
                ;
        }
        else {
            return MandateType::orderBy('name', 'asc')->lists('association_id', 'id');
        }
    }

    public function getModelOptions(){
        return MandateType::distinct()->where('organization_type_model_name', '<>', self::MODEL_NAME_GUEST)->orderBy('organization_type_model_name', 'asc')->lists('organization_type_model_name', 'organization_type_model_name');
    }

    public static function getMandatesTypesForMatrix () {
        $associationIds = Association::all()->pluck('id');

        $mandateTypes = [];

        foreach ($associationIds as $associationId) {
            $mandatesTypesInAssociation = self::where('association_id', $associationId)->orderBy('nest_left', 'desc')->get();
            $mandateTypes[$associationId] = $mandatesTypesInAssociation->map(function ($item){
                return [
                    'id'                            => $item->id,
                    'name'                          => $item->name,
                    'joinAsName'                    => str_replace('-', '_', str_slug($item->name)),
                    'organization_type_model_name'  => $item->organization_type_model_name
                ];
            });
        }

        return $mandateTypes;
    }

}
