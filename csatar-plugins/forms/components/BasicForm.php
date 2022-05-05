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

    public $formId = null;

    public $recordKeyParam = null;

    public $recordKeyValue = null;

    public $readOnly = null;

    public $createRecordKeyword = null;

    public $recordActionParam = null;

    public $actionUpdateKeyword = null;

    public $actionDeleteKeyword = null;


    /**
     * Data model
     * @var Model
     */
    public $model = null;

    /**
     * Contains the rendered component
     * @var string
     */
    public $renderedComponent = null;

    /**
     * Initialise plugin and parse request
     */
    public function init() {

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
            'formId' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.formId.title',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.formId.description',
                'type'              => 'dropdown',
                'options'           => Form::lists('title', 'id'),
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
            'recordActionParam' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.recordActionParam',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.recordlActionParamDescr',
                'type'              => 'text',
                'default'           => 'action',
                'showExternalParam' => false,
                'group'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.groupName',
            ],
            'actionUpdateKeyword' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.actionUpdateKeyword',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.actionUpdateKeywordDescr',
                'type'              => 'text',
                'default'           => 'update',
                'showExternalParam' => false,
                'group'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.groupName',
            ],
            'actionDeleteKeyword' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.actionDeleteKeyword',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.actionDeleteKeywordDescr',
                'type'              => 'text',
                'default'           => 'delete',
                'showExternalParam' => false,
                'group'             => 'csatar.forms::lang.components.basicForm.properties.groupCRUD.groupName',
            ],
        ];
    }

    /**
     * Returns a list of forms
     * @return type
     */
    public function getForms() {
        return Form::findMany($this->property('form'));
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
        $this->addCss('/modules/system/assets/ui/storm.css');
        $this->addJs('/modules/system/assets/ui/storm-min.js');
        $this->addJs('/plugins/csatar/forms/assets/vendor/dropzone/dropzone.js');
        $this->addJs('/plugins/csatar/forms/assets/js/uploader.js');
        $this->addJs('/plugins/csatar/forms/assets/js/positionValidationTags.js');

        $form = $this->getForm();
        $this->recordKeyValue = $this->param($this->recordKeyParam);

        if($this->readOnly){
            $this->renderedComponent = $this->createForm($form, true);
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
                    $this->renderedComponent = $this->createForm(true);
            }
        }


    }

    private function getForm() {
        $form = Form::find($this->property('formId'));
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
