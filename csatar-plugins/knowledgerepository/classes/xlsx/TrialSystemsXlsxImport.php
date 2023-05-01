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
use Lang;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithGroupedHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TrialSystemsXlsxImport implements OnEachRow, WithHeadingRow, WithGroupedHeadingRow, SkipsOnFailure, WithMultipleSheets, SkipsUnknownSheets
{
    use Importable, RemembersRowNumber, SkipsFailures;

    private $associationId;

    private $overwrite = false;

    private $effectiveKnowledgeOnly = false;

    public $errors = [];

    private $worksheetRaw;

    public function __construct($associationId, $overwrite = true, $effectiveKnowledgeOnly = false, $worksheetRaw)
    {
        $this->associationId = $associationId;
        $this->overwrite     = $overwrite;
        $this->effectiveKnowledgeOnly = $effectiveKnowledgeOnly;
        $this->worksheetRaw           = $worksheetRaw;
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

    public function onRow(Row $row)
    {
        $cellsArray = $row->toArray();

        if (!isset($cellsArray['id']) || (!isset($cellsArray['megnevezes']) && !$this->effectiveKnowledgeOnly)) {
            return null;
        }

        $trialSystem = TrialSystem::where('association_id', $this->associationId)
            ->where('id_string', $cellsArray['id'])
            ->withTrashed()
            ->first();

        if (!$this->overwrite && !empty($trialSystem)) {
            $this->errors[$row->getRowIndex()][] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystemAlreadyExists', ['id' => $cellsArray['id']]);
            return;
        }

        $this->convertEffectiveKnowledgeCellToHtml($cellsArray, $row);

        if (!empty($trialSystem) && $this->effectiveKnowledgeOnly) {
            $trialSystem->effective_knowledge = $cellsArray['effektiv_tudas'] ?? null;
            $trialSystem->save();
            return;
        }

        if (empty($trialSystem) && $this->effectiveKnowledgeOnly) {
            $this->errors[$row->getRowIndex()][] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.trialSystem.trialSystemDoesntExist', ['id' => $cellsArray['id']]);
            return;
        }

        if (empty($trialSystem)) {
            $trialSystem = new TrialSystem();
            $trialSystem->association_id = $this->associationId;
            $trialSystem->id_string      = $cellsArray['id'];
        }

        if (isset($cellsArray['kategoria'])) {
            $trialSystem->trial_system_category_id = $this->getModelIds($row, $cellsArray['kategoria'], TrialSystemCategory::class, 'name', null, null, true)[0] ?? null;
        }

        if (isset($cellsArray['tema'])) {
            $trialSystem->trial_system_topic_id = $this->getModelIds($row, $cellsArray['tema'], TrialSystemTopic::class, 'name', null, null, true)[0] ?? null;
        }

        if (isset($cellsArray['subtopic'])) {
            $trialSystem->trial_system_sub_topic_id = $this->getModelIds($row, $cellsArray['altema'], TrialSystemSubTopic::class, 'name', null, null, true)[0] ?? null;
        }

        if (isset($cellsArray['korosztaly'])) {
            $trialSystem->age_group_id = $this->getModelIds($row, $cellsArray['korosztaly'], AgeGroup::class, 'name', 'association_id', $this->associationId)[0] ?? null;
        }

        if (isset($cellsArray['tipus'])) {
            $trialSystem->trial_system_type_id = $this->getModelIds($row, $cellsArray['tipus'], TrialSystemType::class, 'name', null, null, true)[0] ?? null;
        }

        if (isset($cellsArray['proba'])) {
            $trialSystem->trial_system_trial_type_id = $this->getModelIds($row, $cellsArray['proba'], TrialSystemTrialType::class, 'name', null, null, true)[0] ?? null;
        }

        if (!empty($this->errors[$row->getRowIndex()])) {
            return;
        }

        $trialSystem->fill([
            'name' => $cellsArray['megnevezes'] ?? null,
            'for_patrols' => isset($cellsArray['orsi']) ? ($cellsArray['orsi'] == 'x' ? 1 : null) : null,
            'individual' => isset($cellsArray['egyeni']) ? ($cellsArray['egyeni'] == 'x' ? 1 : null) : null,
            'task' => isset($cellsArray['foglalkozas']) ? ($cellsArray['foglalkozas'] == 'x' ? 1 : null) : null,
            'obligatory' => isset($cellsArray['kotelezo']) ? ($cellsArray['kotelezo'] == 'x' ? 1 : null) : null,
            'note' => $cellsArray['megjegyzes'] ?? null,
            'effective_knowledge' => $cellsArray['effektiv_tudas'] ?? null,
        ]);

        $trialSystem->deleted_at = null;
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

    public function getModelIds($row, string $searchFor, string $modelName, string $columnName, string $secondaryColumnName = null, $secondaryColumnValue = null, bool $createIfNotFound = false): array
    {
        $searchFor = array_map('trim', explode('|', $searchFor));
        $searchFor = array_map('strtolower', $searchFor);
        $ids       = $modelName::whereIn(DB::raw('LOWER(' . $columnName . ')'), $searchFor)->when($secondaryColumnName, function ($query) use ($secondaryColumnName, $secondaryColumnValue) {
            $query->where($secondaryColumnName, $secondaryColumnValue);
        })->get();
        $unmatched = array_diff($searchFor, array_map('strtolower', $ids->pluck($columnName)->toArray()));
        if (!empty($unmatched) && !$createIfNotFound) {
            $modelNameForLangKey = (new \ReflectionClass($modelName))->getShortName();
            $this->errors[$row->getRowIndex()][] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.messages.cannotFind' . $modelNameForLangKey) . implode(', ', $unmatched);
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

    /**
     * @param $cellsArray
     * @param Row $row
     */
    public function convertEffectiveKnowledgeCellToHtml(&$cellsArray, Row $row)
    {
        $counter = 0;
        $effectiveKnowledgeColumnNumber = array_search('effektiv_tudas', array_keys($cellsArray));

        if ($effectiveKnowledgeColumnNumber === false) {
            return;
        }

        $worksheet = $this->worksheetRaw->getActiveSheet();

        foreach ($row->getCellIterator() as $cell) {
            if ($counter !== $effectiveKnowledgeColumnNumber) {
                $counter++;
                continue;
            }

            $coordinates = $cell->getCoordinate();
            $cellRaw     = $worksheet->getCell($coordinates);

            // Create a new spreadsheet object
            $newSpreadsheet = new Spreadsheet();

            // Convert to HTML
            $html = (new XlxsHtml($newSpreadsheet))->generateHtmlFromCell($worksheet, $cellRaw);
            $cellsArray['effektiv_tudas'] = $html;
            $counter++;
        }
    }

}
