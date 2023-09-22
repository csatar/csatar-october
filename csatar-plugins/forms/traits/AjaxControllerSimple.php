<?php
namespace Csatar\Forms\Traits;

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
// 'Backend\FormWidgets\RichEditor' => [
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
        // update field list and config based on currentUserRights
        $config->fields        = $this->markFieldsThatRequire2FA($config->fields, $preview, empty($record->id));
        $messageAbout2faFields = $this->generate2FAFieldsMessage($config->fields, $preview, empty($record->id));
        $config->fields        = $this->applyUserRightsToForm($config->fields, $preview, empty($record->id));

        if ($preview) {
            $config->fields = array_map(function ($field) {
                $field['formBuilder']['preview'] = true;
                return $field;
            }, $config->fields);
        }

        $config->arrayName = 'data';
        $config->alias     = $this->alias;
        $config->model     = $record;

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

        if (array_key_exists('pivotPlaceholder', $config->fields)) {
            $html = str_replace('PIVOT_PLACEHOLDER', $this->renderPivotSection($record, !$preview), $html);
        } else {
            $html .= $this->renderPivotSection($record, !$preview);
        }

        $variablesToPass = [
            'form' => $html,
            'formUniqueId' => $this->formUniqueId,
            'additionalData' => $this->additionalData,
            'specialValidationExceptions' => serialize($this->specialValidationExceptions),
            'recordKeyParam' => $this->recordKeyParam ?? Input::get('recordKeyParam'),
            'recordKeyValue' => $record->{$this->recordKeyParam ?? Input::get('recordKeyParam')} ?? 'new',
            'from_id' => $form->id,
            'preview' => $preview,
            'redirectOnClose' => $this->getPreviousUrl($this->formUniqueId),
            'actionUpdateKeyword' => $this->actionUpdateKeyword,
            'messageAbout2faFields' => $messageAbout2faFields,
        ];

        return $this->renderPartial('@partials/form', $variablesToPass);
    }

    public function renderViewMode($widget)
    {
        // gather all cards and fields in arrays
        list($mainCardVariablesToPass, $sheetCardVariablesToPass, $fieldsToPass) = $this->gatherAllCardsAndFieldsInArrays($widget);

        // sort the sheets
        $this->sortArrayByOrder($sheetCardVariablesToPass);

        // set the appropriate field array for each of the cards
        list($mainCardVariablesToPass, $sheetCardVariablesToPass) = $this->setTheAppropriateFieldArrayForEachOfTheCards($fieldsToPass, $mainCardVariablesToPass, $sheetCardVariablesToPass);

        // hide the empty boxes
        foreach ($sheetCardVariablesToPass as $key => $card) {
            if (!isset($card['fields'])) {
                unset($sheetCardVariablesToPass[$key]);
            }
        }

        // render the main card
        $html = '<div class="row">';
        if (isset($sheetCardVariablesToPass['mainAfter'])) {
            $mainCardVariablesToPass['mainAfter'] = $this->renderPartial('@partials/sheetCard', $sheetCardVariablesToPass['mainAfter']);
            unset($sheetCardVariablesToPass['mainAfter']);
        }

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
        $pivotConfig->alias     = 'pivotRelation_' . $relationName;
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

        if (isset(Input::get($relationName)['pivot'])) {
            $pivotData  = array_merge(Input::get($relationName), Input::get($relationName)['pivot']);
            $modelRules = $model->rules ?? [];
            $pivotRules = $record->{$relationName}->find($relationId)->pivot->rules ?? $model->rules ?? [];
            $rules      = array_merge($modelRules, $pivotRules);
        } else {
            $pivotData = Input::get($relationName);
            $rules     = !empty($model->rules) ? $model->rules : [];
        }

        $pivotData = $this->updateNullStringToNull($pivotData);

        $this->handlePivotRelationValidation($model, $pivotData, $rules, $isHasManyRelation, $record, $relationName);

        if ($model && method_exists($model, 'beforeSaveFromForm')) {
            $model->beforeSaveFromForm($pivotData);
        }

        $pivotData = $pivotData['pivot'] ?? $pivotData;

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
                $model::create(isset($model->attributes) ? array_merge($model->attributes, $pivotData) : $pivotData);
            }
        }

        $record->refresh();

        return [
            '#pivotSection' =>
                $this->renderBelongsToManyWithPivotDataAndHasManyRelations($record),
            '#pivot-form' => '',
        ];
    }

    public function onChangeSortOrder() {
        $record       = $this->getRecord();
        $relationName = Input::get('relationName');
        $sortOrder    = Input::get('sortOrder');
        $direction    = Input::get('direction');
        $change       = $direction == 'up' ? -1 : 1;

        $attachedModels = $record->{$relationName};
        $attachedModel1 = $attachedModels->firstWhere('pivot.sort_order', $sortOrder);
        $attachedModel2 = $attachedModels->firstWhere('pivot.sort_order', $sortOrder + $change);

        if (empty($attachedModel1) || empty($attachedModel2)) {
            return false;
        }

        $attachedModel1->pivot->sort_order = $sortOrder + $change;
        $attachedModel2->pivot->sort_order = $sortOrder;
        $attachedModel1->pivot->save();
        $attachedModel2->pivot->save();

        $record->refresh();

        return [
            '#pivotSection' =>
                $this->renderBelongsToManyWithPivotDataAndHasManyRelations($record),
            '#pivot-form' => '',
        ];
    }

    /**
     * Edits a relation
     *
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

        $data = $this->updateNullStringToNull($data);

        // until this point record was displayed based on rights cached in session
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

        $attributeNames = $this->getAttributeNames($config);

        $rules = $this->addRequiredRuleBasedOnUserRights($record->rules, $this->currentUserRights ?? []);

        // add extra fields validation
        $extraFields = $this->getExtraFieldsData();
        list($attributeNames, $rules) = $this->handleExtraFieldsValidationSettings($extraFields, $attributeNames, $rules);

        $this->validateFormData($data, $rules, $record, $attributeNames);

        // resolve extra fields data
        list($extraFields, $data) = $this->resolveExtraFieldsData($extraFields, $data);

        $data = $this->filterDataBasedOnUserRightsBeforeSave($data, $config->fields, $isNew);

        // resolve extra fields data. It needs to be done after the data has been filtered by rights, as that removes extra_field from data, as extra_fields is not part of the permission matrix
        if (!empty($extraFields)) {
            $data['extra_fields'] = json_encode($extraFields);
        }

        $data   = $this->resolveBelongsToRelations($record, $data);
        $record = $this->resolveBelongsToManyRelations($record, $data);

        // save the data
        if ($isNew) {
            $record = $this->saveNewRecord($record, $data);
        }

        if (!$record->update($data) && !$isNew) {
            $error = e(trans('csatar.forms::lang.errors.canNotSaveValidated'));
            throw new ApplicationException($error);
        }

        if (!empty($this->messages) && array_key_exists('warning', $this->messages)) {
            $warnings = implode('\n', $this->messages['warning']);
            Flash::warning($warnings);
        } else {
            Flash::success(e(trans('csatar.forms::lang.success.saved')));
        }

        if (Input::get('close')) {
            return $this->onCloseForm();
        }

        if ($isNew) {
            $redirectUrl = str_replace('default', '', $this->currentPageUrl(false)) . $record->{$this->recordKeyParam ?? Input::get('recordKeyParam')} . '/' . Input::get('actionUpdateKeyword');
            return Redirect::to($redirectUrl)->withInput();
        }

        return Redirect::back()->withInput();
    }

    public function onCloseForm(){
        DeferredBinding::cleanUp(1); // Destroys all bindings that have not been committed and are older than 1 day
        $this->record->cancelDeferred($this->sessionKey); // Destroys current form's bindings
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
            if (isset($field['formBuilder'])
                && isset($field['formBuilder']['readOnlyIfPreselected'])
                && $field['formBuilder']['readOnlyIfPreselected']
                && isset($this->widget->model->{$key})
                && !empty($this->widget->model->{$key})
            ) {
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
                        && (isset($extraFieldActive['dynamicFieldModelId'])
                            && isset($extraFieldValue['dynamicFieldModelId'])
                            && $extraFieldActive['dynamicFieldModelId'] == $extraFieldValue['dynamicFieldModelId']
                        )
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
                && (isset($savedExtraField['dynamicFieldModelId'])
                    && isset($extraFieldActive['dynamicFieldModelId'])
                    && $savedExtraField['dynamicFieldModelId'] == $extraFieldActive['dynamicFieldModelId']
                )
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

    public function renderPivotSection($record, $showEmpty = true) {
        $html  = '<div class="row" id="pivotSection">';
        $html .= $this->renderBelongsToManyWithPivotDataAndHasManyRelations($record, $showEmpty);
        $html .= '</div>';

        return $html;
    }

    public function renderBelongsToManyWithPivotDataAndHasManyRelations($record, $showEmpty = true){

        $html = '';

        // render belongsToMany relations
        foreach ($record->belongsToMany as $relationName => $definition) {
            if (empty($record->id)
                && (isset($definition['renderableOnCreateForm']) && !$definition['renderableOnCreateForm'])) {
                continue;
            }

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
                && (array_key_exists('renderableOnCreateForm', $definition) || array_key_exists('renderableOnUpdateForm', $definition))
                // this is needed to avoid looping though relations that renderable and eager loaded
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

        if (count($record->$relationName) > 0 ||
            (!$record->id && count($record->{$relationName}()->withDeferred($this->sessionKey)->get()) > 0)) {
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

        $record->refresh();

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

    public function onRefresh($refreshRelationFrom = null) {

        if ($refreshRelationFrom) {
            $relationName = Input::get('relationName');
            $relationId   = Input::get('relationId');
            $edit         = Input::get('edit');
            return $this->createPivotForm($relationName, $relationId, $edit);
        }

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
            list($defRecords, $records) = $this->getDeferredRecords($relationName, $isHasManyRelation, $record, $records);
        }

        $records = $records->sortBy(function ($record) {
            return $record->pivot->sort_order ?? $record->id;
        });

        $tableRows = '';
        foreach ($records as $key => $relatedRecord) {
            if ($relatedRecord->is_hidden_frontend) {
                continue;
            }

            $tableRows .= $this->generatePivotTableRow($defRecords, $isHasManyRelation, $key, $relatedRecord, $attributesToDisplay, $relationName);
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
     * @param  $record
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
     * @param  $record
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

        if ($preview) {
            $fieldsThatRequire2FA = $this->getFieldsThatRequire2FAForMessage($attributesArray, ['read']);

            if (!empty($fieldsThatRequire2FA)) {
                return Lang::get('csatar.forms::lang.components.basicForm.2FAtoRead') . implode(', ', $fieldsThatRequire2FA);
            }
        } else if ($isNewRecord) {
            $fieldsThatRequire2FA = $this->getFieldsThatRequire2FAForMessage($attributesArray, ['create']);

            if (!empty($fieldsThatRequire2FA)) {
                return Lang::get('csatar.forms::lang.components.basicForm.2FAtoCreate') . implode(', ', $fieldsThatRequire2FA);
            }
        } else {
            $fieldsThatRequire2FA = $this->getFieldsThatRequire2FAForMessage($attributesArray, ['update', 'delete']);

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
            if (isset($relationsArray[str_replace('@', "", $attribute)]['label'])
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
    private function filterDataBasedOnUserRightsBeforeSave(array $data, $fieldsConfig, bool $isNewRecord = false): array
    {
        // This function is needed because at the time of rendering the form user rights are loaded from session,
        // but before save we confirm the rights from database, and if there were changes, not-allowed data should not be saved.
        // NOTE:
        // In readOnly mode we do not care about create/update/delete rights.
        // In create mode, we do not care about read/update/delete rights.
        // In update mode, we do not care about read/create rights.
        // This function is called only in create/update mode.
        if ($isNewRecord) { // if it's a new record, we care only about create right
            foreach ($data as $attribute => $value) {
                if ($this->shouldIgnoreUserRights($attribute, $fieldsConfig)) {
                    continue;
                }

                if (!$this->canCreate($attribute)) {
                    unset($data[$attribute]);
                }
            }
        }

        if (!$isNewRecord) { // if updating an existing record we don't care about create right
            foreach ($data as $attribute => $value) {
                if ($this->shouldIgnoreUserRights($attribute, $fieldsConfig)) {
                    continue;
                }

                // if user can delete attribute, but he is not allowed to update it, accept only empty value for the attribute
                if ($this->canDelete($attribute) && !$this->canUpdate($attribute)) {
                    if (!empty($value) && $value != $this->record->{$attribute}) {
                        $this->storeMessage('warning', e(trans('csatar.forms::lang.failed.noPermissionForSomeFields')));
                        unset($data[$attribute]);
                    }

                    // if user can delete attribute, but he is not allowed to update it and value is empty for the attribute, continue
                    continue;
                }

                if (!$this->canDelete($attribute) && empty($value) && $value != $this->record->{$attribute}) {
                    $this->storeMessage('warning', e(trans('csatar.forms::lang.failed.noPermissionForSomeFields')));
                    unset($data[$attribute]);
                }

                // if user can't update the attribute, and the above conditions doesn't apply, unset attribute before save
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
                // add required rule if is obligatory for user to fill the attribute
                // BUT this should not remove required rule IF, it's required by model settings
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

    /**
     * @param  $relationName
     * @param  bool $isHasManyRelation
     * @param  $record
     * @param  mixed $records
     * @return array
     */
    public function getDeferredRecords($relationName, bool $isHasManyRelation, $record, $records): array
    {
        $defRecords = DeferredBinding::where('master_field', $relationName)
            ->where('session_key', $this->sessionKey)
            ->get();
        if (!$isHasManyRelation) {
            $records = $record->{$relationName}()->withDeferred($this->sessionKey)->get();
        } else {
            $records = [];
            foreach ($defRecords as $defRecord) {
                $record    = new $defRecord->slave_type();
                $records[] = $record;
            }
        }

        return [$defRecords, $records];
    }

    /**
     * @param  bool $isHasManyRelation
     * @param  $defRecord
     * @param  $relatedRecord
     * @return object
     */
    public function handleDeferredRecord(bool $isHasManyRelation, $defRecord, $relatedRecord): object
    {
        if (!$isHasManyRelation) {
            $relatedRecord->pivot = (object) $defRecord->pivot_data;
        } else {
            $relatedRecord->attributes = $defRecord->pivot_data;
            $relatedRecord->id         = $defRecord->slave_id;
            if (isset($relatedRecord::$relatedModelNameForFormBuilder) && isset($relatedRecord::$relatedFieldForFormBuilder)) {
                $tmp = $relatedRecord->{$relatedRecord::$relatedFieldForFormBuilder};
                unset($relatedRecord->{$relatedRecord::$relatedFieldForFormBuilder});
                $relatedRecord->{$relatedRecord::$relatedFieldForFormBuilder} = ($relatedRecord::$relatedModelNameForFormBuilder)::find($tmp);
            }
        }

        return $relatedRecord;
    }

    /**
     * @param  $data
     * @param  $key
     * @param  $relatedRecord
     * @return string
     */
    public function getColPartial($data, $key, $relatedRecord): string
    {
        $label            = Lang::get($data['label']);
        $tooltipAttribute = array_key_exists('tooltipFrom', $data) ? $data['tooltipFrom'] : null;

        if (array_key_exists('isPivot', $data)) {
            $value   = $relatedRecord->pivot->{$key} ?? '';
            $tooltip = $tooltipAttribute ? $relatedRecord->pivot->{$tooltipAttribute} ?? null : null;
        } else {
            $attribute = array_key_exists('valueFromFormBuilder', $data) ? $data['valueFromFormBuilder'] : 'name';
            $value     = (is_object($relatedRecord->{$key}) ?
                $relatedRecord->{$key}->{$attribute} :
                $relatedRecord->{$key});
            $tooltip   = $tooltipAttribute ? (is_object($relatedRecord->{$key}) ?
                $relatedRecord->{$key}->{$tooltipAttribute} :
                $relatedRecord->{$tooltipAttribute}) : null;
        }

        return $this->renderPartial('@partials/pivotTableRowCol.htm', [
            'label'   => $label,
            'value'   => $value,
            'tooltip' => $tooltip,
        ]);
    }

    /**
     * @param  $defRecords
     * @param  bool $isHasManyRelation
     * @param  $key
     * @param  $relatedRecord
     * @param  $attributesToDisplay
     * @param  $relationName
     * @return string
     */
    public function generatePivotTableRow($defRecords, bool $isHasManyRelation, $key, $relatedRecord, $attributesToDisplay, $relationName): string
    {
        if ($defRecords) {
            $relatedRecord = $this->handleDeferredRecord($isHasManyRelation, $defRecords[$key], $relatedRecord);
        }

        $cols       = '';
        $colButtons = '';
        foreach ($attributesToDisplay as $key => $data) {
            $cols .= $this->getColPartial($data, $key, $relatedRecord);
        }

        if (!$this->readOnly) {
            $colButtons .= $this->renderPartial('@partials/pivotTableRowColButtons.htm', [
                'canUpdate'            => $this->canUpdate($relationName),
                'canDelete'            => $this->canDelete($relationName),
                'relationName'         => $relationName,
                'relationId'           => $relatedRecord->id,
                'sortOrder'            => $relatedRecord->pivot->sort_order ?? false,
                'fieldsThatRequire2FA' => $this->fieldsThatRequire2FA,
            ]);
        }

        return $this->renderPartial('@partials/pivotTableRow', [
            'cols'       => $cols,
            'colButtons' => $colButtons,
        ]);
    }

    /**
     * @param  array $attributesArray
     * @param  array $needles
     * @return array
     */
    public function getFieldsThatRequire2FAForMessage(array $attributesArray, array $needles): array
    {
        $fieldsThatRequire2FA = [];

        foreach ($attributesArray as $key => $value) {
            $actionsThatNeed2FA = $value['formBuilder']['2fa'] ?? null;
            if ($actionsThatNeed2FA && !empty(array_intersect($actionsThatNeed2FA, $needles))) {
                $fieldsThatRequire2FA[] = $this->get2FAFieldName($key, $value);
            }
        }

        return array_filter($fieldsThatRequire2FA, function($value) {
            return strpos($value, '@') === false;
        });
    }

    /**
     * @param  $config
     * @return array
     */
    public function getAttributeNames($config): array
    {
        foreach ($config->fields as $key => $value) {
            $type = $value['type'] ?? null;
            if ($type !== 'section' && isset($value['label'])) {
                $attributeNames[$key] = Lang::get($value['label']);
            }
        }

        return $attributeNames;
    }

    /**
     * @return array
     */
    public function getExtraFieldsData(): array
    {
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
                $extraFields[] = $extraFieldValue;
            }
        }

        return $extraFields;
    }

    /**
     * @param  array $extraFields
     * @param  array $attributeNames
     * @param  array $rules
     * @return array
     */
    public function handleExtraFieldsValidationSettings(array $extraFields, array $attributeNames, array $rules): array
    {
        foreach ($extraFields as $extraField) {
            $dynamicFieldModelId = $extraField['dynamicFieldModelId'] ?? '';
            $id = 'extra_fields_' . $extraField['id'] . '_' . $dynamicFieldModelId;
            $attributeNames[$id] = $extraField['label'];
            $rules[$id]          = 'max:500';
            if ($extraField['required'] == 1) {
                $rules[$id] .= '|required';
            }
        }

        return [$attributeNames, $rules];
    }

    /**
     * @param  $data
     * @param  array $rules
     * @param  $record
     * @param  array $attributeNames
     * @return void
     */
    public function validateFormData($data, array $rules, $record, array $attributeNames): void
    {
        $validation = Validator::make(
            $data,
            $rules,
            $record->customMessages ?? [],
            $attributeNames,
        );

        if ($specialValidationExceptions = Input::get('specialValidationExceptions')) {
            $specialValidationExceptions = unserialize($specialValidationExceptions);
        }

        // validate for conditional rules
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
    }

    /**
     * @param  array $extraFields
     * @param  $data
     * @return array
     */
    public function resolveExtraFieldsData(array $extraFields, $data): array
    {
        if (isset($extraFields)) {
            foreach ($extraFields as &$extraField) {
                $dynamicFieldModelId = $extraField['dynamicFieldModelId'] ?? '';
                $id = 'extra_fields_' . $extraField['id'] . '_' . $dynamicFieldModelId;
                $extraField['value'] = $data[$id];
                unset($data[$id]);
            }
        }

        return [$extraFields, $data];
    }

    /**
     * @param  $record
     * @param  array $data
     * @return array
     */
    public function resolveBelongsToRelations($record, array $data): array
    {
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
        }

        return $data;
    }

    /**
     * @param  $record
     * @param  array $data
     * @return object
     */
    public function resolveBelongsToManyRelations($record, array $data): object
    {
        // Resolve belongsToMany relations
        foreach ($record->belongsToMany as $relationName => $definition) {
            if (!isset($data[$relationName])) {
                continue;
            }

            if ($record->id && $data[$relationName] == '') {
                $record->$relationName()->detach();
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

        return $record;
    }

    /**
     * @param  $record
     * @param  array $data
     * @return mixed
     */
    public function saveNewRecord($record, array $data)
    {
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
        return $record;
    }

    /**
     * @param  $model
     * @param  $pivotData
     * @param  array $rules
     * @param  bool $isHasManyRelation
     * @param  $record
     * @param  $relationName
     * @return void
     */
    public function handlePivotRelationValidation($model, &$pivotData, array $rules, bool $isHasManyRelation, $record, $relationName): void
    {
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
    }

    /**
     * @param  $widget
     * @return array
     */
    public function gatherAllCardsAndFieldsInArrays($widget): array
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
                list($mainCardVariablesToPass, $sheetCardVariablesToPass) = $this->handleCards($field, $key, $mainCardVariablesToPass, $sheetCardVariablesToPass);
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
                list($value, $mainCardVariablesToPass, $continue) = $this->retrieveTheValueForTheField($key, $widget, $field, $mainCardVariablesToPass);

                if ($continue) {
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

                if ($field['type'] == 'richeditor' || !empty($field['formBuilder']['raw'])) {
                    $newField['raw'] = true;
                }

                $fieldsToPass[$field['formBuilder']['card']][] = $newField;
            }
        }

        return [$mainCardVariablesToPass, $sheetCardVariablesToPass, $fieldsToPass];
    }

    /**
     * @param  $fieldsToPass
     * @param  $mainCardVariablesToPass
     * @param  $sheetCardVariablesToPass
     * @return array
     */
    public function setTheAppropriateFieldArrayForEachOfTheCards($fieldsToPass, $mainCardVariablesToPass, $sheetCardVariablesToPass): array
    {
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
                        $titleFields[] = $field;
                    } else if ($field['position'] == 'subtitle') {
                        $mainCardVariablesToPass['subtitleFields'][] = $field;
                    } else if ($field['position'] == 'details') {
                        $mainCardVariablesToPass['fields'][] = $field;
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

        return [$mainCardVariablesToPass, $sheetCardVariablesToPass];
    }

    /**
     * @param  $field
     * @param  $key
     * @param  array $mainCardVariablesToPass
     * @param  array $sheetCardVariablesToPass
     * @return array
     */
    public function handleCards($field, $key, array $mainCardVariablesToPass, array $sheetCardVariablesToPass): array
    {
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

        return [$mainCardVariablesToPass, $sheetCardVariablesToPass];
    }

    /**
     * @param  $key
     * @param  $widget
     * @param  $field
     * @param  $mainCardVariablesToPass
     * @return array
     */
    public function retrieveTheValueForTheField($key, $widget, $field, $mainCardVariablesToPass): array
    {
        // retrieve the value for the field
        $value    = '';
        $continue = false;

        if (is_object($widget->model->{$key}) && array_key_exists('nameFrom', $field) && isset($widget->model->{$key}->{$field['nameFrom']})) { // relation fields
            $value = $widget->model->{$key}->{$field['nameFrom']};
        } else if (is_a($widget->model->{$key}, 'Illuminate\Database\Eloquent\Collection')
            && count($widget->model->{$key}) > 0
            && array_key_exists('nameFrom', $field)
        ) { // belongs to many with no pivot data
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
        } else if (isset($widget->model->{$key})) { // regular fields
            $value = $widget->model->{$key};
        } else {
            $continue = true;
        }

        return [$value, $mainCardVariablesToPass, $continue];
    }

    public function updateNullStringToNull($data) {
        if (!is_array($data)) {
            return $data;
        }

        return array_map(function($value) {
            return $value === "null" ? null : $value;
        }, $data);
    }

    public function getPreviousUrl($formUniqueId) {
        $key = "previous_url_" . $formUniqueId;

        if (!Session::has($key)) {
            Session::put($key, \Url::previous());
            return \Url::previous();
        }

        return Session::get($key);
    }

}
