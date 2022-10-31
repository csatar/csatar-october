<?php namespace Csatar\Csatar\Controllers;

use BackendMenu;
use Flash;
use Lang;
use Backend\Classes\Controller;

class SeederData extends Controller
{
    public $implement = [
        \Backend\Behaviors\ImportExportController::class
    ];

    public $seederData, $testData, $importData, $data;
    public $importExportConfig = 'config_import_export.yaml';
    
    public function __construct()
    {
        // initialize values
        $this->vars['seederData'] = \Csatar\Csatar\Updates\SeederData::DATA;
        $this->vars['testData'] = \Csatar\Csatar\Updates\TestData::DATA;

        // call parent constructor
        parent::__construct();
    }

    public function seeder()
    {
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-seeder-data');
    }

    public function test()
    {
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-test-data');
    }

    public function import()
    {
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-import-data');
    }

    public function onSeederDataUpdateButtonClick()
    {
        $seederData = new \Csatar\Csatar\Updates\SeederData();
        $seederData->run();
        Flash::success(Lang::get('csatar.csatar::lang.plugin.admin.admin.seederData.updateDataSuccess'));
    }

    public function onTestDataUpdateButtonClick()
    {
        $testData = new \Csatar\Csatar\Updates\TestData();
        $testData->run();
        Flash::success(Lang::get('csatar.csatar::lang.plugin.admin.admin.seederData.updateDataSuccess'));
    }

    public function onImportDataButtonClick()
    {
    }
}
