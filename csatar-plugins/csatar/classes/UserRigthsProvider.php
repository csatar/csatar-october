<?php

namespace Csatar\Csatar\Classes;

use Auth;
use Model;
use Session;

class UserRigthsProvider
{
    public static function getUserRigths(Model $record, bool $ignoreCache)
    {
        if (Auth::user()) {
            if (!empty(Auth::user()->scout)) {
                return Auth::user()->scout->getRightsForModel($record, $ignoreCache);
            }
            if (AccidentLogRigthsProvider::isAccidentLogUser(Auth::user())) {
                return AccidentLogRigthsProvider::getAccidentLogRights(Auth::user(), $record) ?? $record->getGuestRightsForModel();
            }
        }

        return $record->getGuestRightsForModel();
    }
}
