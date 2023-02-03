<?php namespace Csatar\Csatar\Components;

use Auth;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Classes\CsvCreator;
use Csatar\Csatar\Classes\Enums\Gender;
use Csatar\Csatar\Classes\Mappers\LegalRelationshipMapper;
use Csatar\Csatar\Classes\Mappers\ReligionMapper;
use Csatar\Csatar\Models\GalleryModelPivot;
use Csatar\Csatar\Models\Scout;
use Db;
use Input;
use Lang;
use Redirect;

class OrganizationUnitFrontend extends ComponentBase
{
    public $model;
    public $content_page;
    public $permissions;
    public $gallery_id;
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
            $this->model = $modelName::find($this->property('model_id'));
            if(isset(Auth::user()->scout)) {
                $this->permissions = Auth::user()->scout->getRightsForModel($this->model);
            }
            if (empty($this->model->content_page))
            {
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
            '#tabContent' => $this->renderPartial('@editor', ['content_page' => $content])
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
            'mandate_team' => [
                'label' => Lang::get('csatar.csatar::lang.plugin.admin.team.team'),
                ],
            'scout' => [
                'label' => Lang::get('csatar.csatar::lang.plugin.admin.mandateType.scout'),
                'nameFrom' => 'name',
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
        if (empty($teamId)) {
            return;
        }

        $fileName = $teamId . '_csapat_' . Carbon::today()->toDateString() . '.csv';
        $csvPath = temp_path() . '/' . $fileName;

        $scouts = Scout::where('team_id', $teamId)->get();
        $model = Scout::getModelName();
        $attributesWithLabels = Scout::getTranslatedAttributeNames($model);

        $attributes = [
            'ecset_code',
            'name_prefix',
            'family_name',
            'given_name',
            'nickname',
            'email',
            'phone',
            'personal_identification_number',
            'gender',
            'legal_relationship_id',
            'religion_id',
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
            'comment',
        ];
        $attributesWithLabels = array_intersect_key($attributesWithLabels, array_flip($attributes));
        $legalRelationshipsMap = (new LegalRelationshipMapper)->idsToNames;
        $religionsMap = (new ReligionMapper)->idsToNames;

        $data = [];
        foreach ($attributesWithLabels as $attribute => $label) {
            $data[0][] = $attribute;
            $data[1][] = $label;
        }

        foreach ($scouts as $record) {
            $dataRow = [];
            foreach (array_keys($attributesWithLabels) as $attribute) {
                if ($attribute == 'gender') {
                    $dataRow[] = Gender::getOptionsWithLabels()[$record->{$attribute}];
                    continue;
                }
                if ($attribute == 'legal_relationship_id') {
                    $dataRow[] = $legalRelationshipsMap[$record->{$attribute}];
                    continue;
                }
                if ($attribute == 'religion_id') {
                    $dataRow[] = $religionsMap[$record->{$attribute}];
                    continue;
                }
                $dataRow[] = strval($record->{$attribute});
            }

            $data[] = $dataRow;
        }

        CsvCreator::writeCsvFile($csvPath, $data);

        return Redirect::to('csv-letoltes/' . $fileName);
    }

    public function onRenderUploadForm(){
        $this->page['permissionValue'] = Input::get('permissionValue');
        $this->page['teamId'] = Input::get('teamId');
        $this->page['showUploadForm'] = true;

        if (Input::get('cancel')) {
            $this->page['showUploadForm'] = false;
        }

        return [
            '#uploadCsv' => $this->renderPartial('@csvUploadForm')
        ];
    }

    public function onImportScoutsToCsv(){
        $file = Input::file('csvFile');
        $teamId = Input::get('teamId');

        if (empty($file) || empty($teamId)) {
            return; //TODO: flash here
        }

        if ($file->isValid()) {
            $file = $file->move(temp_path(), $file->getClientOriginalName());
        }

        $csvData = [];
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle)) !== FALSE) {
                $csvData[] = $data;
            }
        }

        $attributes = $csvData[0];
        $legalRelationshipsMap = (new LegalRelationshipMapper)->namesToIds;
        $religionsMap = (new ReligionMapper)->namesToIds;
        $log = [];

        foreach ($csvData as $rowNumber => $rowData) {
            if ($rowNumber == 0 || $rowNumber == 1) {
                continue;
            }
            $personalIdentificationNumber = $rowData[array_search('personal_identification_number', $attributes)];
            $ecsetCode = $rowData[array_search('ecset_code', $attributes)];

            $data = [];
            foreach ($rowData as $key => $value) {
                if ($attributes[$key] == 'gender') {
                    $data[$attributes[$key]] = array_flip(Gender::getOptionsWithLabels())[$value];
                    continue;
                }
                if ($attributes[$key] == 'legal_relationship_id') {
                    $data[$attributes[$key]] = $legalRelationshipsMap[$value];
                    continue;
                }
                if ($attributes[$key] == 'religion_id') {
                    $data[$attributes[$key]] = $religionsMap[$value];
                    continue;
                }
                $data[$attributes[$key]] = $value;
            }

            $firstOrNewConditions = [
                'team_id' => $teamId,
            ];
            unset($data['team_id']);
            unset($data['ecset_code']);

            if (empty($personalIdentificationNumber) && !empty($ecsetCode)) {
                $firstOrNewConditions['ecset_code'] = $ecsetCode;
            }

            if (empty($ecsetCode) && !empty($personalIdentificationNumber)) {
                $firstOrNewConditions['personal_identification_number'] = $personalIdentificationNumber;
                unset($data['personal_identification_number']);
            }

            if (!empty($ecsetCode) && !empty($personalIdentificationNumber)) {
                $firstOrNewConditions['personal_identification_number'] = $personalIdentificationNumber;
                $firstOrNewConditions['ecset_code'] = $ecsetCode;
                unset($data['personal_identification_number']);
            }

            $scout = Scout::firstOrNew($firstOrNewConditions);
            $scout->fill($data);

            try {

                $scout->save();
                if ($scout->wasRecentlyCreated) {
                    $log['created'][] = $rowNumber . ' - ' . $scout->ecset_code;
                } else {
                    $log['updated'][] = $rowNumber . ' - ' . $scout->ecset_code;
                }
            } catch (\Exception $e) {
                $log['errors'][] = $rowNumber . ' | ' . $scout->name . ' - ' . $scout->ecset_code . ' | ' . $e->getMessage();
            }

            $personalIdentificationNumber = null;
            $ecsetCode = null;
            $data = [];
            $firstOrNewConditions = [];
        }

        $this->page['csvImportLog'] = $log;
        return [
            '#csvImportLog' => $this->renderPartial('@csvImportLog', ['log' => $log])
        ];
    }
}
