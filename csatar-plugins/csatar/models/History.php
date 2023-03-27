<?php namespace Csatar\Csatar\Models;

use Model;
use Db;
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

    public function getModelClassUserFriendlyAttribute()
    {
        if (class_exists($this->model_class) && method_exists($this->model_class, 'getOrganizationTypeModelNameUserFriendly')) {
            return ($this->model_class)::getOrganizationTypeModelNameUserFriendly();
        }
        return $this->model_class;
    }

    public function getRelatedModelClassUserFriendlyAttribute()
    {
        if (class_exists($this->related_model_class) && method_exists($this->related_model_class, 'getOrganizationTypeModelNameUserFriendly')) {
            return ($this->related_model_class)::getOrganizationTypeModelNameUserFriendly();
        }
        return $this->related_model_class;
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
        return $this->getTranslatedLabelForFiled($this->attribute, $this->model_class);
    }
}
