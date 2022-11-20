<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Classes\Enums\Gender;
use Csatar\Csatar\Models\Allergy;
use Csatar\Csatar\Models\ChronicIllness;
use Csatar\Csatar\Models\FoodSensitivity;
use Csatar\Csatar\Models\LegalRelationship;
use Csatar\Csatar\Models\Religion;
use Csatar\Csatar\Models\Scout;
use October\Rain\Filesystem\Zip;
use File;
use Lang;

class ScoutImport extends \Backend\Models\ImportModel
{
    use \System\Traits\ConfigMaker;

    const GENDER_MALE = 'F';
    const GENDER_FEMALE = 'L';
    const ISACTIVE_ACTIVE = 'aktív';
    const ISACTIVE_INACTIVE = 'inaktív';
    const MOTHERSMAIDENNAME = 'Anyja leánykori neve';
    const STR_OTHER = 'Egyéb';
    const DEFAULT = '-';

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [];

    public function importData($results, $sessionKey = null)
    {
        foreach ($results as $row => $data) {
            try {
                // add a "-" to all required fields
                $config = File::symbolizePath('$/csatar/csatar/models/scoutimport/columns.yaml');
                if (File::isFile($config)) {
                    $config = $this->makeConfig($config);
                    foreach ($config->columns as $column => $columnData) {
                        if (isset($columnData['required']) && $columnData['required'] == 1 && empty($data[$column])) {
                            $data[$column] = $this::DEFAULT;
                        }
                    }
                }

                // manipulate fields - gender
                if ($data['gender'] == $this::GENDER_MALE) {
                    $data['gender'] = Gender::MALE;
                }
                else if ($data['gender'] == $this::GENDER_FEMALE) {
                    $data['gender'] = Gender::FEMALE;
                }
                else {
                    $data['comment'] = (!empty($data['comment']) ? $data['comment'] . ' ' : '') . Lang::get('csatar.csatar::lang.plugin.admin.scout.gender.gender') . ': ' . $data['gender'] . '.';
                    $data['gender'] = '';
                }

                // manipulate fields - is active
                if ($data['is_active'] == $this::ISACTIVE_ACTIVE) {
                    $data['is_active'] = 1;
                }
                else if ($data['is_active'] == $this::ISACTIVE_INACTIVE) {
                    $data['is_active'] = 0;
                }
                else {
                    $data['comment'] = (!empty($data['comment']) ? $data['comment'] . ' ' : '') . Lang::get('csatar.csatar::lang.plugin.admin.scout.isActive') . ': ' . $data['is_active'] . '.';
                    $data['is_active'] = '';
                }

                // manipulate fields - legal relationship
                $legalRelationship = LegalRelationship::where('name', $data['legal_relationship_id'])->first();
                if (isset($legalRelationship)) {
                    $data['legal_relationship_id'] = $legalRelationship->id;
                }
                else {
                    $data['comment'] = (!empty($data['comment']) ? $data['comment'] . ' ' : '') . Lang::get('csatar.csatar::lang.plugin.admin.scout.legalRelationship') . ': ' . $data['legal_relationship_id'] . '.';
                    $data['legal_relationship_id'] = '';
                }

                // manipulate fields - religion
                $religion = Religion::where('name', $data['religion_id'])->first();
                if (isset($religion)) {
                    $data['religion_id'] = $religion->id;
                }
                else {
                    $data['comment'] = (!empty($data['comment']) ? $data['comment'] . ' ' : '') . Lang::get('csatar.csatar::lang.plugin.admin.scout.religion') . ': ' . $data['religion_id'] . '.';
                    $data['religion_id'] = '';
                }

                // manipulate fields - chronic illnesses
                $chronicIllnessesStrings = explode(',', $data['chronic_illnesses']);
                unset($data['chronic_illnesses']);

                // manipulate fields - allergies
                $allergiesStrings = explode(',', $data['allergies']);
                unset($data['allergies']);

                // manipulate fields - food sensitivities
                $foodSensitivitieStrings = explode(',', $data['food_sensitivities']);
                unset($data['food_sensitivities']);

                // manipulate fields - mother's maiden name
                if (!empty($data['mothers_maiden_name'])) {
                    $data['comment'] = (!empty($data['comment']) ? $data['comment'] . ' ' : '') . $this::MOTHERSMAIDENNAME . ': ' . $data['mothers_maiden_name'] . '.';
                    unset($data['mothers_maiden_name']);
                }

                // manipulate fields - address number
                $data['address_number'] = $this::DEFAULT;

                // manipulate fields - update reason
                unset($data['update_reason']);

                // save the scout
                $scout = Scout::firstOrNew([
                    'ecset_code' => $data['ecset_code'],
                ]);
                $scout->fill($data);
                $scout->save();

                // save the pivot data - chronic illnesses
                foreach ($chronicIllnessesStrings as $chronicIllnessString) {
                    $chroniclIllness = ChronicIllness::where('name', $chronicIllnessString)->first();
                    $comment = '';
                    if (!isset($chroniclIllness)) {
                        $chroniclIllness = ChronicIllness::where('name', $this::STR_OTHER)->first();
                        $comment = $chronicIllnessString;
                    }
                    if ($scout->allergies->where('id', $chroniclIllness->id)->first() == null) {
                        $scout->allergies()->attach($chroniclIllness, ['comment' => $comment]);
                    }
                }

                // save the pivot data - allergies
                foreach ($allergiesStrings as $allergyString) {
                    $allergy = Allergy::where('name', $allergyString)->first();
                    $comment = '';
                    if (!isset($allergy)) {
                        $allergy = Allergy::where('name', $this::STR_OTHER)->first();
                        $comment = $allergyString;
                    }
                    if ($scout->allergies->where('id', $allergy->id)->first() == null) {
                        $scout->allergies()->attach($allergy, ['comment' => $comment]);
                    }
                }

                // save the pivot data - food sensitivities
                foreach ($foodSensitivitieStrings as $foodSensitivityString) {
                    $foodSensitivity = FoodSensitivity::where('name', $foodSensitivityString)->first();
                    $comment = '';
                    if (!isset($foodSensitivity)) {
                        $foodSensitivity = FoodSensitivity::where('name', $this::STR_OTHER)->first();
                        $comment = $foodSensitivityString;
                    }
                    if ($scout->allergies->where('id', $foodSensitivity->id)->first() == null) {
                        $scout->allergies()->attach($foodSensitivity, ['comment' => $comment]);
                    }
                }

                // count the action
                if ($scout->wasRecentlyCreated) {
                    $this->logCreated();
                }
                else {
                    $this->logUpdated();
                }
            }
            catch (\Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }

    private function stripFileExtension($file) 
    {
        return substr($file, 0, strlen($file) - 4);
    }

    private function unzip($file)
    {
        $dir = $this->stripFileExtension($file->getLocalPath()) . '/' . $this->stripFileExtension($file->file_name);
        if (!file_exists($dir)) {     
            Zip::extract($file->getLocalPath(), $dir);
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        $fileArray = [];
        foreach ($files as $file) {
            $fileArray[$this->stripFileExtension($file)] = $dir . '/' . $file;
        }
        return $fileArray;
    }

    private function getImportFile($sessionKey = null)
    {
        return $this
            ->import_file()
            ->withDeferred($sessionKey)
            ->orderBy('id', 'desc')
            ->first()
        ;
    }

    /**
     * Returns an attached imported file local path, if available.
     * @return string
     */
    public function getImportFilePath($sessionKey = null)
    {
        $file = $this->getImportFile($sessionKey);

        if (!$file) {
            return null;
        }
        if (str_ends_with($file->file_name, '.csv')) {
            return $file->getLocalPath();
        }
        if (str_ends_with($file->file_name, '.zip')) {
            $files = $this->unzip($file);
            return isset($files) && count($files) > 0 ? array_values($files)[0] : null;
        }
        return null;
    }

    public function import($matches, $options = [])
    {
        $files = [];
        $data = [];

        $sessionKey = array_get($options, 'sessionKey');
        $file = $this->getImportFile($sessionKey);
        if (str_ends_with($file->file_name, '.csv')) {
            $files[$this->stripFileExtension($file->file_name)] = $file->getLocalPath();
        }
        if (str_ends_with($file->file_name, '.zip')) {
            $files = $this->unzip($file);
        }

        $importData = [];
        for ($i = 0; $i < count($files); ++$i) {
            $path = array_values($files)[$i];
            $data = $this->processImportData($path, $matches, $options);

            // set the team id
            $teamId = array_keys($files)[$i];
            for ($j = 0; $j < count($data); ++$j) {
                $data[$j]['team_id'] = $teamId;
            }

            $importData += $data;
        }

        return $this->importData($importData, $sessionKey);
    }
}
