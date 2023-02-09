<?php return [
    'plugin' => [
        'name' => 'Tudástár',
        'description' => 'Tudástár plugin az RMCSSZ CSATÁR alkalmazás számára',
        'admin' => [
            'menu' => [
                'knowledgeRepository' => [
                    'knowledgeRepository' => 'Tudástár',
                    'testSystem' => 'Próbarendszer',
                    'games' => 'Játékok',
                    'songs' => 'Énekek',
                    'workPlans' => 'Munkatervek',
                    'methodologies' => 'Módszertan',
                ],
                'knowledgeRepositoryParameters' => [
                    'knowledgeRepositoryParameters' => 'Tudástár Paraméterek',
                    'gameDevelopmentGoals' => 'Játék fejlesztési célok',
                    'gameDevelopmentGoal' => 'Játék fejlesztési cél',
                    'accidentRiskLevels' => 'Baleseti kockázat szintek',
                    'accidentRiskLevel' => 'Baleseti kockázat szint',
                    'tools' => 'Eszközök',
                    'tool' => 'Eszköz',
                    'headCounts' => 'Létszámok',
                    'headCount' => 'Létszám',
                    'durations' => 'Időtartamok',
                    'duration' => 'Időtartam',
                    'locations' => 'Helyszínek',
                    'location' => 'Helyszín',
                    'gameTypes' => 'Játék típusok',
                    'gameType' => 'Játék típus',
                ],
            ],
            'general' => [
                'name' => 'Megnevezés',
                'note' => 'Megjegyzés',
                'order' => 'Sorrend',
                'description' => 'Leírás',
                'approverCsatarCode' => 'Jóváhagyó',
                'proposerCsatarCode' => 'Felterjesztő',
                'isApproved' => 'Jóváhagyva',
                'minute' => 'perc',
            ],
        ],
    ],
];