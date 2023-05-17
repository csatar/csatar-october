<?php
namespace Csatar\Csatar\Models;

use Csatar\Csatar\Classes\Constants;
use Model;

/**
 * Model
 */
class ModelExtended extends Model
{

    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * @param $model
     * @return array
     */
    public static function getFieldsAndRelationArrays($model): array
    {
        $fields         = $model->fillable ?? [];
        $fields         = array_merge($fields, $model->additionalFieldsForPermissionMatrix ?? []);
        $relationArrays = Constants::AVAILABLE_RELATION_TYPES;
        return [$fields, $relationArrays];
    }

}
