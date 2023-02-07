<?php namespace Csatar\KnowledgeRepository\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class GameDevelopmentGoals extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController'    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'csatar.admin'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.KnowledgeRepository', 'main-menu-knowledge-repository-parameters', 'side-menu-item');
    }

    public $fillable = [
        'name',
        'sort_order',
        'note'
    ];

    public $nullable = [
        'note',
        'sort_order'
    ];

    public function beforeValidate()
    {
        if (empty($this->sort_order))
        {
            $this->sort_order = static::max('sort_order') + 1;
        }
    }
}
