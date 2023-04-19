<?php
namespace Csatar\Csatar\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Districts extends Controller
{
    public $implement = ['Backend\Behaviors\ListController', 'Backend\Behaviors\FormController', 'Backend\Behaviors\RelationController'];

    public $listConfig     = 'config_list.yaml';
    public $formConfig     = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-organization-system-data', 'side-menu-item-districts');
    }

    public $requiredPermissions = [
        'csatar.manage.data'
    ];
}
