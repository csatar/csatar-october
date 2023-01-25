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

    public function onLocationDataUpdateButtonClick()
    {
        $locationData = new \Csatar\Csatar\Updates\LocationData();
        $locationData->run();
        Flash::success(Lang::get('csatar.csatar::lang.plugin.admin.admin.seederData.updateDataSuccess'));
    }

    public function onSynchronizePermissionsMatrix(){

        $permissionBasedModels = PermissionBasedAccess::getAllChildClasses();
        $mandateTypes = MandateType::all();

        if(empty($permissionBasedModels) || empty($mandateTypes)) return;

        $tempMandatePermissionsMap = [];
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
