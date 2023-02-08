<?php

namespace Csatar\Csatar\Classes\Enums;

class Status
{
    public const INACTIVE = 0;
    public const ACTIVE = 1;
    public const SUSPENDED = 2;
    public const FORMING = 3;

    public static array $optionsWithLabels = [];

    public static function getOptionsWithLabels(){
        return self::$optionsWithLabels = [
            self::ACTIVE => e(trans('csatar.csatar::lang.plugin.admin.general.active')),
            self::INACTIVE => e(trans('csatar.csatar::lang.plugin.admin.general.inactive')),
        ];
    }
}
