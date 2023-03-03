<?php namespace Csatar\KnowledgeRepository\Updates;

use Csatar\KnowledgeRepository\Models\AccidentRiskLevel;
use Csatar\KnowledgeRepository\Models\GameDevelopmentGoal;
use Csatar\KnowledgeRepository\Models\MethodologyType;
use Csatar\KnowledgeRepository\Models\Tool;
use Csatar\KnowledgeRepository\Models\Headcount;
use Csatar\KnowledgeRepository\Models\Duration;
use Csatar\KnowledgeRepository\Models\Location;
use Csatar\KnowledgeRepository\Models\GameType;
use Csatar\Forms\Models\Form;
use Db;
use Seeder;

class SeederData extends Seeder
{
    public const DATA = [
        'gameDevelopmentGoals' => [
            [
                'name' => 'Ismerkedős',
                'sort_order' => 1,
            ],
            [
                'name' => 'Bemelegítés, ráhangolódás',
                'sort_order' => 2,
            ],
            [
                'name' => 'Energiaszínt felemeléséhez',
                'sort_order' => 3,
            ],
            [
                'name' => 'Csoport és párképző játék',
                'sort_order' => 4,
            ],
            [
                'name' => 'Tábortűzi játékok',
                'sort_order' => 5,
            ],
            [
                'name' => 'Élmény és történt felidézéséhez',
                'sort_order' => 6,
            ],
            [
                'name' => 'Bizalmi játékok',
                'sort_order' => 7,
            ],
            [
                'name' => 'Együttmüködést fejlesztő',
                'sort_order' => 8,
            ],
            [
                'name' => 'Érzések, empátiát fejlesztő',
                'sort_order' => 9,
            ],
            [
                'name' => 'Kommunikációs készséget fejlesztő',
                'sort_order' => 10,
            ],
            [
                'name' => 'Konfliktus kezelő',
                'sort_order' => 11,
            ],
        ],
        'accidentRiskLevels' => [
            [
                'name' => 'Alacsony',
                'sort_order' => 1,
            ],
            [
                'name' => 'Közepes',
                'sort_order' => 2,
            ],
            [
                'name' => 'Magas',
                'sort_order' => 3,
            ],
        ],
        'tools' => [
            [
                'name' => 'Nincs kellék',
                'approved' => true,
            ],
            [
                'name' => 'Papír',
                'approved' => true,
            ],
            [
                'name' => 'Golyóstoll',
                'approved' => true,
            ],
            [
                'name' => 'Olló',
                'approved' => true,
            ],
            [
                'name' => 'Ragasztó',
                'approved' => true,
            ],
            [
                'name' => 'Színes papír',
                'approved' => true,
            ],
            [
                'name' => 'Zsineg/szallag/spárga',
                'approved' => true,
            ],
            [
                'name' => '(Nyak)kendő',
                'approved' => true,
            ],
            [
                'name' => 'Dobókocka',
                'approved' => true,
            ],
            [
                'name' => 'Labda',
                'approved' => true,
            ],
            [
                'name' => 'Lavór',
                'approved' => true,
            ],
            [
                'name' => 'Egyéb',
                'approved' => true,
            ],
        ],
        'headCounts' => [
            [
                'description' => '2-8 fő',
                'min' => 2,
                'max' => 8,
                'note' => '(őrsi találkozó)',
                'sort_order' => 1,
            ],
            [
                'description' => '9-15 fő',
                'min' => 9,
                'max' => 15,
                'note' => '(őrsi és/vagy raji találkozó)',
                'sort_order' => 2,
            ],
            [
                'description' => '16-29 fő',
                'min' => 16,
                'max' => 29,
                'note' => '(raji létszámhoz)',
                'sort_order' => 3,
            ],
            [
                'description' => '30+ fő',
                'min' => 31,
                'max' => 100,
                'note' => '(csapatlétszámhoz)',
                'sort_order' => 4,
            ]
        ],
        'durations' => [
            [
                'name' => '1-5 perc',
                'min' => 1,
                'max' => 5,
            ],
            [
                'name' => '6-10 perc',
                'min' => 6,
                'max' => 10,
            ],
            [
                'name' => '7-15 perc',
                'min' => 7,
                'max' => 15,
            ],
            [
                'name' => '16-20 perc',
                'min' => 16,
                'max' => 20,
            ],
            [
                'name' => '21-25 perc',
                'min' => 21,
                'max' => 25,
            ],
            [
                'name' => '26-35 perc',
                'min' => 26,
                'max' => 35,
            ],
            [
                'name' => '36-50 perc',
                'min' => 36,
                'max' => 50,
            ],
            [
                'name' => '51 - 75 perc',
                'min' => 51,
                'max' => 75,
            ],
            [
                'name' => '76 - 90 perc',
                'min' => 76,
                'max' => 90,
            ],
        ],
        'locations' => [
            [
                'name' => 'Kültéri - mező',
                'sort_order' => 1,
            ],
            [
                'name' => 'Kültéri - erdő',
                'sort_order' => 2,
            ],
            [
                'name' => 'Kültéri - udvar',
                'sort_order' => 3,
            ],
            [
                'name' => 'Beltéri - terem',
                'sort_order' => 4,
            ],
            [
                'name' => 'Beltéri - berendezett (székek, asztalok)',
                'sort_order' => 5,
            ],
            [
                'name' => 'Beltéri - okos (projektor, áramellátás)',
                'sort_order' => 6,
            ],
            [
                'name' => 'Virtuális',
                'sort_order' => 7,
            ],
            [
                'name' => 'Bárhol alkalmazható',
                'sort_order' => 8,
            ],
        ],
        'gameTypes' => [
            [
                'name' => 'Elnemmozdulós',
                'sort_order' => 1,
            ],
            [
                'name' => 'Szorakoztató',
                'sort_order' => 2,
            ],
            [
                'name' => 'Kevés mozgást igénylő',
                'sort_order' => 3,
            ],
            [
                'name' => 'Koncentrációs, mozgós',
                'sort_order' => 4,
            ],
            [
                'name' => 'Erőkifejtő, sok mozgás igénylő',
                'sort_order' => 5,
            ],
            [
                'name' => 'Ez alapján nem besorolható',
                'sort_order' => 6,
            ],
        ],
        'methodologyTypes' => [
            [
                'name' => 'új (eddig az őrs még sosem találkozott az átadott anyaggal)',
                'sort_order' => 1
            ],
            [
                'name' => 'régi/ismétlés (ismétlés, az őrs már találkozott a megnevezett anyaggal)',
                'sort_order' => 2
            ],
            [
                'name' => 'mindkettő',
                'sort_order' => 3
            ],
        ]
        'forms' => [
            [
                'title' => 'Játék',
                'model' => 'Csatar\KnowledgeRepository\Models\Game',
            ],
        ],
    ];

    public function run()
    {
        // Game Development Goals
        foreach ($this::DATA['gameDevelopmentGoals'] as $gameDevelopmentGoalData) {
            $gameDevelopmentGoal = GameDevelopmentGoal::firstOrNew([
                'name' => $gameDevelopmentGoalData['name'],
            ]);
            $gameDevelopmentGoal->sort_order = $gameDevelopmentGoalData['sort_order'];
            $gameDevelopmentGoal->save();
        }

        // Accident Risk Levels
        foreach ($this::DATA['accidentRiskLevels'] as $accidentRiskLevelData) {
            $accidentRiskLevel = AccidentRiskLevel::firstOrNew([
                'name' => $accidentRiskLevelData['name'],
            ]);
            $accidentRiskLevel->sort_order = $accidentRiskLevelData['sort_order'];
            $accidentRiskLevel->save();
        }

        // Tools
        foreach ($this::DATA['tools'] as $toolData) {
            $tool = Tool::firstOrNew([
                'name' => $toolData['name'],
            ]);
            $tool->is_approved = $toolData['approved'];
            $tool->save();
        }

        // Head Counts
        foreach ($this::DATA['headCounts'] as $headCountData) {
            $headCount = Headcount::firstOrNew([
                'description' => $headCountData['description'],
            ]);
            $headCount->min = $headCountData['min'];
            $headCount->max = $headCountData['max'];
            $headCount->note = $headCountData['note'];
            $headCount->sort_order = $headCountData['sort_order'];
            $headCount->save();
        }

        // Durations
        foreach ($this::DATA['durations'] as $durationData) {
            $duration = Duration::firstOrNew([
                'name' => $durationData['name'],
            ]);
            $duration->min = $durationData['min'];
            $duration->max = $durationData['max'];
            $duration->save();
        }

        // Locations
        foreach ($this::DATA['locations'] as $locationData) {
            $location = Location::firstOrNew([
                'name' => $locationData['name'],
            ]);
            $location->sort_order = $locationData['sort_order'];
            $location->save();
        }

        // Game Types
        foreach ($this::DATA['gameTypes'] as $gameTypeData) {
            $gameType = GameType::firstOrNew([
                'name' => $gameTypeData['name'],
            ]);
            $gameType->sort_order = $gameTypeData['sort_order'];
            $gameType->save();
        }

        // Methodology Types

        foreach ($this::DATA['methodologyTypes'] as $methodologyTypeData) {
            $methodologyType = MethodologyType::firstOrNew([
                'name' => $methodologyTypeData['name'],
            ]);
            $methodologyType->sort_order = $methodologyTypeData['sort_order'];
            $methodologyType->save();

        // Forms
        foreach ($this::DATA['forms'] as $formData) {
            $form = Form::firstOrNew($formData);
            $form->save();
        }
    }
}
