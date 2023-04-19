<?php

namespace Csatar\Csatar\Classes;

use Auth;
use Model;
use Session;
use Csatar\Csatar\Models\PermissionBasedAccess;

class UserRigthsProvider
{

    public static function getUserRigths(Model $record, bool $ignoreCache)
    {
        if (Auth::user()) {
            if (!empty(Auth::user()->scout) && $record instanceof PermissionBasedAccess) {
                return Auth::user()->scout->getRightsForModel($record, $ignoreCache);
            }

            if (AccidentLogRigthsProvider::isAccidentLogUser(Auth::user())) {
                return AccidentLogRigthsProvider::getAccidentLogRights(Auth::user(), $record) ?? $record->getGuestRightsForModel();
            }
        }

        if ($record instanceof PermissionBasedAccess) {
            return $record->getGuestRightsForModel();
        }

        return null;
    }

}
