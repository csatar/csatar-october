<?php namespace Csatar\Csatar\Models;

use Model;

/**
 * Model
 */
class GalleryModelPivot extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \Csatar\Csatar\Traits\History;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_gallery_model';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = [
        'model_type',
    ];

    public $morphTo = [
        'model' => []
    ];

}
