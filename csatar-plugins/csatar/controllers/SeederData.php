<?php namespace Csatar\Csatar\Controllers;

use BackendMenu;
use Csatar\Csatar\Models\MandatePermission;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\PermissionBasedAccess;
use Flash;
use Lang;
use Db;
use Backend\Classes\Controller;

class SeederData extends Controller
{
    public $seederData, $testData, $data;

    public function __construct()
    {
        // initialize values
        $this->vars['seederData'] = \Csatar\Csatar\Updates\SeederData::DATA;
        $this->vars['testData'] = \Csatar\Csatar\Updates\TestData::DATA;

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

    public function test()
    {
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-test-data');
    }

    public function location()
    {
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-location-data');
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

    public function onLocationDataUpdateButtonClick()
    {
        $locationData = new \Csatar\Csatar\Updates\LocationData();
        $locationData->run();
        Flash::success(Lang::get('csatar.csatar::lang.plugin.admin.admin.seederData.updateDataSuccess'));
    }
}
