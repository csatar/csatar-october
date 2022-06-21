<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class OrganizationBase extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /**
     * Returns the name of the organization
     */
    public function getNameAttribute()
    {
        return $this->attributes['name'];
    }
}
