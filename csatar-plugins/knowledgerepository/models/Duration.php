<?php namespace Csatar\KnowledgeRepository\Models;

use Model;
use Lang;

/**
 * Model
 */
class Duration extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_durations';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'min' => 'required',
        'max' => 'required',
    ];

    public $fillable = [
        'name',
        'min',
        'max',
    ];

    public $nullable = [
        'min',
        'max',
    ];

    public function beforeSave()
    {
        if ($this->min == null) {
            $this->min = 0;
        }
        if ($this->max == null) {
            $this->max = 0;
        }

        $this->name = $this->generateNameFromMinMax($this->min, $this->max);
    }

    public function filterFields($fields, $context = null) {
        // fill name based on min and max
        if (isset($fields->min) && isset($fields->max)) {
            $fields->name->value = $this->generateNameFromMinMax($fields->min->value, $fields->max->value);
        }

    }

    public function generateNameFromMinMax($min, $max) {
        return $min . '-' . $max . ' ' . Lang::get('csatar.knowledgerepository::lang.plugin.admin.general.minute');
    }
}
