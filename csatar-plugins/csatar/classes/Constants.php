<?php

namespace Csatar\Csatar\Classes;

class Constants
{
    public const AVAILABLE_RELATION_TYPES = [
        'belongsTo',
        'belongsToMany',
        'hasMany',
        'attachOne',
        'hasOne',
        'morphTo',
        'morphOne',
        'morphMany',
        'morphToMany',
        'morphedByMany',
        'attachMany',
        'hasManyThrough',
        'hasOneThrough'
    ];

    public const MANDATE_TYPE_TEAM_LEADER = 'Csapatvezető';
    public const MANDATE_TYPE_DEPUTY_TEAM_LEADER = 'Csapatvezető helyettes';
}
