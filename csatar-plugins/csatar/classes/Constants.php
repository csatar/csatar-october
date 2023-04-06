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
}
