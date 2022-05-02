<?php namespace Csatar\Forms\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Lang;

class Forms extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController',
        ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Forms', 'main-menu-item', 'forms-menu-item');
    }
}
