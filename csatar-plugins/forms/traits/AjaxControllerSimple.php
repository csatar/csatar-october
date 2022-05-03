<?php namespace Csatar\Forms\Traits;

use Input;
use Flash;
use Validator;
use Csatar\Forms\Models\Form;
use Response;
use Cookie;
use Redirect;
use Backend\Classes\WidgetManager;
use October\Rain\Exception\ApplicationException;

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

    public function createForm($form, $model_id) {

        $model = null;

        $modelName = $form->getModelName();

        if(!$model = $modelName::find($model_id)) {
            if($model = $modelName::withTrashed()->find($model_id)){
                // TOOO: in v2 render partial here.
                return 'The item was deleted...';
            }
        }

        if($model_id == 'new') {
            $model = new $modelName;
        }

        $config = $this->makeConfig($form->getFieldsConfig());
        $config->arrayName = 'data';
        $config->alias = $this->alias;
        $config->model = $model;

        $this->widget = new \Backend\Widgets\Form($this, $config);

        $this->loadBackendFormWidgets();

        $html = $this->widget->render();
        $html .= $this->renderValidationTags($model);

        $this->page['form_id'] = $form->id;
        $this->form_id = $form->id;
        $this->model = $model;
        return $this->renderPartial('@partials/form',
            ['form' => $html, 'data_id' => $model->id ?? 'new', 'from_id' => $form->id]);
    }

    /**
     * Edits a relation
     * @return boolean
     */
    public function onEditRelated() {
        if ($response = $this->middleware()) {
            return $response;
        }

        if (! $model = $this->submission->getDataField($this->relation->field)->find(Input('data_id'))) {
           return false;
        }

        return $this->editor($this->relation->target, $this->model);
    }

    public function onSave() {

        $isNew = Input::get('data_id') == 'new' ? true : false;
        $model = null;

        $form_id = Input::get('form_id');
        $form = Form::find($form_id);
        $modelName = $form->getModelName();
        if(!($model = $modelName::find(Input::get('data_id'))) && $isNew) {
            $model = new $modelName;
        }

        if (! $data = Input::get('data')) {
            $error = e(trans('csatar.forms::lang.errors.noDataArray'));
            throw new ApplicationException($error);
        }

        // Resolve belongsTo relations
        foreach($model->belongsTo as $name => $definition) {
            if (! isset($data[$name])) {
                continue;
            }

            $key = isset($definition['key']) ? $definition['key'] : $name . '_id';
            $data[$key] = (int) $data[$name];
            unset($data[$name]);
        }

        if($isNew) {
            $model = $model->create($data);
        }
        if (! $model->update($data) && !$isNew) {
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

    public function renderValidationTags($model) {
        $html = "<div id='validationTags'>";
        foreach($model->rules as $fieldName => $rule) {
            $html .= "<span data-validate-for='" . $fieldName . "'></span>";
        }
        $html .= "</div";

        return $html;
    }

}
