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
                'formId' => [
                    'title'             => 'Form',
                    'description'       => 'Select from',
                ],
                'groupCRUD' => [
                    'groupName'                 => 'CRUD parametes',
                    'recordKeyParam'            => 'Record key link param. name',
                    'recordKeyParamDescr'       => 'The URL parameter and record attribute name that is used to identify to record, for example "/teams/:id". Here "id" is the parameter name and
                        "/teams/123" will display team with id 123, based on record\'s "id" attribute. If you set "/teams/:slug" component will try to find the record by slug.' ,
                    'readOnly'                  => 'Read only',
                    'readOnlyDescr'             => 'Check this box if page doesn\'t need to handle record creation/update/deletion. If checked, parameters below are optional.',
                    'createRecordKeyword'       => 'Create model keyword.',
                    'createRecordKeywordDescr'  => 'Specify a keyword that indicates new record creation. The default keyword is "create", for example "/teams/create" will open a blank for to create a new team.',
                    'recordActionParam'         => 'Action param. name',
                    'recordlActionParamDescr'   => 'The URL parameter that helps you Edit or Delete a record. For example "/teams/:id/:action?".
                        Here "action" is the parameter name that is used to specify the action we want: "/teams/:id/update" or "/teams/:id/delete"',
                    'actionUpdateKeyword'       => 'Update action keyword',
                    'actionUpdateKeywordDescr'  => 'Specify a keyword for editing record. The default keyword is "update", for example "/teams/123/update" will open form for editing team with id 123.',
                    'actionDeleteKeyword'       => 'Delete action keyword',
                    'actionDeleteKeywordDescr'  => 'Specify a keyword for deleting record. The default keyword is "delete", for example "/teams/123/delete" will team with id 123.',
                ],
                'propertiesValidation' => [
                    'formNotSelected' => "Please select a form from the dropdown list. If list is empty, you need to create a form in From menu.",
                    'recordKeyNotSelected'  => "Record key parameter is not specified."
                ]
            ]
        ],
    ],
    'errors' => [
        'formModelNotFound' => "The model could not be found. Please make sure you enter an existing model name and correct path.",
        'formNotFound' => "The selected form doesn't exist, please check 'Basic Form' component settings on page: ",
        'noDataArray' => 'Data array is missing. Check form validation and see how could this happen.',
        'canNotSaveValidated' => 'Can not save validated data. Check form validation and see how could this happen.'
    ],

];
