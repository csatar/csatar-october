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
        }

        // Autoload hasMany relations
        foreach($record->hasMany as $name => $definition) {
            $inp = Input::get('data.' . $name);
            if (!Input::get($name) && !Input::get('data.' . $name)) {
                continue;
            }

            $key = isset($definition['key']) ? $definition['key'] : $name . '_id';
            $record->$key = Input::get($name) ?? Input::get('data.' . $name);
        }

        $this->widget = new \Backend\Widgets\Form($this, $config);

        $this->loadBackendFormWidgets();

        $html = $this->widget->render(['preview' => $preview]);
        if (!$preview){
            $html .= $this->renderValidationTags($record);
        }

        $html .= $this->renderBelongsToManyWithPivotDataAndHasManyRalations($record);

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
        if(empty($relationId)){
            $error = e(trans('csatar.forms::lang.validation.selectOptionBeforeNext'));
            throw new \ValidationException([ $relationName => $error]);
        }
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
        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);
        $relatedModelName = array_key_exists($relationName, $record->belongsToMany) ?
            $record->belongsToMany[$relationName][0] :
            ($isHasManyRelation ?
                (isset($record->hasMany[$relationName][0]::$relatedModelNameForFormBuilder) ?
                    $record->hasMany[$relationName][0]::$relatedModelNameForFormBuilder :
                    $record->hasMany[$relationName][0]) :
                false);

        $allowDuplicates = $isHasManyRelation &&
            isset($record->hasMany[$relationName][0]::$relatedModelAllowDuplicates) &&
            $record->hasMany[$relationName][0]::$relatedModelAllowDuplicates;
        
        $getFunctionName = 'get' . $this->underscoreToCamelCase($relationName, true) . 'Options';
        $options = method_exists($record, $getFunctionName) ? $record->{$getFunctionName}() : null;

        // scope the returned values if a scope is specified in the fields.html
        $scope = null;
        if ($isHasManyRelation) {
            $tmpModelName = is_array($record->hasMany[$relationName]) ? $record->hasMany[$relationName][0] : $record->hasMany[$relationName];
            if (isset($tmpModelName::$relatedFieldForFormBuilder)) {
                $pivotConfig = $this->getConfig($tmpModelName, 'fields.yaml');
                $scope = $pivotConfig->fields[$tmpModelName::$relatedFieldForFormBuilder]['scope'];
            }
        }

        \Model::extend(function($model) use ($getFunctionName, $relatedModelName, $attachedIds, $allowDuplicates, $options, $scope) {
            $model->addDynamicMethod($getFunctionName, function() use ($model, $relatedModelName, $attachedIds, $allowDuplicates, $options, $scope) {
                if (!empty($options)) {
                    return $options;
                }

                return $allowDuplicates ?
                    (isset($scope) ?
                        $relatedModelName::where('id', '>', 0)->{$scope}()->get()->lists('name', 'id') :
                        $relatedModelName::all()->lists('name', 'id')) :
                    (isset($scope) ?
                        $relatedModelName::whereNotIn('id', $attachedIds)->{$scope}()->get()->lists('name', 'id') :
                        $relatedModelName::whereNotIn('id', $attachedIds)->get()->lists('name', 'id'));
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
        $html .= '<div class="mt-0 mb-2 errormsg" data-validate-for="' . $relationName . '" data-position-for="' . $relationName . '"></div>';

        return [
            '#add-edit-' . $relationName => $this->renderPartial('@partials/relationOptions', [ 'html' => $html, 'relationName' => $relationName ])
        ];
    }

    public function createPivotForm($relationName, $relationId) {
        $preview = $this->readOnly;
        $record = $this->getRecord();

        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);
        $relatedModelName = array_key_exists($relationName, $record->belongsToMany) ?
            $record->belongsToMany[$relationName][0] :
            ($isHasManyRelation ?
                $record->hasMany[$relationName][0] :
                false);

        if ($isHasManyRelation) {
            $relatedModel = new $relatedModelName();
            $pivotConfig = $this->getConfig($record->hasMany[$relationName][0], 'fields.yaml');
            $pivotConfig->model = $relatedModel;
            if (isset($relatedModel::$relatedModelNameForFormBuilder) && isset($relatedModel::$relatedFieldForFormBuilder)) {
                $rModel = ($relatedModel::$relatedModelNameForFormBuilder)::find($relationId);
                $relatedModel->{$relatedModel::$relatedFieldForFormBuilder} = $rModel;
                $pivotConfig->fields[$relatedModel::$relatedFieldForFormBuilder]['readOnly'] = 1;
            }
            if (method_exists($relatedModel, 'initFromForm')) {
                $relatedModel->initFromForm($record);
            }
        }
        else {
            $relatedModel = $relatedModelName::find($relationId);
            $pivotConfig = $this->getConfig($relatedModelName, 'fieldsPivot.yaml');
            $pivotConfig->model = $relatedModel;
        }
        $pivotConfig->arrayName = $relationName;
        $pivotConfig->alias = $relatedModelName;
        $widget = new \Backend\Widgets\Form($this, $pivotConfig);

        $this->loadBackendFormWidgets();

        $html = $widget->render(['preview' => $preview]);
        $pivotModel = $this->getPivotModelIfSet($relationName);
        if(!$preview && !empty($pivotModel->rules)){
            $html .= $this->renderValidationTags($pivotModel, array_key_exists($relationName, $record->belongsToMany), $relationName);
        }

        return [
            '#add-edit-' . $relationName => $this->renderPartial('@partials/relationForm', [
                'html' => $html,
                'relationName' => $relationName,
                'relationId' => $relationId,
            ])
        ];
    }

    public function onSavePivotRelation(){
        $record = $this->getRecord();
        $relationName = Input::get('relationName');
        $relationId = Input::get('relationId');

        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);
        $pivotData = isset(Input::get($relationName)['pivot']) ? Input::get($relationName)['pivot'] : Input::get($relationName);
        $model = $this->getPivotModelIfSet($relationName);

        if ($model && method_exists($model, 'beforeValidateFromForm')) {
            $model->beforeValidateFromForm($pivotData);
        }
        if(!empty($model->rules)) {
            $rules = $model->rules;
            $pivotConfig = $isHasManyRelation ?
                $this->getConfig($record->hasMany[$relationName][0], 'fields.yaml') :
                $this->getConfig($record->belongsToMany[$relationName][0], 'fieldsPivot.yaml');
            $attributeNames = [];
            foreach ($pivotConfig->fields as $key => $value) {
                $attributeNames[$key] = Lang::get($value['label']);
            }

            $validation = Validator::make(
                $pivotData,
                $rules,
                [],
                $attributeNames,
            );
            if ($validation->fails()) {
                throw new \ValidationException($validation);
            }
        }
        if ($model && method_exists($model, 'beforeSaveFromForm')) {
            $model->beforeSaveFromForm($pivotData);
        }

        if (!$isHasManyRelation) {
            if (!$record->id){
                $modelToAttach = $record->$relationName()->getRelated()->find($relationId);
                $record->{$relationName}()->add($modelToAttach, $this->sessionKey, $pivotData);
            }
            else {
                $record->{$relationName}()->attach($relationId, $pivotData);
            }
        }
        else {
            $relatedModelName = $record->hasMany[$relationName][0];
            if (!$record->id && isset($relatedModelName::$relatedModelNameForFormBuilder) && isset($relatedModelName::$relatedFieldForFormBuilder)) {
                $form = Form::find($this->formId ?? Input::get('formId'));
                $modelName = $form->getModelName();
                $max_slave_id = DeferredBinding::where('master_type', substr($modelName, 1))->where('master_field', $relationName)->where('session_key', $this->sessionKey)->max('slave_id');
                $model->id = isset($max_slave_id) ? $max_slave_id + 1 : 1;
                $record->bindDeferred('mandates', $model, $this->sessionKey, $pivotData);
            }
            else {
                $model = $model::create(isset($model->attributes) ? array_merge($model->attributes, $pivotData) : $pivotData);
            }
        }

        return [
            '#pivotSection' =>
                $this->renderBelongsToManyWithPivotDataAndHasManyRalations($record),
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
            // remove hasMany relations
            $defRecords = [];
            foreach ($record->hasMany as $relationName => $definition) {

                $defRecords = array_merge($defRecords, DeferredBinding::where('master_field', $relationName)
                    ->where('session_key', $this->sessionKey)
                    ->get()->toArray());

                foreach ($defRecords as $defRecord) {
                    DeferredBinding::find($defRecord['id'])->delete();
                }
            }

            // save the record
            $record = $record->create($data, $this->sessionKey);

            // Resolve hasMany relations
            foreach ($defRecords as $defRecord) {
                $model = new $defRecord['slave_type'];
                if (method_exists($model, 'initFromForm')) {
                    $model->initFromForm($record);
                }
                foreach ($model->fillable as $fillable) {
                    if (array_key_exists($fillable, $defRecord['pivot_data'])) {
                        $model->{$fillable} = $defRecord['pivot_data'][$fillable];
                    }
                }
                if (isset($model::$relatedModelNameForFormBuilder) && isset($model::$relatedFieldForFormBuilder)) {
                    $tmp = $defRecord['pivot_data'][$model::$relatedFieldForFormBuilder];
                    $model->{$model::$relatedFieldForFormBuilder} = ($model::$relatedModelNameForFormBuilder)::find($tmp);
                }
                $model = $model->save();
            }

            // resolve other relations
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

    public function renderValidationTags($model, $forPivot = false, $relationName = false)
    {
        if (!empty($model->rules)) {
            $html = "<div class='validationTags'>";
            foreach($model->rules as $fieldName => $rule) {
                if (!$forPivot && !$relationName) {
                    $positionData = $fieldName;
                }
                else if (!$forPivot) {
                    $positionData = $relationName . '[' . $fieldName . ']';
                }
                else {
                    $positionData = $relationName . '[pivot][' . $fieldName . ']';
                }
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

    public function getConfig($model, $config) {
        if ($config[0] != '$') {
            $config = '$/' . str_replace('\\', '/', strtolower($model)) . '/' . $config;
        }
        $config = File::symbolizePath($config);
        if (!File::isFile($config)) {
            return false;
        }
        return $this->makeConfig($config);
    }

    public function renderBelongsToManyWithPivotDataAndHasManyRalations($record){
        $html = '<div class="row" id="pivotSection">';

        // render belongsToMany relations
        foreach($record->belongsToMany as $relationName => $definition) {
            if (!empty($definition['pivot'])) {
                $pivotConfig = $this->getConfig($definition[0], 'columnsPivot.yaml');
                if ($pivotConfig) {
                    $attributesToDisplay = $this->attributesToDisplay($pivotConfig);
                    $html .= $this->generatePivotSection($record, $relationName, $definition, $attributesToDisplay);
                }
            }
        }

        // render hasMany relations
        foreach($record->hasMany as $relationName => $definition) {
            if (is_array($definition) && $definition['renderableOnForm']) {
                $pivotConfig = $this->getConfig($definition[0], 'columns.yaml');
                $attributesToDisplay = $pivotConfig->columns;
                $html .= $this->generatePivotSection($record, $relationName, $definition, $attributesToDisplay);
            }
        }
        $html .= '</div>';

        return $html;
    }

    public function attributesToDisplay($pivotConfig) {
        $attributesToDisplay = [];
        foreach ($pivotConfig->columns as $columnName => $data) {
            if (strpos($columnName, 'pivot') !== false) {
                $pivotColumn = str_replace(']', '', str_replace('pivot[', '', $columnName));
                $data['isPivot'] = true;
                $attributesToDisplay[$pivotColumn] = $data;
            }
            else {
                $attributesToDisplay[$columnName] = $data;
            }
        }
        return $attributesToDisplay;
    }

    public function generatePivotSection($record, $relationName, $definition, $attributesToDisplay) {
        $relationLabel = array_key_exists('label', $definition) ? \Lang::get($definition['label']) : $relationName;
        $html = '<div class="col-12 mb-4">';
        $html .= '<div class="field-section toolbar-item toolbar-primary mb-2"><h4 style="display:inline;">' . $relationLabel . '</h4>';

        if (!$this->readOnly) {
            $html .= '<div class="add-remove-button-container"><button class="btn btn-xs rounded btn-primary me-2"
                data-request="onListAttachOptions"
                data-request-data="relationName: \'' . $relationName . '\'"><i class="bi bi-plus-square"></i></button>';
            $html .= '<button class="btn btn-xs rounded btn-danger"
                data-request="onDeletePivotRelation" data-request-data="relationName: \'' . $relationName . '\'"><i class="bi bi-trash"></i></button></div></div>';
            $html .= '<div id="add-edit-' . $relationName . '"></div>';
        } else {
            $html .= '</div>';
        }

        if (count($record->$relationName)>0 ||
            (!$record->id && count($record->{$relationName}()->withDeferred($this->sessionKey)->get())>0)) {
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
        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);
        $data = Input::get('data');
        $recordsToDelete = array_key_exists($relationName, $data) ? $data[$relationName] : [];

        $defRecords = DeferredBinding::where('master_field', $relationName)
            ->where('session_key', $this->sessionKey)
            ->whereIn('slave_id', $recordsToDelete)
            ->delete();
        if (!$isHasManyRelation) {
            $record->{$relationName}()->detach($recordsToDelete);
        }
        else {
            ($record->hasMany[$relationName][0])::whereIn('id', $recordsToDelete)->delete();
        }

        return [
            '#pivotSection' =>
                $this->renderBelongsToManyWithPivotDataAndHasManyRalations($record)
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

    public function generatePivotTableRows($record, $relationName, $attributesToDisplay) {
        $tableRows = '';
        $records = $record->{$relationName};
        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);
        $defRecords = null;
        if (!$record->id) {
            $defRecords = DeferredBinding::where('master_field', $relationName)
                ->where('session_key', $this->sessionKey)
                ->get();
            if (!$isHasManyRelation) {
                $records = $record->{$relationName}()->withDeferred($this->sessionKey)->get();
            }
            else {
                $records = [];
                foreach ($defRecords as $defRecord) {
                    $record = new $defRecord->slave_type();
                    array_push($records, $record);
                }
            }
        }

        foreach ($records as $key => $relatedRecord){
            if ($defRecords) {
                if (!$isHasManyRelation) {
                    $relatedRecord->pivot = (object)$defRecords[$key]->pivot_data;
                }
                else {
                    $relatedRecord->attributes = $defRecords[$key]->pivot_data;
                    $relatedRecord->id = $defRecords[$key]->slave_id;
                    if (isset($relatedRecord::$relatedModelNameForFormBuilder) && isset($relatedRecord::$relatedFieldForFormBuilder)) {
                        $tmp = $relatedRecord->{$relatedRecord::$relatedFieldForFormBuilder};
                        unset($relatedRecord->{$relatedRecord::$relatedFieldForFormBuilder});
                        $relatedRecord->{$relatedRecord::$relatedFieldForFormBuilder} = ($relatedRecord::$relatedModelNameForFormBuilder)::find($tmp);
                    }
                }
            }
            $tableRows .= '<tr>';
            if (!$this->readOnly) {
                $tableRows .= '<td><input type="checkbox" name="data[' . $relationName . '][]" value="' . $relatedRecord->id . '"></td>';
            }

            foreach ($attributesToDisplay as $key => $data) {
                $tableRows .= '<td>' . (array_key_exists('isPivot', $data) ?
                    $relatedRecord->pivot->{$key} :
                    (is_object($relatedRecord->{$key}) ?
                        $relatedRecord->{$key}->name :
                        $relatedRecord->{$key})) . '</td>';
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
        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);

        if (array_key_exists($relationName, $record->belongsToMany)) {
            $relationConfigArray = $record->belongsToMany[$relationName];
            if (array_key_exists('pivotModel', $relationConfigArray)) {
                return new $relationConfigArray['pivotModel']($record, [], '');
            }
        }
        else if ($isHasManyRelation) {
            $relationConfigArray = $record->hasMany[$relationName];
            $relatedModel = new $relationConfigArray[0]();
            if (method_exists($relatedModel, 'initFromForm')) {
                $relatedModel->initFromForm($record);
            }
            return $relatedModel;
        }

        return false;
    }
}
