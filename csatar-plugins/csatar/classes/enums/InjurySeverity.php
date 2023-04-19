<?php

namespace Csatar\Csatar\Classes\Enums;

class InjurySeverity
{
    public const SLIGHT  = 1;
    public const MEDIUM  = 2;
    public const SERIOUS = 3;
    public const FATAL   = 4;


    public static $optionsWithLabels = [];

    public static function getOptionsWithLabels(){
        return self::$optionsWithLabels = [
            self::SLIGHT => e(trans('csatar.csatar::lang.plugin.component.accidentLog.injurySeverity.slight')),
            self::MEDIUM => e(trans('csatar.csatar::lang.plugin.component.accidentLog.injurySeverity.medium')),
            self::SERIOUS => e(trans('csatar.csatar::lang.plugin.component.accidentLog.injurySeverity.serious')),
            self::FATAL => e(trans('csatar.csatar::lang.plugin.component.accidentLog.injurySeverity.fatal')),
        ];
    }
}
