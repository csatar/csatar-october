<?php 
namespace Csatar\Csatar\Models;

use Auth;
use Model;
use Csatar\Csatar\Classes\Enums\Gender;
use Csatar\Csatar\Classes\Enums\InjurySeverity;
use Lang;

/**
 * Model
 */
class AccidentLogRecord extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \October\Rain\Database\Traits\Nullable;

    use \Csatar\Csatar\Traits\History;

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
        'accident_date_time',
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

    public static function getAttributesWithLabels(bool $forTable = false){
        if ($forTable) {
            return [
                'accident_date_time'    => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.accidentDateTime'),
                'injured_person_age'    => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.injuredPersonAge'),
                'injury_severity'   => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.injurySeverity.injurySeverity'),
                'createdBy'   => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.createdBy'),
            ];
        }

        return [
            'created_at'    => Lang::get('csatar.csatar::lang.plugin.admin.general.createdAt'),
            'updated_at'    => Lang::get('csatar.csatar::lang.plugin.admin.general.updatedAt'),
            'accident_date_time'    => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.accidentDateTime'),
            'examiner_name' => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.examinerName'),
            'instructors'   => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.instructors'),
            'program_name'  => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.programName'),
            'program_type'  => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.programType'),
            'location'  => Lang::get('csatar.csatar::lang.plugin.admin.general.location'),
            'activity'  => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.activity'),
            'reason'    => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.reason'),
            'injured_person_age'    => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.injuredPersonAge'),
            'injured_person_gender_list' => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.injuredPersonGender'),
            'injured_person_name'   => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.injuredPersonName'),
            'injury'    => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.injury'),
            'injury_severity_list'   => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.injurySeverity.injurySeverity'),
            'skipped_days_number'   => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.skippedDaysNumber'),
            'tools_used'    => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.toolsUsed'),
            'transport_to_doctor'   => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.transportToDoctor'),
            'evacuation'    => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.evacuation'),
            'persons_involved_in_care'  => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.personsInvolvedInCare'),
            'attachmentLinks' => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.attachments'),
            'url'   => Lang::get('csatar.csatar::lang.plugin.admin.general.url'),
            'createdBy'   => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.createdBy'),
        ];
    }

    public function beforeCreate() {
        if (!Auth::user()) {
            return;
        }

        $this->user_id = Auth::user()->id;
    }

    public function getInjuredPersonGenderOptions() {
        return Gender::getOptionsWithLabels();
    }

    public function getInjurySeverityOptions() {
        return InjurySeverity::getOptionsWithLabels();
    }

    public function getInjuredPersonGenderListAttribute($value)
    {
        return Gender::getOptionsWithLabels()[$value] ?? null;
    }

    public function getInjurySeverityListAttribute($value)
    {
        return InjurySeverity::getOptionsWithLabels()[$value] ?? null;
    }

    public function getCreatedByAttribute($value)
    {
        return $this->user->name ?? null;
    }

    public function getAttachmentLinksAttribute(){
        if (empty($this->attachments)) {
            return null;
        }

        return $this->attachments->pluck('path', 'file_name');

    }
}
