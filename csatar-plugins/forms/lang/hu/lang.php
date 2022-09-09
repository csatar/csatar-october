<?php return [
    'plugin' => [
        'name' => 'Űrlapkészítő',
        'description' => 'Űrlapkészítő a felhasználói felülethez',
        'author' => 'Csatár csapat',
        'admin' => [
            'form' => [
                'title' => 'Cím',
                'model' => 'Modell',
                'modelComment' => 'Névtér elérési útja a modellhez, pl. Rainlab\\Blog\\Models\\Post',
                'fields' => 'Mezők',
                'fieldsComment' => 'A mezők YAML konfigurációs fájljának relatív vagy abszolút elérési útja, pl. forms_fields.yaml vagy $\\Rainlab\\Blog\\Models\\Post\\fields.yaml',
                'fieldConfig' => 'Mezőkonfiguráció',
                'slug' => 'Slug',
            ],
        ],
    ],
    'components' => [
        'basicForm' => [
            'name' => 'Alap űrlap',
            'description' => 'Megjeleníti az űrlapot',
            'properties' => [
                'formId' => [
                    'title'             => 'Űrlap',
                    'description'       => 'Űrlapválasztás',
                ],
                'groupCRUD' => [
                    'groupName'                 => 'CRUD paraméterek',
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
                    'formNotSelected'       => "Please select a form from the dropdown list. If the form you're looking for is not there, you need to create it in the Forms menu.",
                    'recordKeyNotSelected'  => "Record key parameter is not specified."
                ]
            ]
        ],
    ],
    'widgets' => [
        'frontendFileUpload' => [
            'browse' => 'Böngéssz',
        ],
        'frontendFileUploadValidation' => [
            'mimeTypeMismatch' => 'A(z) :attribute fájltípusa kizárólag :values lehet.',
        ],
        'frontendFileUploadException' => [
            'fileExceedsUploadLimit' => 'A fájl mérete nagyobb mint a megengedtett ',
            'fileIsNotValid'         => 'A %s fájl érvénytelen.',
            'invalidField'           => 'Érvénytelen mező.',
        ],
    ],
    'errors' => [
        'formModelNotFound'     => 'A modell nem található. Adj meg egy létező modellt és helyes útvonalat.',
        'formNotFound'          => 'A kiválasztott űrlap nem található. Ellenőrizd az \'Alap űrlap\' komponens beállításait a követketző oldalon: ',
        'noDataArray'           => 'Az adatok hiányoznak. Ellenőrizd az űrlap érvényesítését.',
        'canNotSaveValidated'   => 'Nem sikerült elmenteni az érvényesített adatokat. Ellenőrizd az űrlap érvényesítését.',
        'noFiles'               => 'Nincsenek fájlok megadva'
    ],
    'validation' => [
        'selectOptionBeforeNext' => 'Válasszon egy opciót!',
    ],
    'success' => [
        'saved' => 'Az adatok sikeresen elmentve...'
    ],
    'failed' => [
        'noPermissionToDelete' => 'Nincs jogosultsága a törléshez!'
    ]
];
