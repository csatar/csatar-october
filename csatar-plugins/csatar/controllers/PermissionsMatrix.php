<?php namespace Csatar\Csatar\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Backend\Widgets\Lists;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\PermissionsMatrix as PermissionModel;
use File;
use Illuminate\Validation\Rules\In;
use Input;
use Session;

class PermissionsMatrix extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController'    ];

    public $listConfig = ['config_list.yaml'];

    public $sessionValues;

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-organization-system-data', 'side-menu-item-permissions-matrix');
        $this->sessionValues = $this->getSessionValues();
    }

    public function listExtendQuery($query) {
        $query->leftJoin('csatar_csatar_mandate_types', 'csatar_csatar_mandates_permissions.mandate_type_id', '=', 'csatar_csatar_mandate_types.id');
    }

    public function listExtendRecords($records) {
        if ($this->action === 'edit') {
            // this is needed to instert special first row that can manipulate the selects in every row below it
            $model = new PermissionModel();
            $model->id = 'all';
            $model->field = PermissionModel::PALCEHOLDER_VALUE;
            $model->model = PermissionModel::PALCEHOLDER_VALUE;
            $model->obligatory = 'all';
            $model->create = 'all';
            $model->read = 'all';
            $model->update = 'all';
            $model->delete = 'all';
            $records->prepend($model);
        }
    }

    public function edit() {
        $this->addCss("/plugins/csatar/csatar/assets/permissionsMatrix.css");
        $this->addJs("/plugins/csatar/csatar/assets/permissionsMatrix.js");
        $this->makeLists();
    }

    public function onValueChange(){

        $permissionId = Input::get('recordId');
        $action = Input::get('action');
        $key = $action . '_' . $permissionId;
        $sessionValues = $this->getSessionValues();
        $sessionValues[$key] = [
            'id' => $permissionId,
            'action' => $action,
            'value' => Input::get($key),
            'initialValue' => Input::get('initialValue')
        ];
        if(Input::get($key) == Input::get('initialValue')) {
            unset($sessionValues[$key]);
        }
        Session::put('permissionValueChanges', $sessionValues);

    }

    public function onMultipleValueChange(){
        $sessionValues = $this->getSessionValues();
        $ajaxData = Input::get('data');
        $sessionValues = array_replace($sessionValues, $ajaxData);
        Session::put('permissionValueChanges', $sessionValues);
    }

    public function onSave(){
        $sessionValues = $this->getSessionValues();
        if(empty($sessionValues)) {
            return; //flash nothing to save
        }
        foreach ($sessionValues as $sessionValue) {
            $permissionModel = PermissionModel::find($sessionValue['id']);
            if ($permissionModel) {
                $permissionModel->{$sessionValue['action']} = $sessionValue['value'];
                $permissionModel->save();
            }

        }

        Session::forget('permissionValueChanges');
    }

    public function onCancel(){
        Session::forget('permissionValueChanges');
    }

    private function getSessionValues(): array
    {
        return Session::get('permissionValueChanges', []);
    }
    public function onGetSessionValues(): array
    {
        return $this->getSessionValues();
    }
}
