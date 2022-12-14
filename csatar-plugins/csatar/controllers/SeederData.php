<?php namespace Csatar\Csatar\Controllers;

use BackendMenu;
use Csatar\Csatar\Models\MandatePermission;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\PermissionBasedAccess;
use Flash;
use Lang;
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

    public function seeder()
    {
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-seeder-data');
    }

    public function test()
    {
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-test-data');
    }

    public function synchronizePermissionsMatrix()
    {
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-test-data');
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

    public function onSynchronizePermissionsMatrix(){
        $permissionBasedModels = PermissionBasedAccess::getAllChildClasses();
        $mandateTypes = MandateType::all();

        if(empty($permissionBasedModels) || empty($mandateTypes)) return;

        try {
            foreach ($mandateTypes as $mandateType) {
                foreach ($permissionBasedModels as $permissionBasedModel) {
                    if ($permissionBasedModel == MandateType::MODEL_NAME_GUEST) return;

                    $model = new $permissionBasedModel();
                    $fields = $model->fillable ?? [];
                    $relationArrays = ['belongsTo', 'belongsToMany', 'hasMany', 'attachOne', 'hasOne', 'morphTo', 'morphOne',
                        'morphMany', 'morphToMany', 'morphedByMany', 'attachMany', 'hasManyThrough', 'hasOneThrough'];

                    foreach ($relationArrays as $relationArray) {
                        $fields = array_merge($fields, array_keys($model->$relationArray));
                    }

                    $this->filterFieldsForRealtionKeys($fields);

                    //add permission for the model in general
                    MandatePermission::firstOrCreate(
                        [ 'mandate_type_id' => $mandateType->id, 'model' => $permissionBasedModel, 'field' => 'MODEL_GENERAL', 'own' => 0],
                    );

                    if ($mandateType->organization_type_model_name != MandateType::MODEL_NAME_GUEST) {
                        //add permission for the model in general for own
                        MandatePermission::firstOrCreate(
                            [ 'mandate_type_id' => $mandateType->id, 'model' => $permissionBasedModel, 'field' => 'MODEL_GENERAL', 'own' => 1],
                        );
                    }


                    //add permission for each attribute for general, own

                    foreach ($fields as $field) {
                        //add permission for the model->field
                        MandatePermission::firstOrCreate(
                            [ 'mandate_type_id' => $mandateType->id, 'model' => $permissionBasedModel, 'field' => $field, 'own' => 0],
                        );

                        if ($mandateType->organization_type_model_name != MandateType::MODEL_NAME_GUEST) {
                            //add permission for the model->field for own
                            MandatePermission::firstOrCreate(
                                ['mandate_type_id' => $mandateType->id, 'model' => $permissionBasedModel, 'field' => $field, 'own' => 1],
                            );
                        }
                    }
                }
            }

            Flash::success(e(trans('csatar.csatar::lang.plugin.admin.admin.seederData.synchronizeComplete')));

        } catch (Exception $exception) {
            Flash::success($exception->getMessage());
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
