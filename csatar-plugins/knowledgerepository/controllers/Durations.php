<?php namespace Csatar\KnowledgeRepository\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Durations extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.KnowledgeRepository', 'main-menu-knowledge-repository-parameters', 'side-menu-duration');
    }

    public $requiredPermissions = [
        'csatar.manage.data'
    ];
}
