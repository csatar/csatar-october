<?php namespace Csatar\Csatar\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Csatar\Csatar\Classes\BackendExtensions;
use Csatar\Csatar\Models\Scout;
use Flash;
use Lang;
use Redirect;

class Scouts extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController', 'Backend\Behaviors\RelationController'];

    public $listConfig = [
        'list' => 'config_list.yaml',
        'trashed' => 'config_list_trashed.yaml'
    ];

    public $formConfig     = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-scout');
    }

    public $requiredPermissions = [
        'csatar.manage.data'
    ];

    public function onResetFA()
    {
        if (isset($this->params[0])) {
            $scout = Scout::find($this->params[0]);
            if (isset($scout)) {
                $scout->google_two_fa_secret_key   = null;
                $scout->google_two_fa_is_activated = null;
                $scout->ignoreValidation           = true;
                $scout->forceSave();
                Flash::success(Lang::get('csatar.csatar::lang.plugin.component.twoFactorAuthentication.resetSuccess'));
            }
        }
    }

    public function onDelete(){
        return BackendExtensions::onDelete($this);
    }

    public function onDeleteWithPersonalData(){
        return BackendExtensions::onDelete($this, 'deletePersonalInformation');
    }

    public function deleted(){
        $this->asExtension('ListController')->index();
        $this->pageTitle = Lang::get('csatar.csatar::lang.plugin.admin.scout.deletedScouts');
    }

    public function listExtendQuery($query, $definition)
    {
        if ($definition == 'trashed')
        {
            $query->onlyTrashed();
        }
    }

    public function formExtendQuery($query)
    {
        $query->withTrashed();
    }

    public function onRestoreScouts()
    {
        $checkedIds = post('checked');
        if (!$checkedIds || !is_array($checkedIds) || !count($checkedIds)) {
            Flash::error(Lang::get('csatar.csatar::lang.plugin.admin.scout.noScoutsSelected'));
            return Redirect::refresh();
        }
        $scouts = Scout::onlyTrashed()->whereIn('id', $checkedIds)->get();
        $scouts->each(function($scout){
            $scout->inactivated_at   = $scout->inactivated_at ?? date('Y-m-d H:i:s');
            $scout->ignoreValidation = true;
            $scout->deleted_at       = null;
            $scout->forceSave();
        });
        Flash::success(Lang::get('csatar.csatar::lang.plugin.admin.scout.restoreSuccess'));
        return Redirect::refresh();
    }
}
