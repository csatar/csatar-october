<?php namespace Csatar\KnowledgeRepository\Models;

use Model;

/**
 * Model
 */
class Location extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\Sortable;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_knowledgerepository_locations';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'name',
        'note',
        'description',
        'sort_order',
    ];

    public $nullable = [
        'note',
        'description',
        'sort_order',
    ];

    public function beforeSave()
    {
        if (empty($this->sort_order))
        {
            $this->sort_order = static::max('sort_order') + 1;
        }
    }
}
