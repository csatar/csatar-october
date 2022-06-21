<?php namespace Csatar\Forms\Components;

use Lang;
use Cms\Classes\ComponentBase;
use Csatar\Forms\Models\Form;
use Csatar\Forms\Traits\ManagesUploads;
use Csatar\Forms\Traits\AjaxControllerSimple;
use October\Rain\Exception\ApplicationException;

class BasicForm extends ComponentBase  {

    use AjaxControllerSimple;
    use ManagesUploads;

    /**
     * The relation model
     * @var type
     */
    public $relation = null;

    /**
     * The Id of the form
     * @var type
     */
    public $formId = null;

    /**
     * The URL parameter and DB column
     * to identify a record(id, slug etc.)
     * @var int
     */
    public $recordKeyParam = null;

    /**
     * The URL parameter and DB column
     * to identify a record(id, slug etc.)
     * @var int
     */
    public $additionalData = null;

    /**
     * The value of the key parameter
     * @var string
     */
    public $recordKeyValue = null;

    /**
     * Component property, if true, form is displayed in preview mode
     * @var mixed
     */
    public $readOnly = null;

    /**
     * If value of $recordKeyValue == $createRecordKeyword
     * an empty form will be rendered to create new record
     * @var boolean
     */
    public $createRecordKeyword = null;

    /**
     * The URL parameter to specify update/delete action
     * @var string
     */
    public $recordActionParam = null;

    /**
     * Keyword for update action
     * @var string
     */
    public $actionUpdateKeyword = null;

    /**
     * Keyword for delete action
     * @var string
     */
    public $actionDeleteKeyword = null;


    /**
     * Data model
     * @var Model
     */
    public $record = null;

    /**
     * Contains the rendered component
     * @var string
     */
    public $renderedComponent = null;

    /**
     * Initialise plugin and parse request
     */
    public function init() {
        $this->record = $this->getRecord();
    }

    /**
     * Register component details
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'csatar.forms::lang.components.basicForm.name',
            'description' => 'csatar.forms::lang.components.basicForm.description',
        ];
    }

    /**
     * Register properties
     * @return array
     */
    public function defineProperties()
    {
        return [
            'formSlug' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.formId.title',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.formId.description',
                'type'              => 'dropdown',
                'options'           => Form::lists('title', 'slug'),
                'default'           => null,
                'validation'  => [
                    'required' => [
                        'message' => Lang::get('csatar.forms::lang.components.basicForm.properties.propertiesValidation.formNotSelected')
                    ]
                ]
            ],
            'recordKeyParam' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.recordKeyParam',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.recordKeyParamDescr',
                'type'              => 'string',
                'default'           => 'id',
                'showExternalParam' => false,
                'group'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.groupName',
                'validation'  => [
                    'required' => [
                        'message' => Lang::get('csatar.forms::lang.components.basicForm.properties.propertiesValidation.recordKeyNotSelected')
                    ]
                ]
            ],
            'recordActionParam' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.recordActionParam',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.recordlActionParamDescr',
                'type'              => 'text',
                'default'           => 'action',
                'showExternalParam' => false,
                'group'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.groupName',
            ],
            'readOnly' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.readOnly',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.readOnlyDescr',
                'type'              => 'checkbox',
                'showExternalParam' => false,
                'group'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.groupName',
            ],
            'createRecordKeyword' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.createRecordKeyword',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.createRecordKeywordDescr',
                'type'              => 'string',
                'default'           => 'create',
                'showExternalParam' => false,
                'group'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.groupName',
            ],
            'actionUpdateKeyword' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.actionUpdateKeyword',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.actionUpdateKeywordDescr',
                'type'              => 'string',
                'default'           => 'update',
                'showExternalParam' => false,
                'group'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.groupName',
            ],
            'actionDeleteKeyword' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.actionDeleteKeyword',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.actionDeleteKeywordDescr',
                'type'              => 'string',
                'default'           => 'delete',
                'showExternalParam' => false,
                'group'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.groupName',
            ],
        ];
    }

    /**
     * Renders the frontend
     * @return mixed
     */
    public function onRun() {

        // Handle file upload requests
        if ($handler = $this->processUploads()) {
            return $handler;
        }

        $this->getComponentSettings();

        // Render frontend
        $this->addCss('/plugins/csatar/forms/assets/css/storm.css');
        $this->addJs('/modules/system/assets/ui/storm-min.js');
        $this->addJs('/plugins/csatar/forms/assets/vendor/dropzone/dropzone.js');
        $this->addJs('/plugins/csatar/forms/assets/js/uploader.js');
        $this->addJs('/plugins/csatar/forms/assets/js/positionValidationTags.js');

        $form = $this->getForm();
        $this->recordKeyValue = $this->param($this->recordKeyParam);

        if($this->readOnly){
            $this->renderedComponent = $this->createForm(true);
        }

        if($this->recordKeyValue === $this->createRecordKeyword && !$this->readOnly) {
            $this->renderedComponent = $this->createForm();
        }

        if($this->recordKeyValue !== $this->createRecordKeyword && !$this->readOnly && $this->recordActionParam) {
            $action = $this->param($this->recordActionParam);

            switch ($action) {
                case $this->actionUpdateKeyword:
                    $this->renderedComponent = $this->createForm();
                    break;
                case $this->actionDeleteKeyword:
                    $this->renderedComponent = $this->onDelete();
                    break;
                default:
                    $this->readOnly = true;
                    $this->renderedComponent = $this->createForm(true);
            }
        }
    }

    public function onRefresh()
    {
    }

    private function getForm() {
        $form = Form::where('slug', $this->property('formSlug'))->first();
        if (!empty($form)) {
            $this->formId = $form->id;
            return $form;
        } else {
            $error = e(trans('csatar.forms::lang.errors.formNotFound'));
            throw new ApplicationException($error . $this->page->title);
        }
    }

    private function getComponentSettings() {
        $this->recordKeyParam   = $this->property('recordKeyParam');
        $this->readOnly         = $this->property('readOnly');

        if(!$this->readOnly){
            $this->createRecordKeyword  = $this->property('createRecordKeyword');
            $this->recordActionParam    = $this->property('recordActionParam');
            $this->actionUpdateKeyword  = $this->property('actionUpdateKeyword');
            $this->actionDeleteKeyword  = $this->property('actionDeleteKeyword');
        }
    }

}
