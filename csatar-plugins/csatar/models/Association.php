<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class Association extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_associations';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'contact_name' => 'required|min:5',
        'contact_email' => 'required|email',
        'address' => 'required|min:5',
        'bank_account' => 'min:5',
        'leadership_presentation' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'contact_name',
        'contact_email',
        'address',
        'leadership_presentation',
    ];
    
    /**
     * Relations
     */

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];
}
