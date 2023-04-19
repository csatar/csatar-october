<?php

namespace Csatar\Csatar\Classes;

use Csatar\Csatar\Models\AccidentLogRecord;
use Model;
use RainLab\User\Models\User;

class AccidentLogRigthsProvider
{
    public const GROUP_CODE_ADMIN = 'accident-log-admin';
    public const GROUP_CODE_ENTRY = 'accident-log-data-entry';

    public const ADMIN_RIGHTS = [
        "MODEL_GENERAL" => [
            "obligatory" => 0,
            "create" => 1,
            "read" => 1,
            "update" => 1,
            "delete" => 1,
            ],
    ];

    public const ENTRY_RIGHTS = [
        "MODEL_GENERAL" => [
            "obligatory" => 0,
            "create" => 1,
            "read" => 0,
            "update" => 0,
            "delete" => 0,
        ],
    ];

    public static function getAccidentLogRights(User $user, Model $record = null) {
        if (empty($user) || (!empty($record && !($record instanceof AccidentLogRecord)))) {
            return null;
        }

        $isOwn = $user->id == $record->user_id;

        if (self::isInGroup($user, self::GROUP_CODE_ADMIN)) {
            return collect(self::addRecordSpecificRights($record, self::ADMIN_RIGHTS, true, $isOwn));
        }

        if (self::isInGroup($user, self::GROUP_CODE_ENTRY)) {
            return collect(self::addRecordSpecificRights($record, self::ENTRY_RIGHTS, false, $isOwn));
        }
    }

    private static function isInGroup(User $user, string $code) {
        return $user->groups->where('code', $code)->count() > 0;
    }

    private static function addRecordSpecificRights(AccidentLogRecord $record, array $recordGeneralRights, bool $isAdmin = false, bool $isOwn = false) {
        if (empty($record) || empty($record->fillable)) {
            return $recordGeneralRights;
        }

        $rights         = $recordGeneralRights;
        $fields         = $record->fillable ?? [];
        $relationArrays = ['belongsTo', 'belongsToMany', 'hasMany', 'attachOne', 'attachMany', 'hasOne', 'morphTo', 'morphOne',
                           'morphMany', 'morphToMany', 'morphedByMany', 'attachMany', 'hasManyThrough', 'hasOneThrough'];

        foreach ($relationArrays as $relationArray) {
            $fields = array_merge($fields, array_keys($record->$relationArray));
        }

        self::filterFieldsForRealtionKeys($fields);

        if ($isOwn) {
            $rights['MODEL_GENERAL']['read']   = 2;
            $rights['MODEL_GENERAL']['update'] = 2;
        }

        foreach ($fields as $field) {
            //add rights for the record->field
            $rights[$field] = [
                "obligatory" => 0,
                'create' => 2,
                'read'   => ($isAdmin || $isOwn) ? 2 : 0,
                'update' => ($isAdmin || $isOwn) ? 2 : 0,
                'delete' => ($isAdmin || $isOwn) ? 2 : 0,
            ];
        }
        return $rights;
    }

    private static function filterFieldsForRealtionKeys(&$fields) {
        // filters the $fields array to remove relation key field, if relation field exists
        // for example removes: "currency_id" field if there is "currency" field in the array
        foreach ($fields as $key => $field) {
            if (substr($field, -3) === '_id') {
                $relationField = str_replace('_id', '', $field);
                if (in_array($relationField, $fields)) {
                    unset($fields[$key]);
                }
            }
        }
    }

    public static function isAccidentLogUser(User $user) {
        return self::inAccidentLogEntryGroup($user) || self::inAccidentLogAdminGroup($user);
    }

    public static function inAccidentLogEntryGroup(User $user) {
        return self::isInGroup($user, self::GROUP_CODE_ENTRY);
    }

    public static function inAccidentLogAdminGroup(User $user) {
        return self::isInGroup($user, self::GROUP_CODE_ADMIN);
    }
}
