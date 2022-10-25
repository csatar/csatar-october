<?php

namespace Csatar\Csatar\Classes\Enums;

class Gender
{
    public const MALE = 1;
    public const FEMALE = 2;
    public const OTHER = 3;

    public static $optionsWithLables = [];

    public static function getGptionsWithLables(){
        return self::$optionsWithLables = [
            self::MALE => e(trans('csatar.csatar::lang.plugin.admin.scout.gender.male')),
            self::FEMALE => e(trans('csatar.csatar::lang.plugin.admin.scout.gender.female')),
            self::OTHER => e(trans('csatar.csatar::lang.plugin.admin.scout.gender.other')),
        ];
    }
}
