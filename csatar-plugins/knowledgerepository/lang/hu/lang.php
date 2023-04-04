<?php return [
    'plugin' => [
        'name' => 'Tudástár',
        'description' => 'Tudástár plugin az RMCSSZ CSATÁR alkalmazás számára',
        'permissions' => [
            'manageKnowledgeRepository' => 'Tudástár kezelése',
        ],
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
                    'methodology' => 'Módszertan',
                    'ageGroup' => 'Korosztály',
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
                'link' => 'Link',
                'Attachment' => 'Csatolmány',
                'sortOrder' => 'Sorszám',
                'version' => 'Verzió',
                'created_at' => 'Feltöltés Dátuma',
                'forms' => 'Űrlapok',
                'obligatory' => 'Kötelező',
                'import' => 'Importálás',
                'row' => 'sor',
                'file' => 'Fájl',
            ],
            'game' => [
                'game' => 'Játék',
                'games' => 'Játékok',
                'name' => 'Játék neve',
                'uploader' => 'Feltöltő',
                'approver' => 'Jóváhagyó',
                'otherTools' => 'Egyéb kellékek',
                'attachements' => 'Csatolmányok',
                'uploadedAt' => 'Feltöltés dátuma',
                'approvedAt' => 'Jóváhagyás dátuma',
                'version' => 'Verzió',
                'ageGroupsComment' => 'A korosztályok csak a szövetség kiválasztása után választhatók ki.',
                'gameAlreadyExists' => 'Már létezik játék ezzel a névvel: :name!',
                'overwriteExistingGames' => 'Létező játékok felülírása. Ha be van jelölve, akkor a feltöltött játékok felülírják a már létező, azonos nevű játékokat!',
            ],
            'trialSystem' => [
                'trialSystem' => 'Próbarendszer',
                'trialSystems' => 'Próbarendszerek',
                'idString' => 'Azonosító',
                'ageGroup' => 'Korosztály',
                'trialSystemCategory' => 'Kategória',
                'trialSystemCategories' => 'Próbarendszer kategóriák',
                'trialSystemTopic' => 'Téma',
                'trialSystemTopics' => 'Próbarendszer témák',
                'trialSystemSubTopic' => 'Altéma',
                'trialSystemSubTopics' => 'Próbarendszer altémák',
                'trialSystemType' => 'Típus',
                'trialSystemTypes' => 'Próbarendszer típusok',
                'trialSystemTrialType' => 'Próba típus',
                'trialSystemTrialTypes' => 'Próbarendszer próba típusok',
                'forPatrols' => 'Őrsi',
                'individual' => 'Egyéni',
                'task' => 'Foglalkozás',
            ],
            'messages' => [
                'cannotFindHeadcount' => 'A következő létszám paraméter(ek) nem található(k): ',
                'cannotFindDuration' => 'A következő időtartam paraméter(ek) nem található(k): ',
                'cannotFindAgeGroup' => 'A következő korosztály paraméter(ek) nem található(k): ',
                'cannotFindLocation' => 'A következő helyszín paraméter(ek) nem található(k): ',
                'cannotFindGameDevelopmentGoal' => 'A következő játék fejlesztési cél paraméter(ek) nem található(k): ',
                'cannotFindGameType' => 'A következő játék típus paraméter(ek) nem található(k): ',
                'cannotFindTool' => 'A következő kellék paraméter(ek) nem található(k): ',
                'cannotFindTrialSystem' => 'A következő próbarendszer paraméter(ek) nem található(k): ',
                'errorsOccurred' => 'Az importálás során a következő hibák léptek fel: ',
                'importSuccessful' => 'Az importálás sikeresen megtörtént!',
            ],
        ],
        'components' => [
            'gameForm' => [
                'name' => 'Játék űrlap',
                'description' => 'Játék űrlap komponens',
            ],
            'methodologyForm' => [
                'name' => 'Módszertan űrlap',
                'description' => 'Módszertan űrlap komponens'
            ]
        ],
    ],
];
