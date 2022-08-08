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
    public function getExtendedNameAttribute()
    {
        return $this->attributes['name'];
    }

    public function filterNameForWords($name, $filterWords){
        $filterWords = array_map('trim',$filterWords);
        $nameExploded = explode(' ', $name);
        $nameFiltered = array_map(function($word) use ($filterWords){
            if(in_array(mb_strtolower($word), $filterWords)){
                return '';
            }

            return $word;
        }, $nameExploded);

        return trim(implode(' ', $nameFiltered));
    }
}
