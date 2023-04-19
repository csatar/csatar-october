<?php namespace Csatar\Csatar\Models;

use DateTime;
use Lang;
use ValidationException;
use Csatar\Csatar\Classes\CsatarPivot;
use Csatar\Csatar\Models\Training;

/**
 * Pivot Model
 */
class ScoutLeadershipQualificationPivot extends CsatarPivot
{
    use \October\Rain\Database\Traits\Validation;

    use \Csatar\Csatar\Traits\History;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_scouts_leadership_qualifications';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'date' => 'required',
        'location' => 'required',
        'qualification_certificate_number' => 'required',
        'training_id' => 'required',
        'qualification_leader' => 'required',
    ];

    /**
     * Add custom validation
     */
    public function beforeValidate()
    {
        // set the attribute names
        $this->setValidationAttributeNames([
            'date' => Lang::get('csatar.csatar::lang.plugin.admin.general.date'),
            'location' => Lang::get('csatar.csatar::lang.plugin.admin.general.location'),
            'qualification_certificate_number' => Lang::get('csatar.csatar::lang.plugin.admin.general.qualificationCertificateNumber'),
            'training_id' => Lang::get('csatar.csatar::lang.plugin.admin.general.training'),
            'qualification_leader' => Lang::get('csatar.csatar::lang.plugin.admin.general.qualificationLeader'),
        ]);

        // check that the date is not in the future
        if (isset($this->date) && (new DateTime($this->date) > new DateTime())) {
            throw new ValidationException(['date' => Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.dateInTheFuture')]);
        }
    }

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'date',
        'location',
        'qualification_certificate_number',
        'training_id',
        'qualification_leader',
    ];

    public function getTrainingIdOptions(){
        return Training::lists('name', 'id');
    }

    public function beforeSave() {
        if ($this->training_id) {
            $trainingName        = Training::find($this->training_id)->name ?? null;
            $this->training_name = Training::find($this->training_id)->name;
        }
    }

}
