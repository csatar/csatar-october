<?php

namespace Csatar\KnowledgeRepository\Classes\Xlsx;

use DB;
trait XlsxImportHelper {
    public function getModelIds($row, string $searchFor, string $modelName, string $columnName, string $secondaryColumnName = null, $secondaryColumnValue = null, bool $createIfNotFound = false): array
    {
        if (empty($searchFor)) {
            return [];
        }

        $searchFor = array_map('trim', explode('|', $searchFor));
        $searchFor = array_map('strtolower', $searchFor);
        $ids       = $modelName::whereIn(DB::raw('LOWER(' . $columnName . ')'), $searchFor)->when($secondaryColumnName, function ($query) use ($secondaryColumnName, $secondaryColumnValue) {
            $query->where($secondaryColumnName, $secondaryColumnValue);
        })->get();
        $unmatched = array_diff($searchFor, array_map('strtolower', $ids->pluck($columnName)->toArray()));
        if (!empty($unmatched) && !$createIfNotFound) {
            $rowNumber = $row ? $row->getRowIndex() : $this->getRowNumber();
            $modelNameForLangKey = (new \ReflectionClass($modelName))->getShortName();
            $this->errors[$rowNumber][] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.messages.cannotFind' . $modelNameForLangKey) . implode(', ', $unmatched);
        }

        if ($createIfNotFound && !empty($unmatched)) {
            foreach ($unmatched as $unmatchedItem) {
                $model = new $modelName();
                $model->$columnName = $unmatchedItem;

                if ($secondaryColumnName) {
                    $model->$secondaryColumnName = $secondaryColumnValue;
                }

                $model->save();
                $ids->push($model);
            }
        }

        return $ids->pluck('id')->toArray();
    }
}