<?php namespace Csatar\Forms\Models;

use File;
use Lang;
use Model;
use Validator;
use October\Rain\Exception\ApplicationException;
use \October\Rain\Exception\ValidationException;


/**
 * Model
 */
class Form extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
        'title' => 'required',
    ];



    public function beforeSave()
    {
        $model = '\\' . $this->model;
        if (! class_exists($model)) {
            $error = e(trans('csatar.forms::lang.errors.formModelNotFound'));
            throw new ValidationException(['model' => $error]);
        }

        //TODO in v2: before save check if yaml file exists...
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

    public function getFieldsConfig() {
        if ($this->fields_config[0] != '$') {
            return '$/' . str_replace('\\', '/', strtolower($this->model)) . '/' . $this->fields_config;
        }

        return $this->fields_config;
    }

}
