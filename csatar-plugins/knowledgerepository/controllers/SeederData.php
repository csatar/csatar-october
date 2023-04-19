<?php
namespace Csatar\KnowledgeRepository\Controllers;

use BackendMenu;
use Flash;
use Lang;
use Db;
use Backend\Classes\Controller;

class SeederData extends Controller
{
    public array $seederData;

    public function __construct()
    {
        // initialize values
        $this->vars['seederData'] = \Csatar\KnowledgeRepository\Updates\SeederData::DATA;

        // call parent constructor
        parent::__construct();
    }

    public $requiredPermissions = [
        'csatar.admin'
    ];

    public function seeder()
    {
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-seeder-data');
    }

    public function onSeederDataUpdateButtonClick()
    {
        $seederData = new \Csatar\KnowledgeRepository\Updates\SeederData();
        $seederData->run();
        Flash::success(Lang::get('csatar.csatar::lang.plugin.admin.admin.seederData.updateDataSuccess'));
    }
}
