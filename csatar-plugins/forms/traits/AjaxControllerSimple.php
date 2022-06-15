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

        $this->model = $record;

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

        $html .= $this->renderBelongsToManyRalationsWithPivotData($record);
        $variablesToPass = [
            'form' => $html,
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

        // Resolve belongsToMany relations

        foreach($record->belongsToMany as $name => $definition) {
            if (! isset($data[$name])) {
                continue;
            }
            $record->$name()->sync($data[$name]);
//            dd($name, $definition, $data[$name]);
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

        if(!$record) {
            //TODO handle trashed records
            return null;
        }

        return $record;
    }

    public function getPivotFieldsConfig($model, $pivotFieldsConfig = 'fieldsPivot.yaml') {
        if ($pivotFieldsConfig[0] != '$') {
            return '$/' . str_replace('\\', '/', strtolower($model)) . '/' . $pivotFieldsConfig;
        }

        return $pivotFieldsConfig;
    }

    public function getPivotListConfig($model, $pivotListConfig = 'columnsPivot.yaml') {
        if ($pivotListConfig[0] != '$') {
            return '$/' . str_replace('\\', '/', strtolower($model)) . '/' . $pivotListConfig;
        }

        return $pivotListConfig;
    }

    public function getPivotFormsConfig($record){
        $pivotFormConfigs = [];
        foreach($record->belongsToMany as $name => $definition) {
            if(!empty($definition['pivot'])){
                $pivotModelName = $definition[0];
                $pivotConfig = $this->makeConfig($this->getPivotFieldsConfig($pivotModelName));
                $pivotConfig->arrayName = $name . '-data';
                $pivotConfig->alias = $pivotModelName;
                $pivotConfig->model = new $pivotModelName();
                $pivotFormConfigs[$name] = $pivotConfig;
            }

        }

        return $pivotFormConfigs;
    }

    public function getPivotListsConfig($record){
        $pivotListsConfigs = [];
        foreach($record->belongsToMany as $name => $definition) {
            if(!empty($definition['pivot'])){
                $pivotModelName = $definition[0];
                $pivotConfig = $this->makeConfig($this->getPivotListConfig($pivotModelName));
                $pivotConfig->arrayName = $name . '-data';
                $pivotConfig->alias = $pivotModelName;
                $pivotListsConfigs[$name] = $pivotConfig;
            }
        }

        return $pivotListsConfigs;
    }

    public function renderBelongsToManyRalationsWithPivotData($record){
        $html = '<div class="row" id="pivotSection">';
        foreach($record->belongsToMany as $relationName => $definition) {
            if(!empty($definition['pivot']) ){
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
//                array_push($attributesToDisplay, ['pivot->' . $pivot, $data]);
                $data['isPivot'] = true;
                $attributesToDisplay[$pivotColumn] = $data;
            } else {
//                array_push($attributesToDisplay, [$columnName, $data]);
                $attributesToDisplay[$columnName] = $data;
            }
        }
        return $attributesToDisplay;
    }

    public function generatePivotSection($record, $relationName, $definition){
        $pivotModelName = $definition[0];
        $pivotConfig = $this->makeConfig($this->getPivotListConfig($pivotModelName));
        $attributesToDisplay = $this->attributesToDisplay($pivotConfig);
        $relatoinLabel = array_key_exists('label', $definition) ? \Lang::get($definition['label']) : $relationName;
        $html = '<div class="col-12 mb-4">';
        $html .= '<div class="field-section"><h4>' . $relatoinLabel . '</h4></div>';
        $html .= '<button class="btn btn-default btn-danger oc-icon-times" data-request="onDeletePivotRelation" data-request-data="relationName: \'' . $relationName . '\'">Törlés</button>';

        if(count($record->$relationName)>0){
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
        $record->{$relationName}()->detach($recordsToDelete);

        return [
            '#pivotSection' =>
                $this->renderBelongsToManyRalationsWithPivotData($record)
        ];
    }

    public function onRefresh(){

    }

    public function generatePivotTableHeader($attributesToDisplay){
        $tableHeaderRow = '<tr><th></th>';
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
        foreach ($record->{$relationName} as $relatedRecord){
            $tableRows .= '<tr>';
            $tableRows .= '<td><input type="checkbox" name="data[' . $relationName . '][]" value="' . $relatedRecord->id .'"></td>';
            foreach ($attributesToDisplay as $key => $data){
                $tableRows .= '<td>' . ( array_key_exists('isPivot', $data) ? $relatedRecord->pivot->{$key} : $relatedRecord->{$key})  . '</td>';
            }
            $tableRows .= '</tr>';
        }

        return $tableRows;
    }
}
