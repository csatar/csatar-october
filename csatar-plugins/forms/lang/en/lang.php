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
        ],
        'componentValidation' => [
            'formNotSelected' => "Please select a form from the dropdown list. If list is empty, you need to create a form in From menu."
        ]
    ],
    'errors' => [
        'formModelNotFound' => "The model could not be found. Please make sure you enter an existing model name and correct path.",
        'formNotFound' => "The selected form doesn't exist, please check 'Basic Form' component settings on page: ",
        'noDataArray' => 'Data array is missing. Check form validation and see how could this happen.',
        'canNotSaveValidated' => 'Can not save validated data. Check form validation and see how could this happen.'
    ],

];
