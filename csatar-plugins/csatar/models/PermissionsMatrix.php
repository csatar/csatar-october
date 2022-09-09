<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class PermissionsMatrix extends Model
{
    /**
     * @var array Validation rules
     */
    public $rules = [];

    public const MODEL_GENERAL_VALUE = 'MODEL_GENERAL';
    public const PALCEHOLDER_VALUE = 'PALCEHOLDER';

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


    public function getTranslatedLabelForFiled(string $field, string $model): string
    {
        if ($model === self::MODEL_GENERAL_VALUE || $model === self::PALCEHOLDER_VALUE) {
            return '';
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
}
