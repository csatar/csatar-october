<?php 
namespace Csatar\Csatar\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class History extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'csatar.manage.data', 
        'csatar.admin' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-history');
    }
}
