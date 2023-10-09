<?php namespace Csatar\KnowledgeRepository\Models;

use Csatar\Csatar\Models\Association;
use Model;
/**
 * Model
 */
class SharingSetting extends Model
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
    public $table = 'csatar_knowledgerepository_sharing_settings';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function getAssociationIdOptions() {
        return Association::lists('name', 'id');
    }

    public function getAssociation2IdOptions() {
        if ($this->association_id !== null) {
            return Association::where('id', '<>', $this->association_id)->lists('name', 'id');
        }

        return [];
    }

    public function beforeValidate() {
        $existingSharingSetting = SharingSetting::where('association_id', $this->association_id)
            ->where('association2_id', $this->association2_id)
            ->first();
        if ($existingSharingSetting !== null) {
            throw new \ValidationException(['association_id' => e(trans('csatar.knowledgerepository::lang.plugin.admin.sharingSettings.sharingSettingsAlreadyExists'))]);
        }
    }
}
