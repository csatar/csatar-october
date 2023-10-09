<?php namespace Csatar\KnowledgeRepository\Models;

use Db;
use Model;
use Csatar\Csatar\Models\AgeGroup;
/**
 * Model
 */
class AgeGroupMatching extends Model
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
    public $table = 'csatar_knowledgerepository_age_group_matchings';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'age_group1_id' => 'required',
        'age_group2_id' => 'required',
    ];

    public function beforeValidate() {
        $existingAgeGroupMatching = AgeGroupMatching::where('age_group1_id', $this->age_group1_id)
            ->where('age_group2_id', $this->age_group2_id)
            ->first();
        if ($existingAgeGroupMatching !== null) {
            throw new \ValidationException(['age_group1_id' => e(trans('csatar.knowledgerepository::lang.plugin.admin.ageGroupMatchings.ageGroupMatchingsAlreadyExists'))]);
        }
    }

    public function getAgeGroup1IdOptions() {
        return AgeGroup::orderBy('name', 'asc')
            ->select(Db::raw("concat((SELECT `name_abbreviation` FROM csatar_csatar_associations
                    WHERE id = association_id), ' - ', name) as name, id"))
            ->lists('name', 'id');
    }

    public function getAgeGroup2IdOptions() {
        if ($this->age_group1_id !== null) {
            $selected1AgeGroup = AgeGroup::where('id', $this->age_group1_id)->first();
            return AgeGroup::where('id', '<>', $this->age_group1_id)
                ->where('association_id', '<>', $selected1AgeGroup->association_id)
                ->orderBy('name', 'asc')
                ->select(Db::raw("concat((SELECT `name_abbreviation` FROM csatar_csatar_associations
                    WHERE id = association_id), ' - ', name) as name, id"))
                ->lists('name', 'id');
        }

        return [];
    }
}
