<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class District extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_districts';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'phone' => 'required|regex:(^[0-9+-.()]{5,}$)',
        'email' => 'required|email',
        'website' => 'url',
        'facebook_page' => 'url|regex:(facebook)',
        'contact_name' => 'required|min:5',
        'contact_email' => 'required|email',
        'address' => 'required|min:5',
        'bank_account' => 'min:5',
        'leadership_presentation' => 'required',
        'description' => 'required',
        'association_id' => 'required',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'phone',
        'email',
        'contact_name',
        'contact_email',
        'address',
        'leadership_presentation',
        'description',
        'association_id',
    ];
    
    /**
     * Relations
     */
    
    public $belongsTo = [
        'association' => '\Csatar\Csatar\Models\Association',
    ];

    public $attachOne = [
        'logo' => 'System\Models\File'
    ];
    
    /**
     * Scope a query to only include districts with a given association id.
     */
    public function scopeAssociationId($query, $id)
    {
        return $query->where('association_id', $id);
    }
}
