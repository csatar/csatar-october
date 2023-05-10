<?php
namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class ModelExtended extends Model
{
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }
}
