<?php namespace Csatar\KnowledgeRepository\Updates;

use Csatar\KnowledgeRepository\Models\AccidentRiskLevel;
use Csatar\KnowledgeRepository\Models\GameDevelopmentGoal;
use Csatar\KnowledgeRepository\Models\Tool;
use Csatar\KnowledgeRepository\Models\HeadCount;
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
            $headCount = HeadCount::firstOrNew([
                'description' => $headCountData['description'],
            ]);
            $headCount->min = $headCountData['min'];
            $headCount->max = $headCountData['max'];
            $headCount->note = $headCountData['note'];
            $headCount->sort_order = $headCountData['sort_order'];
            $headCount->save();
        }
    }
}
