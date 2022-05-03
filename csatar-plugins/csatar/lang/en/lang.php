<?php return [
    'plugin' => [
        'name' => 'CSATÁR',
        'description' => 'Plugin for the RMCSSZ\'s CSATÁR project',
        'author' => 'CSATÁR team',
        'admin' => [
            'scout' => [
                'scoutData' => 'Scout data',
                'userId' => 'User Id',
                'familyName' => 'Family name',
                'givenName' => 'Given name',
                'email' => 'Email',
                'personalIdentificationNumber' => 'Personal identification number',
                'gender' => [
                    'gender' => 'Gender',
                    'male' => 'Male',
                    'female' => 'Female',
                    'select' => 'Select...'
                ]
            ],
            'admin' => [
                'menu' => [
                    'scout' => 'Scout',
                ],
            ],
        ],
        'component' => [
            'resetPassword' => [
                'name' => 'Reset Password',
                'description' => 'Enables restoring the user\'s password.'
            ]
        ]
    ],
];
