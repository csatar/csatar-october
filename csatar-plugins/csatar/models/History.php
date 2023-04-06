<?php namespace Csatar\Csatar\Models;

use Model;
use Db;
use RainLab\Builder\Classes\ComponentHelper;

/**
 * Model
 */
class History extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_history';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'frontendUser' => [
            'RainLab\User\Models\User',
            'key' => 'fe_user_id',
        ],
        'backendUser' => [
            'Backend\Models\User',
            'key' => 'be_user_id',
        ],
    ];

    public $morphTo = [
        'model' => []
    ];

    public function getFrontendUserAttribute()
    {
        if (empty($this->fe_user_id)) {
            return null;
        }

        return Db::select(
            "SELECT IFNULL((SELECT CONCAT(IFNULL(family_name, ''), ' ', IFNULL(given_name, ''), ' - ', IFNULL(ecset_code, '')) as name FROM csatar_csatar_scouts WHERE user_id = $this->fe_user_id), (SELECT CONCAT(IFNULL(name, ''), ' ', IFNULL(surname, ''), ' - ', IFNULL(email, '')) as name FROM users WHERE id = $this->fe_user_id)) as name"
        )[0]->name ?? null;
    }

    public function getBackendUserAttribute()
    {
        if (empty($this->be_user_id)) {
            return null;
        }

        return Db::select(
            "SELECT CONCAT(IFNULL(first_name, ''), ' ', IFNULL(last_name, ''), ' - ', IFNULL(id, '')) as name FROM backend_users WHERE id = $this->be_user_id"
        )[0]->name ?? null;
    }

    public function getmodelTypeUserFriendlyAttribute()
    {
        if (class_exists($this->model_type) && method_exists($this->model_type, 'getOrganizationTypeModelNameUserFriendly')) {
            return ($this->model_type)::getOrganizationTypeModelNameUserFriendly();
        }
        return $this->model_type;
    }

    public function getRelatedmodelTypeUserFriendlyAttribute()
    {
        if (class_exists($this->related_model_type) && method_exists($this->related_model_type, 'getOrganizationTypeModelNameUserFriendly')) {
            return ($this->related_model_type)::getOrganizationTypeModelNameUserFriendly();
        }
        return $this->related_model_type;
    }

    public function getTranslatedLabelForFiled(string $attribute, string $model): string
    {
        if (class_exists($model) && method_exists($model, 'getTranslatedAttributeNames')) {
            $translatedLabelsForFields = ($model)::getTranslatedAttributeNames('\\' . $model);
        }

        return $translatedLabelsForFields[$attribute] ?? $attribute;
    }

    public function getTranslatedAttributeLabelAttribute(): string
    {
        return $this->getTranslatedLabelForFiled($this->attribute, $this->model_type);
    }

    public function getModelOptions(){
        $modelOptions = self::distinct()->orderBy('model_type', 'asc')->lists('model_type', 'model_type');
        array_walk($modelOptions, function (&$item) {
            if (class_exists($item) && method_exists($item, 'getOrganizationTypeModelNameUserFriendly')) {
                $item = ($item)::getOrganizationTypeModelNameUserFriendly();
            }
            if (empty($item)) {
                $item = 'N/A';
            }
        });
        return $modelOptions;
    }

    public function getRelatedModelOptions(){
        $modelOptions = self::distinct()->orderBy('related_model_type', 'asc')->lists('related_model_type', 'related_model_type');
        array_walk($modelOptions, function (&$item) {
            if (class_exists($item) && method_exists($item, 'getOrganizationTypeModelNameUserFriendly')) {
                $item = ($item)::getOrganizationTypeModelNameUserFriendly();
            }
            if (empty($item)) {
                $item = 'N/A';
            }
        });
        return $modelOptions;
    }

    public function getAttributeOptions(){
        $attributeOptions = self::distinct()->orderBy('attribute', 'asc')->select('attribute', 'model_type')->get()->toArray();
        $options = [];
        foreach ($attributeOptions as $attributeOption) {
            $options[$attributeOption['attribute']] = $attributeOption['attribute'];
            if (class_exists($attributeOption['model_type']) && method_exists($attributeOption['model_type'], 'getTranslatedAttributeNames')) {
                $translatedLabelsForFields = ($attributeOption['model_type'])::getTranslatedAttributeNames('\\' . $attributeOption['model_type']);
                $options[$attributeOption['attribute']] = $translatedLabelsForFields[$attributeOption['attribute']] ?? $attributeOption['attribute'];
            }
        }

        return $options;
    }

    public function getFrontendUserOptions() {
        $frontendUserOptions = Db::select(
            "SELECT IFNULL((SELECT CONCAT(IFNULL(family_name, ''), ' ', IFNULL(given_name, ''), ' - ', IFNULL(ecset_code, '')) as name FROM csatar_csatar_scouts WHERE user_id = fe_user_id), (SELECT CONCAT(IFNULL(name, ''), ' ', IFNULL(surname, ''), ' - ', IFNULL(email, '')) as name FROM users WHERE id = fe_user_id)) as name, fe_user_id as id FROM csatar_csatar_history WHERE fe_user_id IS NOT NULL GROUP BY fe_user_id ORDER BY name ASC"
        );
        $options = [];
        foreach ($frontendUserOptions as $frontendUserOption) {
            $options[$frontendUserOption->id] = $frontendUserOption->name;
        }
        return $options;
    }

    public function getBackendUserOptions() {
        $backendUserOptions = Db::select(
            "SELECT (SELECT CONCAT(IFNULL(first_name, ''), ' ', IFNULL(last_name, ''), ' - ', IFNULL(id, '')) as name FROM backend_users WHERE id = be_user_id) as name, be_user_id as id FROM csatar_csatar_history WHERE be_user_id IS NOT NULL GROUP BY be_user_id ORDER BY name ASC"
        );
        $options = [];
        foreach ($backendUserOptions as $backendUserOption) {
            $options[$backendUserOption->id] = $backendUserOption->name;
        }
        return $options;
    }
}
