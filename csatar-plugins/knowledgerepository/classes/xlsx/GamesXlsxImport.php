<?php

namespace Csatar\KnowledgeRepository\Classes\Xlsx;

use Csatar\Csatar\Models\AgeGroup;
use Csatar\KnowledgeRepository\Models\Duration;
use Csatar\KnowledgeRepository\Models\Game;
use Csatar\KnowledgeRepository\Models\GameDevelopmentGoal;
use Csatar\KnowledgeRepository\Models\GameType;
use Csatar\KnowledgeRepository\Models\Headcount;
use Csatar\KnowledgeRepository\Models\Location;
use Csatar\KnowledgeRepository\Models\Tool;
use Csatar\KnowledgeRepository\Models\TrialSystem;
use Db;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Lang;


class GamesXlsxImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithMultipleSheets, SkipsUnknownSheets
{
    use Importable, RemembersRowNumber, SkipsFailures, XlsxImportHelper;

    private $associationId;
    private $uploaderCsatarCode;
    private $approverCsatarCode;

    private $overwrite = false;

    public $errors = [];

    public function __construct($associationId, $uploaderCsatarCode, $approverCsatarCode, $overwrite = false)
    {
        $this->associationId      = $associationId;
        $this->uploaderCsatarCode = $uploaderCsatarCode;
        $this->approverCsatarCode = $approverCsatarCode;
        $this->overwrite          = $overwrite;
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

    public function model(array $row)
    {
        if (!isset($row['jatek_neve'])) {
            return null;
        }

        $game = Game::where('association_id', $this->associationId)
            ->where('name', $row['jatek_neve'])
            ->first();

        if (!$this->overwrite && !empty($game)) {
            $this->errors[$this->getRowNumber()][] = Lang::get('csatar.knowledgerepository::lang.plugin.admin.game.gameAlreadyExists', ['name' => $row['jatek_neve']]);
            return;
        }

        if (empty($game)) {
            $game = new Game();
            $game->association_id = $this->associationId;
            $game->name           = $row['jatek_neve'];
        }

        $pivotRelationIds = [];

        $pivotRelationIds['headcounts'] = $this->getModelIds(null, $row['letszam'], Headcount::class, 'description');

        $pivotRelationIds['durations'] = $this->getModelIds(null, $row['idotartam'], Duration::class, 'name');

        $pivotRelationIds['age_groups'] = $this->getModelIds(null, $row['korosztaly'], AgeGroup::class, 'name', 'association_id', $this->associationId);

        $pivotRelationIds['locations'] = $this->getModelIds(null, $row['helyszin'], Location::class, 'name');

        $pivotRelationIds['game_development_goals'] = $this->getModelIds(null, $row['cel'], GameDevelopmentGoal::class, 'name');

        $pivotRelationIds['game_types'] = $this->getModelIds(null, $row['tipus'], GameType::class, 'name');

        $pivotRelationIds['trial_systems'] = $this->getModelIds(null, $row['probarendszer'], TrialSystem::class, 'id_string', 'association_id', $this->associationId);

        $pivotRelationIds['tools'] = $this->getModelIds(null, $row['kellekek'], Tool::class, 'name', null, null, true);

        $pivotRelationIds = array_filter($pivotRelationIds, function ($value) {
            return $value !== null;
        });

        if (!empty($this->errors[$this->getRowNumber()])) {
            return;
        }

        $game->fill([
            'description' => $row['leiras'] ?? null,
            'link' => $row['link'] ?? null,
            'uploader_csatar_code' => $this->uploaderCsatarCode,
            'approver_csatar_code' => $this->approverCsatarCode,
            'approved_at' => $this->approverCsatarCode ? date('Y-m-d H:i:s') : null,
            'note' => $row['megjegyzes'],
        ]);

        $game->save();

        $this->syncRelations($game, $pivotRelationIds);
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function syncRelations(Game $game, array $pivotRelationIds)
    {
        foreach ($pivotRelationIds as $relationName => $relationIds) {
            $game->{$relationName}()->sync($relationIds);
        }
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

}
