<?php namespace Csatar\KnowledgeRepository\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Csatar\Csatar\Models\Association;
use Csatar\KnowledgeRepository\Classes\Xlsx\GamesXlsxImport;
use Db;
use Flash;
use Input;
use Vdomah\Excel\Classes\Excel;
use Validator;
use Lang;

class Games extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'csatar.manage.data',
        'csatar.manage.knowledgerepository',
    ];

    public $errors = [];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.KnowledgeRepository', 'main-menu-knowledge-repository', 'side-menu-games');
        $this->pageTitle = Lang::get('csatar.knowledgerepository::lang.plugin.admin.general.import');
    }

    public function import()
    {
    }

    public function onGetScoutOptions() {
        $searchTerm = post('term');
        $queryResults = Db::table('csatar_csatar_scouts')->whereRaw("CONCAT(family_name, ' ', given_name, ' ', ecset_code) like ?", ['%'.$searchTerm.'%'])->paginate(15);
        $results = [];
        foreach ($queryResults as $result) {
            $results[] = [
                'id' => $result->ecset_code,
                'text' => $result->family_name . ' ' . $result->given_name . ' - ' . $result->ecset_code,
            ];
        }

        return [
            'results' => $results,
            'pagination' => [
                'more' => true
            ],
        ];
    }

    public function onImportGames(){
        $rules = [
            'association_id' => 'required',
            'uploader_csatar_code' => 'required',
            'xlsx_file' => 'required',
        ];

        $attributeNames = [
            'association_id' => Lang::get('csatar.csatar::lang.plugin.admin.association.association'),
            'uploader_csatar_code' => Lang::get('csatar.knowledgerepository::lang.plugin.admin.game.uploader'),
            'xlsx_file' => Lang::get('csatar.knowledgerepository::lang.plugin.admin.general.file'),
        ];

        $validator = Validator::make(Input::all(), $rules, [], $attributeNames);
        if ($validator->fails()) {
            throw new \ValidationException($validator);
        }

        $associationId = Input::get('association_id');
        $uploaderCsatarCode = Input::get('uploader_csatar_code');
        $approverCsatarCode = Input::get('approver_csatar_code');
        $owerwriteExistingData = Input::get('overwrite_existing_data');
        $xlsxFile = Input::file('xlsx_file');

        if (empty($xlsxFile) || !$xlsxFile->isValid() || ($xlsxFile->getMimeType() != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
            Flash::error(Lang::get('csatar.csatar::lang.plugin.component.organizationUnitFrontend.csv.fileMissingOrInvalid'));
            return;
        }

        $xlsxFile = $xlsxFile->move(temp_path(), $xlsxFile->getClientOriginalName());

        $import = new GamesXlsxImport($associationId, $uploaderCsatarCode, $approverCsatarCode, $owerwriteExistingData);
        $import->import($xlsxFile);

        if (!empty($import->failures()) && count($import->failures()) > 0) {
            $this->vars['errors'] = $import->failures();

            return [
                '#errors' => $this->makePartial('import_errors'),
            ];
        }

        Flash::success(Lang::get('csatar.knowledgerepository::lang.plugin.admin.messages.importSuccessful'));
    }

    public function onGetAssociationOptions() {
        $associations = Association::all()->lists('name', 'id');
        $results = [];
        foreach ($associations as $id => $name) {
            $results[] = [
                'id' => $id,
                'text' => $name,
                'selected' => $name == 'Romániai Magyar Cserkészszövetség' ? true : false,
            ];
        }

        return [
            'results' => $results,
        ];
    }
}
