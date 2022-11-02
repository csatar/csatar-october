<?php namespace Csatar\Csatar\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Backend\Widgets\Lists;
use Csatar\Csatar\Models\MandatePermission;
use Db;
use File;
use Illuminate\Validation\Rules\In;
use Input;
use Log;
use Session;

class PermissionsMatrix extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        '\Backend\Behaviors\FormController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

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
            $model = new MandatePermission();
            $model->id = 'all';
            $model->field = MandatePermission::PALCEHOLDER_VALUE;
            $model->model = MandatePermission::PALCEHOLDER_VALUE;
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
        $this->pageTitle = e(trans('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.editPermissions'));
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
        $sessionValuesGroupedByAction = (collect($this->getSessionValues()))->groupBy('action');
        if($sessionValuesGroupedByAction->count() === 0) {
            \Flash::warning(e(trans('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.noPermissionChanged')));
            return;
        }

        foreach ($sessionValuesGroupedByAction as $action => $actionGroup ) {
            $groupedByValue = $actionGroup->groupBy('value');
            foreach ($groupedByValue as $value => $valueGroup) {
                $permissionsIdsToUpdate = $valueGroup->pluck('id');
                $numberOfUpdatedPermissions = MandatePermission::whereIn('id', $permissionsIdsToUpdate)
                    ->update([$action => $value]);
                if ($numberOfUpdatedPermissions < $valueGroup->count()) {
                    Log::warning(
                        sprintf('Could not update all mandate permissions with ids: %s for action: %s->%s vale. Updated %s of %s. ',
                            implode(', ', $permissionsIdsToUpdate->toArray()),
                            $action,
                            $value,
                            $numberOfUpdatedPermissions,
                            $valueGroup->count())
                    );
                }
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

    // permissions manage page

    public function manage() {
        $this->pageTitle = e(trans('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.managePermissions'));
        $this->initForm(new MandatePermission());
    }

    public function onExecute() {
        $formData = Input::get('MandatePermission');
        if (empty($formData)) {
            return;
        }

        if ($formData['action'] === 'copy') {
            $permissionsToCopy = MandatePermission::where('mandate_type_id', $formData['fromMandateType'])->get();
            foreach ($permissionsToCopy as $permissionToCopy) { //dd($formData, $permissionToCopy);
                foreach ($formData['toMandateTypes'] as $toMandateTypeId) {
                    Db::table('csatar_csatar_mandates_permissions')
                        ->updateOrInsert(
                            [
                                'mandate_type_id'   => $toMandateTypeId,
                                'model'             => $permissionToCopy->model,
                                'field'             => $permissionToCopy->field,
                                'own'               => $permissionToCopy->own,
                            ],
                            [
                                'create'        => $permissionToCopy->create,
                                'read'          => $permissionToCopy->read,
                                'update'        => $permissionToCopy->update,
                                'delete'        => $permissionToCopy->delete,
                            ]
                        );
                }
            }
            \Flash::success(e(trans('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.copySuccess')));
            if (Input::get('close')) {
                return \Backend::redirect('csatar/csatar/permissionsmatrix');
            }

        }

        if ($formData['action'] === 'delete') {
            MandatePermission::where('mandate_type_id', $formData['fromMandateType'])->delete();
            \Flash::success(e(trans('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.deleteSuccess')));
            if (Input::get('close')) {
                return \Backend::redirect('csatar/csatar/permissionsmatrix');
            }
        }
    }
}
