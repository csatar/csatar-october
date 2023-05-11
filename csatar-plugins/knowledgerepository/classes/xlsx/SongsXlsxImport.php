<?php

namespace Csatar\KnowledgeRepository\Classes\Xlsx;

use Csatar\Csatar\Models\AgeGroup;
use Csatar\KnowledgeRepository\Models\FolkSongRhythm;
use Csatar\KnowledgeRepository\Models\FolkSongType;
use Csatar\KnowledgeRepository\Models\Region;
use Csatar\KnowledgeRepository\Models\Song;
use Csatar\KnowledgeRepository\Models\SongType;
use Csatar\KnowledgeRepository\Models\TrialSystem;
use Db;
use Flash;
use Lang;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithGroupedHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SongsXlsxImport implements OnEachRow, WithHeadingRow, WithGroupedHeadingRow, SkipsOnFailure, WithMultipleSheets, SkipsUnknownSheets
{
    use Importable, RemembersRowNumber, SkipsFailures;

    private $associationId;

    private $uploaderCsatarCode;

    private $approverCsatarCode;

    private $overwrite = false;

    public $errors = [];

    private $worksheetRaw;

    public function __construct($associationId, $overwrite, $richTextColumns, $worksheetRaw, $uploaderCsatarCode, $approverCsatarCode)
    {
        $this->associationId      = $associationId;
        $this->overwrite          = $overwrite;
        $this->richTextColumns    = $richTextColumns;
        $this->worksheetRaw       = $worksheetRaw;
        $this->uploaderCsatarCode = $uploaderCsatarCode;
        $this->approverCsatarCode = $approverCsatarCode ?? null;
        $this->approved_at        = $approverCsatarCode ? date('Y-m-d H:i:s') : null;
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

        if (!isset($cellsArray['sorszam']) || !isset($cellsArray['dal_cim'])) {
            return null;
        }

        $song = Song::where('association_id', $this->associationId)
            ->where('title', str_replace("\n", "", $cellsArray['dal_cim']))
            ->first();

        if (!$this->overwrite && !empty($song)) {
            $this->errors[$row->getRowIndex()][] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.song.songAlreadyExists', ['title' => $cellsArray['dal_cim']]);
            return;
        }

        $this->convertRichTextToHtml($cellsArray, $row, $this->richTextColumns);

        if (empty($song)) {
            $song = new Song();
            $song->association_id = $this->associationId;
            $song->title          = $cellsArray['dal_cim'];
        }

        $song->song_type_id = $this->getModelIds($row, $cellsArray['dal_tipusa'] ?? '', SongType::class, 'name', null, null, true)[0] ?? null;

        $song->region_id = $this->getModelIds($row, $cellsArray['tajegyseg'] ?? '', Region::class, 'name', null, null, true)[0] ?? null;

        $song->folk_song_type_id = $this->getModelIds($row, $cellsArray['tipus'] ?? '', FolkSongType::class, 'name', null, null, true)[0] ?? null;

        $song->rhythm_id = $this->getModelIds($row, $cellsArray['ritmus'] ?? '', FolkSongRhythm::class, 'name', null, null, true)[0] ?? null;

        $pivotRelationIds = [];

        $pivotRelationIds['agegroups'] = $this->getModelIds($row, $cellsArray['korosztaly'] ?? '', AgeGroup::class, 'name', 'association_id', $this->associationId) ?? null;

        $pivotRelationIds['trial_systems'] = $this->getModelIds($row, $cellsArray['probarendszer'] ?? '', TrialSystem::class, 'name', null, null) ?? null;

        if (!empty($this->errors[$row->getRowIndex()])) {
            return;
        }

        $song->fill([
            'author' => $cellsArray['szerzo'] ?? null,
            'text' => $cellsArray['szoveg'] ?? null,
            'link' => $cellsArray['link'] ?? null,
            'note' => $cellsArray['megjegyzes'] ?? null,
            'uploader_csatar_code' => $this->uploaderCsatarCode ?? null,
            'approver_csatar_code' => $this->approverCsatarCode ?? null,
            'approved_at' => $this->approverCsatarCode ? date('Y-m-d H:i:s') : null,
        ]);

        $song->save();

        $this->syncRelations($song, $pivotRelationIds);
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function syncRelations(Song $song, array $pivotRelationIds)
    {
        foreach ($pivotRelationIds as $relationName => $relationIds) {
            $song->{$relationName}()->sync($relationIds);
        }
    }

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
    public function convertRichTextToHtml(&$cellsArray, Row $row, string $richTextColumns)
    {
        if (empty($richTextColumns)) {
            return;
        }

        $richTextColumns = array_map('trim', explode(',', $richTextColumns));
        $richTextColumns = array_map('str_slug', $richTextColumns);
        $counter         = 0;
        $richTextColumnsNumbers = [];
        foreach ($richTextColumns as $richTextColumn) {
            $richTextColumnsNumbers[$richTextColumn] = array_search($richTextColumn, array_keys($cellsArray));
        }

        if (in_array(false, $richTextColumnsNumbers)) {
            $columnsNotFound = implode(', ', array_keys(array_filter($richTextColumnsNumbers, function ($item) use ($richTextColumnsNumbers) {
                return $item === false;
            })));
            return Flash::error(Lang::get('csatar.knowledgerepository::lang.plugin.admin.general.richTextColumnsNotFound', ['columns' => $columnsNotFound]));
        }

        $worksheet = $this->worksheetRaw->getActiveSheet();

        foreach ($row->getCellIterator() as $cell) {
            if (!in_array($counter, $richTextColumnsNumbers)) {
                $counter++;
                continue;
            }

            $coordinates = $cell->getCoordinate();
            $cellRaw     = $worksheet->getCell($coordinates);

            // Create a new spreadsheet object
            $newSpreadsheet = new Spreadsheet();

            // Convert to HTML
            $html = (new XlxsHtml($newSpreadsheet))->generateHtmlFromCell($worksheet, $cellRaw);
            $cellsArray[array_search($counter, $richTextColumnsNumbers)] = $html;
            $counter++;
        }
    }

}
