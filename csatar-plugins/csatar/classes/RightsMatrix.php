<?php

namespace Csatar\Csatar\Classes;

use Db;

class RightsMatrix
{
    public static function getRightsMatrixLastUpdateTime() {
        return Db::table('csatar_csatar_mandates_permissions')->
            select('updated_at')->orderBy('updated_at','DESC')->first()->updated_at;
    }
}
