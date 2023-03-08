<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Classes\Enums\Status;
use Lang;
use Model;
use ValidationException;

/**
 * Model
 */
class MembershipCard extends Model
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
    public $table = 'csatar_csatar_membership_cards';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'rfid_tag',
        'active',
        'note',
        'issued_date_time',
        'scout_id',
    ];

    /**
     * Relations
     */
    public $belongsTo = [
        'scout' => '\Csatar\Csatar\Models\Scout',
    ];
    
    /**
    * Add custom validation
    */
    public function beforeValidate()
    {
        // if the scout assigned to this card is inactive, then this card cannot be set to active
        if ($this->active == 1 && $this->scout->inactivated_at != null) {
            throw new ValidationException(['active' => Lang::get('csatar.csatar::lang.plugin.admin.membershipCard.inactiveScoutError')]);
        }
    }

    public function afterSave() {
        if (!empty($this->scout_id)) {
            $membershipCards = MembershipCard::where('scout_id', $this->scout_id)
                ->where('active', Status::ACTIVE)
                ->orderBy('issued_date_time', 'desc')
                ->get();

            if (isset($membershipCards[0])) {
                unset($membershipCards[0]);
            }

            $membershipCards->each->update(['active' => Status::INACTIVE]);
        }
    }
}
