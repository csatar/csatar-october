<?php namespace Csatar\Csatar\Models;

use Model;
use Db;
use Event;
use Request;
use DateTime;
use Csatar\Csatar\Classes\HistoryService;

/**
 * Model
 */
class MandatePermission extends Model
{
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /**
     * @var array Validation rules
     */
    public $rules = [];

    public const MODEL_GENERAL_VALUE = 'MODEL_GENERAL';
    public const PALCEHOLDER_VALUE = 'PALCEHOLDER';

    public $belongsTo = [
        'mandateType' => '\Csatar\Csatar\Models\MandateType',
    ];

    public $translatedLabelsForFields = [];

    function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_mandates_permissions';

    protected $fillable = [
        'mandate_type_id',
        'model',
        'field',
        'own',
        'obligatory',
        'create',
        'read',
        'update',
        'delete',
        'updated_at'
    ];

    public function afterSave() {
        Event::fire('mandatePermission.afterSave', [$this]);
    }

    public function afterDelete() {
        Event::fire('mandatePermission.afterDelete', [$this]);
    }

    public function getFieldOptions($scopes = null){

        if (!empty($scopes['model']->value)) {
            $fields = self::whereIn('model', array_keys($scopes['model']->value))
                ->lists('model', 'field');
        } else {
            return [];
        }

        $returnFields = [];

        foreach ($fields as $field => $model) {
            $returnFields[$field] = $this->getTranslatedLabelForFiled($field, $model);
        }
        return $returnFields;
    }

    public function getTranslatedLabelAttribute()
    {
        return $this->getTranslatedLabelForFiled($this->field, $this->model);
    }

    public function getModelUserFriendlyAttribute()
    {
        if (class_exists($this->model)) {
            return ($this->model)::getOrganizationTypeModelNameUserFriendly();
        }
        return '';
    }

    public function getTranslatedLabelForFiled(string $field, string $model): string
    {
        if ($model === self::PALCEHOLDER_VALUE) {
            return '';
        }

        if ($field === self::MODEL_GENERAL_VALUE) {
            return e(trans('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.' . self::MODEL_GENERAL_VALUE));
        }

        $translatedLabelsForFields = ($model)::getTranslatedAttributeNames($model);

        return $translatedLabelsForFields[$field] ?? $field;
    }

    public function getModelGeneralValue(){
        return self::MODEL_GENERAL_VALUE;
    }

    public function getPlaceHolderValue(){
        return self::PALCEHOLDER_VALUE;
    }

    public function getFromAssociationOptions(){
        $mandateTypeIds = Db::table('csatar_csatar_mandates_permissions')
            ->whereNull('deleted_at')
            ->select('mandate_type_id')
            ->distinct()
            ->get()
            ->pluck('mandate_type_id');
        $associationIds = Db::table('csatar_csatar_mandate_types')
            ->whereNull('deleted_at')
            ->whereIn('id', $mandateTypeIds)
            ->select('association_id')
            ->distinct()
            ->get()
            ->pluck('association_id');

        return Db::table('csatar_csatar_associations')
            ->whereNull('deleted_at')
            ->whereIn('id', $associationIds)
            ->orderBy('name', 'asc')
            ->lists('name', 'id');
    }

    public function getToAssociationOptions(){
        return Db::table('csatar_csatar_associations')
            ->whereNull('deleted_at')
            ->orderBy('name', 'asc')
            ->lists('name', 'id');
    }

    public function getFromMandateTypeOptions(){
        $mandateTypes = [];
        if ($this->fromAssociation) {
            $mandateTypes = Db::table('csatar_csatar_mandate_types')->whereNull('deleted_at')->where('association_id', $this->fromAssociation)->lists('name', 'id');
        }

        return $mandateTypes;
    }

    public function getToMandateTypesOptions(){
        $mandateTypes = [];
        if ($this->toAssociation) {
            $mandateTypes = Db::table('csatar_csatar_mandate_types')->whereNull('deleted_at')->where('association_id', $this->toAssociation)->lists('name', 'id');
        }

        return $mandateTypes;
    }

    public function getModelOptions(){
        $modelOptions = self::distinct()->orderBy('model', 'asc')->lists('model', 'model');
        array_walk($modelOptions, function (&$item) {
            $item = ($item)::getOrganizationTypeModelNameUserFriendly();
        });
        return $modelOptions;
    }


    public function historyRecordBulkAction($dataArray, $error = null) {

        $historyRelationName = HistoryService::getHistoryRelationName($this);
        $historyRelationObject = $this->{$historyRelationName}();
        $historyModel = $historyRelationObject->getRelated();

        $modelClass = $historyRelationObject->getMorphClass();

        $toSave = [];
        if (empty($dataArray)) {
            return;
        }

        foreach ($dataArray as $data) {

            $toSave[] = [
                'fe_user_id' => HistoryService::historyGetUser(),
                'be_user_id' => HistoryService::historyGetBackendUser(),
                'model_class' => $modelClass,
                'model_id' => $data['id'] ?? null,
                'attribute' => $data['action'] ?? null,
                'old_value' => $data['initialValue'] ?? null,
                'new_value' => $data['value'] ?? null,
                'description' => $error ?? null,
                'ip_address' => Request::ip(),
                'created_at' => new DateTime,
                'updated_at' => new DateTime
            ];
        }

        // Nothing to do
        if (!count($toSave)) {
            return;
        }

        Db::table($historyModel->getTable())->insert($toSave);
    }

    public function historyRecordMatrixSynchronization($dataArray) {
        $historyRelationName = HistoryService::getHistoryRelationName($this);
        $historyRelationObject = $this->{$historyRelationName}();
        $historyModel = $historyRelationObject->getRelated();

        $modelClass = $historyRelationObject->getMorphClass();

        $toSave = [];
        if (empty($dataArray)) {
            return;
        }

        foreach ($dataArray as $data) {
            foreach ($data as $key => $value) {
                $toSave[] = [
                    'fe_user_id' => HistoryService::historyGetUser(),
                    'be_user_id' => HistoryService::historyGetBackendUser(),
                    'model_class' => $modelClass,
                    'model_id' => null,
                    'attribute' => $key,
                    'old_value' => null,
                    'new_value' => $value,
                    'description' => null,
                    'ip_address' => Request::ip(),
                    'created_at' => new DateTime,
                    'updated_at' => new DateTime
                ];
            }
        }

        Db::table($historyModel->getTable())->insert($toSave);
    }
}
