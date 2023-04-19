<?php
namespace Csatar\Csatar\Models;

use Csatar\Csatar\Classes\Enums\Gender;
use Csatar\Csatar\Models\Allergy;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\ChronicIllness;
use Csatar\Csatar\Models\FoodSensitivity;
use Csatar\Csatar\Models\LegalRelationship;
use Csatar\Csatar\Models\Religion;
use Csatar\Csatar\Models\Scout;
use Csatar\Csatar\Models\SpecialDiet;
use Csatar\Csatar\Models\Team;
use October\Rain\Filesystem\Zip;
use File;
use Lang;

class ScoutImport extends \Backend\Models\ImportModel
{
    use \System\Traits\ConfigMaker;

    const GENDER_MALE       = 'F';
    const GENDER_FEMALE     = 'L';
    const ISACTIVE_ACTIVE   = 'aktív';
    const ISACTIVE_INACTIVE = 'inaktív';
    const MOTHERSMAIDENNAME = 'Anyja leánykori neve';
    const STR_OTHER         = 'Egyéb';
    const DEFAULT           = '-';

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [];

    public function importData($results, $sessionKey = null)
    {
        $legalRelationshipInvalidDataId = LegalRelationship::getInvalidDataId();
        $specialDietNoneId = SpecialDiet::getNoneId();
        $religionOtherId   = Religion::getOtherReligionId();

        foreach ($results as $row => $data) {
            try {
                // manipulate fields - gender
                if ($data['gender'] == $this::GENDER_MALE) {
                    $data['gender'] = Gender::MALE;
                } else if ($data['gender'] == $this::GENDER_FEMALE) {
                    $data['gender'] = Gender::FEMALE;
                } else {
                    $data['comment'] = (!empty($data['comment']) ? $data['comment'] . ' ' : '') . Lang::get('csatar.csatar::lang.plugin.admin.scout.gender.gender') . ': ' . $data['gender'] . '.';
                    $data['gender']  = '';
                }

                // manipulate fields - birthdate
                if (preg_match('(^(\d+)/(\d+)/(\d+)$)i', $data['birthdate'])) {
                    $pos1  = strpos($data['birthdate'], '/');
                    $pos2  = strpos($data['birthdate'], '/', $pos1 + 1);
                    $month = substr($data['birthdate'], 0, $pos1);
                    $day   = substr($data['birthdate'], $pos1 + 1, $pos2 - $pos1 - 1);
                    $year  = substr($data['birthdate'], $pos2 + 1);
                    $data['birthdate'] = $year . '-' . $month . '-' . $day;
                }

                // manipulate fields - is active
                if ($data['is_active'] == $this::ISACTIVE_ACTIVE) {
                    $data['is_active'] = 1;
                } else if ($data['is_active'] == $this::ISACTIVE_INACTIVE) {
                    $data['is_active'] = 0;
                } else {
                    $data['comment']   = (!empty($data['comment']) ? $data['comment'] . ' ' : '') . Lang::get('csatar.csatar::lang.plugin.admin.scout.isActive') . ': ' . $data['is_active'] . '.';
                    $data['is_active'] = '';
                }

                // manipulate fields - legal relationship
                $legalRelationship = LegalRelationship::where('name', $data['legal_relationship_id'])->first();
                if (isset($legalRelationship)) {
                    $data['legal_relationship_id'] = $legalRelationship->id;
                } else {
                    $data['comment'] = (!empty($data['comment']) ? $data['comment'] . ' ' : '') . Lang::get('csatar.csatar::lang.plugin.admin.scout.legalRelationship') . ': ' . $data['legal_relationship_id'] . '.';
                    $data['legal_relationship_id'] = '';
                }

                // manipulate fields - religion
                $religion = Religion::where('name', $data['religion_id'])->first();
                if (isset($religion)) {
                    $data['religion_id'] = $religion->id;
                } else {
                    $data['comment']     = (!empty($data['comment']) ? $data['comment'] . ' ' : '') . Lang::get('csatar.csatar::lang.plugin.admin.scout.religion') . ': ' . $data['religion_id'] . '.';
                    $data['religion_id'] = '';
                }

                // manipulate fields - chronic illnesses
                $chronicIllnessesStrings = !empty($data['chronic_illnesses']) ? explode(',', $data['chronic_illnesses']) : [];
                unset($data['chronic_illnesses']);

                // manipulate fields - allergies
                $allergiesStrings = !empty($data['allergies']) ? explode(',', $data['allergies']) : [];
                unset($data['allergies']);

                // manipulate fields - food sensitivities
                $foodSensitivitieStrings = !empty($data['food_sensitivities']) ? explode(',', $data['food_sensitivities']) : [];
                unset($data['food_sensitivities']);

                // manipulate fields - mother's maiden name
                if (!empty($data['mothers_maiden_name'])) {
                    $data['comment'] = (!empty($data['comment']) ? $data['comment'] . ' ' : '') . $this::MOTHERSMAIDENNAME . ': ' . $data['mothers_maiden_name'] . '.';
                    unset($data['mothers_maiden_name']);
                }

                // manipulate fields - address street and address number
                $data['address_street']   = trim($data['address_street']);
                $streetLastSpaceCharacter = strrpos($data['address_street'], ' ');
                if ($streetLastSpaceCharacter != false) {
                    $streetLastSection = substr($data['address_street'], $streetLastSpaceCharacter + 1);
                    if (preg_match('/[0-9]/i', $streetLastSection)) {
                        $data['address_number'] = $streetLastSection;
                        $data['address_street'] = trim(substr($data['address_street'], 0, $streetLastSpaceCharacter));
                    }
                }

                if (!isset($data['address_number'])) {
                    $data['address_number']        = $this::DEFAULT;
                    $data['legal_relationship_id'] = $legalRelationshipInvalidDataId;
                }

                // manipulate fields - registration form
                unset($data['registration_form']);

                // manipulate fields - special diet
                $data['special_diet_id'] = $specialDietNoneId;

                // add a "-" to all required fields
                $config = File::symbolizePath('$/csatar/csatar/models/scoutimport/columns.yaml');
                if (File::isFile($config)) {
                    $config = $this->makeConfig($config);
                    foreach ($config->columns as $column => $columnData) {
                        if (isset($columnData['required']) && $columnData['required'] == 1 && empty($data[$column])) {
                            switch ($column) {
                                case 'religion_id':
                                    $data[$column] = $religionOtherId;
                                    break;
                                default:
                                    $data[$column] = $this::DEFAULT;
                                    break;
                            }

                            $data['legal_relationship_id'] = $legalRelationshipInvalidDataId;
                            $data['is_active'] = 0;
                        }
                    }
                }

                // retrieve/create the scout
                $scout = Scout::firstOrNew([
                    'ecset_code' => $data['ecset_code'],
                ]);

                if ($data['is_active'] != 1) {
                    $scout->inactivated_at   = $scout->inactivated_at == null ? date('Y-m-d H:i:s') : $scout->inactivated_at;
                    $scout->ignoreValidation = true;
                    unset($data['is_active']);
                }

                $scout->fill($data);

                // generate an empty registration form
                if ($scout->wasRecentlyCreated) {
                    $file            = (new \System\Models\File)->fromData('', $data['ecset_code'] . '.pdf');
                    $file->is_public = true;
                    $file->content_type = 'application/pdf';
                    $file->save();
                    $scout->registration_form()->add($file);
                }

                // save the scout
                $scout->forceSave();

                // save the pivot data
                $this->savePivotData($scout, $chronicIllnessesStrings, ChronicIllness::class, 'chronic_illnesses');
                $this->savePivotData($scout, $allergiesStrings, Allergy::class, 'allergies');
                $this->savePivotData($scout, $foodSensitivitieStrings, FoodSensitivity::class, 'food_sensitivities');

                // count the action
                if ($scout->wasRecentlyCreated) {
                    $this->logCreated();
                } else {
                    $this->logUpdated();
                }
            } catch (\Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }

    private function savePivotData($scout, $dataString, $modelName, $attributeName)
    {
        $comment = '';
        foreach ($dataString as $dataItemString) {
            $data = ($modelName)::where('name', $dataItemString)->first();
            if (isset($data)) {
                if ($scout->{$attributeName}->where('id', $data->id)->first() == null) {
                    $scout->{$attributeName}()->attach($data, ['comment' => $comment]);
                }
            } else {
                $comment = $comment . (!empty($comment) ? ', ' : '') . $dataItemString;
            }
        }

        if (!empty($comment)) {
            $data = ($modelName)::where('name', $this::STR_OTHER)->first();
            if ($scout->{$attributeName}->isEmpty() || $scout->{$attributeName}->where('id', $data->id)->first() == null) {
                $scout->{$attributeName}()->attach($data, ['comment' => $comment]);
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

        $files     = array_diff(scandir($dir), ['.', '..']);
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
        if (empty($this->association)) {
            throw new \ApplicationException(Lang::get('csatar.csatar::lang.plugin.admin.scout.validationExceptions.associationRequired'));
        }

        $files = [];
        $data  = [];

        $sessionKey = array_get($options, 'sessionKey');
        $file       = $this->getImportFile($sessionKey);
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
            $teamNumber = array_keys($files)[$i];
            $teamId     = Team::getTeamIdByAssociationAndTeamNumber($this->association, $teamNumber);
            for ($j = 0; $j < count($data); ++$j) {
                $data[$j]['team_id'] = $teamId;
            }

            $importData += $data;
        }

        return $this->importData($importData, $sessionKey);
    }

    public function getAssociationOptions()
    {
        return Association::lists('name', 'id');
    }

}
