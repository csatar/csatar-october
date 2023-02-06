<?php namespace Csatar\Csatar\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Csatar\Csatar\Classes\BackendExtensions;

use ApplicationException;
use Lang;
use Flash;

class MandateTypes extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-organization-system-data', 'side-menu-item-mandate-types');
    }

    public $requiredPermissions = [
        'csatar.manage.data'
    ];

    public function onDelete(){
        return BackendExtensions::onDelete($this);
    }
}
