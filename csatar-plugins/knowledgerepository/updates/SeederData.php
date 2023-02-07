<?php namespace Csatar\KnowledgeRepository\Updates;

use Csatar\KnowledgeRepository\Models\GameDevelopmentGoal;
use Seeder;
use Db;

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
        ]

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
    }
}
