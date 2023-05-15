<?php

namespace Csatar\Csatar\Classes;

use ApplicationException;
use Lang;
use Flash;
use Session;

class BackendExtensions
{

    public static function onDelete($controller, $methodToRunAfterDelete = null)
    {
        /*
         * Validate checked identifiers
         */
        $checkedIds = post('checked');

        if (!$checkedIds || !is_array($checkedIds) || !count($checkedIds)) {
            Flash::error(Lang::get('backend::lang.list.delete_selected_empty'));
            return $controller->listRefresh();
        }

        $modelName = $controller->listGetConfig('list')->modelClass;
        $model     = new $modelName();
        $query     = $model->newQuery();

        $query->whereIn($model->getKeyName(), $checkedIds);
        /*
         * Delete records
         */
        $records = $query->get();

        $deletedRecords = 0;
        $errors         = [];
        if ($records->count()) {
            foreach ($records as $record) {
                if (method_exists($record, 'canDelete') && !$record->canDelete()) {
                    $sessionKey = $record::getModelName() . $record->id;
                    $errors[]   = Session::pull($sessionKey, 'N/A');
                    continue;
                }

                if ($record->delete()) {
                    $deletedRecords++;
                    if ($methodToRunAfterDelete !== null) {
                        self::runAfterDelete($record, $methodToRunAfterDelete);
                    }
                } else {
                    $sessionKey = $record::getModelName() . $record->id;
                    $errors[]   = Session::pull($sessionKey, 'N/A');
                }
            }

            if (!empty($errors)) {
                $errorMessages = implode(', ', $errors);
                $message       = $deletedRecords > 0 ?
                    Lang::get('csatar.csatar::lang.plugin.admin.general.bulkDeletePartialSuccess', [
                        'deletedCount' => $deletedRecords,
                        'totalCount' => $records->count()
                    ]) : '';
                $message      .= Lang::get('csatar.csatar::lang.plugin.admin.general.bulkDeleteError');
                $message      .= $errorMessages;
                Flash::error($message);
            } else if ($deletedRecords > 0) {
                Flash::success(Lang::get('backend::lang.list.delete_selected_success'));
            }
        } else {
            Flash::error(Lang::get('backend::lang.list.delete_selected_empty'));
        }

        return $controller->listRefresh();
    }

    private static function runAfterDelete($record, $methodToRunAfterDelete){
        if (method_exists($record, $methodToRunAfterDelete)) {
            $record->$methodToRunAfterDelete();
        }
    }

}
