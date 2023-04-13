<?php namespace Csatar\Csatar\Components;

use Auth;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Classes\CsvCreator;
use Csatar\Csatar\Classes\Enums\Gender;
use Csatar\Csatar\Classes\Enums\Status;
use Csatar\Csatar\Classes\Mappers\CountryMapper;
use Csatar\Csatar\Classes\Mappers\LegalRelationshipMapper;
use Csatar\Csatar\Classes\Mappers\ReligionMapper;
use Csatar\Csatar\Classes\Mappers\TShirtSizeMapper;
use Csatar\Csatar\Classes\StructureTree;
use Csatar\Csatar\Classes\Xlsx\ScoutXlsxExport;
use Csatar\Csatar\Classes\Xlsx\ScoutXlsxImport;
use Csatar\Csatar\Models\GalleryModelPivot;
use Csatar\Csatar\Models\Scout;
use Csatar\Csatar\Models\Team;
use Db;
use Input;
use Lang;
use Redirect;
use Vdomah\Excel\Classes\Excel;

class OrganizationUnitFrontend extends ComponentBase
{
    public $model;
    public $content_page;
    public $permissions;
    public $gallery_id;
    public $inactiveMandates;
    public $inactiveMandatesColumns;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.organizationUnitFrontend.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.organizationUnitFrontend.description'),
        ];
    }

    public function defineProperties()
    {
        return [
            'model_name' => [
                'title'             => 'csatar.csatar::lang.plugin.component.structure.properties.model_name.title',
                'description'       => 'csatar.csatar::lang.plugin.component.structure.properties.model_name.description',
                'type'              => 'string',
                'default'           => null
            ],
            'model_id' => [
                'title'             => 'csatar.csatar::lang.plugin.component.structure.properties.model_id.title',
                'description'       => 'csatar.csatar::lang.plugin.component.structure.properties.model_id.description',
                'type'              => 'string',
                'default'           => null
            ]
        ];
    }

    public function onRun()
    {
        $modelName = "Csatar\Csatar\Models\\" . $this->property('model_name');
        if (is_numeric($this->property('model_id'))) {
            $eagerLoadUseCase = 'inactiveMandates';
            $eagerLoadUseCase = $eagerLoadUseCase . ($this->property('model_name') == 'Patrol' ? 'Patrol' : '');
            $eagerLoadUseCase = $eagerLoadUseCase . ($this->property('model_name') == 'Troop' ? 'Troop' : '');
            $eagerLoadSettings = $modelName::getEagerLoadSettings($eagerLoadUseCase);
            $this->model = $modelName::where('id', $this->property('model_id'))->with($eagerLoadSettings)->first(); //5 queries

            $this->inactiveMandates = $this->model->mandatesInactive->toArray();

            if (isset(Auth::user()->scout)) {
                $this->permissions = Auth::user()->scout->getRightsForModel($this->model);
            }
            if (empty($this->model->content_page)) {
                $this->content_page = $this->model->content_page()->create([
                    'title' => '',
                    'content' => ''
                ]);
            } else {
                $this->content_page = $this->model->content_page;
            }

            $this->gallery_id = GalleryModelPivot::where('model_type', $modelName)->where('model_id', $this->property('model_id'))->value('gallery_id');

        }

        $this->inactiveMandatesColumns = $this->getInactiveMandatesColumns();
    }

    public function onEditContent()
    {
        $modelName = "Csatar\Csatar\Models\\" . $this->property('model_name');
        $model = $modelName::find($this->property('model_id'));

        $content = $model->content_page;
        return [
            '#contentPage' => $this->renderPartial('@editor', ['content_page' => $content])
        ];
    }

    public function onSaveContent()
    {
        $modelName = "Csatar\Csatar\Models\\" . $this->property('model_name');
        $model = $modelName::find($this->property('model_id'));

        $content = $model->content_page;
        $content->title = post('title');
        $content->content = post('content');
        $content->save();

        return Redirect::refresh();
    }

    public function getInactiveMandatesColumns() {
        return [
            'mandate_type' => [
                'label' => Lang::get('csatar.csatar::lang.plugin.admin.mandateType.mandateType'),
                'nameFrom' => 'name',
                ],
            'mandate_model_name' => [
                'label' => Lang::get('csatar.csatar::lang.plugin.admin.mandateType.organizationTypeModelName'),
                ],
            'scout' => [
                'label' => Lang::get('csatar.csatar::lang.plugin.admin.mandateType.scout'),
                'nameFrom' => 'full_name',
                'link' => '/tag/',
                'linkParam' => 'ecset_code',
                ],
            'scout_team' => [
                'label' => Lang::get('csatar.csatar::lang.plugin.admin.scout.scoutTeam'),
                ],
            'start_date' => [
                'label' => Lang::get('csatar.csatar::lang.plugin.admin.mandateType.startDate'),
                ],
            'end_date' => [
                'label' => Lang::get('csatar.csatar::lang.plugin.admin.mandateType.endDate'),
                ],
            'comment' => [
                'label' => Lang::get('csatar.csatar::lang.plugin.admin.general.comment'),
                ],
        ];
    }

    public function onCancelEdit()
    {
        return Redirect::refresh();
    }

    public function onExportScoutsToCsv()
    {
        $teamId = Input::get('teamId');
        $team = Team::find($teamId);
        if (empty($teamId) || empty($team->team_number)) {
            return;
        }

        $fileName = $team->team_number . '_csapat_' . Carbon::today()->toDateString() . '.csv';
        $csvPath = temp_path() . '/' . $fileName;

        $data = $this->prepareScoutsDownloadData($teamId);

        CsvCreator::writeCsvFile($csvPath, $data);

        return Redirect::to('letoltes/csv/' . $fileName);
    }

    public function onExportScoutsToXlsx()
    {
        $teamId = Input::get('teamId');
        $team = Team::find($teamId);
        if (empty($teamId) || empty($team->team_number)) {
            return;
        }

        $fileName = $team->team_number . '_csapat_' . Carbon::today()->toDateString() . '.xlsx';

        $data = $this->prepareScoutsDownloadData($teamId);

        Excel::store(new ScoutXlsxExport(2, $data), $fileName);

        return Redirect::to('letoltes/xlsx/' . $fileName);

    }

    private function prepareScoutsDownloadData($teamId) {
        $scouts = Scout::where('team_id', $teamId)->get();
        $model = Scout::getModelName();
        $attributesWithLabels = Scout::getTranslatedAttributeNames($model);

        $attributesWithLabels['mothers_name']  = '(' . Lang::get('csatar.csatar::lang.plugin.admin.scout.mother') . ') ' . $attributesWithLabels['mothers_name'];
        $attributesWithLabels['mothers_phone'] = '(' . Lang::get('csatar.csatar::lang.plugin.admin.scout.mother') . ') ' . $attributesWithLabels['mothers_phone'];
        $attributesWithLabels['mothers_email'] = '(' . Lang::get('csatar.csatar::lang.plugin.admin.scout.mother') . ') ' . $attributesWithLabels['mothers_email'];
        $attributesWithLabels['fathers_name']  = '(' . Lang::get('csatar.csatar::lang.plugin.admin.scout.father') . ') ' . $attributesWithLabels['fathers_name'];
        $attributesWithLabels['fathers_phone'] = '(' . Lang::get('csatar.csatar::lang.plugin.admin.scout.father') . ') ' . $attributesWithLabels['fathers_phone'];
        $attributesWithLabels['fathers_email'] = '(' . Lang::get('csatar.csatar::lang.plugin.admin.scout.father') . ') ' . $attributesWithLabels['fathers_email'];
        $attributesWithLabels['legal_representative_name']  = '(' . Lang::get('csatar.csatar::lang.plugin.admin.scout.legalRepresentative') . ') ' . $attributesWithLabels['legal_representative_name'];
        $attributesWithLabels['legal_representative_phone'] = '(' . Lang::get('csatar.csatar::lang.plugin.admin.scout.legalRepresentative') . ') ' . $attributesWithLabels['legal_representative_phone'];
        $attributesWithLabels['legal_representative_email'] = '(' . Lang::get('csatar.csatar::lang.plugin.admin.scout.legalRepresentative') . ') ' . $attributesWithLabels['legal_representative_email'];

        $attributes = [
            'ecset_code',
            'name_prefix',
            'family_name',
            'given_name',
            'nickname',
            'email',
            'phone',
            'citizenship_country_id',
            'personal_identification_number',
            'gender',
            'legal_relationship_id',
            'religion_id',
            'tshirt_size_id',
            'nationality',
            'birthdate',
            'nameday',
            'maiden_name',
            'birthplace',
            'address_country',
            'address_zipcode',
            'address_county',
            'address_location',
            'address_street',
            'address_number',
            'mothers_name',
            'mothers_phone',
            'mothers_email',
            'fathers_name',
            'fathers_phone',
            'fathers_email',
            'legal_representative_name',
            'legal_representative_phone',
            'legal_representative_email',
            'elementary_school',
            'primary_school',
            'secondary_school',
            'post_secondary_school',
            'college',
            'university',
            'occupation',
            'workplace',
            'foreign_language_knowledge',
            'comment',
            'is_active',
        ];

        $attributesWithLabels['citizenship_country_id'] = $attributesWithLabels['citizenship_country'];
        $attributes = array_flip($attributes);
        $attributesWithLabels = array_intersect_key(array_replace($attributes, $attributesWithLabels), $attributes);
        $legalRelationshipsMap = (new LegalRelationshipMapper)->idsToNames;
        $religionsMap = (new ReligionMapper)->idsToNames;
        $tShirtSizesMap = (new TShirtSizeMapper)->idsToNames;
        $countryMap = (new CountryMapper)->idToCountryCode;

        $data = [];
        foreach ($attributesWithLabels as $attribute => $label) {
            $data[0][] = $attribute;
            $data[1][] = $label;
        }

        foreach ($scouts as $record) {
            $dataRow = [];
            foreach (array_keys($attributesWithLabels) as $attribute) {
                if ($attribute == 'gender') {
                    $dataRow[] = Gender::getOptionsWithLabels()[$record->{$attribute}] ?? '';
                    continue;
                }
                if ($attribute == 'legal_relationship_id') {
                    $dataRow[] = $legalRelationshipsMap[$record->{$attribute}] ?? '';
                    continue;
                }
                if ($attribute == 'religion_id') {
                    $dataRow[] = $religionsMap[$record->{$attribute}] ?? '';
                    continue;
                }
                if ($attribute == 'tshirt_size_id') {
                    $dataRow[] = $tShirtSizesMap[$record->{$attribute}] ?? '';
                    continue;
                }
                if ($attribute == 'citizenship_country_id') {
                    $dataRow[] = $countryMap[$record->{$attribute}] ?? '';
                    continue;
                }
                if ($attribute == 'is_active') {
                    $dataRow[] = $record->inactivated_at ? '0' : '1';
                    continue;
                }
                $dataRow[] = strval($record->{$attribute});
            }

            $data[] = $dataRow;
        }

        return $data;
    }

    public function onRenderUploadForm(){
        $this->page['permissionValue'] = Input::get('permissionValue');
        $this->page['teamId'] = Input::get('teamId');
        $this->page['showUploadForm'] = true;

        if (Input::get('cancel')) {
            $this->page['showUploadForm'] = false;
        }

        return [
            '#uploadCsv' => $this->renderPartial('@csvXlsxUploadForm.htm')
        ];
    }

    public function onImportScoutsFromCsvXlsx(){
        $file = Input::file('csvXlsxFile');
        $teamId = Input::get('teamId');

        if (
            empty($file)
            || !$file->isValid()
            || ($file->getMimeType() != 'text/csv'
                && $file->getMimeType() != 'application/vnd.ms-excel'
                && $file->getMimeType() != 'text/plain'
                && $file->getMimeType() != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                )
            ) {
            \Flash::error(Lang::get('csatar.csatar::lang.plugin.component.organizationUnitFrontend.csv.fileMissingOrInvalid'));
            return;
        }

        if (empty($teamId)) {
            \Flash::error(Lang::get('csatar.csatar::lang.plugin.component.organizationUnitFrontend.csv.teamIdMissing'));
            return;
        }

        $data = [];
        $file = $file->move(temp_path(), $file->getClientOriginalName());

        if ($file->getExtension() == 'xlsx' or $file->getExtension() == 'xls') {
            $data = $this->getDataFromXlsx($file);
        }

        if ($file->getExtension() == 'csv' && ($handle = fopen($file, "r")) !== FALSE) {
            while (($csvData = fgetcsv($handle)) !== FALSE) {
                $data[] = $csvData;
            }
        }


        $attributes = $data[0];
        $log = [];

        foreach ($data as $rowNumber => $rowData) {
            if ($rowNumber == 0 || $rowNumber == 1) {
                continue;
            }

            $scout = $this->convertCsvRowToScout($teamId, $attributes, $rowData);

            try {
                $scout->skipCacheRefresh = true; //important, otherwise cache will be refreshed for each scout
                if (empty($scout->personal_identification_number)) {
                    $log['errors'][] = $rowNumber . ' | ' . Lang::get('csatar.csatar::lang.plugin.component.organizationUnitFrontend.csv.personalIdentificationNumberMissing');
                    continue;
                }
                if ($scout->is_active != Status::ACTIVE) {
                    $scout->inactivated_at = empty($scout->inactivated_at) ? date('Y-m-d H:i:s') : $scout->inactivated_at;
                    $scout->ignoreValidation = true;
                    $scout->forceSave();
                } else {
                    $scout->save();
                }
                if ($scout->wasRecentlyCreated) {
                    $log['created'][] = $rowNumber . ' - ' . $scout->ecset_code;
                } else {
                    $log['updated'][] = $rowNumber . ' - ' . $scout->ecset_code;
                }
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'DateTime::__construct()') !== false) {
                    $log['errors'][] = $rowNumber . ' | ' . $scout->name . ' - ' . $scout->ecset_code . ' | ' . Lang::get('csatar.csatar::lang.plugin.admin.scout.import.invalidDateTimeFormat');
                } else {
                    $log['errors'][] = $rowNumber . ' | ' . $scout->name . ' - ' . $scout->ecset_code . ' | ' . $e->getMessage();
                }

            }

        }

        $this->page['csvXlsxImportLog'] = $log;
        StructureTree::updateTeamTree($teamId);
        return [
            '#csvXlsxImportLog' => $this->renderPartial('@csvXlsxImportLog', ['log' => $log])
        ];
    }

    public function getDataFromXlsx($file){
        $importClass = new ScoutXlsxImport();
        Excel::excel()->import($importClass, $file);

        return $importClass->data;
    }

    private function convertCsvRowToScout($teamId, $attributes, $rowData): Scout
    {
        if ($attributes instanceof \Illuminate\Support\Collection) {
            $attributes = $attributes->toArray();
        }
        $legalRelationshipsMap = (new LegalRelationshipMapper)->namesToIds;
        $religionsMap = (new ReligionMapper)->namesToIds;
        $tShirtSizesMap = (new TShirtSizeMapper)->namesToIds;
        $countryMap = (new CountryMapper)->countryCodeToId;

        $personalIdentificationNumber = $rowData[array_search('personal_identification_number', $attributes)] ?? null;
        $ecsetCode = $rowData[array_search('ecset_code', $attributes)];

        $data = [
            'team_id' => $teamId,
        ];
        foreach ($rowData as $key => $value) {
            if ($attributes[$key] == 'gender') {
                $data[$attributes[$key]] = array_flip(Gender::getOptionsWithLabels())[$value] ?? null;
                continue;
            }
            if ($attributes[$key] == 'legal_relationship_id') {
                $data[$attributes[$key]] = $legalRelationshipsMap[$value] ?? null;
                continue;
            }
            if ($attributes[$key] == 'religion_id') {
                $data[$attributes[$key]] = $religionsMap[$value] ?? null;
                continue;
            }
            if ($attributes[$key] == 'tshirt_size_id') {
                $data[$attributes[$key]] = $tShirtSizesMap[$value] ?? null;
                continue;
            }
            if ($attributes[$key] == 'citizenship_country_id') {
                $data[$attributes[$key]] = $countryMap[$value] ?? null;
                continue;
            }
            $data[$attributes[$key]] = $value;
        }

        if (!empty($ecsetCode)) {
            $scout = Scout::where('team_id', $teamId)->where('ecset_code', $ecsetCode)->first();
        }
        if (empty($scout) && !empty($personalIdentificationNumber)) {
            unset($data['ecset_code']);
            $scout = Scout::where('team_id', $teamId)->where('personal_identification_number', $personalIdentificationNumber)->first();
        }
        if (empty($scout)) {
            $scout = new Scout();
        }

        $scout->fill($data);
        $scout->accepted_at = null;

        return $scout;
    }
}
