<?php namespace Csatar\Forms\Traits;

use http\Env\Request;
use Input;
use Flash;
use File;
use Lang;
use Validator;
use Session;
use Csatar\Forms\Models\Form;
use Response;
use Cookie;
use Redirect;
use Backend\Classes\WidgetManager;
use October\Rain\Exception\ApplicationException;
use October\Rain\Exception\NotFoundException;
use October\Rain\Database\Models\DeferredBinding;

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

    public function createForm($preview = false)
    {
        $form  = Form::find($this->formId);
        $record = $this->getRecord();

        if(!$record) {
            throw new NotFoundException();
        }

        $config = $this->makeConfig($form->getFieldsConfig());
        $config->arrayName = 'data';
        $config->alias = $this->alias;
        $config->model = $record;

        // Autoload belongsTo relations
        foreach($record->belongsTo as $name => $definition) {
            $inp = Input::get('data.' . $name);
            if (!Input::get($name) && !Input::get('data.' . $name)) {
                continue;
            }

            $key = isset($definition['key']) ? $definition['key'] : $name . '_id';
            $record->$key = Input::get($name) ?? Input::get('data.' . $name);
//            $config->fields[$name]['readOnly'] = 1;
        }


        $this->widget = new \Backend\Widgets\Form($this, $config);

        $this->loadBackendFormWidgets();

        $html = $this->widget->render(['preview' => $preview]);
        if(!$preview){
            $html .= $this->renderValidationTags($record);
        }

        $html .= $this->renderBelongsToManyRalationsWithPivotData($record);

        $variablesToPass = [
            'form' => $html,
            'additionalData' => $this->additionalData,
            'recordKeyParam' => $this->recordKeyParam ?? Input::get('recordKeyParam'),
            'recordKeyValue' => $record->{$this->recordKeyParam ?? Input::get('recordKeyParam')} ?? 'new',
            'from_id' => $form->id,
            'preview' => $preview,
            'redirectOnClose' => Input::old('redirectOnClose') ?? \Url::previous(),
            'actionUpdateKeyword' => $this->actionUpdateKeyword
        ];

        return $this->renderPartial('@partials/form', $variablesToPass);
    }

    public function onAddPivotRelation(){
        $relationName = Input::get('relationName');
        $relationId = Input::get($relationName);
        return $this->createPivotForm($relationName, $relationId);
    }

    public function onCloseAddEditArea(){
        $relationName = Input::get('relationName');

        return [
            '#add-edit-' . $relationName => ''
        ];
    }

    public function onListAttachOptions(){
        $record = $this->getRecord();
        $relationName = Input::get('relationName');
        $defRecords = DeferredBinding::where('master_field', $relationName)
            ->where('session_key', $this->sessionKey)
            ->get();
        $attachedIds = $record->id ? $record->{$relationName}->pluck('id') : $defRecords->pluck('slave_id');
        $relatedModelName = array_key_exists($relationName, $record->belongsToMany) ? $record->belongsToMany[$relationName][0] : false;
        $getFunctionName = 'get' . $this->underscoreToCamelCase($relationName, true) . 'Options';
        $options = null;
        if(method_exists($record, $getFunctionName)){
            $options = $record->{$getFunctionName}();
        }

        \Model::extend(function($model) use ($getFunctionName, $relatedModelName, $attachedIds, $options){
            $model->addDynamicMethod($getFunctionName, function() use ($model, $relatedModelName, $attachedIds, $options) {
                if(!empty($options)){
                    return $options;
                }
                return $relatedModelName::whereNotIn('id', $attachedIds)->get()
                    ->lists('name', 'id');
            });
        });

        $model = new \Model();

        $dropDownConfig = [
            'fields' => [
                $relationName => [
                    "span" => "full",
                    "type" => "dropdown",
                ],
            ],
            'model' => $model,
        ];

        $widget = new \Backend\Widgets\Form($this, $dropDownConfig);

        $this->loadBackendFormWidgets();

        $html = $widget->render();

        return [
            '#add-edit-' . $relationName => $this->renderPartial('@partials/relationOptions', [ 'html' => $html, 'relationName' => $relationName ])
        ];
    }

    public function createPivotForm($relationName, $relationId) {
        $preview = $this->readOnly;
        $record = $this->getRecord();
        $relatedModelName = array_key_exists($relationName, $record->belongsToMany) ? $record->belongsToMany[$relationName][0] : false;
        $relatedModel = $relatedModelName::find($relationId);

        $pivotConfig = $this->makeConfig($this->getPivotFieldsConfig($relatedModelName));
        $pivotConfig->arrayName = $relationName;
        $pivotConfig->alias = $relatedModelName;
        $pivotConfig->model = $relatedModel;
        $widget = new \Backend\Widgets\Form($this, $pivotConfig);

        $this->loadBackendFormWidgets();

        $html = $widget->render(['preview' => $preview]);
        $pivotModel = $this->getPivotModelIfSet($relationName);
        if(!$preview && !empty($pivotModel->rules)){
            $html .= $this->renderValidationTags($pivotModel, true, $relationName);
        }

        return [
            '#add-edit-' . $relationName => $this->renderPartial('@partials/relationForm', [
                'html' => $html,
                'relationName' => $relationName,
                'relationId' => $relatedModel->id
            ])
        ];
    }

    public function onSavePivotRelation(){
        $record = $this->getRecord();
        $relationName = Input::get('relationName');
        $relationId = Input::get('relationId');
        $pivotData = Input::get($relationName)['pivot'];
        $pivotModel = $this->getPivotModelIfSet($relationName);

        if ($pivotModel && method_exists($pivotModel, 'beforeValidateFromForm')) {
            $pivotModel->beforeValidateFromForm($pivotData);
        }
        if(!empty($pivotModel->rules)) {
            $rules = $pivotModel->rules;

            $validation = Validator::make(
                $pivotData,
                $rules
            );
            if ($validation->fails()) {
                throw new \ValidationException($validation);
            }
        }
        if ($pivotModel && method_exists($pivotModel, 'beforeSaveFromForm')) {
            $pivotModel->beforeSaveFromForm($pivotData);
        }

        if(!$record->id){
            $modelToAttach = $record->$relationName()->getRelated()->find($relationId);
            $record->{$relationName}()->add($modelToAttach, $this->sessionKey, $pivotData);
        } else {
            $record->{$relationName}()->attach($relationId, $pivotData);
        }

        return [
            '#pivotSection' =>
                $this->renderBelongsToManyRalationsWithPivotData($record),
            '#pivot-form' => '',
        ];
    }

    /**
     * Edits a relation
     * @return boolean
     */
    public function onEditRelated()
    {
        if ($response = $this->middleware()) {
            return $response;
        }

        if (!$model = $this->submission->getDataField($this->relation->field)->find(Input('recordKeyValue'))) {
           return false;
        }

        return $this->editor($this->relation->target, $this->model);
    }

    public function onSave()
    {
        $isNew = Input::get('recordKeyValue') == 'new' ? true : false;
        $record = $this->getRecord();

        if (!$data = Input::get('data')) {
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
//            unset($data[$name]);
        }

        // Resolve belongsToMany relations

        foreach($record->belongsToMany as $relationName => $definition) {
            if (!isset($data[$relationName]) || $data[$relationName] =='') {
                continue;
            }

            if(!$record->id){
                $relatedModel = $definition[0];
                if(is_array($data[$relationName])){
                    foreach ($data[$relationName] as $recordToAttachId) {
                        $deferred = new DeferredBinding();
                        $deferred->master_type = get_class($record);
                        $deferred->master_field = $relationName;
                        $deferred->slave_type = $relatedModel;
                        $deferred->slave_id = $recordToAttachId;
                        $deferred->session_key = $this->sessionKey;
                        $deferred->save();
                    }
                }
            } else {
                $record->$relationName()->sync($data[$relationName]);
            }
        }

        // validate the form
        $form = Form::find($this->formId ?? Input::get('formId'));
        $config = $this->makeConfig($form->getFieldsConfig());
        $attributeNames = [];
        foreach ($config->fields as $key => $value) {
            $attributeNames[$key] = Lang::get($value['label']);
        }
        $validation = Validator::make(
            $data,
            $record->rules,
            [],
            $attributeNames,
        );
        if ($validation->fails()) {
            throw new \ValidationException($validation);
        }

        // save the data
        if ($isNew) {
            $record = $record->create($data, $this->sessionKey);
            $record->commitDeferred($this->sessionKey);
        }

        // Resolve belongsToMany relations
        foreach($record->belongsToMany as $name => $definition) {
            if (!isset($data[$name]) || $data[$name] =='') {
                continue;
            }
            $record->$name()->sync($data[$name]);
        }

        if (!$record->update($data) && !$isNew) {
            $error = e(trans('csatar.forms::lang.errors.canNotSaveValidated'));
            throw new ApplicationException($error);
        }

        if (Input::get('close')) {
            return $this->onCloseForm();
        }

        if ($isNew) {
            $redirectUrl = str_replace('default', '', $this->currentPageUrl(false)) . $record->{$this->recordKeyParam ?? Input::get('recordKeyParam')} . '/' .Input::get('actionUpdateKeyword');
            return Redirect::to($redirectUrl)->withInput();
        }

        \Flash::success(e(trans('csatar.forms::lang.success.saved')));
        return Redirect::back()->withInput();
    }

    public function onCloseForm(){
        return Redirect::to(Input::get('redirectOnClose') ?? '/');
    }

    public function onDelete()
    {
        $record = $this->getRecord();
        if($record){
            $record->delete();
        } else {
            throw new NotFoundException();
        }
    }

    public function renderValidationTags($model, $forPivot = false, $relationName = null)
    {
        if(!empty($model->rules)){
            $html = "<div class='validationTags'>";
            foreach($model->rules as $fieldName => $rule) {
                $positionData = !$forPivot ? $fieldName : $relationName . '[pivot][' . $fieldName . ']';
                $html .= "<span class='errormsg' data-validate-for='" . $fieldName . "' data-position-for='" . $positionData . "'></span>";
            }
            $html .= "</div>";

            return $html;
        }

        return '';
    }

    private function getRecord() {
        $form       = Form::find($this->formId ?? Input::get('formId'));
        $modelName  = $form->getModelName();
        $key        = $this->recordKeyParam ?? Input::get('recordKeyParam');
        $value      = $this->recordKeyValue ?? Input::get('recordKeyValue');

        $record     = $modelName::where($key, $value)->first();
        if (!$record && ($value == 'new' || $value == 'letrehozas')) {
            $record = new $modelName;
        }

        if (!$record) {
            //TODO handle trashed records
            return null;
        }

        return $record;
    }

    public function getPivotFieldsConfig($model, $pivotFieldsConfig = 'fieldsPivot.yaml') {
        if ($pivotFieldsConfig[0] != '$') {
            $pivotFieldsConfig = '$/' . str_replace('\\', '/', strtolower($model)) . '/' . $pivotFieldsConfig;
        }
        $pivotFieldsConfig = File::symbolizePath($pivotFieldsConfig);
        if(!File::isFile($pivotFieldsConfig)){
            return false;
        }

        return $pivotFieldsConfig;
    }

    public function getPivotListConfig($model, $pivotListConfig = 'columnsPivot.yaml') {
        if ($pivotListConfig[0] != '$') {
            $pivotListConfig = '$/' . str_replace('\\', '/', strtolower($model)) . '/' . $pivotListConfig;
        }
        $pivotListConfig = File::symbolizePath($pivotListConfig);
        if(!File::isFile($pivotListConfig)){
            return false;
        }
        return $pivotListConfig;
    }

    public function renderBelongsToManyRalationsWithPivotData($record){
        $html = '<div class="row" id="pivotSection">';
        foreach($record->belongsToMany as $relationName => $definition) {
            if(!empty($definition['pivot']) && $this->getPivotListConfig($definition[0])){
                $html .= $this->generatePivotSection($record, $relationName, $definition);
            }
        }
        $html .= '</div>';

        return $html;
    }

    public function attributesToDisplay($pivotConfig){
        $attributesToDisplay = [];
        foreach ($pivotConfig->columns as $columnName => $data){
            if(strpos($columnName, 'pivot') !== false){
                $pivotColumn = str_replace(']', '', str_replace('pivot[', '', $columnName));
                $data['isPivot'] = true;
                $attributesToDisplay[$pivotColumn] = $data;
            } else {
                $attributesToDisplay[$columnName] = $data;
            }
        }
        return $attributesToDisplay;
    }

    public function generatePivotSection($record, $relationName, $definition){
        $relatedModelName = $definition[0];
        $pivotConfig = $this->makeConfig($this->getPivotListConfig($relatedModelName));
        $attributesToDisplay = $this->attributesToDisplay($pivotConfig);
        $relationLabel = array_key_exists('label', $definition) ? \Lang::get($definition['label']) : $relationName;
        $html = '<div class="col-12 mb-4">';
        $html .= '<div class="field-section toolbar-item toolbar-primary mb-2"><h4 style="display:inline;">' . $relationLabel . '</h4>';

        if(!$this->readOnly) {
            $html .= '<div class="add-remove-button-container"><button class="btn btn-xs rounded btn-primary me-2"
                data-request="onListAttachOptions"
                data-request-data="relationName: \'' . $relationName . '\'"><i class="bi bi-plus-square"></i></button>';
            $html .= '<button class="btn btn-xs rounded btn-danger"
                data-request="onDeletePivotRelation" data-request-data="relationName: \'' . $relationName . '\'"><i class="bi bi-trash"></i></button></div></div>';
            $html .= '<div id="add-edit-' . $relationName . '"></div>';
        } else {
            $html .= '</div>';
        }

        if(count($record->$relationName)>0 ||
            (count($record->{$relationName}()->withDeferred($this->sessionKey)->get())>0 && !$record->id)){
            $html .= '<table style="width: 100%">';
            $html .= $this->generatePivotTableHeader($attributesToDisplay);
            $html .= $this->generatePivotTableRows($record, $relationName, $attributesToDisplay);
            $html .= '</table>';
        }

        $html .= '</div>';

        return $html;
    }

    public function onDeletePivotRelation(){
        $record = $this->getRecord();
        $relationName = Input::get('relationName');
        $data = Input::get('data');
        $recordsToDelete = array_key_exists($relationName, $data) ? $data[$relationName] : [];
        $defRecords = DeferredBinding::where('master_field', $relationName)
            ->where('session_key', $this->sessionKey)
            ->whereIn('slave_id', $recordsToDelete)
            ->delete();
        $record->{$relationName}()->detach($recordsToDelete);

        return [
            '#pivotSection' =>
                $this->renderBelongsToManyRalationsWithPivotData($record)
        ];
    }

    public function onRefresh(){
        return [
            '#renderedFormArea' => $this->createForm(),
        ];
    }

    public function generatePivotTableHeader($attributesToDisplay){
        $tableHeaderRow = '<tr>';
        if(!$this->readOnly){
            $tableHeaderRow .= '<th></th>';
        }
        foreach ($attributesToDisplay as $data){
            // generate table header
            $label = $data['label'];
            $tableHeaderRow .= '<th>' . \Lang::get($label) . '</th>';
        }
        $tableHeaderRow .= '</tr>';

        return $tableHeaderRow;
    }

    public function generatePivotTableRows($record, $relationName, $attributesToDisplay){
        $tableRows = '';
        $records = $record->{$relationName};
        $defRecords = null;
        if(!$record->id){
            $defRecords = DeferredBinding::where('master_field', $relationName)
                ->where('session_key', $this->sessionKey)
                ->get();
            $records = $record->{$relationName}()->withDeferred($this->sessionKey)->get();
        }

        foreach ($records as $key => $relatedRecord){
            if($defRecords){
                $relatedRecord->pivot = (object)$defRecords[$key]->pivot_data;
            }
            $tableRows .= '<tr>';
            if(!$this->readOnly) {
                $tableRows .= '<td><input type="checkbox" name="data[' . $relationName . '][]" value="' . $relatedRecord->id . '"></td>';
            }
            foreach ($attributesToDisplay as $key => $data){
                $tableRows .= '<td>' . ( array_key_exists('isPivot', $data) ? $relatedRecord->pivot->{$key} : $relatedRecord->{$key})  . '</td>';
            }
            $tableRows .= '</tr>';
        }

        return $tableRows;
    }

    public function underscoreToCamelCase($string, $capitalizeFirstCharacter = false){

        $str = str_replace('_', '', ucwords($string, '_'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

    return $str;
    }

    public function getPivotModelIfSet($relationName) {
        $record = $this->getRecord();
        $relationConfigArray = $record->belongsToMany[$relationName];

        if(array_key_exists('pivotModel', $relationConfigArray)) {
            return new $relationConfigArray['pivotModel']($record, [], '');
        }

        return false;
    }
}
