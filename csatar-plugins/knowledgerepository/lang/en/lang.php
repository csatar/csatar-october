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
                    'gameDevelopmentGoal' => 'Game Development Goal',
                    'accidentRiskLevels' => 'Accident Risk Levels',
                    'accidentRiskLevel' => 'Accident Risk Level',
                    'tools' => 'Tools',
                    'tool' => 'Tool',
                    'headCounts' => 'Head Counts',
                    'headCount' => 'Head Count',
                    'durations' => 'Durations',
                    'duration' => 'Duration',
                    'locations' => 'Locations',
                    'location' => 'Location',
                    'gameTypes' => 'Game Types',
                    'gameType' => 'Game Type',
                ],
            ],
            'general' => [
                'name' => 'Name',
                'note' => 'Note',
                'order' => 'Order',
                'description' => 'Description',
                'approverCsatarCode' => 'Approver',
                'proposerCsatarCode' => 'Proposer',
                'isApproved' => 'Approved',
                'minute' => 'minute',
            ],
        ],
    ],
];
