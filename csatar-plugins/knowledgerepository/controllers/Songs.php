<?php
namespace Csatar\KnowledgeRepository\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\Scout;
use Csatar\KnowledgeRepository\Classes\Xlsx\SongsXlsxImport;
use Db;
use Flash;
use Input;
use Lang;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Validator;
use Vdomah\Excel\Classes\Excel;

class Songs extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.KnowledgeRepository', 'main-menu-knowledge-repository', 'side-menu-songs');
    }

    public $requiredPermissions = [
        'csatar.manage.data',
        'csatar.manage.knowledgerepository',
    ];

    public function import()
    {
        $this->pageTitle = Lang::get('csatar.knowledgerepository::lang.plugin.admin.general.import');
    }

    public function onImportSongs(){
        $rules = [
            'association_id' => 'required',
            'uploader_csatar_code' => 'required',
            'xlsx_file' => 'required',
        ];

        $attributeNames = [
            'association_id' => Lang::get('csatar.csatar::lang.plugin.admin.association.association'),
            'uploader_csatar_code' => Lang::get('csatar.knowledgerepository::lang.plugin.admin.general.uploaderCsatarCode'),
            'xlsx_file' => Lang::get('csatar.knowledgerepository::lang.plugin.admin.general.file'),
        ];

        $validator = Validator::make(Input::all(), $rules, [], $attributeNames);
        if ($validator->fails()) {
            throw new \ValidationException($validator);
        }

        $associationId         = Input::get('association_id');
        $uploaderCsatarCode    = Input::get('uploader_csatar_code');
        $approverCsatarCode    = Input::get('approver_csatar_code');
        $owerwriteExistingData = Input::get('overwrite_existing_data');
        $richTextColumns       = 'Szöveg';

        $xlsxFile = Input::file('xlsx_file');

        $worksheetRaw = IOFactory::load($xlsxFile);

        if (empty($xlsxFile) || !$xlsxFile->isValid() || ($xlsxFile->getMimeType() != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
            Flash::error(Lang::get('csatar.csatar::lang.plugin.component.organizationUnitFrontend.csv.fileMissingOrInvalid'));
            return;
        }

        $xlsxFile = $xlsxFile->move(temp_path(), $xlsxFile->getClientOriginalName());

        $import = new SongsXlsxImport($associationId, $owerwriteExistingData, $richTextColumns, $worksheetRaw, $uploaderCsatarCode, $approverCsatarCode);

        Excel::import($import, $xlsxFile);

        if (!empty($import->errors) && count($import->errors) > 0) {
            $this->vars['errors'] = $import->errors;

            return [
                '#errors' => $this->makePartial('import_errors'),
            ];
        }

        Flash::success(Lang::get('csatar.knowledgerepository::lang.plugin.admin.messages.importSuccessful'));
    }

    public function onGetScoutOptions() {
        $searchTerm   = post('term');
        return Scout::getScoutOptionsForSelect($searchTerm);
    }

    public function onGetAssociationOptions() {
        return Association::getAssociationOptionsForSelect();
    }

}
