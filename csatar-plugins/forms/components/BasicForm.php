<?php
namespace Csatar\Forms\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Classes\UserRigthsProvider;
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
    use \System\Traits\ViewMaker;

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
     * @var int
     */
    public $formId = null;

    /**
     * The form
     * @var Form
     */
    public $form = null;

    /**
     * The unique Id of the form instance
     * @var type
     */
    public $formUniqueId = null;

    /**
     * The URL parameter and DB column
     * to identify a record(id, slug etc.)
     * @var int
     */
    public $recordKeyParam = null;

    /**
     * To pass additional html data for rendering to the form
     * @var string
     */
    public $additionalData = null;

    /**
     * Special validation exceptions, generated outside
     * the standard validation flow
     * @var array
     */
    public array $specialValidationExceptions = [];

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
     * List of fields that require 2FA for any CRUD action
     * @var array
     */
    public $fieldsThatRequire2FA = null;

    /**
     * To store custom messages for special cases when no error or validation error is thrown
     * @var array
     */
    public $messages = null;

    /**
     * Initialise plugin and parse request
     */
    public function init() {
        if (post('redirect')) {
            return;
        }

        $this->controller->bindEvent('ajax.beforeRunHandler', function ($handler) {
            //check if handler starts with "relation"
            if (substr($handler, 0, 13) === 'pivotRelation' && strpos($handler, '::')) {
                [$componentAlias, $handlerName] = explode('::', $handler);
                return $this->$handlerName($componentAlias);
            }
        });

        $this->getForm();
        $this->setOrGetFormUniqueId();
        if ($this->properties['subForm']) {
            $this->record = $this->getRecordFromParent();
            if (empty($this->record)) {
                return;
            }

            $this->readOnly = $this->properties['readOnly'] ?? false;
        } else {
            $this->getComponentSettings();
            $this->recordKeyValue    = $this->param($this->recordKeyParam);
            $this->record            = $this->getRecord();
            $this->currentUserRights = $this->getRights($this->record);
        }

        $this->setOrGetSessionKey();
        $this->fieldsThatRequire2FA = $this->getFieldsThatRequire2FA($this->currentUserRights);
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
            'subForm' => [
                'title'             => 'csatar.forms::lang.components.basicForm.properties.subForm.title',
                'description'       => 'csatar.forms::lang.components.basicForm.properties.subForm.description',
                'type'              => 'checkbox',
                'default'           => null,
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

        $this->injectAssets();

        if ($this->properties['subForm'] && empty($this->record->id)) {
            return;
        }

        if ($this->readOnly) {
            $this->initReadOnlyMode();
            return;
        }

        if ($this->recordKeyValue === $this->createRecordKeyword && !$this->readOnly) {
            $this->initCreateMode();
            return;
        }

        if ($this->recordKeyValue !== $this->createRecordKeyword && !$this->readOnly && $this->recordActionParam) {
            $action = $this->properties['action'] ?? $this->param($this->recordActionParam) ?? null;
            switch ($action) {
                case $this->actionUpdateKeyword:
                    $this->initUpdateMode();
                    break;
                case $this->actionDeleteKeyword:
                    $this->initDeleteMode();
                    break;
                default:
                    $this->readOnly = true;
                    $this->initReadOnlyMode();
            }
        }
    }

    private function getForm() {
        $form = Form::where('slug', $this->property('formSlug'))->first();
        if (!empty($form)) {
            $this->form   = $form;
            $this->formId = $form->id;
            return $form;
        } else {
            $error = e(trans('csatar.forms::lang.errors.formNotFound'));
            throw new ApplicationException($error . $this->page->title);
        }
    }

    private function getRecord() {
        if (!empty($this->record)) {
            return $this->record;
        }

        $form      = $this->form ?? Form::find($this->formId ?? Input::get('formId'));
        $modelName = $form->getModelName();
        $key       = $this->recordKeyParam ?? Input::get('recordKeyParam');
        $value     = $this->recordKeyValue ?? Input::get('recordKeyValue');

        $record = null;
        if (!empty($key) && !empty($value)) {
            $record = $modelName::where($key, $value)->first();
        }

        if (!empty($record) && method_exists($modelName, 'getEagerLoadSettings')) {
            $eagerLoadSettings = $modelName::getEagerLoadSettings('formBuilder');
            $record->load($eagerLoadSettings);
        }

        if (!$record && $value == $this->createRecordKeyword) {
            $record = new $modelName();
        }

        if (!$record) {
            return null;
        }

        return $record;
    }

    private function getParent() {
        $parentClass    = $this->properties['parentModel']['class'] ?? null;
        $recordKeyParam = $this->properties['parentModel']['recordKeyParam'] ?? null;
        $recordKeyValue = $this->properties['parentModel']['recordKeyValue'] ?? null;

        return $parentClass::where($recordKeyParam, $recordKeyValue)->first();
    }

    private function getRecordFromParent() {

        $relationName = $this->properties['getRecordFromParent'] ?? null;
        $parent       = $this->getParent();

        if (empty($parent) || empty($relationName)) {
            return;
        }

        $this->getRightsFromParent($parent, $relationName);

        $hasCreateRights = $this->currentUserRights['MODEL_GENERAL']['create'] ?? -1;
        $hasUpdateRights = $this->currentUserRights['MODEL_GENERAL']['update'] ?? -1;
        $hasReadRights   = $this->currentUserRights['MODEL_GENERAL']['read'] ?? -1;

        if ($parent->$relationName && $hasUpdateRights > 0) {
            $record = $parent->$relationName;
            $this->properties['action'] = 'update';
        } elseif ($hasCreateRights > 0) {
            $form      = $this->form ?? Form::find($this->formId ?? Input::get('formId'));
            $modelName = $form->getModelName();
            $record    = new $modelName();
            $this->properties['action'] = 'create';
        } elseif ($hasReadRights > 0) {
            $record = $parent->$relationName;
            $this->properties['action'] = 'read';
        }

        if (empty($record)) {
            return;
        }

        return $record;
    }

    private function getRightsFromParent($parent, $relationName, $ignoreCache = false) {
        if (empty($parent) || empty($relationName)) {
            return null;
        }

        $userRightsForParent = $this->getRights($parent, $ignoreCache);
        if (empty($userRightsForParent[$relationName])) {
            return [];
        }

        $this->currentUserRights['MODEL_GENERAL']['create'] = $userRightsForParent[$relationName]['create'];
        $this->currentUserRights['MODEL_GENERAL']['update'] = $userRightsForParent[$relationName]['update'];
        $this->currentUserRights['MODEL_GENERAL']['read']   = $userRightsForParent[$relationName]['read'];

        $this->currentUserRights = collect($this->currentUserRights);
    }

    private function getComponentSettings() {
        $this->recordKeyParam = $this->property('recordKeyParam');
        $this->readOnly       = $this->property('readOnly');

        if (!$this->readOnly) {
            $this->createRecordKeyword = $this->property('createRecordKeyword');
            $this->recordActionParam   = $this->property('recordActionParam');
            $this->actionUpdateKeyword = $this->property('actionUpdateKeyword');
            $this->actionDeleteKeyword = $this->property('actionDeleteKeyword');
        }
    }

    public function setOrGetFormUniqueId(){
        $this->formUniqueId = Input::get('formUniqueId') ?? uniqid();
    }

    public function setOrGetSessionKey(){
        $prefix           = $this->formUniqueId . '_form_key_';
        $sessionKey       = Session::get($this->formUniqueId) ?? uniqid($prefix, true);
        $this->sessionKey = $sessionKey;
        Session::put($this->formUniqueId, $sessionKey);
    }

    private function getRights($record, $ignoreCache = false)
    {
        if (!$record) {
            throw new NotFoundException();
        }

        $this->autoloadBelongsToRelations($record);

        return UserRigthsProvider::getUserRigths($record, $ignoreCache);
    }

    private function getFieldsThatRequire2FA($userRights)    {
        if (!isset($userRights['is2fa'])) {
            return [];
        }

        return $userRights->map(function ($item, $key) use($userRights) {
            if ($key == 'is2fa') {
                return;
            }

            if (isset($item['obligatory'])) {
                unset($item['obligatory']);
            }

            $valueThatIndicates2FANeed = $userRights['is2fa'] ? 1 : 0;
            return array_keys($item, $valueThatIndicates2FANeed);
        })->filter(function ($item) {
            return $item != [] || $item != null;
        });
    }

    private function canCreate(string $attribute): bool
    {
        return $this->rightsCollectionHasKey($attribute)
            && is_array($this->currentUserRights[$attribute])
            && $this->currentUserRights[$attribute]['create'] > 0;
    }

    private function canRead(string $attribute): bool
    {
        return $this->rightsCollectionHasKey($attribute)
            && is_array($this->currentUserRights[$attribute])
            && isset($this->currentUserRights[$attribute]['read'])
            && $this->currentUserRights[$attribute]['read'] > 0;
    }

    private function canUpdate(string $attribute): bool
    {
        return $this->rightsCollectionHasKey($attribute)
            && is_array($this->currentUserRights[$attribute])
            && isset($this->currentUserRights[$attribute]['update'])
            && $this->currentUserRights[$attribute]['update'] > 0;
    }

    private function canDelete(string $attribute): bool
    {
        return $this->rightsCollectionHasKey($attribute)
            && is_array($this->currentUserRights[$attribute])
            && isset($this->currentUserRights[$attribute]['delete'])
            && $this->currentUserRights[$attribute]['delete'] > 0;
    }

    /**
     * @return void
     */
    public function initReadOnlyMode(): void
    {
        // check if user has permissions to view record
        if (!$this->canRead('MODEL_GENERAL')) {
            \App::abort(403, 'Access denied!');
        }

        $this->renderedComponent = $this->createForm(true);
    }

    /**
     * @return void
     */
    public function initCreateMode(): void
    {
        // check if user has permissions to create record
        if (!$this->canCreate('MODEL_GENERAL')) {
            \App::abort(403, 'Access denied!');
        }

        $this->renderedComponent = $this->createForm();
    }

    /**
     * @return void
     */
    public function initUpdateMode(): void
    {
        // check if user has permissions to update record
        if (!$this->canUpdate('MODEL_GENERAL')) {
            \App::abort(403, 'Access denied!');
        }

        $this->renderedComponent = $this->createForm();
    }

    /**
     * @return void
     */
    public function initDeleteMode(): void
    {
        $this->currentUserRights = $this->getRights($this->record, true); // getting user rights from database before delete
        if (!$this->canDelete('MODEL_GENERAL')) {
            \App::abort(403, 'Access denied!');
        }

        $this->renderedComponent = $this->onDelete();
    }

    /**
     * @return void
     */
    public function injectAssets(): void
    {
        // Render frontend
        $this->addCss('/plugins/csatar/forms/assets/css/storm-select2.css');
        $this->addCss('/plugins/csatar/forms/assets/css/storm.css');
        $this->addJs('/modules/system/assets/ui/storm-min.js');
        $this->addJs('/plugins/csatar/forms/assets/vendor/dropzone/dropzone.js');
        $this->addJs('/plugins/csatar/forms/assets/js/uploader.js');
        $this->addJs('/plugins/csatar/forms/assets/js/positionValidationTags.js');
        $this->addJs('/plugins/csatar/forms/assets/js/addCheckboxClass.js');
    }

    private function isObligatory(string $attribute): bool
    {
        return $this->rightsCollectionHasKey($attribute)
            && is_array($this->currentUserRights[$attribute])
            && isset($this->currentUserRights[$attribute]['obligatory'])
            && $this->currentUserRights[$attribute]['obligatory'] > 0;
    }

    private function rightsCollectionHasKey($attribute): bool
    {
        return !empty($this->currentUserRights) && $this->currentUserRights->has($attribute);
    }

    private function shouldIgnoreUserRights($attribute, $fieldsConfig): bool
    {
        if (isset($fieldsConfig[$attribute]['formBuilder']['ignoreUserRights']) && $fieldsConfig[$attribute]['formBuilder']['ignoreUserRights'] == 1) {
            return true;
        }

        return false;
    }

}
