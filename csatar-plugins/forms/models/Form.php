<?php
namespace Csatar\Forms\Models;

use Cache;
use File;
use Lang;
use Model;
use Validator;
use October\Rain\Exception\ValidationException;
use October\Rain\Parse\Yaml;

/**
 * Model
 */
class Form extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sluggable;
    /*
     * Validation
     */
    public $rules = [
        'title' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'title',
        'model',
        'fields_config',
        'description',
    ];

    protected $slugs = ['slug' => 'title'];

    public function beforeSave()
    {
        $this->getModelName();

        // TODO in v2: before save check if yaml file exists...
    }

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_forms_forms';

    public function getFieldsConfig()
    {
        if ($this->fields_config[0] != '$') {
            return '$/' . str_replace('\\', '/', strtolower($this->model)) . '/' . $this->fields_config;
        }

        return $this->fields_config;
    }

    public function getModelName()
    {
        $modelName = $this->model;
        if (substr( $modelName, 0, 1 ) !== "\\") {
            $modelName = '\\' . $modelName;
        }

        if (! class_exists($modelName)) {
            $error = e(trans('csatar.forms::lang.errors.formModelNotFound'));
            throw new ValidationException(['model' => $error]);
        }

        return $modelName;
    }
}
