<?php namespace Csatar\Csatar\Models;

use Auth;
use Model;
use Csatar\Csatar\Classes\Enums\Gender;
use Csatar\Csatar\Classes\Enums\InjurySeverity;

/**
 * Model
 */
class AccidentLogRecord extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \October\Rain\Database\Traits\Nullable;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_accident_log_records';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'attachments' => 'nullable|max:5',
        'attachments.*' => 'mimes:jpeg,jpg,pdf,doc,docx,png|max:5120',
    ];

    public $fillable = [
        'accident_date_time',
        'examiner_name',
        'instructors',
        'program_name',
        'program_type',
        'location',
        'activity',
        'reason',
        'injured_person_age',
        'injured_person_gender',
        'injured_person_name',
        'injury',
        'injury_severity',
        'skipped_days_number',
        'tools_used',
        'transport_to_doctor',
        'evacuation',
        'persons_involved_in_care',
        'url',
        'user_id',
    ];

    protected $nullable = [
        'injured_person_age',
        'injured_person_gender',
        'injury_severity',
        'attachments',
    ];

    public $attachMany = [
        'attachments' => 'System\Models\File'
    ];

    public $belongsTo = [
        'user' => [
            '\Rainlab\User\Models\User',
            'label' => 'csatar.csatar::lang.plugin.admin.scout.user',
        ],
    ];

    public static function getModelName()
    {
        return '\\' . static::class;
    }

    public function beforeCreate() {
        if (!Auth::user()) {
            return;
        }
        $this->user_id = Auth::user()->id;
    }

    public function getInjuredPersonGenderOptions() {
        return Gender::getGptionsWithLables();
    }

    public function getInjurySeverityOptions() {
        return InjurySeverity::getGptionsWithLables();
    }
}
