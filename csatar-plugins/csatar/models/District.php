<?php namespace Csatar\Csatar\Models;

use Lang;
use Csatar\Csatar\Models\OrganizationBase;

/**
 * Model
 */
class District extends OrganizationBase
{
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
        'website' => 'url|nullable',
        'facebook_page' => 'url|regex:(facebook)|nullable',
        'contact_name' => 'required|min:5',
        'contact_email' => 'required|email',
        'address' => 'required|min:5',
        'bank_account' => 'min:5|nullable',
        'leadership_presentation' => 'required',
        'description' => 'required',
        'association' => 'required',
        'logo' => 'image|nullable',
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'name',
        'phone',
        'email',
        'website',
        'facebook_page',
        'coordinates',
        'contact_name',
        'contact_email',
        'address',
        'leadership_presentation',
        'description',
        'bank_account',
        'association_id',
        'logo',
    ];

    /**
     * Relations
     */

    public $belongsTo = [
        'association' => '\Csatar\Csatar\Models\Association',
    ];

    public $hasMany = [
        'teams' => '\Csatar\Csatar\Models\Team',
    ];

    public $attachOne = [
        'logo' => 'System\Models\File',
    ];

    public $morphOne = [
        'content_page' => ['\Csatar\Csatar\Models\ContentPage', 'name' => 'model']
    ];

    /**
     * Override the getExtendedNameAttribute function
     */
    public function getExtendedNameAttribute()
    {
        return isset($this->attributes['name']) ? $this->attributes['name'] . ' ' . Lang::get('csatar.csatar::lang.plugin.admin.district.nameSuffix') : null;
    }

    /**
     * Scope a query to only include districts with a given association id.
     */
    public function scopeAssociationId($query, $id)
    {
        return $query->where('association_id', $id);
    }

    public function beforeSave()
    {
        $filterWords = explode(',', Lang::get('csatar.csatar::lang.plugin.admin.district.filterOrganizationUnitNameForWords'));
        $this->name = $this->filterNameForWords($this->name, $filterWords);
    }
}
