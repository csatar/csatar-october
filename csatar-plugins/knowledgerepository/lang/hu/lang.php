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
                ],
            ],
            'general' => [
                'name' => 'Megnevezés',
                'note' => 'Megjegyzés',
                'order' => 'Sorrend',
            ],
        ],
    ],
];