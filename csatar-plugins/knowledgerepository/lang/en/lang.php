<?php return [
    'plugin' => [
        'name' => 'Knowledge Repository',
        'description' => 'Plugin for Knowledge Repository in the RMCSSZ\'s CSATÁR project',
        'permissions' => [
            'manageKnowledgeRepository' => 'Manage Knowledge Repository',
        ],
        'admin' => [
            'menu' => [
                'knowledgeRepository' => [
                    'knowledgeRepository' => 'Knowledge Repository',
                    'testSystem' => 'Test System',
                    'games' => 'Games',
                    'songs' => 'Songs',
                    'workPlans' => 'Work Plans',
                    'methodologies' => 'Methodologies',
                    'songTypes' => 'Song types',
                    'folkSongTypes' => 'Folk song types',
                    'regions' => 'Regions',
                    'folkSongRhythms' => 'Folk song rhythms'
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
                    'methodologyType' => 'Methodology type',
                    'methodologyTypes' => 'Methodology types',
                    'methodologyName' => 'Methodology name',
                    'methodology' => 'Methodology',
                    'ageGroup' => 'Age group',
                    'songType' => 'Song type',
                    'folkSongType' => 'Folk song type',
                    'region' => 'Region',
                    'bigRegion' => 'Big Region',
                    'midRegion' => 'Mid Region',
                    'smallRegion' => 'Small Region',
                    'folkSongRhythm' => 'Folk song rhythm',
                    'song' => 'Song'
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
                'link' => 'Link',
                'Attachment' => 'Attachment',
                'sortOrder' => 'Sort order',
                'version' => 'Version',
                'created_at' => 'Created at',
                'forms' => 'Forms',
                'select' => 'Select...'
            ],
            'game' => [
                'game' => 'Game',
                'name' => 'Name',
                'uploader' => 'Uploader',
                'approver' => 'Approver',
                'otherTools' => 'Other tool(s)',
                'attachements' => 'Attachements',
                'uploadedAt' => 'Upload date',
                'approvedAt' => 'Approval date',
                'version' => 'Version',
                'ageGroupsComment' => 'Age groups can be selected only after association is selected.',
            ],
            'trialSystem' => [
                'trialSystem' => 'Trial System',
                'trialSystems' => 'Trial Systems',
                'idString' => 'Id string',
                'ageGroup' => 'Age group',
                'trialSystemCategory' => 'Category',
                'trialSystemCategories' => 'Trial system categories',
                'trialSystemTopic' => 'Topic',
                'trialSystemTopics' => 'Trial system topics',
                'trialSystemSubTopic' => 'Subtopic',
                'trialSystemSubTopics' => 'Trial system subtopics',
                'trialSystemType' => 'Type',
                'trialSystemTypes' => 'Trial system types',
                'trialSystemTrialType' => 'Trial type',
                'trialSystemTrialTypes' => 'Trial system trial types',
                'forPatrols' => 'For patrols',
                'individual' => 'Individual',
                'task' => 'Task',
            ],
            'song' => [
                'songTitle' => 'Dal cím',
                'author' => 'Szerző',
                'text' => 'Szöveg',

            ]
        ],
        'components' => [
            'gameForm' => [
                'name' => 'Game Form',
                'description' => 'Game Form component',
            ],
            'songForm' => [
                'name' => 'Dal űrlap',
                'description' => 'Dal űrlap komponens'
            ]
        ],
    ],
];
