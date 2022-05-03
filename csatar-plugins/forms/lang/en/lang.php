<?php return [
    'plugin' => [
        'name' => 'Form Builder',
        'description' => 'Front end form builder',
        'author' => 'Csatár team',
        'admin' => [
            'form' => [
                'title' => 'Title',
                'model' => 'Model',
                'modelComment' => 'Namespace path to model e.g. Rainlab\\Blog\\Models\\Post',
                'fields' => 'Fields',
                'fieldsComment' => 'Relative or absolute path to the fields YAML config file e.g. forms_fields.yaml or $\\Rainlab\\Blog\\Models\\Post\\fields.yaml',
                'fieldConfig' => 'Field configuration',
            ],
        ],
    ],
    'components' => [
        'basicForm' => [
            'name' => 'Basic form',
            'description' => 'Renders a from',
            'properties' => [
                'form_id' => [
                    'title'             => 'Form',
                    'description'       => 'Select from',
                ]
            ]
        ]
    ],
    'errors' => [
        'formModelNotFound' => "The model could not be found. Please make sure you enter an existing model name and correct path.",
        'formNotFound' => "The form doesn't exist.",
        'canNotSave' => 'The form could not be saved.',
    ]
];
