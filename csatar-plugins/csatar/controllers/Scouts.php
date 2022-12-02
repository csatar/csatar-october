<?php namespace Csatar\Csatar\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Csatar\Csatar\Models\Scout;
use Flash;
use Lang;

class Scouts extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController', 'Backend\Behaviors\RelationController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-scout');
    }

    public function onResetFA()
    {
        if (isset($this->params[0])) {
            $scout = Scout::find($this->params[0]);
            if (isset($scout)) {
                $scout->google_two_fa_secret_key = null;
                $scout->ignoreValidation = true;
                $scout->forceSave();
                Flash::success(Lang::get('csatar.csatar::lang.plugin.component.twoFactorAuthentication.resetSuccess'));
            }
        }
    }
}
