<?php namespace Csatar\Forms\Traits;

use Auth;
use http\Env\Request;
use DateTime;
use Input;
use Flash;
use File;
use Lang;
use Validator;
use Session;
use Csatar\Csatar\Models\DynamicFields;
use Csatar\Forms\Models\Form;
use Response;
use Cookie;
use Redirect;
use Backend\Classes\WidgetManager;
use October\Rain\Exception\ApplicationException;
use October\Rain\Exception\NotFoundException;
use October\Rain\Database\Models\DeferredBinding;
use October\Rain\Database\Collection;
use Media\Widgets\MediaManager;

trait AjaxControllerSimple {

    use \System\Traits\ConfigMaker;
    use \Backend\Traits\VueMaker;

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
//            'Backend\FormWidgets\RichEditor' => [
            'Csatar\Forms\Widgets\RichEditor' => [
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
            'Media\Widgets\MediaManager' => [
                'label' => 'MediaManager',
                'code'  => 'mediamanager'
            ],
            'Csatar\Forms\Widgets\TagList' => [
                'label' => 'TagList',
                'code'  => 'taglist'
            ],
            // Custom file upload for frontend use
            'Csatar\Forms\Widgets\FrontendFileUpload' => [
                'label' => 'FileUpload',
                'code'  => 'fileupload'
            ]
        ];

        $this->registerGlobalInstance();

        foreach ($widgets as $className => $widgetInfo) {
            WidgetManager::instance()->registerFormWidget($className, $widgetInfo);
        }
    }

    protected function registerGlobalInstance()
    {
        \Backend\Classes\Controller::extend(function($controller) {
            $manager = new MediaManager($controller, 'ocmediamanager');
            $manager->bindToController();
        });
    }

    public function createForm($preview = false)
    {
        $form   = $this->form ?? Form::find($this->formId);
        $record = $this->getRecord();

        if (!$record) {
            throw new NotFoundException();
        }

        if ($record->is_hidden_fronend) {
            throw new NotFoundException();
        }

        $record->fill($data = Input::get('data') ?? []);

        $config = $this->makeConfig($form->getFieldsConfig());
        //update field list and config based on currentUserRights
        $config->fields        = $this->markFieldsThatRequire2FA($config->fields, $preview, empty($record->id));
        $messageAbout2faFields = $this->generate2FAFieldsMessage($config->fields, $preview, empty($record->id));
        $config->fields        = $this->applyUserRightsToForm($config->fields, $preview, empty($record->id));
        $config->arrayName     = 'data';
        $config->alias         = $this->alias;
        $config->model         = $record;

        if (method_exists($record, 'initFromForm')) {
            $record->initFromForm();
        }
        $this->autoloadBelongsToRelations($record);
        $this->autoloadhasManyRelations($record);

        $this->widget = new \Backend\Widgets\Form($this, $config);

        $this->loadBackendFormWidgets();

        // render the extra fields if they are set
        $this->renderExtraFields($record->{$this->recordKeyParam ?? Input::get('recordKeyParam')} ?? 'new');

        if (isset($config->formBuilder_card_design) && $config->formBuilder_card_design && $preview) {
            $html = $this->renderViewMode($this->widget);
        } else {
            $this->makePreselectedFieldsReadOnly();
            $html  = $this->widget->render(['preview' => $preview]);
            $html .= $this->renderValidationTags($record);
        }
        $html .= $this->renderBelongsToManyWithPivotDataAndHasManyRelations($record, !$preview);

        $variablesToPass = [
            'form' => $html,
            'formUniqueId' => $this->formUniqueId,
            'additionalData' => $this->additionalData,
            'specialValidationExceptions' => serialize($this->specialValidationExceptions),
            'recordKeyParam' => $this->recordKeyParam ?? Input::get('recordKeyParam'),
            'recordKeyValue' => $record->{$this->recordKeyParam ?? Input::get('recordKeyParam')} ?? 'new',
            'from_id' => $form->id,
            'preview' => $preview,
            'redirectOnClose' => Input::old('redirectOnClose') ?? \Url::previous(),
            'actionUpdateKeyword' => $this->actionUpdateKeyword,
            'messageAbout2faFields' => $messageAbout2faFields,
        ];

        return $this->renderPartial('@partials/form', $variablesToPass);
    }

    public function renderViewMode($widget)
    {
        $mainCardVariablesToPass  = [];
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
                    $mainCardVariablesToPass['name']  = $key;
                    $mainCardVariablesToPass['class'] = $field['formBuilder']['class'];
                } else if ($field['formBuilder']['position'] == 'sheets') {
                    $sheetCardVariablesToPass[$key]         = [];
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
            } else if ($field['formBuilder']['type'] == 'field') {
                // if mandatory config is not set, then don't show the field
                if (!isset($field['formBuilder']['card'])) {
                    continue;
                }

                // if no value is set, then don't show the field
                if (!isset($widget->model->{$key}) || empty(($widget->model->{$key}))) {
                    continue;
                }

                // retrieve the value for the field
                $value = '';

                if (is_object($widget->model->{$key}) && array_key_exists('nameFrom', $field) && isset($widget->model->{$key}->{$field['nameFrom']})) { // relation fields
                    $value = $widget->model->{$key}->{$field['nameFrom']};
                } else if (is_a($widget->model->{$key}, 'Illuminate\Database\Eloquent\Collection') && count($widget->model->{$key}) > 0 && array_key_exists('nameFrom', $field)) { // belongs to many with no pivot data
                    $value = '';
                    foreach ($widget->model->{$key} as $item) {
                        if (isset($item->{$field['nameFrom']})) {
                            $value .= $item->{$field['nameFrom']} . ', ';
                        }
                    }
                } else if ($field['type'] == 'dropdown' && array_key_exists('options', $field) && is_array($field['options']) && count($field['options']) > 0) { // dropdown fields
                    $value = Lang::get($field['options'][$widget->model->{$key}]);
                } else if ($field['type'] == 'checkbox') { // bool fields
                    $value = $widget->model->{$key} == 1 ? Lang::get('csatar.csatar::lang.plugin.admin.general.yes') : Lang::get('csatar.csatar::lang.plugin.admin.general.no');
                } else if ($field['type'] == 'fileupload' && $field['mode'] == 'image') { // images
                    $value = $widget->model->{$key}->getPath();
                    $mainCardVariablesToPass['customImage'] = true;
                } else if ($field['type'] == 'custom') { // custom field type, which permits to list title-value pairs in the descriptionList part of the mainCard
                    $value = $widget->model->{$key};
                } else if (is_array($widget->model->customAttributes) && in_array($key, $widget->model->customAttributes)) { // to display values from get...Arribute() functions
                    $value = $widget->model->{$key};
                } else if (isset($widget->model->attributes[$key]) && !empty($widget->model->attributes[$key])) { // regular fields
                    $value = $widget->model->attributes[$key];
                } else {
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
                $newField['value'] = $value;

                if (array_key_exists('position', $field['formBuilder'])) {
                    $newField['position'] = $field['formBuilder']['position'];
                }
                if (array_key_exists('order', $field['formBuilder'])) {
                    $newField['order'] = $field['formBuilder']['order'];
                }
                if ($field['type'] == 'richeditor') {
                    $newField['raw'] = true;
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
                $mainCardVariablesToPass['fields']         = [];

                // gather the fields by position
                foreach ($fields as $field) {
                    if ($field['position'] == 'image') {
                        $mainCardVariablesToPass['image'] = $field['value'];
                    } else if ($field['position'] == 'title') {
                        array_push($titleFields, $field);
                    } else if ($field['position'] == 'subtitle') {
                        array_push($mainCardVariablesToPass['subtitleFields'], $field);
                    } else if ($field['position'] == 'details') {
                        array_push($mainCardVariablesToPass['fields'], $field);
                    } else if ($field['position'] == 'descriptionList') {
                        $mainCardVariablesToPass['descriptionList'] = $field['value'];
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
            } else if (isset($sheetCardVariablesToPass[$key])) {
                $this->sortArrayByOrder($fields);
                $sheetCardVariablesToPass[$key]['fields'] = $fields;
            }
        }

        // hide the empty boxes
        foreach ($sheetCardVariablesToPass as $key => $card) {
            if (!isset($card['fields'])) {
                unset($sheetCardVariablesToPass[$key]);
            }
        }

        // render the main card
        $html  = '<div class="row">';
        $html .= $this->renderPartial('@partials/mainCard', $mainCardVariablesToPass);

        // render the sheets
        if (count($sheetCardVariablesToPass) > 0) {
            $html .= '<div class="col"><div class="row">';
            foreach ($sheetCardVariablesToPass as $sheet) {
                $html .= $this->renderPartial('@partials/sheetCard', $sheet);
            }
            $html .= '</div></div>';
        }

        $html .= '</div>';
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
                    $v         = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $v;
                }
            }
        }
    }

    public function onAddPivotRelation(){
        $relationName = Input::get('relationName');
        $relationId   = Input::get($relationName);

        if ($relationName && $relationId) {
            return $this->createPivotForm($relationName, $relationId);
        }

        $error = e(trans('csatar.forms::lang.errors.nothingSelectedOnPivotRelation'));
        throw new \ValidationException([$relationName => $error]);
    }

    public function onCloseAddEditArea(){
        $relationName = Input::get('relationName');

        return [
            '#add-edit-' . $relationName => ''
        ];
    }

    public function onListAttachOptions(){
        $record       = $this->getRecord();
        $relationName = Input::get('relationName');
        $defRecords   = DeferredBinding::where('master_field', $relationName)
            ->where('session_key', $this->sessionKey)
            ->get();

        $attachedIds       = $record->id ? $record->{$relationName}->pluck('id') : $defRecords->pluck('slave_id');
        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);
        $relatedModelName  = array_key_exists($relationName, $record->belongsToMany) ?
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
        $options         = method_exists($record, $getFunctionName) ? $record->{$getFunctionName}() : null;

        // scope the returned values if a scope is specified in the fields.html
        $scope = null;
        if ($isHasManyRelation) {
            $tmpModelName = is_array($record->hasMany[$relationName]) ? $record->hasMany[$relationName][0] : $record->hasMany[$relationName];
            if (isset($tmpModelName::$relatedFieldForFormBuilder)) {
                $pivotConfig = $this->getConfig($tmpModelName, 'fields.yaml');
                $scope       = $pivotConfig->fields[$tmpModelName::$relatedFieldForFormBuilder]['scope'];
            }
        }

        \Model::extend(function($model) use ($getFunctionName, $relatedModelName, $attachedIds, $allowDuplicates, $options, $scope) {
            $model->addDynamicMethod($getFunctionName, function() use ($model, $relatedModelName, $attachedIds, $allowDuplicates, $options, $scope) {
                if (!empty($options)) {
                    return $options;
                }

                $options = $allowDuplicates ?
                    (isset($scope) ?
                        $relatedModelName::where('id', '>', 0)->{$scope}()->get() :
                        $relatedModelName::all()) :
                    (isset($scope) ?
                        $relatedModelName::whereNotIn('id', $attachedIds)->{$scope}()->get() :
                        $relatedModelName::whereNotIn('id', $attachedIds)->get());

                $options = $options->filter(function($item) {
                    return $item->is_hidden_frontend != 1;
                });

                return $options->lists('name', 'id');
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

    public function createPivotForm($relationName, $relationId, $edit = false) {
        $preview = $this->readOnly;
        $record  = $this->getRecord();

        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);
        $relatedModelName  = array_key_exists($relationName, $record->belongsToMany) ?
            $record->belongsToMany[$relationName][0] :
            ($isHasManyRelation ?
                $record->hasMany[$relationName][0] :
                false);

        if ($isHasManyRelation) {
            $pivotConfig = $this->getConfig($record->hasMany[$relationName][0], 'fields.yaml');
            if ($edit) {
                $relatedModel       = $relatedModelName::find($relationId);
                $pivotConfig->model = $relatedModel;
            } else {
                $relatedModel       = new $relatedModelName();
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
        } else {
            $relatedModel = $relatedModelName::find($relationId);
            if ($edit) {
                if (!$record->id) {
                    $defRecord = DeferredBinding::where('master_field', $relationName)
                        ->where('session_key', $this->sessionKey)
                        ->where('slave_id', $relationId)
                        ->first();
                    $relatedModel->attributes['pivot'] = $defRecord ? $defRecord->pivot_data : null;
                } else {
                    $relatedModel->attributes['pivot'] = $record->{$relationName}->find($relationId)->pivot->attributes;
                }
            }
            $pivotConfig        = $this->getConfig($relatedModelName, 'fieldsPivot.yaml');
            $pivotConfig->model = $relatedModel;
        }
        $pivotConfig->arrayName = $relationName;
        $pivotConfig->alias     = $relatedModelName;
        $widget = new \Backend\Widgets\Form($this, $pivotConfig);
        $this->loadBackendFormWidgets();

        $html       = $widget->render(['preview' => $preview]);
        $pivotModel = $this->getPivotModelIfSet($relationName);
        if (!$preview && !empty($pivotModel->rules)) {
            $html .= $this->renderValidationTags($pivotModel, array_key_exists($relationName, $record->belongsToMany), $relationName);
        }

        return [
            '#add-edit-' . $relationName => $this->renderPartial('@partials/relationForm', [
                'html' => $html,
                'relationName' => $relationName,
                'relationId' => $relationId,
                'edit' => $edit,
            ])
        ];
    }

    public function onSavePivotRelation(){
        $record       = $this->getRecord();
        $relationName = Input::get('relationName');
        $relationId   = Input::get('relationId');
        $edit         = Input::get('edit') == 1 ? true : false;

        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);
        $relatedModelName  = array_key_exists($relationName, $record->belongsToMany) ?
            $record->belongsToMany[$relationName][0] :
            ($isHasManyRelation ?
                $record->hasMany[$relationName][0] :
                false);

        $model = $edit ? $relatedModelName::find($relationId) : $this->getPivotModelIfSet($relationName);

        if (isset(Input::get($relationName)['pivot']) && $edit) {
            $pivotData = Input::get($relationName)['pivot'];
            $rules     = $record->{$relationName}->find($relationId)->pivot->rules ?? [];
        } else {
            $pivotData = Input::get($relationName);
            $rules     = !empty($model->rules) ? $model->rules : [];
        }

        if ($model && method_exists($model, 'beforeValidateFromForm')) {
            $model->beforeValidateFromForm($pivotData);
        }
        if (count($rules) > 0) {
            $pivotConfig    = $isHasManyRelation ?
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

        if ($edit && !$isHasManyRelation && $record->id) {  // edit relation, regular pivot, existing record
            $attachedModel = $record->{$relationName}->find($relationId)->pivot;
            $attachedModel = $attachedModel->fill($pivotData);
            $attachedModel->save();
        } else if ($edit && !$isHasManyRelation && !$record->id) {    // edit relation, regular pivot, new record
            $defRecord = DeferredBinding::where('master_field', $relationName)
                        ->where('session_key', $this->sessionKey)
                        ->where('slave_id', $relationId)
                        ->first();
            $defRecord->pivot_data = $pivotData;
            $defRecord->save();
        } else if ($edit) {   // edit relation, polimorphic
            $attachedModel = $record->$relationName()->getRelated()->find($relationId);
            $attachedModel = $attachedModel->fill($pivotData);
            $attachedModel->save();
        } else if (!$isHasManyRelation) { // add relation, regular pivot
            if (!$record->id) {      // new record
                $modelToAttach = $record->$relationName()->getRelated()->find($relationId);
                $record->{$relationName}()->add($modelToAttach, $this->sessionKey, $pivotData);
            } else {                  // existing record
                $record->{$relationName}()->attach($relationId, $pivotData);
                $record->refresh();
            }
        } else {  // add relation, polimorphic
            $relatedModelName = $record->hasMany[$relationName][0];
            if (!$record->id && isset($relatedModelName::$relatedModelNameForFormBuilder) && isset($relatedModelName::$relatedFieldForFormBuilder)) {
                $form         = Form::find($this->formId ?? Input::get('formId'));
                $modelName    = $form->getModelName();
                $max_slave_id = DeferredBinding::where('master_type', substr($modelName, 1))->where('master_field', $relationName)->where('session_key', $this->sessionKey)->max('slave_id');
                $model->id    = isset($max_slave_id) ? $max_slave_id + 1 : 1;
                $record->bindDeferred($relationName, $model, $this->sessionKey, $pivotData);
            } else {
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
        $isNew  = Input::get('recordKeyValue') == 'new' ? true : false;
        $record = $this->record;

        if (!$data = Input::get('data')) {
            $error = e(trans('csatar.forms::lang.errors.noDataArray'));
            throw new ApplicationException($error);
        }

        //until this point record was displayed based on rights cached in session
        $this->currentUserRights = $this->getRights($record, true); // now we get rights from database and ignore session

        if ($this->properties['subForm']) {
            $relationName = $this->properties['getRecordFromParent'] ?? null;
            $parent       = $this->getParent();

            $this->getRightsFromParent($parent, $relationName);

            if ($this->properties['action'] == 'create' && $this->currentUserRights['MODEL_GENERAL']['create'] < 1) {
                return;
            }

            if ($this->properties['action'] == 'update' && $this->currentUserRights['MODEL_GENERAL']['update'] < 1) {
                return;
            }
        }

        // validate the form
        $form   = Form::find($this->formId ?? Input::get('formId'));
        $config = $this->makeConfig($form->getFieldsConfig());

        $attributeNames = [];

        foreach ($config->fields as $key => $value) {
            if ($value['type'] !== 'section' && isset($value['label'])) {
                $attributeNames[$key] = Lang::get($value['label']);
            }
        }

        $rules = $this->addRequiredRuleBasedOnUserRights($record->rules, $this->currentUserRights ?? []);

        // add extra fields validation
        $extraFields      = $this->getExtraFields($this->record, (new DateTime())->format('Y-m-d')) ?? [];
        $extraFieldValues = json_decode($this->record->extra_fields, true) ?? [];

        foreach ($extraFieldValues as $extraFieldValue) {
            $found = false;
            foreach ($extraFields as $key => $extraField) {
                if ($extraField['id'] == $extraFieldValue['id']) {
                    $extraField[$key]['required'] = $extraFieldValue['required'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                array_push($extraFields, $extraFieldValue);
            }
        }
        foreach ($extraFields as $extraField) {
            $dynamicFieldModelId = $extraField['dynamicFieldModelId'] ?? '';
            $id = 'extra_fields_' . $extraField['id'] . '_' . $dynamicFieldModelId;
            $attributeNames[$id] = $extraField['label'];
            $rules[$id]          = 'max:500';
            if ($extraField['required'] == 1) {
                $rules[$id] .= '|required';
            }
        }

        $validation = Validator::make(
            $data,
            $rules,
            $record->customMessages ?? [],
            $attributeNames,
        );

        if ($specialValidationExceptions = Input::get('specialValidationExceptions')) {
            $specialValidationExceptions = unserialize($specialValidationExceptions);
        }

        //validate for conditional rules
        if (isset($record->conditionalRules)) {
            foreach ($record->conditionalRules as $conditionalRule) {
                $validation->sometimes($conditionalRule['fields'], $conditionalRule['rules'], function ($input) use ($record, $conditionalRule) {
                    return $record->{$conditionalRule['validationFunctionName']}($input);
                });
            }
        }

        if ($validation->fails() || !empty($specialValidationExceptions)) {
            foreach ((array) $specialValidationExceptions as $key => $value) {
                $validation->messages()->add('special_validation_exception_' . $key, $value);
            }

            throw new \ValidationException($validation);
        }

        // resolve extra fields data
        if (isset($extraFields)) {
            foreach ($extraFields as &$extraField) {
                $dynamicFieldModelId = $extraField['dynamicFieldModelId'] ?? '';
                $id = 'extra_fields_' . $extraField['id'] . '_' . $dynamicFieldModelId;
                $extraField['value'] = $data[$id];
                unset($data[$id]);
            }
        }

        $data = $this->filterDataBasedOnUserRightsBeforeSave($data, $config->fields, $isNew);

        // resolve extra fields data. It needs to be done after the data has been filtered by rights, as that removes extra_field from data, as extra_fields is not part of the permission matrix
        if (isset($extraFields)) {
            $data['extra_fields'] = json_encode($extraFields);
        }

        // Resolve belongsTo relations
        foreach ($record->belongsTo as $name => $definition) {
            if (empty($data[$name])) {
                continue;
            }

            $key = isset($definition['key']) ? $definition['key'] : $name . '_id';
            if (isset($definition['keyType'])) {
                $data[$key] = $data[$name];
                settype($data[$key], $definition['keyType']);
            } else {
                $data[$key] = (int) $data[$name];
            }
//            unset($data[$name]);
        }

        // Resolve belongsToMany relations
        foreach ($record->belongsToMany as $relationName => $definition) {
            if (!isset($data[$relationName]) || $data[$relationName] =='') {
                continue;
            }

            if (!$record->id) {
                $relatedModel = $definition[0];
                if (is_array($data[$relationName])) {
                    foreach ($data[$relationName] as $recordToAttachId) {
                        $deferred = new DeferredBinding();
                        $deferred->master_type  = get_class($record);
                        $deferred->master_field = $relationName;
                        $deferred->slave_type   = $relatedModel;
                        $deferred->slave_id     = $recordToAttachId;
                        $deferred->session_key  = $this->sessionKey;
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

        if (!empty($this->messages) && array_key_exists('warning', $this->messages)) {
            $warnings = implode('\n', $this->messages['warning']);
            Flash::warning($warnings);
        } else {
            Flash::success(e(trans('csatar.forms::lang.success.saved')));
        }
        return Redirect::back()->withInput();
    }

    public function onCloseForm(){
        DeferredBinding::cleanUp(1); //Destroys all bindings that have not been committed and are older than 1 day
        $this->record->cancelDeferred($this->sessionKey); //Destroys current form's bindings
        return Redirect::to(Input::get('redirectOnClose') ?? '/');
    }

    public function onDelete()
    {
        $record = $this->getRecord();
        if ($record) {
            $record->delete();
        } else {
            throw new NotFoundException();
        }
    }

    public function renderValidationTags($model, $forPivot = false, $relationName = false)
    {
        if (!empty($model->rules)) {
            $rules = $this->addRequiredRuleBasedOnUserRights($model->rules, $this->currentUserRights);
            return $this->renderPartial('@partials/validationTags.htm', [
                'rules' => $rules,
                'forPivot' => $forPivot,
                'relationName' => $relationName
            ]);
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

    private function renderExtraFields($recordKeyValue)
    {
        if (!array_key_exists('extra_fields', $this->widget->model->attributes) && $recordKeyValue !== 'new') {
            return;
        }

        // decode extra field values
        $extraFieldValues = json_decode($this->widget->model->extra_fields, true) ?? [];

        $extraFieldsActive = $this->getExtraFields($this->widget->model, (new DateTime())->format('Y-m-d'));

        if (isset($extraFieldsActive)) {
            // add any newly added fields to the list
            foreach ($extraFieldsActive as $extraFieldActive) {
                $found = false;
                foreach ($extraFieldValues as $key => $extraFieldValue) {
                    if (($extraFieldActive['id'] == $extraFieldValue['id'])
                        && (isset($extraFieldActive['dynamicFieldModelId']) && isset($extraFieldValue['dynamicFieldModelId']) && $extraFieldActive['dynamicFieldModelId'] == $extraFieldValue['dynamicFieldModelId'])
                    ) {
                        $extraFieldValues[$key]['required'] = $extraFieldActive['required'];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    array_push($extraFieldValues, $extraFieldActive);
                }
            }
        }

        // add the extra field values to the model
        foreach ($extraFieldValues as $key => $extraFieldValue) {
            $dynamicFieldModelId = $extraFieldValue['dynamicFieldModelId'] ?? '';
            $id = 'extra_fields_' . $extraFieldValue['id'] . '_' . $dynamicFieldModelId;
            if (empty($extraFieldsActive) || !$this->isSavedExtraFieldActive($extraFieldValue, $extraFieldsActive)) {
                $extraFieldValues[$key]['disabled'] = true;
            }
            $this->widget->model->attributes[$id] = isset($extraFieldValue['value']) ? $extraFieldValue['value'] : '';
        }

        // add section for the extra fields
        $this->widget->fields['extraFieldsData'] = $this->createExtraFieldsSection();

        // add the extra fields
        $this->widget->fields = array_merge($this->widget->fields, $this->createExtraFieldFields($extraFieldValues, $this->widget->model->rules));
    }

    private function isSavedExtraFieldActive($savedExtraField, $extraFieldsActive)
    {
        foreach ($extraFieldsActive as $extraFieldActive) {
            if (($savedExtraField['id'] == $extraFieldActive['id'])
                && (isset($savedExtraField['dynamicFieldModelId']) && isset($extraFieldActive['dynamicFieldModelId']) && $savedExtraField['dynamicFieldModelId'] == $extraFieldActive['dynamicFieldModelId'])
            ) {
                return true;
            }
        }
        return false;
    }

    private function createExtraFieldsSection() {
        return [
            'label' => Lang::get('csatar.csatar::lang.plugin.admin.dynamicFields.extraFields'),
            'span' => 'full',
            'type' => 'section',
            'hidden' => 1,
            'formBuilder' => [
                'type' => 'card',
                'position' => 'sheets',
                'order' => 1000,
                'class' => 'col-lg-12 col-md-12 col-sm-12',
                'color' => 'csat-data-parent',
            ],
        ];
    }

    private function createExtraFieldFields($extraFields, &$rules = []) {
        $fields = [];
        $order  = 1;
        foreach ($extraFields as $extraField) {
            $dynamicFieldModelId = $extraField['dynamicFieldModelId'] ?? '';
            $id          = 'extra_fields_' . $extraField['id'] . '_' . $dynamicFieldModelId;
            $fields[$id] = [
                'label' => $extraField['label'],
                'span' => 'auto',
                'type' => 'textarea',
                'formBuilder' => [
                    'type' => 'field',
                    'card' => 'extraFieldsData',
                    'order' => $order,
                ],
            ];
            if ($extraField['required'] == 1) {
                $fields[$id]['required'] = 1;
                $rules[$id] = 'required';
            }
            if (isset($extraField['disabled'])) {
                $fields[$id]['disabled'] = $extraField['disabled'];
            }
            $order++;
        }
        return $fields;
    }

    private function getExtraFields($model, $date) {
        // get the association and the model name
        if (!method_exists($model, 'getAssociation') || !method_exists($model, 'getModelName')) {
            return null;
        }
        $association = $model->getAssociation();
        if (!isset($association)) {
            return null;
        }
        $modelName = $model::getModelName();

        // get the extra fields defined for the given association, model and current date
        $dynamicFields = DynamicFields::where('association_id', $association->id)
            ->where('model', $modelName)
            ->where(function ($query) use ($date) {
                return $query->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->orWhere('end_date', null);
            })
            ->get();
        if (count($dynamicFields) == 0) {
            return null;
        }

        $extraFields = [];

        foreach ($dynamicFields[0]->extra_fields_definition as $key => $extraField) {
            $extraFields[$key] = $extraField;
            $extraFields[$key]['dynamicFieldModelId'] = $dynamicFields[0]->id;
        }

        return $extraFields;
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
        foreach ($record->belongsToMany as $relationName => $definition) {
            if ($this->canRead($relationName) && !empty($definition['pivot']) && (count($record->{$relationName}) > 0 || $showEmpty)) {
                $pivotConfig = $this->getConfig($definition[0], 'columnsPivot.yaml');
                if ($pivotConfig) {
                    $attributesToDisplay = $this->attributesToDisplay($pivotConfig);
                    $html .= $this->generatePivotSection($record, $relationName, $definition, $attributesToDisplay);
                }
            }
        }

        // render hasMany relations
        foreach ($record->hasMany as $relationName => $definition) {
            if ($this->canRead($relationName)
                && is_array($definition)
                && (array_key_exists('renderableOnCreateForm', $definition) || array_key_exists('renderableOnUpdateForm', $definition)) //this is needed to avoid looping though relations that renderable and eager loaded
                && (count($record->{$relationName}) > 0 || $showEmpty)
                && ((!$record->id
                    && array_key_exists('renderableOnCreateForm', $definition)
                    && $definition['renderableOnCreateForm'])
                    || ($record->id
                        && array_key_exists('renderableOnUpdateForm', $definition)
                        && $definition['renderableOnUpdateForm'])
                )
            ) {
                $pivotConfig         = $this->getConfig($definition[0], 'columns.yaml');
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
                $pivotColumn     = str_replace(']', '', str_replace('pivot[', '', $columnName));
                $data['isPivot'] = true;
                $attributesToDisplay[$pivotColumn] = $data;
            } else {
                $attributesToDisplay[$columnName] = $data;
            }
        }
        return $attributesToDisplay;
    }

    public function generatePivotSection($record, $relationName, $definition, $attributesToDisplay) {
        $relationLabel = array_key_exists('label', $definition) ? Lang::get($definition['label']) : $relationName;

        if (count($record->$relationName)>0 ||
            (!$record->id && count($record->{$relationName}()->withDeferred($this->sessionKey)->get())>0)) {
            $pivotTableHeader = $this->generatePivotTableHeader($attributesToDisplay);
            $pivotTableRows   = $this->generatePivotTableRows($record, $relationName, $attributesToDisplay);
        }

        return $this->renderPartial('@partials/pivotSection.htm', [
            'relationName' => $relationName,
            'relationLabel' => $relationLabel,
            'record' => $record,
            'attributesToDisplay' => $attributesToDisplay,
            'readOnly' => $this->readOnly,
            'canUpdate' => $this->canUpdate($relationName),
            'fieldsThatRequire2FA' => $this->fieldsThatRequire2FA,
            'pivotTableHeader' => $pivotTableHeader ?? null,
            'pivotTableRows' => $pivotTableRows ?? null,
        ]);
    }

    public function onDeletePivotRelation(){
        $record       = $this->getRecord();
        $relationName = Input::get('relationName');
        $relationId   = Input::get('relationId');

        if (!$this->canDelete($relationName)) {
            Flash::warning(e(trans('csatar.forms::lang.failed.noPermissionToDeleteRecord')));
            return;
        }
        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);

        $defRecords = DeferredBinding::where('master_field', $relationName)
            ->where('session_key', $this->sessionKey)
            ->where('slave_id', $relationId)
            ->delete();
        if (!$isHasManyRelation) {
            $record->{$relationName}()->detach($relationId);
        } else {
            ($record->hasMany[$relationName][0])::where('id', $relationId)->delete();
        }

        return [
            '#pivotSection' =>
                $this->renderBelongsToManyWithPivotDataAndHasManyRelations($record)
        ];
    }

    public function onModifyPivotRelation(){
        $relationName = Input::get('relationName');
        $relationId   = Input::get('relationId');
        return $this->createPivotForm($relationName, $relationId, true);
    }

    public function onRefresh(){
        return [
            '#renderedFormArea' => $this->createForm(),
        ];
    }

    public function generatePivotTableHeader($attributesToDisplay) {
        return $this->renderPartial('@partials/pivotTableHeader.htm', [
            'attributesToDisplay' => $attributesToDisplay,
            'readOnly' => $this->readOnly,
        ]);
    }

    public function generatePivotTableRows($record, $relationName, $attributesToDisplay) {

        $records           = $record->{$relationName};
        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);
        $defRecords        = null;
        if (!$record->id) {
            $defRecords = DeferredBinding::where('master_field', $relationName)
                ->where('session_key', $this->sessionKey)
                ->get();
            if (!$isHasManyRelation) {
                $records = $record->{$relationName}()->withDeferred($this->sessionKey)->get();
            } else {
                $records = [];
                foreach ($defRecords as $defRecord) {
                    $record = new $defRecord->slave_type();
                    array_push($records, $record);
                }
            }
        }

        $tableRows = '';
        foreach ($records as $key => $relatedRecord) {
            if ($relatedRecord->is_hidden_frontend) {
                continue;
            }
            if ($defRecords) {
                if (!$isHasManyRelation) {
                    $relatedRecord->pivot = (object)$defRecords[$key]->pivot_data;
                } else {
                    $relatedRecord->attributes = $defRecords[$key]->pivot_data;
                    $relatedRecord->id         = $defRecords[$key]->slave_id;
                    if (isset($relatedRecord::$relatedModelNameForFormBuilder) && isset($relatedRecord::$relatedFieldForFormBuilder)) {
                        $tmp = $relatedRecord->{$relatedRecord::$relatedFieldForFormBuilder};
                        unset($relatedRecord->{$relatedRecord::$relatedFieldForFormBuilder});
                        $relatedRecord->{$relatedRecord::$relatedFieldForFormBuilder} = ($relatedRecord::$relatedModelNameForFormBuilder)::find($tmp);
                    }
                }
            }

            $cols       = '';
            $colButtons = '';
            foreach ($attributesToDisplay as $key => $data) {
                $label = Lang::get($data['label']);

                if (array_key_exists('isPivot', $data)) {
                    $value = $relatedRecord->pivot->{$key} ?? '';
                } else {
                    $attribute = array_key_exists('valueFromFormBuilder', $data) ? $data['valueFromFormBuilder'] : 'name';
                    $value     = (is_object($relatedRecord->{$key}) ?
                        $relatedRecord->{$key}->{$attribute} :
                        $relatedRecord->{$key});
                }

                $cols .= $this->renderPartial('@partials/pivotTableRowCol.htm', [
                    'label' => $label,
                    'value' => $value,
                ]);
            }

            if (!$this->readOnly) {
                $colButtons .= $this->renderPartial('@partials/pivotTableRowColButtons.htm', [
                    'canUpdate' => $this->canUpdate($relationName),
                    'canDelete' => $this->canDelete($relationName),
                    'relationName' => $relationName,
                    'relationId' => $relatedRecord->id,
                    'fieldsThatRequire2FA' => $this->fieldsThatRequire2FA,
                ]);
            }
            $tableRows .= $this->renderPartial('@partials/pivotTableRow', [
                'cols' => $cols,
                'colButtons' => $colButtons,
            ]);
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
        $record            = $this->getRecord();
        $isHasManyRelation = array_key_exists($relationName, $record->hasMany);

        if (array_key_exists($relationName, $record->belongsToMany)) {
            $relationConfigArray = $record->belongsToMany[$relationName];
            if (array_key_exists('pivotModel', $relationConfigArray)) {
                return new $relationConfigArray['pivotModel']($record, [], '');
            }
        } else if ($isHasManyRelation) {
            $relationConfigArray = $record->hasMany[$relationName];
            $relatedModel        = new $relationConfigArray[0]();
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

        // Autoload belongsTo relations
        foreach ($record->belongsTo as $name => $definition) {

            if (empty($_POST[$name]) && empty($_POST['data'][$name])) {
                if (!empty($definition['formBuilder']['requiredBeforeRender'])
                    && $definition['formBuilder']['requiredBeforeRender']
                    && empty($record->{$name . '_id'})
                ) {
                    \App::abort(403, 'Access denied');
                };
                continue;
            }

            $key          = isset($definition['key']) ? $definition['key'] : $name . '_id';
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
        foreach ($record->hasMany as $name => $definition) {

            if (!Input::get($name) && !Input::get('data.' . $name)) {
                continue;
            }

            $key          = isset($definition['key']) ? $definition['key'] : $name . '_id';
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

            if (isset($settings['type']) && ($settings['type'] == 'custom' || $settings['type'] == 'section')) {
                continue;
            }

            if (isset($settings['type']) && $settings['type'] == 'relation' && $this->hasPivotColumns($attribute)) {
                continue;
            }

            if (isset($settings['formBuilder']['ignoreUserRights']) && $settings['formBuilder']['ignoreUserRights'] == 1) {
                continue;
            }

            if (!$this->rightsCollectionHasKey($attribute)) {
                unset($attributesArray[$attribute]);
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
     * Marks fields that require 2FA
     */
    private function markFieldsThatRequire2FA(array $attributesArray, bool $isReadOnly, bool $isNewRecord = false): array
    {
        if ($isReadOnly) {
            foreach ($this->fieldsThatRequire2FA as $attribute => $settings) {
                if (in_array('read', $settings)) {
                    $key = isset($attributesArray[$attribute]) ? $attribute : '@' . $attribute;
                    $attributesArray[$key]['formBuilder']['2fa'] = $this->fieldsThatRequire2FA[$attribute];
                    $attributesArray[$key]['cssClass']           = 'csat-2fa-field';
                }
            }
            return $attributesArray;
        } else if ($isNewRecord) {
            foreach ($this->fieldsThatRequire2FA as $attribute => $settings) {
                if (in_array('create', $settings)) {
                    $key = isset($attributesArray[$attribute]) ? $attribute : '@' . $attribute;
                    $attributesArray[$key]['formBuilder']['2fa'] = $this->fieldsThatRequire2FA[$attribute];
                    $attributesArray[$key]['cssClass']           = 'csat-2fa-field';
                }
            }
            return $attributesArray;
        } else {
            foreach ($this->fieldsThatRequire2FA as $attribute => $settings) {
                if (in_array('update', $settings) || in_array('delete', $settings)) {
                    $key = isset($attributesArray[$attribute]) ? $attribute : '@' . $attribute;
                    $attributesArray[$key]['formBuilder']['2fa'] = $this->fieldsThatRequire2FA[$attribute];
                    $attributesArray[$key]['cssClass']           = 'csat-2fa-field';
                }
            }
            return $attributesArray;
        }

        return [];
    }

    /**
     * Generates info message about 2fa fields
     */
    private function generate2FAFieldsMessage($attributesArray, $preview, $isNewRecord) {
        if (empty(Auth::user())) {
            return;
        }

        $fieldsThatRequire2FA = [];

        if ($preview) {
            foreach ($attributesArray as $key => $value) {
                $actionsThatNeed2FA = $value['formBuilder']['2fa'] ?? null;
                if ( $actionsThatNeed2FA && in_array('read', $actionsThatNeed2FA)) {
                    $fieldsThatRequire2FA[] = $this->get2FAFieldName($key, $value);
                }
            }

            if (!empty($fieldsThatRequire2FA)) {
                return Lang::get('csatar.forms::lang.components.basicForm.2FAtoRead') . implode(', ', $fieldsThatRequire2FA);
            }
        } else if ($isNewRecord) {
            foreach ($attributesArray as $key => $value) {
                $actionsThatNeed2FA = $value['formBuilder']['2fa'] ?? null;
                if ( $actionsThatNeed2FA
                    && in_array('create', $actionsThatNeed2FA)
                ) {
                    $fieldsThatRequire2FA[] = $this->get2FAFieldName($key, $value);
                }
            }

            $fieldsThatRequire2FA = array_filter($fieldsThatRequire2FA, function($value) {
                return strpos($value, '@') === false;
            });

            if (!empty($fieldsThatRequire2FA)) {
                return Lang::get('csatar.forms::lang.components.basicForm.2FAtoCreate') . implode(', ', $fieldsThatRequire2FA);
            }
        } else {
            foreach ($attributesArray as $key => $value) {
                $actionsThatNeed2FA = $value['formBuilder']['2fa'] ?? null;
                if ( $actionsThatNeed2FA
                    && (in_array('update', $actionsThatNeed2FA)
                        || in_array('delete', $actionsThatNeed2FA))
                ) {
                    $fieldsThatRequire2FA[] = $this->get2FAFieldName($key, $value);
                }
            }

            $fieldsThatRequire2FA = array_filter($fieldsThatRequire2FA, function($value) {
                return strpos($value, '@') === false;
            });

            if (!empty($fieldsThatRequire2FA)) {
                return Lang::get('csatar.forms::lang.components.basicForm.2FAtoModify') . implode(', ', $fieldsThatRequire2FA);
            }
        }

        return null;
    }

    public function get2FAFieldName(string $attribute, array $attributeSettings)
    {
        if (isset($attributeSettings['label']) && !empty(Lang::get($attributeSettings['label']))) {
            return Lang::get($attributeSettings['label']);
        }

        if ($relationsArray = array_merge($this->record->belongsToMany, $this->record->hasMany)) {
            if (
                isset($relationsArray[str_replace('@', "", $attribute)]['label'])
            ) {
                return Lang::get($relationsArray[str_replace('@', "", $attribute)]['label']);
            }

        }

        return $attribute;
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
    private function filterDataBasedOnUserRightsBeforeSave(array $data, $fieldsConfig, bool $isNewRecord = false ): array
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
                if ($this->shouldIgnoreUserRights($attribute, $fieldsConfig)) {
                    continue;
                }

                if (!$this->canCreate($attribute)) {
                    unset($data[$attribute]);
                }
            }
        }

        if (!$isNewRecord) { //if updating an existing record we don't care about create right
            foreach ($data as $attribute => $value) {
                if ($this->shouldIgnoreUserRights($attribute, $fieldsConfig)) {
                    continue;
                }
                //if user can delete attribute, but he is not allowed to update it, accept only empty value for the attribute
                if ($this->canDelete($attribute) && !$this->canUpdate($attribute)) {
                    if (!empty($value) && $value != $this->record->{$attribute}) {
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
                if (!$this->canUpdate($attribute) && $value != $this->record->{$attribute}) {
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
}
