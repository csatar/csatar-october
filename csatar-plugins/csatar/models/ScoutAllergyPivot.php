<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Classes\CsatarPivot;

/**
 * Pivot Model
 */
class ScoutAllergyPivot extends CsatarPivot
{
    use \Csatar\Csatar\Traits\History;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_scouts_allergies';

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'comment',
    ];

    public $morphMany = [
        'history' => [
            \Csatar\Csatar\Models\History::class,
            'name' => 'history'
        ],
    ];
}
