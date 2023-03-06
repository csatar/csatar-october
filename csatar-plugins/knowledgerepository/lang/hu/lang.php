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
                    'trialSystemTopic' => 'Próbarendszer téma',
                    'trialSystemTopics' => 'Próbarendszer témák',
                    'trialSystemSubTopic' => 'Próbarendszer altéma',
                    'trialSystemSubTopics' => 'Próbarendszer altémák',
                    'trialSystemType' => 'Próbarendszer típus',
                    'trialSystemTypes' => 'Próbarendszer típusok',
                    'trialSystemTrialType' => 'Próbarendszer próba típus',
                    'trialSystemTrialTypes' => 'Próbarendszer próba típusok',
                ],
            ],
            'general' => [
                'name' => 'Megnevezés',
                'note' => 'Megjegyzés',
                'order' => 'Sorrend',
                'description' => 'Leírás',
                'approverCsatarCode' => 'Jóváhagyó - igazolványszám',
                'proposerCsatarCode' => 'Felterjesztő - igazolványszám',
                'isApproved' => 'Jóváhagyva',
                'minute' => 'perc',
                'forms' => 'Űrlapok',
            ],
            'game' => [
                'game' => 'Játék',
                'name' => 'Játék neve',
                'uploader' => 'Feltöltő',
                'approver' => 'Jóváhagyó',
                'otherTools' => 'Egyéb kellékek',
                'attachements' => 'Csatolmányok',
                'uploadedAt' => 'Feltöltés dátuma',
                'approvedAt' => 'Jóváhagyás dátuma',
                'version' => 'Verzió',
                'ageGroupsComment' => 'A korosztályok csak a szövetség kiválasztása után választhatók ki.',
            ],
        ],
        'components' => [
            'gameForm' => [
                'name' => 'Játék űrlap',
                'description' => 'Játék űrlap komponens',
            ],
        ],
    ],
];
