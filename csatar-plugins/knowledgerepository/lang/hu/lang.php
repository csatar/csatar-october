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
                    'tools' => 'Kellékek',
                    'tool' => 'Kellék',
                    'headCounts' => 'Létszámok',
                    'headCount' => 'Létszám',
                    'durations' => 'Időtartamok',
                    'duration' => 'Időtartam',
                    'locations' => 'Helyszínek',
                    'location' => 'Helyszín',
                    'gameTypes' => 'Játék típusok',
                    'gameType' => 'Játék típus',
                    'methodologyType' => 'Módszertan típus',
                    'methodologyTypes' => 'Módszertan típusok',
                    'methodologyName' => 'Módszer neve',
                    'ageGroup' => 'Korosztály',
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
                'link' => 'Link',
                'Attachment' => 'Csatolmány',
                'sortOrder' => 'Sorszám',
                'version' => 'Verzió',
                'created_at' => 'Feltöltés Dátuma'
            ],
        ],
    ],
];
