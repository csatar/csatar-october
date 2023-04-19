<?php 
namespace Csatar\Csatar\Models;

use Model;
use Lang;

/**
 * Model
 */
class ContentPage extends PermissionBasedAccess
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    use \Csatar\Csatar\Traits\History;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'csatar_csatar_content_pages';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var array Fillable values
     */
    public $fillable = [
        'title',
        'content'
    ];

    public $morphTo = [
        'model' => []
    ];

    public static function getOrganizationTypeModelNameUserFriendly()
    {
        return Lang::get('csatar.csatar::lang.plugin.admin.general.contentPage');
    }
}
