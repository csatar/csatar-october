<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\MandatePermission;
use Lang;

class PermissionImport extends \Backend\Models\ImportModel
{
    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [];
    private $mandateTypeAssociationMap = []; //[$associationName][$mandateName] = $mandateId;

    public function importData($results, $sessionKey = null)
    {
        $mandateTypesWithAssociation = (collect($results))->unique( function ($item) {
            return $item['association'].$item['mandate_type'];
        });

        $mandateTypesWithAssociation = $mandateTypesWithAssociation->mapToGroups(function ($item, $key) {
            return [$item['association'] => $item['mandate_type']];
        });

        $intialMaxExecutionTime = ini_get("max_execution_time");
        set_time_limit(2000);

        foreach ($mandateTypesWithAssociation as $associationName => $mandateNames) {
            $association = Association::where('name', $associationName)->first();
            if (!empty($association)) {
                foreach ($mandateNames as $mandateTypeName) {
                    $mandateType = MandateType::where('association_id', $association->id)
                        ->where('name', $mandateTypeName)->first();
                    if (!empty($mandateType)) {
                        $this->mandateTypeAssociationMap[$associationName][$mandateTypeName] = $mandateType->id;
                    }
                }
            }
        }

        foreach ($results as $row => $data) {
            $associationName = $data['association'];
            $mandateTypeName = $data['mandate_type'];

            if (!isset($this->mandateTypeAssociationMap[$associationName])) {
                $message = Lang::get('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.errorCanNotFindAssociation', [
                    'associationName' => $associationName,
                ]);
                $this->logSkipped($row, $message);
                continue;
            }

            if (!isset($this->mandateTypeAssociationMap[$associationName][$mandateTypeName])) {
                $message = Lang::get('csatar.csatar::lang.plugin.admin.admin.permissionsMatrix.errorCanNotFindMandateType', [
                    'mandateTypeName' => $mandateTypeName,
                    'associationName' => $associationName,
                ]);
                $this->logSkipped($row, $message);
                continue;
            }

            $data["mandate_type_id"] = $this->mandateTypeAssociationMap[$associationName][$mandateTypeName];

            try {
                $mandatePermission = MandatePermission::firstOrCreate([
                    "mandate_type_id" => $this->mandateTypeAssociationMap[$associationName][$mandateTypeName],
                    "own" => $data["own"],
                    "model" => $data["model"],
                    "field" => $data["field"],
                ]);
                
                $mandatePermission->update([
                    "obligatory" => $data["obligatory"] != "" ? $data["obligatory"] : null,
                    "create" => $data["create"] != "" ? $data["create"] : null,
                    "read" => $data["read"] != "" ? $data["read"] : null,
                    "update" => $data["update"] != "" ? $data["update"] : null,
                    "delete" => $data["delete"] != "" ? $data["delete"] : null,
                ]);
                $mandatePermission->save();

                if ($mandatePermission->wasRecentlyCreated) {
                    $this->logCreated();
                } else {
                    $this->logUpdated();
                }
            } catch (\Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }

        set_time_limit($intialMaxExecutionTime);
    }
}
