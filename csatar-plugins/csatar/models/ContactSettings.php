<?php namespace Csatar\Csatar\Models;

use Model;

class ContactSettings extends Model
{
    use \Csatar\Csatar\Traits\History;

    /**
     * @var array implement these behaviors
     */
    public $implement = [
        \System\Behaviors\SettingsModel::class
    ];

    /**
     * @var string settingsCode unique to this model
     */
    public $settingsCode = 'csatar_csatar_contact_settings';

    /**
     * @var string settingsFields configuration
     */
    public $settingsFields = 'fields.yaml';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'contact_email' => 'email|nullable',
        'address' => 'min:5|nullable',
        'bank_account' => 'min:5|nullable',
        'phone_numbers' => 'regex:(^[0-9+-.()]{5,}$)|nullable',
    ];

    protected $jsonable = ['offices'];

    public $morphMany = [
        'history' => [
            \Csatar\Csatar\Models\History::class,
            'name' => 'history',
            'ignoreInPermissionsMatrix' => true,
        ],
    ];
}
