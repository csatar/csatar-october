<?php namespace Csatar\KnowledgeRepository\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Csatar\Csatar\Models\Association;
use Csatar\KnowledgeRepository\Classes\Xlsx\TrialSystemsXlsxImport;
use Csatar\KnowledgeRepository\Classes\Xlsx\TrialSystemsXlsxImport2;
use Vdomah\Excel\Classes\Excel;
use Flash;
use Input;
use Lang;
use Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TrialSystems extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.KnowledgeRepository', 'main-menu-knowledge-repository', 'side-menu-trialsystem');
    }

    public $requiredPermissions = [
        'csatar.manage.data',
        'csatar.manage.knowledgerepository',
    ];

    public function import()
    {
        $this->pageTitle = Lang::get('csatar.knowledgerepository::lang.plugin.admin.general.import');
    }

    public function onGetAssociationOptions() {
        return Association::getAssociationOptionsForSelect();
    }

    public function onImportTrialSystems(){
        $rules = [
            'association_id' => 'required',
            'xlsx_file' => 'required',
        ];

        $attributeNames = [
            'association_id' => Lang::get('csatar.csatar::lang.plugin.admin.association.association'),
            'xlsx_file' => Lang::get('csatar.knowledgerepository::lang.plugin.admin.general.file'),
        ];

        $validator = Validator::make(Input::all(), $rules, [], $attributeNames);
        if ($validator->fails()) {
            throw new \ValidationException($validator);
        }

        $associationId = Input::get('association_id');
        $effectiveKnowledgeOnly = Input::get('effective_knowledge_only');
        $xlsxFile = Input::file('xlsx_file');

        if (empty($xlsxFile) || !$xlsxFile->isValid() || ($xlsxFile->getMimeType() != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
            Flash::error(Lang::get('csatar.csatar::lang.plugin.component.organizationUnitFrontend.csv.fileMissingOrInvalid'));
            return;
        }

        $xlsxFile = $xlsxFile->move(temp_path(), $xlsxFile->getClientOriginalName());

        $worksheetRaw = IOFactory::load($xlsxFile);

        $import = new TrialSystemsXlsxImport($associationId, true, $effectiveKnowledgeOnly, $worksheetRaw);

        Excel::import($import, $xlsxFile);

        if (!empty($import->failures()) && count($import->failures()) > 0) {
            $this->vars['errors'] = $import->failures();

            return [
                '#errors' => $this->makePartial('import_errors'),
            ];
        }

        if (!empty($import->errors) && count($import->errors) > 0) {
            $this->vars['errors'] = $import->errors;

            return [
                '#errors' => $this->makePartial('import_errors'),
            ];
        }

        Flash::success(Lang::get('csatar.knowledgerepository::lang.plugin.admin.messages.importSuccessful'));
    }
}
