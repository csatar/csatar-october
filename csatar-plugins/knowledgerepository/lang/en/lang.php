<?php return [
    'plugin' => [
        'name' => 'Knowledge Repository',
        'description' => 'Plugin for Knowledge Repository in the RMCSSZ\'s CSATÁR project',
        'admin' => [
            'menu' => [
                'knowledgeRepository' => [
                    'knowledgeRepository' => 'Knowledge Repository',
                    'testSystem' => 'Test System',
                    'games' => 'Games',
                    'songs' => 'Songs',
                    'workPlans' => 'Work Plans',
                    'methodologies' => 'Methodologies',
                ],
                'knowledgeRepositoryParameters' => [
                    'knowledgeRepositoryParameters' => 'Knowledge Repository Parameters',
                    'gameDevelopmentGoals' => 'Game Development Goals',
                    'accidentRiskLevels' => 'Accident Risk Levels',
                ],
            ],
            'general' => [
                'name' => 'Name',
                'note' => 'Note',
                'order' => 'Order',
            ],
        ],
    ],
];
