<?php

namespace Csatar\Csatar\Classes;

use App;
use Auth;
use BackendAuth;
use DateTime;
use Db;
use Event;
use Exception;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Csatar\Csatar\Classes\CsatarPivot;
use Request;
use Csatar\Csatar\Models\History as HistoryModelDefault;

class HistoryService
{
    public const SENSITIVE_FIELDS = ['password', 'password_confirmation', 'google_two_fa_secret_key'];

    public const PERMANENTLY_EXCLUDED_FIELDS = ['updated_at', 'persist_code', ];

    /**
     * @param array $models example: ['\RainLab\User\Models\User' => ['basicEvents' => true, 'relationEvents' => true, 'addDefaultHistoryRelation' => true, 'extraEvents' => [], 'extraEventListeners' => []]]
     * the extraEvents and extraEventListeners arrays should contain the event name as key and the method name of this class as value, example: ['model.afterSave' => 'historyAfterSave']
     * @return void
     */
    public static function init(array $models) {
        if (empty($models)) {
            return;
        }

        foreach ($models as $model => $params) {
            if (!class_exists($model)) {
                return;
            }

            if (HistoryService::isHistoryDisabled($model)) {
                return;
            }

            // set up bindings for the model
            $model::extend(function($model) use ($params) {
                $basicEvents    = $params['basicEvents'] ?? true;
                $relationEvents = $params['relationEvents'] ?? true;
                $customHistoryRelationName = $params['customHistoryRelationName'] ?? null;

                HistoryService::addHistoryRelationToModel($model, $customHistoryRelationName);

                HistoryService::bindEventsToModel($model, $basicEvents, $relationEvents);
                $extraEvents = $params['extraEvents'] ?? [];
                HistoryService::bindExtraEventsToModel($model, $extraEvents);
            });
            $extraEventListeners = $params['extraEventListeners'] ?? [];

            // set up extra event listeners
            HistoryService::setupExtraEventListener($extraEventListeners);
        }
    }

    public static function setupExtraEventListener($extraEventListeners) {
        if (empty($extraEventListeners)) {
            return;
        }

        foreach ($extraEventListeners as $event => $historyServiceMethod) {
            if (!method_exists(HistoryService::class, $historyServiceMethod)) {
                continue;
            }

            Event::listen($event, function ($model) use ($event, $historyServiceMethod) {
                call_user_func_array([HistoryService::class, $historyServiceMethod], [$model, $event]);
            });
        }
    }

    public static function bindExtraEventsToModel($model, $extraEvents) {
        if (empty($extraEvents)) {
            return;
        }

        foreach ($extraEvents as $event => $historyServiceMethod) {
            if (!method_exists(HistoryService::class, $historyServiceMethod)) {
                continue;
            }

            $model->bindEvent($event, function () use ($model, $event, $historyServiceMethod) {
                call_user_func_array([HistoryService::class, $historyServiceMethod], [$model, $event]);
            });
        }
    }

    public static function bindEventsToModel($model, $basicEvents = true, $relationEvents = true) {
        // MODEL EVENTS
        if ($basicEvents) {
            $model->bindEvent('model.afterSave', function () use ($model) {
                HistoryService::historyAfterSave($model);
            });

            $model->bindEvent('model.afterDelete', function () use ($model) {
                HistoryService::historyAfterDelete($model);
            });
        }

        if ($relationEvents) {
            // RELATIONS EVENTS
            // for attach one or many files, hasOne or hasMany, morphOne or morphMany relations
            $model->bindEvent('model.relation.add', function ($relationName, $relatedModel) use ($model) {
                // CONCLUSION:
                // 1. tested with attachOne and attachMany, it works as expected
                // 2. with has many it is not working as expected, for example when creating a new team under district event is not triggered, AND when changing the district of a team, the event it is also not triggered - we ignore this for now because these actions are recorded on the attached model
                // 3. with morphOne is not working as expected, when creating new content_page to team event is not triggered - we ignore this for now
                // 4. can't test with morphMany, currently we don't have any morphMany relation adding implemented that should be tracked - we ignore this for now
                HistoryService::historyRelationAdd($model, $relationName, $relatedModel);
            });
//
            // for detach one or many files, hasOne or hasMany, morphOne or morphMany relations
            $model->bindEvent('model.relation.remove', function ($relationName, $relatedModel) use ($model) {
                // CONCLUSION:
                // 1. tested with attachOne and attachMany, it works as expected
                // 2. with has many it is not working as expected, for example when deleting a team the event is not triggered, AND when changing the district of a team, the event it is also not triggered - we ignore this for now because these actions are recorded on the attached model
                // 3. can't test with morphOne, currently we don't have any morphOne relation removing implemented
                // 4. can't test with morphMany, currently we don't have any morphMany relation adding implemented that should be tracked
                HistoryService::historyRelationRemove($model, $relationName, $relatedModel);
            });

            // for attach belongsTo, morphTo relations
// $model->bindEvent('model.relation.associate', function ($relationName, $model) {
// });
            // CONCLUSION:
            // 1. tested with belongsTo, it is not working as expected, for example when changing currency of an association, the event it is not triggered AND when creating new mandateType for an association, the event it is not triggered - we ignore this for now because these actions are recorded with the updated event
            // 2. can not test with morphTo, currently we don't have any morphTo relation adding implemented that should be tracked
            // for detach belongsTo, morphTo relations, params: [$model->relationName, $model]
// $model->bindEvent('model.relation.dissociate', function ($relationName) {
// });
            // CONCLUSION:
            // 1. tested with belongsTo, it is not working as expected, for example when changing currency of an association, the event it is not triggered AND when deleting mandateType for an association, the event it is not triggered - we ignore this for now because these actions are recorded with the updated event
            // 2. can not test with morphTo, currently we don't have any morphTo relation removing implemented that should be tracked
            // when creating new mandateType for an association, the event it IS triggered for "parent" relation
            // for attach belongsToMany relations
            $model->bindEvent('model.relation.attach', function ($relationName, $parsedIds, $attributes) use ($model) {
                // CONCLUSION: works as expected
                HistoryService::historyRelationAttach($model, $relationName, $parsedIds, $attributes);
            });

            // for detach belongsToMany relations
            $model->bindEvent('model.relation.detach', function ($relationName, $parsedIds, $result) use ($model) {
                // CONCLUSION: works as expected
                HistoryService::historyRelationDetach($model, $relationName, $parsedIds, $result);
            });
        }
    }
    public static function historyGetUser()
    {
        if (Auth::check() && !App::runningInBackend()) {
            return Auth::getUser()->getKey();
        }

        return null;
    }

    public static function historyGetBackendUser()
    {
        if (BackendAuth::check() && (App::runningInBackend() || Auth::isImpersonator())) {
            return BackendAuth::getUser()->getKey();
        }

        return null;
    }

    public static function historyGetCastType($model, $attribute)
    {
        if (in_array($attribute, $model->getDates())) {
            return 'date';
        }

        return null;
    }

    public static function getHistoryRelationName($model)
    {
        $class = get_class($model);
        return defined("$class::HISTORY_RELATION_NAME") ? $model::HISTORY_RELATION_NAME : 'history';
    }

    public static function isHistoryDisabled($model)
    {
        return isset($model->historyDisabled) && $model->historyDisabled;
    }

    public static function historyAfterSave($model)
    {
        $historyRelationName   = self::getHistoryRelationName($model);
        $historyRelationObject = $model->{$historyRelationName}();
        $historyModel          = $historyRelationObject->getRelated();

        $modelClass        = $historyRelationObject->getMorphClass();
        $modelId           = $model->getKey();
        $relatedModelId    = null;
        $relatedModelClass = null;

        if ($model instanceof CsatarPivot) {
            $modelClass        = $model->getParentClass();
            $modelId           = $model->getAttribute($model->getForeignKey());
            $relatedModelClass = get_class($model);
            $relatedModelId    = $model->getAttribute($model->getOtherKey());
        }

        $toSave = [];
        $dirty  = $model->getDirty();
        foreach ($dirty as $attribute => $value) {
            if ((is_array($model->exlcudedFromHistory) && in_array($attribute, $model->exlcudedFromHistory))
                ||
                (is_array(HistoryService::PERMANENTLY_EXCLUDED_FIELDS) && in_array($attribute, HistoryService::PERMANENTLY_EXCLUDED_FIELDS))
                ||
                ($model->wasRecentlyCreated && empty($value))
            ) {
                continue;
            }

            $old_value = $model->getOriginal($attribute);

            if (is_array(HistoryService::SENSITIVE_FIELDS) && in_array($attribute, HistoryService::SENSITIVE_FIELDS)) {
                $value     = '***';
                $old_value = '***';
            }

            $toSave[] = [
                'fe_user_id' => self::historyGetUser(),
                'be_user_id' => self::historyGetBackendUser(),
                'model_type' => $modelClass,
                'model_id' => $modelId,
                'related_model_type' => $relatedModelClass,
                'related_model_id' => $relatedModelId,
                'attribute' => $attribute,
                'cast' => self::historyGetCastType($model, $attribute),
                'old_value' => $old_value,
                'new_value' => $value,
                'ip_address' => Request::ip(),
                'created_at' => new DateTime,
                'updated_at' => new DateTime
            ];
        }

        // Nothing to do
        if (!count($toSave)) {
            return;
        }

        Db::table($historyModel->getTable())->insert($toSave);
    }

    public static function historyAfterDelete($model)
    {
        $softDeletes = in_array(
            \October\Rain\Database\Traits\SoftDelete::class,
            class_uses_recursive(get_class($model))
        );

        if (is_array($model->exlcudedFromHistory) && in_array('deleted_at', $model->exlcudedFromHistory)
            ||
            (is_array(HistoryService::PERMANENTLY_EXCLUDED_FIELDS) && in_array('deleted_at', HistoryService::PERMANENTLY_EXCLUDED_FIELDS))
        ) {
            return;
        }

        $historyRelationName   = self::getHistoryRelationName($model);
        $historyRelationObject = $model->{$historyRelationName}();
        $historyModel          = $historyRelationObject->getRelated();

        $toSave[] = [
            'fe_user_id' => self::historyGetUser(),
            'be_user_id' => self::historyGetBackendUser(),
            'model_type' => $historyRelationObject->getMorphClass(),
            'model_id' => $model->getKey(),
            'attribute' => $softDeletes ? 'deleted_at' : 'HARD_DELETE',
            'old_value' => null,
            'new_value' => $softDeletes ? $model->deleted_at : new DateTime,
            'ip_address' => Request::ip(),
            'created_at' => new DateTime,
            'updated_at' => new DateTime
        ];

        Db::table($historyModel->getTable())->insert($toSave);
    }

    public static function historyRelationAdd($model, $relationName, $relatedModel) {
        $historyRelationName   = self::getHistoryRelationName($model);
        $historyRelationObject = $model->{$historyRelationName}();
        $historyModel          = $historyRelationObject->getRelated();

        $toSave[] = [
            'fe_user_id' => self::historyGetUser(),
            'be_user_id' => self::historyGetBackendUser(),
            'model_type' => $historyRelationObject->getMorphClass(),
            'model_id' => $model->getKey(),
            'related_model_type' => get_class($relatedModel),
            'related_model_id' => $relatedModel->getKey(),
            'attribute' => $relationName,
            'description' => 'Relation added',
            'old_value' => null,
            'new_value' => $relatedModel->getKey(),
            'ip_address' => Request::ip(),
            'created_at' => new DateTime,
            'updated_at' => new DateTime
        ];

        Db::table($historyModel->getTable())->insert($toSave);
    }

    public static function historyRelationRemove($model, $relationName, $relatedModel) {
        $historyRelationName   = self::getHistoryRelationName($model);
        $historyRelationObject = $model->{$historyRelationName}();
        $historyModel          = $historyRelationObject->getRelated();

        $toSave[] = [
            'fe_user_id' => self::historyGetUser(),
            'be_user_id' => self::historyGetBackendUser(),
            'model_type' => $historyRelationObject->getMorphClass(),
            'model_id' => $model->getKey(),
            'related_model_type' => $relatedModel->getMorphClass(),
            'related_model_id' => $relatedModel->getKey(),
            'attribute' => $relationName,
            'description' => 'Relation removed',
            'old_value' => $relatedModel->getKey(),
            'new_value' => null,
            'ip_address' => Request::ip(),
            'created_at' => new DateTime,
            'updated_at' => new DateTime
        ];

        Db::table($historyModel->getTable())->insert($toSave);
    }

    public static function historyRelationAttach($model, $relationName, $parsedIds, $attributes) {
        $historyRelationName   = self::getHistoryRelationName($model);
        $historyRelationObject = $model->{$historyRelationName}();
        $historyModel          = $historyRelationObject->getRelated();

        $relatedModelObject = $model->{$relationName}();
        $relatedModel       = $relatedModelObject->getRelated();

        $toSave = [];
        foreach ($parsedIds as $id) {
            $toSave[] = [
                'fe_user_id' => self::historyGetUser(),
                'be_user_id' => self::historyGetBackendUser(),
                'model_type' => $historyRelationObject->getMorphClass(),
                'model_id' => $model->getKey(),
                'related_model_type' => get_class($relatedModel),
                'related_model_id' => $id,
                'attribute' => $relationName,
                'description' => 'Relation attached',
                'old_value' => null,
                'new_value' => $id,
                'ip_address' => Request::ip(),
                'created_at' => new DateTime,
                'updated_at' => new DateTime
            ];
        }

        Db::table($historyModel->getTable())->insert($toSave);
    }

    public static function historyRelationDetach($model, $relationName, $parsedIds, $result) {
        $historyRelationName   = self::getHistoryRelationName($model);
        $historyRelationObject = $model->{$historyRelationName}();
        $historyModel          = $historyRelationObject->getRelated();

        $relatedModelObject = $model->{$relationName}();
        $relatedModel       = $relatedModelObject->getRelated();

        $toSave = [];
        if (!empty($parsedIds) || !is_array($parsedIds)) {
            return;
        }

        foreach ($parsedIds as $id) {
            $toSave[] = [
                'fe_user_id' => self::historyGetUser(),
                'be_user_id' => self::historyGetBackendUser(),
                'model_type' => $historyRelationObject->getMorphClass(),
                'model_id' => $model->getKey(),
                'related_model_type' => get_class($relatedModel),
                'related_model_id' => $id,
                'attribute' => $relationName,
                'description' => 'Relation detached',
                'old_value' => $id,
                'new_value' => null,
                'ip_address' => Request::ip(),
                'created_at' => new DateTime,
                'updated_at' => new DateTime
            ];
        }

        Db::table($historyModel->getTable())->insert($toSave);
    }

    public static function historyRecordEvent($model = null, $event = null){
        $historyModel = null;
        if (is_object($model)) {
            $historyRelationName   = self::getHistoryRelationName($model);
            $historyRelationObject = $model->{$historyRelationName}();
            $historyModel          = $historyRelationObject->getRelated();
        }

        $toSave[] = [
            'fe_user_id' => self::historyGetUser(),
            'be_user_id' => self::historyGetBackendUser(),
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->getKey() : null,
            'attribute' => 'event',
            'description' => $event ?? 'Event',
            'old_value' => null,
            'new_value' => null,
            'ip_address' => Request::ip(),
            'created_at' => new DateTime,
            'updated_at' => new DateTime
        ];

        Db::table($historyModel ? $historyModel->getTable() : HistoryModelDefault::getTable())->insert($toSave);
    }

    /**
     * @param $model
     * @return void
     */
    public static function addHistoryRelationToModel($model, $customHistoryRelationName = null): void
    {
        $historyRelationName = $customHistoryRelationName ?? self::getHistoryRelationName($model);
        $model->morphMany[$historyRelationName] = [
            \Csatar\Csatar\Models\History::class,
            'name'                      => 'model',
            'ignoreInPermissionsMatrix' => true,
        ];
    }
}
