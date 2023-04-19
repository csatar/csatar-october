<?php
namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class MethodologyType extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\Sortable;

    use \Csatar\Csatar\Traits\History;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_methodology_types';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'name',
        'sort_order',
    ];

    public $nullable = [
        'name',
        'sort_order',
    ];

    public $belongsToMany = [
        'methodologies' => '\Csatar\Csatar\Models\Methodology'
    ];


    public function beforeSave()
    {
        if (empty($this->sort_order)) {
            $this->sort_order = static::max('sort_order') + 1;
        }
    }
}
