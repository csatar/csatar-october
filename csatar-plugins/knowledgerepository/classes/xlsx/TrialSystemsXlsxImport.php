<?php

namespace Csatar\KnowledgeRepository\Classes\Xlsx;

use Csatar\Csatar\Models\AgeGroup;
use Csatar\KnowledgeRepository\Models\TrialSystem;
use Csatar\KnowledgeRepository\Models\TrialSystemCategory;
use Csatar\KnowledgeRepository\Models\TrialSystemSubTopic;
use Csatar\KnowledgeRepository\Models\TrialSystemTopic;
use Csatar\KnowledgeRepository\Models\TrialSystemTrialType;
use Csatar\KnowledgeRepository\Models\TrialSystemType;
use Db;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Lang;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithGroupedHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;


class TrialSystemsXlsxImport implements ToModel, WithHeadingRow, WithGroupedHeadingRow, WithValidation, SkipsOnFailure, WithMultipleSheets, SkipsUnknownSheets
{
    use Importable, RemembersRowNumber, SkipsFailures;

    private $associationId;

    private $overwrite = false;

    private $effectiveKnowledgeOnly = false;

    public $errors = [];

    public function __construct($associationId, $overwrite = true, $effectiveKnowledgeOnly = false)
    {
        $this->associationId = $associationId;
        $this->overwrite = $overwrite;
        $this->effectiveKnowledgeOnly = $effectiveKnowledgeOnly;
    }

    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }

//    public function bindValue(Cell $cell, $value)
//    {
//        if (is_numeric($value)) {
//            $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);
//
//            return true;
//        }
//
//        // else return default behavior
//        return parent::bindValue($cell, $value);
//    }

//    public function onRow(Row $row)
//    {
//        $cellsArray = $row->toArray();
//        foreach ($row->getCellIterator() as $cell) {
//            $styles = $cell->getStyle();
//            $value = $cell->getValue();
//            $header = $cell->getCoordinate();
//            $combined = [
//                'value' => $value,
//                'styles' => $styles,
//            ];
//        }
//    }

//    public function getCellStyles($cell)
//    {
//        $getStyle = $cell->getStyle();
//    }

    public function model(array $row)
    {
        if (!isset($row['id']) || (!isset($row['megnevezes']) && !$this->effectiveKnowledgeOnly) ) {
            return null;
        }

        $trialSystem = TrialSystem::where('association_id', $this->associationId)
            ->where('id_string', $row['id'])
            ->first();

        if (!$this->overwrite && !empty($trialSystem)) {
            $this->errors[$this->getRowNumber()][] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystemAlreadyExists', ['id' => $row['id']]);
            return;
        }

        if (!empty($trialSystem) && $this->effectiveKnowledgeOnly) {
            $trialSystem->effective_knowledge = $row['effektiv_tudas'] ? $this->replaceNewLineWithBr($row['effektiv_tudas']) : null;
            $trialSystem->save();
            return;
        }

        if (empty($trialSystem) && $this->effectiveKnowledgeOnly) {
            $this->errors[$this->getRowNumber()][] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystemDoesntExist', ['id' => $row['id']]);
            return;
        }

        if (empty($trialSystem)) {
            $trialSystem = new TrialSystem();
            $trialSystem->association_id = $this->associationId;
            $trialSystem->id_string = $row['id'];
        }

        if (isset($row['kategoria'])) {
            $trialSystem->trial_system_category_id = $this->getModelIds($row['kategoria'], TrialSystemCategory::class, 'name', null, null, true)[0] ?? null;
        }

        if (isset($row['tema'])) {
            $trialSystem->trial_system_topic_id = $this->getModelIds($row['tema'], TrialSystemTopic::class, 'name', null, null, true)[0]  ?? null;
        }

        if (isset($row['subtopic'])) {
            $trialSystem->trial_system_sub_topic_id = $this->getModelIds($row['altema'], TrialSystemSubTopic::class, 'name', null, null, true)[0] ?? null;
        }

        if (isset($row['korosztaly'])) {
            $trialSystem->age_group_id = $this->getModelIds($row['korosztaly'], AgeGroup::class, 'name', 'association_id', $this->associationId)[0] ?? null;
        }

        if (isset($row['tipus'])) {
            $trialSystem->trial_system_type_id = $this->getModelIds($row['tipus'], TrialSystemType::class, 'name', null, null, true)[0] ?? null;
        }

        if (isset($row['proba'])) {
            $trialSystem->trial_system_trial_type_id = $this->getModelIds($row['proba'], TrialSystemTrialType::class, 'name', null, null, true)[0] ?? null;
        }

        if (!empty($this->errors[$this->getRowNumber()])) {
            return;
        }

        $trialSystem->fill([
            'name' => $row['megnevezes'] ?? null,
            'for_patrols' => $row['orsi'] == 'x' ? 1 : null,
            'individual' => $row['egyeni'] == 'x' ? 1 : null,
            'task' => $row['foglalkozas'] == 'x' ? 1 : null,
            'obligatory' => $row['kotelezo'] == 'x' ? 1 : null,
            'note' => $row['megjegyzes'] ?? null,
            'effective_knowledge' => $row['effektiv_tudas'] ? $this->replaceNewLineWithBr($row['effektiv_tudas']) : null,
        ]);

        $trialSystem->save();
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function syncRelations(TrialSystem $trialSystem, array $pivotRelationIds)
    {
        foreach ($pivotRelationIds as $relationName => $relationIds) {
            $trialSystem->{$relationName}()->sync($relationIds);
        }
    }

    public function getModelIds(string $searchFor, string $modelName, string $columnName, string $secondaryColumnName = null, $secondaryColumnValue = null, bool $createIfNotFound = false): array
    {
        $searchFor = array_map('trim', explode('|', $searchFor));
        $searchFor = array_map('strtolower', $searchFor);
        $ids = $modelName::whereIn(DB::raw('LOWER(' . $columnName . ')'), $searchFor)->when($secondaryColumnName, function ($query) use ($secondaryColumnName, $secondaryColumnValue) {
            $query->where($secondaryColumnName, $secondaryColumnValue);
        })->get();
        $unmatched = array_diff($searchFor, array_map('strtolower', $ids->pluck($columnName)->toArray()));
        if (!empty($unmatched) && !$createIfNotFound) {
            $modelNameForLangKey = (new \ReflectionClass($modelName))->getShortName();
            $this->errors[$this->getRowNumber()][] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.messages.cannotFind' . $modelNameForLangKey) . implode(', ', $unmatched);
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

    public function rules(): array
    {
        return [];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!empty($this->errors) && isset($this->errors[$this->getRowNumber()])) {
                $validator->errors()->add($this->getRowNumber(), $this->errors[$this->getRowNumber()]);
            }
        });
    }

    public function replaceNewLineWithBr($string)
    {
        return str_replace("\n", "<br>", $string);
    }
}
