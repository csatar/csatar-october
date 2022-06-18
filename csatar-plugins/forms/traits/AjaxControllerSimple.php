<?php namespace Csatar\Forms\Traits;

use http\Env\Request;
use Input;
use Flash;
use Validator;
use Csatar\Forms\Models\Form;
use Response;
use Cookie;
use Redirect;
use Backend\Classes\WidgetManager;
use October\Rain\Exception\ApplicationException;
use October\Rain\Exception\NotFoundException;

trait AjaxControllerSimple {

    use \System\Traits\ConfigMaker;

    public $widget;

    protected $current_model;

    public function formGetWidget()
    {
        return $this->widget;
    }

    /**
     * Registers backend widgets for frontend use
     */
    public function loadBackendFormWidgets() {
        $widgets = [
            'Backend\FormWidgets\DatePicker' => [
                'label' => 'Date picker',
                'code'  => 'datepicker'
            ],
            'Backend\FormWidgets\RichEditor' => [
                'label' => 'Rich editor',
                'code'  => 'richeditor'
            ],
            'Backend\FormWidgets\Relation' => [
                'label' => 'Relation',
                'code'  => 'relation'
            ],
            'Backend\FormWidgets\MarkdownEditor' => [
                'label' => 'MarkdownEditor',
                'code'  => 'markdown'
            ],
            // Custom file upload for frontend use
            'Csatar\Forms\Widgets\FrontendFileUpload' => [
                'label' => 'FileUpload',
                'code'  => 'fileupload'
            ]
        ];

        foreach ($widgets as $className => $widgetInfo) {
            WidgetManager::instance()->registerFormWidget($className, $widgetInfo);
        }
    }

    public function createForm($preview = false) {
        
        $form  = Form::find($this->formId);
        $record = $this->getRecord();

        if(!$record && $this->recordKeyValue == $this->createRecordKeyword) {
            $modelName  = $form->getModelName();
            $record      = new $modelName;
        }

        if(!$record) {
            throw new NotFoundException();
        }

        $config = $this->makeConfig($form->getFieldsConfig());
        $config->arrayName = 'data';
        $config->alias = $this->alias;
        $config->model = $record;

        // Autoload belongsTo relations
        foreach($record->belongsTo as $name => $definition) {
            if (!Input::get($name)) {
                continue;
            }

            $key = isset($definition['key']) ? $definition['key'] : $name . '_id';
            $record->$key = Input::get($name);
            $config->fields[$name]['readOnly'] = 1;
        }

        $this->widget = new \Backend\Widgets\Form($this, $config);

        $this->loadBackendFormWidgets();

        $html = $this->widget->render(['preview' => $preview]);
        if(!$preview){
            $html .= $this->renderValidationTags($record);
        }
        
        $variablesToPass = [
            'form' => $html,
            'additionalData' => $this->additionalData,
            'recordKeyParam' => 'id',
            'recordKeyValue' => $record->id ?? 'new',
            'from_id' => $form->id,
            'preview' => $preview ];
        
        return $this->renderPartial('@partials/form', $variablesToPass);
    }

    /**
     * Edits a relation
     * @return boolean
     */
    public function onEditRelated() {
        if ($response = $this->middleware()) {
            return $response;
        }

        if (!$model = $this->submission->getDataField($this->relation->field)->find(Input('recordKeyValue'))) {
           return false;
        }

        return $this->editor($this->relation->target, $this->model);
    }

    public function onSave() {
        $isNew = Input::get('recordKeyValue') == 'new' ? true : false;
        $record = $this->getRecord();

        $form = Form::find(Input::get('formId'));
        $modelName = $form->getModelName();

        if(!$record && $isNew) {
            $record = new $modelName;
        }

        if (! $data = Input::get('data')) {
            $error = e(trans('csatar.forms::lang.errors.noDataArray'));
            throw new ApplicationException($error);
        }

        // Resolve belongsTo relations
        foreach($record->belongsTo as $name => $definition) {
            if (! isset($data[$name])) {
                continue;
            }

            $key = isset($definition['key']) ? $definition['key'] : $name . '_id';
            $data[$key] = (int) $data[$name];
            unset($data[$name]);
        }

        if($isNew) {
            $record = $record->create($data);
        }
        if (!$record->update($data) && !$isNew) {
            $error = e(trans('csatar.forms::lang.errors.canNotSaveValidated'));
            throw new ApplicationException($error);
        }

        if (Input::get('close')) {
            return $this->onCloseForm();
        }

        return [
            '#renderedFormArea' => $this->renderPartial('@partials/saved')
        ];
    }

    public function onDelete() {

        $record = $this->getRecord();
        if($record){
            $record->delete();
        } else {
            throw new NotFoundException();
        }

    }

    public function renderValidationTags($model) {
        $html = "<div id='validationTags'>";
        foreach($model->rules as $fieldName => $rule) {
            $html .= "<span data-validate-for='" . $fieldName . "'></span>";
        }
        $html .= "</div>";

        return $html;
    }

    private function getRecord() {
        $form       = Form::find($this->formId ?? Input::get('formId'));
        $modelName  = $form->getModelName();
        $key        = $this->recordKeyParam ?? Input::get('recordKeyParam');
        $value      = $this->recordKeyValue ?? Input::get('recordKeyValue');

        $record      = $modelName::where($key, $value)->first();

        // set attribute names
        $config = $this->makeConfig($form->getFieldsConfig());
        $attributeNames = [];
        foreach ($config->fields as $key => $value) {
            $attributeNames[$key] = $value['label']; 
        }
        $record->setValidationAttributeNames($attributeNames);

        if(!$record) {
            //TODO handle trashed records
            return null;
        }

        return $record;
    }

}
