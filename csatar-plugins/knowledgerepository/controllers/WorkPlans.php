<?php
namespace Csatar\KnowledgeRepository\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class WorkPlans extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'csatar.manage.knowledgerepository'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.KnowledgeRepository', 'main-menu-knowledge-repository', 'side-menu-work-plans');
    }

}
