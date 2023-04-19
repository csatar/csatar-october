<?php
namespace Csatar\Csatar\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Backend\Widgets\Lists;
use Csatar\Csatar\Classes\Constants;
use Csatar\Csatar\Models\History;
use Csatar\Csatar\Models\MandatePermission;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\PermissionBasedAccess;
use Db;
use File;
use Illuminate\Validation\Rules\In;
use Input;
use Log;
use Lang;
use Session;
use Flash;

class PermissionsMatrix extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ImportExportController',
    ];

    public $listConfig         = 'config_list.yaml';
    public $formConfig         = 'config_form.yaml';
    public $importExportConfig = 'config_import_export.yaml';

    public $sessionValues;

    public $requiredPermissions = [
        'csatar.manage.data'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-organization-system-data', 'side-menu-item-permissions-matrix');
        $this->sessionValues = $this->getSessionValues();
    }

    public function synchronizePermissionsMatrix()
    {
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-item-synchronize-permissions-matrix');
    }

    public function listExtendQuery($query) {
        $query->leftJoin('csatar_csatar_mandate_types', 'csatar_csatar_mandates_permissions.mandate_type_id', '=', 'csatar_csatar_mandate_types.id');
    }

    public function listExtendRecords($records) {
        if ($this->action === 'edit') {
            // this is needed to instert special first row that can manipulate the selects in every row below it
            $model        = new MandatePermission();
            $model->id    = 'all';
            $model->field = MandatePermission::PALCEHOLDER_VALUE;
            $model->model = MandatePermission::PALCEHOLDER_VALUE;
            $model->obligatory = 'all';
            $model->create     = 'all';
            $model->read       = 'all';
            $model->update     = 'all';
            $model->delete     = 'all';
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

        $permissionId  = Input::get('recordId');
        $action        = Input::get('action');
        $key           = $action . '_' . $permissionId;
        $sessionValues = $this->getSessionValues();
        $sessionValues[$key] = [
            'id' => $permissionId,
            'action' => $action,
            'value' => Input::get($key),
            'initialValue' => Input::get('initialValue')
        ];
        if (Input::get($key) == Input::get('initialValue')) {
            unset($sessionValues[$key]);
        }

        Session::put('permissionValueChanges', $sessionValues);

    }

    public function onMultipleValueChange(){
        $sessionValues = $this->getSessionValues();
        $ajaxData      = Input::get('data');
        $sessionValues = array_replace($sessionValues, $ajaxData ?? []);
        Session::put('permissionValueChanges', $sessionValues);
    }

    public function onSave(){
        $sessionValuesGroupedByAction = (collect($this->getSessionValues()))->groupBy('action');
        if ($sessionValuesGroupedByAction->count() === 0) {
            \Flash::warning(e(trans('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.noPermissionChanged')));
            return;
        }

        foreach ($sessionValuesGroupedByAction as $action => $actionGroup) {
            $groupedByValue = $actionGroup->groupBy('value');
            foreach ($groupedByValue as $value => $valueGroup) {
                $permissionsIdsToUpdate     = $valueGroup->pluck('id');
                $numberOfUpdatedPermissions = MandatePermission::whereIn('id', $permissionsIdsToUpdate)
                    ->update([$action => $value]);
                if ($numberOfUpdatedPermissions == $valueGroup->count()) {
                    (new MandatePermission())->historyRecordBulkAction($valueGroup->toArray());
                }

                if ($numberOfUpdatedPermissions < $valueGroup->count()) {
                    $warning = Lang::get('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.notAllPermissionChanged',
                        [
                            'ids' => implode(', ', $permissionsIdsToUpdate->toArray()),
                            'action' => $action,
                            'value' => $value,
                            'updated' => $numberOfUpdatedPermissions,
                            'from' => $valueGroup->count()
                        ]);
                    (new MandatePermission())->historyRecordBulkAction($valueGroup->toArray(), $warning);
                    Log::warning($warning);
                    \Flash::warning($warning);
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
            foreach ($permissionsToCopy as $permissionToCopy) {
                foreach ($formData['toMandateTypes'] as $toMandateTypeId) {
                    $mandatePermission = MandatePermission::firstOrNew([
                        'mandate_type_id'   => $toMandateTypeId,
                        'model'             => $permissionToCopy->model,
                        'field'             => $permissionToCopy->field,
                        'own'               => $permissionToCopy->own,
                    ]);

                    $mandatePermission->create = $permissionToCopy->create;
                    $mandatePermission->read   = $permissionToCopy->read;
                    $mandatePermission->update = $permissionToCopy->update;
                    $mandatePermission->delete = $permissionToCopy->delete;
                    $mandatePermission->save();
                }
            }

            \Flash::success(e(trans('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.copySuccess')));
            if (Input::get('close')) {
                return \Backend::redirect('csatar/csatar/permissionsmatrix');
            }
        }

        if ($formData['action'] === 'delete') {
            $ids = MandatePermission::where('mandate_type_id', $formData['fromMandateType'])->get()->pluck('id');
            MandatePermission::destroy($ids);
            \Flash::success(e(trans('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.deleteSuccess')));
            if (Input::get('close')) {
                return \Backend::redirect('csatar/csatar/permissionsmatrix');
            }
        }
    }

    public function onSynchronizePermissionsMatrix(){

        $permissionBasedModels = PermissionBasedAccess::getAllChildClasses();
        $mandateTypes          = MandateType::all();

        if (empty($permissionBasedModels) || empty($mandateTypes)) return;

        $tempMandatePermissionsMap = [];
        try {
            foreach ($mandateTypes as $mandateType) {
                foreach ($permissionBasedModels as $permissionBasedModel) {
                    if ($permissionBasedModel == MandateType::MODEL_NAME_GUEST) return;

                    $model          = new $permissionBasedModel();
                    $fields         = $model->fillable ?? [];
                    $fields         = array_merge($fields, $model->additionalFieldsForPermissionMatrix ?? []);
                    $relationArrays = Constants::AVAILABLE_RELATION_TYPES;

                    foreach ($relationArrays as $relationArrayName) {
                        $relationArray = $model->$relationArrayName;
                        // filter out the value if ignoreInPermissionsMatrix is set to true
                        if (is_array($relationArray)) {
                            $relationArray = array_filter($relationArray, function ($value) {
                                return !isset($value['ignoreInPermissionsMatrix']) || $value['ignoreInPermissionsMatrix'] === false;
                            });
                        }

                        $fields = array_merge($fields, array_keys($relationArray));
                    }

                    $this->filterFieldsForRealtionKeys($fields);
                    $fields = array_unique($fields);

                    //add permission for the model in general
                    $tempMandatePermissionsMap[] = [ 'mandate_type_id' => $mandateType->id, 'model' => $permissionBasedModel, 'field' => 'MODEL_GENERAL', 'own' => 0];

                    if ($mandateType->organization_type_model_name == MandateType::MODEL_NAME_SCOUT && $permissionBasedModel == MandateType::MODEL_NAME_SCOUT) {
                        //add permission for the model in general for own
                        $tempMandatePermissionsMap[] = [ 'mandate_type_id' => $mandateType->id, 'model' => $permissionBasedModel, 'field' => 'MODEL_GENERAL', 'own' => 1];
                    }

                    //add permission for each attribute for general, own
                    foreach ($fields as $field) {
                        $tempMandatePermissionsMap[] = [ 'mandate_type_id' => $mandateType->id, 'model' => $permissionBasedModel, 'field' => $field, 'own' => 0];

                        if ($mandateType->organization_type_model_name == MandateType::MODEL_NAME_SCOUT && $permissionBasedModel == MandateType::MODEL_NAME_SCOUT) {
                            $tempMandatePermissionsMap[] = ['mandate_type_id' => $mandateType->id, 'model' => $permissionBasedModel, 'field' => $field, 'own' => 1];
                        }
                    }
                }
            }

            $existingMandatePermissionsMap = MandatePermission::all()->map(function ($item) {
                return serialize([
                    'mandate_type_id' => $item->mandate_type_id,
                    'model' => $item->model,
                    'field' => $item->field,
                    'own' => $item->own,
                ]);
            });

            $tempMandatePermissionsMap = collect($tempMandatePermissionsMap);
            $tempMandatePermissionsMap = $tempMandatePermissionsMap->map(function ($item) {
                return serialize($item);
            });

            $newPermissionsToSave = $tempMandatePermissionsMap->diff($existingMandatePermissionsMap);
            $newPermissionsToSave = $newPermissionsToSave->map(function ($item) {
                return unserialize($item);
            });

            $newPermissionsToSave->chunk(1000)->each(function ($item){
                Db::table('csatar_csatar_mandates_permissions')->insert($item->toArray());
                (new MandatePermission())->historyRecordMatrixSynchronization($item->toArray());
            });

            Flash::success(e(trans('csatar.csatar::lang.plugin.admin.admin.seederData.synchronizeComplete')));
        } catch (Exception $exception) {
            Flash::error($exception->getMessage());
        }
    }

    private function filterFieldsForRealtionKeys(&$fields) {
        // filters the $fields array to remove relation key field, if relation field exists
        // for example removes: "currency_id" field if there is "currency" field in the array
        foreach ($fields as $key => $field) {
            if (substr($field, -3) === '_id') {
                $relationField = str_replace('_id', '', $field);
                if (in_array($relationField, $fields)) {
                    unset($fields[$key]);
                }
            }
        }
    }
}
