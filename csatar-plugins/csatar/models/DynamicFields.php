<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\TeamReport;
use DateTime;
use Lang;
use Model;
use ValidationException;

/**
 * Model
 */
class DynamicFields extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_dynamic_fields';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'association' => 'required',
        'start_date' => 'required',
        'model' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'association_id',
        'start_date',
        'end_date',
        'model',
        'extra_fields_max_id',
        'extra_fields_definition',
    ];

    protected $jsonable = ['extra_fields_definition'];

    /**
     * Relations
     */
    public $belongsTo = [
        'association' => '\Csatar\Csatar\Models\Association',
    ];
    
    /**
     * Add custom validation
     */
    public function beforeValidate()
    {
        // check that no other dynamic field item is already existing, which overlaps with the given period
        if (isset($this->association_id) && isset($this->model) && isset($this->start_date)) {
            $startDate = new DateTime($this->start_date);
            $endDate = isset($this->end_date) ? new DateTime($this->end_date) : null;

            $existingDynamicFields = self::where('association_id', $this->association_id)->where('model', $this->model)->get();

            foreach ($existingDynamicFields as $existingDynamicField) {
                // if we are editing the dynamic fields record: the record shouldn't be compared to itself
                if ($this->id == $existingDynamicField->id) {
                    continue;
                }
    
                // check that the date isn't (partially) overlapping with a different record for the same period: overlap if max(start1, start2) < min(end1, end2)
                $existingDynamicFieldStartDate = new DateTime($existingDynamicField['start_date']);
                $existingDynamicFieldEndDate = isset($existingDynamicField['end_date']) ? new DateTime($existingDynamicField['end_date']) : null;

                $this->validateDates($existingDynamicFieldStartDate, $existingDynamicFieldEndDate, $startDate, $endDate);
            }
        }
    }

    private function validateDates($existingDynamicFieldStartDate, $existingDynamicFieldEndDate, $startDate, $endDate)
    {
        if (($endDate !== null && $existingDynamicFieldEndDate !== null && max($startDate, $existingDynamicFieldStartDate) < min($endDate, $existingDynamicFieldEndDate))
            || ($endDate == null && max($startDate, $existingDynamicFieldStartDate) < $existingDynamicFieldEndDate)
            || ($existingDynamicFieldEndDate == null && max($startDate, $existingDynamicFieldStartDate) < $endDate)
            || ($endDate == null && $existingDynamicFieldEndDate == null)) {
                throw new ValidationException(['start_date' => Lang::get('csatar.csatar::lang.plugin.admin.dynamicFields.overlappingDynamicFieldsError')]);
        }
    }

    public function beforeSave()
    {
        // add unique IDs and other parameters
        $fields = isset($this->original['extra_fields_definition']) ? json_decode($this->original['extra_fields_definition'], true) : [];
        $maxId = $this->extra_fields_max_id ?? 0;
        $fieldValues = $this->extra_fields_definition;

        // delete fields
        $this->beforeSaveDeleteFields($fields, $fieldValues);

        // add new fields
        $this->beforeSaveAddFields($fields, $fieldValues, $maxId);

        // update values
        $this->extra_fields_max_id = $this->extra_fields_max_id < $maxId ? $maxId : $this->extra_fields_max_id;
        $this->extra_fields_definition = $fields;
    }

    private function beforeSaveDeleteFields(&$fields, &$fieldValues)
    {
        $fieldsToDelete = [];
        foreach ($fields as $key => $field) {            
            $found = false;
            foreach ($fieldValues as $fieldValue) {
                if ($fieldValue['label'] == $field['label']) {
                    $found = true;
                    break;
                }
            }
    
            if (!$found) {
                array_push($fieldsToDelete, $key);
            }
        }
        foreach ($fieldsToDelete as $key) {
            unset($fields[$key]);
        }
    }

    private function beforeSaveAddFields(&$fields, &$fieldValues, &$maxId)
    {
        foreach ($fieldValues as $fieldValue) {
            $found = false;
            foreach ($fields as &$field) {
                if ($fieldValue['label'] == $field['label']) {
                    $field['required'] = $fieldValue['required'];
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                array_push($fields, [
                    'label' => $fieldValue['label'],
                    'required' => $fieldValue['required'],
                    'id' => $maxId,
                    'type' => 'textarea',
                    'size' => 'tiny',
                    'span' => 'auto',
                ]);
                $maxId++;
            }
        }
    }

    function getModelOptions()
    {
        return [
            TeamReport::getModelName() => TeamReport::getOrganizationTypeModelNameUserFriendly(),
        ];
    }

    function getOrganizationTypeModelNameUserFriendlyAttribute()
    {
        return $this->attributes['model']
            ? ($this->attributes['model'])::getOrganizationTypeModelNameUserFriendly()
                : '';
    }
}
