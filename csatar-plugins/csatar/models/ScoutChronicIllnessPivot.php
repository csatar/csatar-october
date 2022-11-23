<?php namespace Csatar\Csatar\Models;

use October\Rain\Database\Pivot;

/**
 * Pivot Model
 */
class ScoutChronicIllnessPivot extends Pivot
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_scouts_chronic_illnesses';

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'comment',
    ];
}
