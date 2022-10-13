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
use October\Rain\Database\Collection;

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

        $record->fill($data = Input::get('data') ?? []);

        $config = $this->makeConfig($form->getFieldsConfig());
        //update field list and config based on currentUserRights
        $config->fields = $this->applyUserRightsToForm($config->fields, $preview, empty($record->id));
        $config->arrayName = 'data';
        $config->alias = $this->alias;
        $config->model = $record;

        $this->autoloadBelongsToRelations($record);
        $this->autoloadhasManyRelations($record);

        $this->widget = new \Backend\Widgets\Form($this, $config);

        $this->loadBackendFormWidgets();

        if (isset($config->formBuilder_card_design) && $config->formBuilder_card_design && $preview) {
            $html = $this->renderViewMode($this->widget);
        }
        else {
            $this->makePreselectedFieldsReadOnly();
            $html = $this->widget->render(['preview' => $preview]);
            $html .= $this->renderValidationTags($record);
        }
        $html .= $this->renderBelongsToManyWithPivotDataAndHasManyRelations($record, !$preview);

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

    public function renderViewMode($widget)
    {
        $mainCardVariablesToPass = [];
        $sheetCardVariablesToPass = [];
        $fieldsToPass = [];

        // gather all cards and fields in arrays
        foreach ($widget->fields as $key => $field) {
            // if no formBuilder data is set; or if any of the mandatory formBuilder attributes are not set, then continue
            if (!array_key_exists('formBuilder', $field) || !array_key_exists('type', $field['formBuilder'])) {
                continue;
            }

            // gather all cards and fields in arrays
            if ($field['formBuilder']['type'] == 'card') {
                if ($field['formBuilder']['position'] == 'main') {
                    $mainCardVariablesToPass['name'] = $key;
                    $mainCardVariablesToPass['class'] = $field['formBuilder']['class'];
                }
                else if ($field['formBuilder']['position'] == 'sheets') {
                    $sheetCardVariablesToPass[$key] = [];
                    $sheetCardVariablesToPass[$key]['name'] = array_key_exists('label', $field) ? Lang::get($field['label']) : null;
                    if (array_key_exists('class', $field['formBuilder'])) {
                        $sheetCardVariablesToPass[$key]['class'] = $field['formBuilder']['class'];
                    }
                    if (array_key_exists('color', $field['formBuilder'])) {
                        $sheetCardVariablesToPass[$key]['color'] = $field['formBuilder']['color'];
                    }
                    if (array_key_exists('order', $field['formBuilder'])) {
                        $sheetCardVariablesToPass[$key]['order'] = $field['formBuilder']['order'];
                    }
                }
            }
            else if ($field['formBuilder']['type'] == 'field') {
                // if mandatory config is not set, then don't show the field
                if (!isset($field['formBuilder']['card'])) {
                    continue;
                }

                // if no value is set and no default is set, then don't show the field
                if ((!isset($widget->model->{$key}) || empty(($widget->model->{$key}))) && !isset($field['formBuilder']['default'])) {
                    continue;
                }

                // if an array for the card does not exist yet, then create it
                if (!array_key_exists($field['formBuilder']['card'], $fieldsToPass)) {
                    $fieldsToPass[$field['formBuilder']['card']] = [];
                }

                $newField = [];
                if (isset($field['label'])) {
                    $newField['label'] = Lang::get($field['label']);
                }

                // retrieve the value for the field
                $value = isset($field['formBuilder']['default']) ? $field['formBuilder']['default'] : '';
                if (isset($widget->model->{$key})) {
                    if (is_object($widget->model->{$key}) && array_key_exists('nameFrom', $field) && isset($widget->model->{$key}->{$field['nameFrom']})) { // relation fields
                        $value = $widget->model->{$key}->{$field['nameFrom']};
                    }
                    else if (is_a($widget->model->{$key}, 'Illuminate\Database\Eloquent\Collection') && count($widget->model->{$key}) > 0 && array_key_exists('nameFrom', $field)) { // belongs to many with no pivot data
                        $value = '';
                        foreach ($widget->model->{$key} as $item) {
                            if (isset($item->{$field['nameFrom']})) {
                                $value .= $item->{$field['nameFrom']} . ', ';
                            }
                        }
                    }
                    else if ($field['type'] == 'dropdown' && array_key_exists('options', $field) && is_array($field['options']) && count($field['options']) > 0) { // dropdown fields
                        $value = Lang::get($field['options'][$widget->model->{$key}]);
                    }
                    else if ($field['type'] == 'checkbox') { // bool fields
                        $value = $widget->model->{$key} == 1 ? Lang::get('csatar.csatar::lang.plugin.admin.general.yes') : Lang::get('csatar.csatar::lang.plugin.admin.general.no');
                    }
                    else if ($field['type'] == 'fileupload' && $field['mode'] == 'image') { // images
                        $value = $widget->model->{$key}->getPath();
                        $mainCardVariablesToPass['customImage'] = true;
                    }
                    else if (isset($widget->model->attributes[$key]) && !empty($widget->model->attributes[$key])) { // regular fields
                        $value = $widget->model->attributes[$key];
                    }
                }
                $newField['value'] = $value;

                if (array_key_exists('position', $field['formBuilder'])) {
                    $newField['position'] = $field['formBuilder']['position'];
                }
                if (array_key_exists('order', $field['formBuilder'])) {
                    $newField['order'] = $field['formBuilder']['order'];
                }
                array_push($fieldsToPass[$field['formBuilder']['card']], $newField);
            }
        }

        // sort the sheets
        $this->sortArrayByOrder($sheetCardVariablesToPass);

        // set the appropriate field array for each of the cards
        foreach ($fieldsToPass as $key => $fields) {
            if ($key == $mainCardVariablesToPass['name']) {
                $titleFields = [];
                $mainCardVariablesToPass['subtitleFields'] = [];
                $mainCardVariablesToPass['fields'] = [];

                // gather the fields by position
                foreach ($fields as $field) {
                    if ($field['position'] == 'image') {
                        $mainCardVariablesToPass['image'] = $field['value'];
                    }
                    else if ($field['position'] == 'title') {
                        array_push($titleFields, $field);
                    }
                    else if ($field['position'] == 'subtitle') {
                        array_push($mainCardVariablesToPass['subtitleFields'], $field);
                    }
                    else if ($field['position'] == 'details') {
                        array_push($mainCardVariablesToPass['fields'], $field);
                    }
                }

                // sort the title fields and create the title
                $this->sortArrayByOrder($titleFields);
                $mainCardVariablesToPass['title'] = '';
                foreach ($titleFields as $field) {
                    $mainCardVariablesToPass['title'] .= $field['value'] . ' ';
                }

                // sort the subtitle fields
                $this->sortArrayByOrder($mainCardVariablesToPass['subtitleFields']);

                // sort the fields array
                $this->sortArrayByOrder($mainCardVariablesToPass['fields']);
            }
            else if (isset($sheetCardVariablesToPass[$key])) {
                $this->sortArrayByOrder($fields);
                $sheetCardVariablesToPass[$key]['fields'] = $fields;
            }
        }

        // render the main card
        $html = '<div class="row">';
        $html .= $this->renderPartial('@partials/mainCard', $mainCardVariablesToPass);

        // render the sheets
        $html .= '<div class="col"><div class="row">';
        foreach ($sheetCardVariablesToPass as $sheet) {
            $html .= $this->renderPartial('@partials/sheetCard', $sheet);
        }

        $html .= '</div></div></div>';
        return $html;
    }

    private function sortArrayByOrder(&$array)
    {
        foreach ($array as $i => $field) {
            foreach ($array as $j => $field) {
                if (!array_key_exists('order', $array[$i]) || !array_key_exists('order', $array[$j])) {
                    continue;
                }
                if ($array[$i]['order'] < $array[$j]['order']) {
                    $v = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $v;
                }
            }
        }
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
                $key = str_replace(']', '', str_replace('pivot[', '', $key));
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
                $record->bindDeferred($relationName, $model, $this->sessionKey, $pivotData);
            }
            else {
                $model = $model::create(isset($model->attributes) ? array_merge($model->attributes, $pivotData) : $pivotData);
            }
        }

        return [
            '#pivotSection' =>
                $this->renderBelongsToManyWithPivotDataAndHasManyRelations($record),
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
        $record = $this->record;

        if (!$data = Input::get('data')) {
            $error = e(trans('csatar.forms::lang.errors.noDataArray'));
            throw new ApplicationException($error);
        }

        //until this point record was displayed based on rights cached in session
        $this->currentUserRights = $this->getRights($record, true); // now we get rights from database and ignore session

        // validate the form
        $form = Form::find($this->formId ?? Input::get('formId'));
        $config = $this->makeConfig($form->getFieldsConfig());

        $attributeNames = [];

        foreach ($config->fields as $key => $value) {
            if ($value['type'] !== 'section') {
                $attributeNames[$key] = Lang::get($value['label']);
            }
        }

        $rules = $this->addRequiredRuleBasedOnUserRights($record->rules, $this->currentUserRights, $isNew);

        $validation = Validator::make(
            $data,
            $rules,
            [],
            $attributeNames,
        );

        //validate for conditional rules
        if (isset($record->conditionalRules)) {
            foreach ($record->conditionalRules as $conditionalRule) {
                $validation->sometimes($conditionalRule['fields'], $conditionalRule['rules'], function ($input) use ($record, $conditionalRule) {
                    return $record->{$conditionalRule['validationFunctionName']}($input);
                });
            }
        }

        if ($validation->fails()) {
            throw new \ValidationException($validation);
        }

        $data = $this->filterDataBasedOnUserRightsBeforeSave($data, $isNew);

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
            if (!isset($data[$relationName]) || $data[$relationName] =='' || !isset($data[$relationName]['pivot'])) {
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
                    if (array_key_exists($fillable, $defRecord['pivot_data']) && !isset($model->{$fillable})) {
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
            if (!isset($data[$name]) || $data[$name] =='' || !isset($data[$name]['pivot'])) {
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

        if(!empty($this->messages) && array_key_exists('warning', $this->messages)) {
            $warnings = implode('\n', $this->messages['warning']);
            Flash::warning($warnings);
        } else {
            Flash::success(e(trans('csatar.forms::lang.success.saved')));
        }
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
            $rules = $this->addRequiredRuleBasedOnUserRights($model->rules, $this->currentUserRights);
            foreach($rules as $fieldName => $rule) {
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

    public function makePreselectedFieldsReadOnly()
    {
        foreach ($this->widget->fields as $key => &$field) {
            if (isset($field['formBuilder']) && isset($field['formBuilder']['readOnlyIfPreselected']) && $field['formBuilder']['readOnlyIfPreselected'] && isset($this->widget->model->{$key}) && !empty($this->widget->model->{$key})) {
                $field['readOnly'] = 1;
            }
        }
    }

    private function getRecord() {
        $form       = Form::find($this->formId ?? Input::get('formId'));
        $modelName  = $form->getModelName();
        $key        = $this->recordKeyParam ?? Input::get('recordKeyParam');
        $value      = $this->recordKeyValue ?? Input::get('recordKeyValue');

        $record = null;
        if (!empty($key) && !empty($value)) {
            $record = $modelName::where($key, $value)->first();
        }
        if (!$record && $value == $this->createRecordKeyword) {
            $record = new $modelName();
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

    public function renderBelongsToManyWithPivotDataAndHasManyRelations($record, $showEmpty = true){
        $html = '<div class="row" id="pivotSection">';

        // render belongsToMany relations
        foreach($record->belongsToMany as $relationName => $definition) {
            if ($this->canRead($relationName) && !empty($definition['pivot']) && (count($record->{$relationName}) > 0 || $showEmpty)) {
                $pivotConfig = $this->getConfig($definition[0], 'columnsPivot.yaml');
                if ($pivotConfig) {
                    $attributesToDisplay = $this->attributesToDisplay($pivotConfig);
                    $html .= $this->generatePivotSection($record, $relationName, $definition, $attributesToDisplay);
                }
            }
        }

        // render hasMany relations
        foreach($record->hasMany as $relationName => $definition) {
            if ($this->canRead($relationName)
                && is_array($definition)
                && (count($record->{$relationName}) > 0 || $showEmpty)
                && ((!$record->id
                    && array_key_exists('renderableOnCreateForm', $definition)
                    && $definition['renderableOnCreateForm'])
                    || ($record->id
                        && array_key_exists('renderableOnUpdateForm', $definition)
                        && $definition['renderableOnUpdateForm']))) {
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
        $relationLabel = array_key_exists('label', $definition) ? Lang::get($definition['label']) : $relationName;
        $html = '<div class="col-12 mb-4">';
        $html .= '<div class="field-section toolbar-item toolbar-primary mb-2"><h4 style="display:inline;">' . $relationLabel . '</h4>';

        if (!$this->readOnly && $this->canUpdate($relationName)) {
            $html .= '<div class="add-remove-button-container"><button class="btn btn-xs rounded btn-primary me-2"
                data-request="onListAttachOptions"
                data-request-data="relationName: \'' . $relationName . '\'"><i class="bi bi-plus-square"></i></button>';
            $html .= '<button class="btn btn-xs rounded btn-danger" data-request-flash
                data-request="onDeletePivotRelation" data-request-data="relationName: \'' . $relationName . '\'"><i class="bi bi-trash"></i></button></div></div>';
            $html .= '<div id="add-edit-' . $relationName . '"></div>';
        } else {
            $html .= '</div>';
        }

        if (count($record->$relationName)>0 ||
            (!$record->id && count($record->{$relationName}()->withDeferred($this->sessionKey)->get())>0)) {
            $html .= '<div class="table csat-grid">';
            $html .= $this->generatePivotTableHeader($attributesToDisplay);
            $html .= $this->generatePivotTableRows($record, $relationName, $attributesToDisplay);
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    public function onDeletePivotRelation(){
        $record = $this->getRecord();
        $relationName = Input::get('relationName');
        if(!$this->canDelete($relationName)){
            Flash::warning(e(trans('csatar.forms::lang.failed.noPermissionToDeleteRecord')));
            return;
        }
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
                $this->renderBelongsToManyWithPivotDataAndHasManyRelations($record)
        ];
    }

    public function onRefresh(){
        return [
            '#renderedFormArea' => $this->createForm(),
        ];
    }

    public function generatePivotTableHeader($attributesToDisplay){
        $tableHeaderRow = '<div class="tr d-none d-lg-block">';
        $tableHeaderRow .= '<div class="card csat-resp-gdtable csat-border-lg-none">';
        $tableHeaderRow .= '<div class="row">';
        if(!$this->readOnly){ //this is the checkbox column header
            $tableHeaderRow .=
                '
                <div class="col-6 col-lg-1">
                    <div class="th-grid"></div>
                </div>
                ';
        }
        foreach ($attributesToDisplay as $data){
            // generate table header
            $label = Lang::get($data['label']);
            $tableHeaderRow .=
                '
                <div class="col-6 col-lg">
                    <div class="th-grid">' . $label . '</div>
                </div>
                ';
        }
        $tableHeaderRow .= '</div></div></div>';

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
            $tableRows .= '<div class="tr">';
            $tableRows .= '<div class="card csat-resp-gdtable csat-border-lg-none">';
            $tableRows .= '<div class="row">';
            if (!$this->readOnly) {
                $tableRows .= '<div class="col-6 col-lg-1">';
                $tableRows .= '<p class="td label d-block d-lg-none">' . Lang::get('csatar.forms::lang.components.basicForm.select') . '</p> ';
                $tableRows .= '<p class="td">';
                $tableRows .= '<input type="checkbox" name="data[' . $relationName . '][]" value="' . $relatedRecord->id . '">';
                $tableRows .= '</p></div>';
            }

            foreach ($attributesToDisplay as $key => $data) {
                $label = Lang::get($data['label']);
                $tableRows .= '<div class="col-6 col-lg">';
                $tableRows .= '<p class="td label d-block d-lg-none">' . $label . '</p> ';
                $tableRows .= '<p class="td">';
                if (array_key_exists('isPivot', $data)) {
                    $value = $relatedRecord->pivot->{$key} ?? '';
                } else {
                    $value = (is_object($relatedRecord->{$key}) ?
                        $relatedRecord->{$key}->name :
                        $relatedRecord->{$key});
                }
                $tableRows .= $value;
                $tableRows .= '</p></div>';
            }
            $tableRows .= '</div></div></div>';
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

    /**
     * @param $record
     * @return void
     * Presets new record with required values that are not selectable from the from
     * and should be set before form rendering
     */
    public function autoLoadBelongsToRelations(&$record) {

        if (($this->recordKeyValue ?? Input::get('recordKeyValue')) != $this->createRecordKeyword) {
            return; // do not autoload values if not a new record
        }

        // Autoload belongsTo relations
        foreach ($record->belongsTo as $name => $definition) {

            if (empty($_POST[$name]) && empty($_POST['data'][$name])) {
                if (!empty($definition['formBuilder']['requiredBeforeRender']) && $definition['formBuilder']['requiredBeforeRender']) {
                    \App::abort(403, 'Access denied');
                };
                continue;
            }

            $key = isset($definition['key']) ? $definition['key'] : $name . '_id';
            $record->$key = (Input::get($name) ?? Input::get('data.' . $name));
        }

    }

    /**
     * @param $record
     * @return void
     * Presets new record with required values that are not selectable from the from
     * and should be set before form rendering
     */
    public function autoLoadHasManyRelations(&$record) {

        if (($this->recordKeyValue ?? Input::get('recordKeyValue')) != $this->createRecordKeyword) {
            return; // do not autoload values if not a new record
        }

        // Autoload hasMany relations
        foreach($record->hasMany as $name => $definition) {

            if (!Input::get($name) && !Input::get('data.' . $name)) {
                continue;
            }

            $key = isset($definition['key']) ? $definition['key'] : $name . '_id';
            $record->$key = Input::get($name) ?? Input::get('data.' . $name);
        }
    }

    /**
     * Applies rights to form fields config. Run before form render
     */
    private function applyUserRightsToForm(array $attributesArray, bool $isReadOnly, bool $isNewRecord = false): array
    {
        // NOTE:
        // In readOnly mode we do not care about create/update/delete rights.
        // In create mode, we do not care about read/update/delete rights.
        // In update mode, we do not care about read/create rights.
        // This function is called in every mode.

        // NOTE: in create mode, we do not care about update/delete

        foreach ($attributesArray as $attribute => $settings) {

            if ($settings['type'] == 'section') {
                continue;
            }

            if ($settings['type'] == 'relation' && $this->hasPivotColumns($attribute)) {
                continue;
            }

            if ($isReadOnly) {
                if (!$this->canRead($attribute)) {
                    unset($attributesArray[$attribute]);
                    continue;
                }
            }

            if ($this->isObligatory($attribute)) {
                $settings['required'] = true;
            }

            if ($isNewRecord) {
                if (!$this->canCreate($attribute)) {
                    unset($attributesArray[$attribute]);
                    continue;
                }
            }

            if (!$isNewRecord) {
                if (!$this->canUpdate($attribute) && !$this->canDelete($attribute)) {
                    $settings['readOnly'] = true;
                }
            }

            $attributesArray[$attribute] = $settings;
        }

        return $attributesArray;
    }

    /**
     * Checks if relation has pivot data
     */

    private function hasPivotColumns(string $relationName): bool
    {
        $relationTypesToCheck = ['belongsToMany', 'hasMany'];
        foreach ($relationTypesToCheck as $relationType) {
            if (isset($this->record->{$relationType}[$relationName]['pivot'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Filters posted data array, run before $record->save();
     */
    private function filterDataBasedOnUserRightsBeforeSave(array $data, bool $isNewRecord = false): array
    {
        // This function is needed because at the time of rendering the form user rights are loaded from session,
        // but before save we confirm the rights from database, and if there were changes, not-allowed data should not be saved.

        // NOTE:
        // In readOnly mode we do not care about create/update/delete rights.
        // In create mode, we do not care about read/update/delete rights.
        // In update mode, we do not care about read/create rights.
        // This function is called only in create/update mode.

        if ($isNewRecord) { //if it's a new record, we care only about create right
            foreach ($data as $attribute => $value) {
                if (!$this->canCreate($attribute)) {
                    unset($data[$attribute]);
                }
            }
        }

        if (!$isNewRecord) { //if updating an existing record we don't care about create right
            foreach ($data as $attribute => $value) {
                //if user can delete attribute, but he is not allowed to update it, accept only empty value for the attribute
                if ($this->canDelete($attribute) && !$this->canUpdate($attribute)) {
                    if (!empty($value) && $value != $this->record->{$attribute}){
                        $this->storeMessage('warning', e(trans('csatar.forms::lang.failed.noPermissionForSomeFields')));
                        unset($data[$attribute]);
                    }
                    //if user can delete attribute, but he is not allowed to update it and value is empty for the attribute, continue
                    continue;
                }

                if (!$this->canDelete($attribute) && empty($value) && $value != $this->record->{$attribute}) {
                    $this->storeMessage('warning', e(trans('csatar.forms::lang.failed.noPermissionForSomeFields')));
                    unset($data[$attribute]);
                }

                //if user can't update the attribute, and the above conditions doesn't apply, unset attribute before save
                if (!$this->canUpdate($attribute)) {
                    $this->storeMessage('warning', e(trans('csatar.forms::lang.failed.noPermissionForSomeFields')));
                    unset($data[$attribute]);
                }
            }
        }

        return $data;
    }

    /**
     * Applies rights on validation rules, run before rendering validation tags and before form validate.
     */
    private function addRequiredRuleBasedOnUserRights(array $rules, $rights): array
    {
        // NOTE:
        // In readOnly mode we do not care about create/update/delete rights.
        // In create mode, we do not care about read/update/delete rights.
        // In update mode, we do not care about read/create rights.
        // This function is called only in create/update mode.

        foreach ($rights as $attribute => $right) {
            if ($this->isObligatory($attribute)) {
                //add required rule if is obligatory for user to fill the attribute
                //BUT this should not remove required rule IF, it's required by model settings
                if (!array_key_exists($attribute, $rules)) {
                    // if there are no rules for the attribute
                    $rules[$attribute] = 'required';
                } elseif (!is_array($rules[$attribute]) && !strpos($rules[$attribute], 'required')) {
                    // if there are rules for the attribute in string format, but it's not required
                    $rules[$attribute] = strlen($rules[$attribute]) == 0 ? 'required' : $rules[$attribute] . '|required';
                } elseif (is_array($rules[$attribute]) && !in_array('required', $rules[$attribute])) {
                    // if there are rules for the attribute in array format, but it's not required
                    array_push($rules[$attribute], 'required');
                }
            }
        }

        return $rules;
    }

    public function storeMessage(string $messageType, string $message): void
    {
        $this->messages[$messageType][$message] = $message;
    }

//    private function removeRulesBasedOnUserRights(array $rules, $rights, bool $isNewRecord): array
//    {
//        // Should we remove rules for attributes that user can't create/update?
//        // If we do, will there be another validation on model level?
//        //        $rules = array_intersect_key($rules, $rights->toArray());
//
//        foreach ($rules as $attribute => $value) {
//            if(!$this->canSaveAttribute($attribute) ) {
//                unset($rules[$attribute]);
//            }
//        }
//
//        return $rules;
//    }
//
//    private function validateDataBasedOnUserRights(array $rules, $rights, array $data, bool $isNewRecord){
//        if ($isNewRecord) {
//            //we do not care about read/update/delete rights
//            //what if can NOT create but is obligatory --> there will be validation error
//            //what if can NOT create but attribute is required by default --> there will be validation error
//
//            // trow exception to contact admin and review rights?
//        }
//
//        if (!$isNewRecord) {
//            //we do not care about read/create rights.
//            //what if can NOT update but is obligatory and empty?
//            // --> field should be read only, and value should not be empty BUT what if it is
//            //what if can NOT update but attribute is required by default
//            // --> field should be read only AND there should be no record with empty value
//            // There will be validation error in both cases
//        }
//    }
}
