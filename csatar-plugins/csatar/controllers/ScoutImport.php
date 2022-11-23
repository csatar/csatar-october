<?php namespace Csatar\Csatar\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

class ScoutImport extends Controller
{
    public $implement = [
        \Csatar\Csatar\Behaviors\ImportExportControllerForScoutImport::class
    ];

    public $importExportConfig = 'config_import_export.yaml';
    
    public function __construct($parent = null)
    {
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-import-data');

        // call parent constructor
        parent::__construct();
    }
}
