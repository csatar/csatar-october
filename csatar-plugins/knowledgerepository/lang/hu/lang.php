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
                    'accidentRiskLevels' => 'Baleseti kockázat szintek',
                    'tools' => 'Eszközök',
                    'headCount' => 'Létszám',
                    'duration' => 'Időtartam',
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
