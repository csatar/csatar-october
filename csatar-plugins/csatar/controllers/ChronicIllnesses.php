<?php
namespace Csatar\Csatar\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class ChronicIllnesses extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-scout-system-data', 'side-menu-chronic-illness');
    }

    public $requiredPermissions = [
        'csatar.manage.data'
    ];
}
