<?php namespace Csatar\Forms\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Csatar\Forms\Models\Form;
use Csatar\Forms\Traits\AjaxControllerSimple;
use Csatar\Forms\Traits\ManagesUploads;
use Input;
use Lang;
use October\Rain\Database\Collection;
use October\Rain\Database\Models\DeferredBinding;
use October\Rain\Exception\ApplicationException;
use October\Rain\Exception\NotFoundException;
use Redirect;
use Session;

class BasicForm extends ComponentBase  {

    use AjaxControllerSimple;
    use ManagesUploads;

    /**
     * Session key for deferred bindings
     * @var mixed
     */
    public $sessionKey = null;

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
     * Current user rights for the record/model
     * @var array
     */
    public $currentUserRights = null;

    /**
     * Initialise plugin and parse request
     */
    public function init() {
        $this->getForm();
        $this->getComponentSettings();
        $this->recordKeyValue = $this->param($this->recordKeyParam);
        $this->record = $this->getRecord();
        $this->setOrGetSessionKey();
        $this->currentUserRights = $this->getRights($this->record);
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

        if($this->readOnly){
            //check if user has permissions to view record
            if (!$this->canRead('MODEL_GENERAL')) {
                \App::abort(403, 'Access denied!');
            }
            $this->renderedComponent = $this->createForm(true);
        }

        if($this->recordKeyValue === $this->createRecordKeyword && !$this->readOnly) {
            //check if user has permissions to create record
            if (!$this->canCreate('MODEL_GENERAL')) {
                \App::abort(403, 'Access denied!');
            }
            $this->renderedComponent = $this->createForm();
        }

        if($this->recordKeyValue !== $this->createRecordKeyword && !$this->readOnly && $this->recordActionParam) {
            $action = $this->param($this->recordActionParam);

            switch ($action) {
                case $this->actionUpdateKeyword:
                    //check if user has permissions to update record
                    if (!$this->canUpdate('MODEL_GENERAL')) {
                        \App::abort(403, 'Access denied!');
                    }
                    if(!Auth::check()){
                        return Redirect::to('/bejelentkezes');
                    }
                    $this->renderedComponent = $this->createForm();
                    break;
                case $this->actionDeleteKeyword:
                    $this->currentUserRights = $this->getRights($this->record, true); //getting user rights from database before delete
                    if (!$this->canDelete('MODEL_GENERAL')) {
                        \App::abort(403, 'Access denied!');
                    }
                    $this->renderedComponent = $this->onDelete();
                    break;
                default:
                    if (!$this->canRead('MODEL_GENERAL')) {
                        \App::abort(403, 'Access denied!');
                    }
                    $this->readOnly = true;
                    $this->renderedComponent = $this->createForm(true);
            }
        }
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

    public function setOrGetSessionKey(){
        $sessionKey = Session::get('key') ?? uniqid('session_key', true);
        $this->sessionKey = $sessionKey;
        Session::put('key', $sessionKey);
    }

    private function getRights($record, $ignoreCache = false)
    {
        if(!$record) {
            throw new NotFoundException();
        }

        $this->autoloadBelongsToRelations($record);

        if(Auth::user() && !empty(Auth::user()->scout)) {
            return Auth::user()->scout->getRightsForModel($record, $ignoreCache);
        } else {
            return $record->getGuestRightsForModel();
        }
    }

    private function canCreate(string $attribute): bool
    {
        return $this->rightsCollectionHasKey($attribute) && $this->currentUserRights[$attribute]['create'] === 1;
    }

    private function canRead(string $attribute): bool
    {
        return $this->rightsCollectionHasKey($attribute) && $this->currentUserRights[$attribute]['read'] === 1;
    }

    private function canUpdate(string $attribute): bool
    {
        return $this->rightsCollectionHasKey($attribute) && $this->currentUserRights[$attribute]['update'] === 1;
    }

    private function canDelete(string $attribute): bool
    {
        return $this->rightsCollectionHasKey($attribute) && $this->currentUserRights[$attribute]['delete'] === 1;
    }

    private function isObligatory(string $attribute): bool
    {
        return $this->rightsCollectionHasKey($attribute) && $this->currentUserRights[$attribute]['obligatory'] === 1;
    }

    private function rightsCollectionHasKey($attribute): bool
    {
        return !empty($this->currentUserRights) && $this->currentUserRights->has($attribute);
    }
}
