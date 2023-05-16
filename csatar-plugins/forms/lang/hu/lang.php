<?php
return [
    'plugin' => [
        'name' => 'Űrlapkészítő',
        'description' => 'Űrlapkészítő a felhasználói felülethez',
        'author' => 'Csatár csapat',
        'permissions' => [
            'manageData' => 'Űrlapok kezelése',
        ],
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
                'subForm' => [
                    'title'             => 'Al űrlap',
                    'description'       => 'Ha ez az opció be van pipálva, akkor a CRUD paraméterek nem lesznek figyelembe véve.',
                ],
                'groupCRUD' => [
                    'groupName'                 => 'CRUD paraméterek',
                    'recordKeyParam'            => 'Model kulcs paraméter neve',
                    'recordKeyParamDescr'       => 'A URL paraméter és a model attríbutum neve, ami alapján be lehet azonosítani a modelt, pl. "/csapatok/:id". 
                        Itt az "id" a paraméter neve és a "csapatok/123" a 123-as azonosítóval rendelkező csapatot fogja visszatéríteni',
                    'readOnly'                  => 'Csak olvasás',
                    'readOnlyDescr'             => 'Jelöld be ezt a négyzetet, ha az oldal nem foglalkozik rekord létrehozással/szerkesztéssel/törléssel. Ha be van jelölve, akkor az alábbi paraméterek opcionálisak.',
                    'createRecordKeyword'       => 'Új rekord kulcsszó',
                    'createRecordKeywordDescr'  => 'Add meg a kulcsszót, ami az új rekord létrehozását jelzi. Az alapértelmezett kulcsszó a 
                        "create", pl. "/csapatok/create" egy üres űrlapot fog megnyitni egy új csapat létrehozásához.',
                    'recordActionParam'         => 'Akció URL paraméter neve',
                    'recordlActionParamDescr'   => 'A URL paraméter, ami lehetővé teszi a rekord szerkesztését vagy törlését. Például "/csapatok/:id/:action?".
                        Itt az "action" a paraméter neve, ami alapján a szerkesztés vagy törlés akciót fogja végrehajtani: "/csapatok/:id/update" vagy "/csapatok/:id/delete"',
                    'actionUpdateKeyword'       => 'Szerkesztési akció kulcsszó',
                    'actionUpdateKeywordDescr'  => 'Add meg a kulcsszót, ami a rekord szerkesztését jelzi. Az alapértelmezett kulcsszó a 
                        "update", pl. "/csapatok/123/update" egy űrlapot fog megnyitni a 123-as azonosítójú csapat szerkesztéséhez.',
                    'actionDeleteKeyword'       => 'Törlési akció kulcsszó',
                    'actionDeleteKeywordDescr'  => 'Add meg a kulcsszót, ami a rekord törlését jelzi. Az alapértelmezett kulcsszó a 
                        "delete", pl. "/csapatok/123/delete" törli a 123-as azonosítójú csapatot.',
                ],
                'propertiesValidation' => [
                    'formNotSelected'       => "Válassz ki egy űrlapot a legördülő listából. Ha az űrlap, amit keresel nincs a listában, akkor létre kell hoznod a Űrlapok menüben.",
                    'recordKeyNotSelected'  => "A rekord kulcs paraméter nincs megadva.",
                ]
            ],
            'select'      => 'Kiválasztás',
            '2FAtoRead'   => 'A következő információk megjelenítéséhez két faktoros hitelesítés szükséges: ',
            '2FAtoCreate' => 'A következő információk megadásához két faktoros hitelesítés szükséges: ',
            '2FAtoModify' => 'A következő információk szerkesztéséhez két faktoros hitelesítés szükséges: ',
            '2FANeeded'   => 'Ez a funkció csak két faktoros hitelesítéssel érhető el!',
        ],
    ],
    'widgets' => [
        'frontendFileUpload' => [
            'browse' => 'Böngéssz',
        ],
        'frontendFileUploadValidation' => [
            'mimeTypeMismatch' => 'A(z) :attribute fájltípusa kizárólag :values lehet.',
            'maxNumberOfAttachements' => 'A maximálisan feltölthető fájlok száma: :value.',
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
        'noFiles'               => 'Nincsenek fájlok megadva',
        'nothingSelectedOnPivotRelation'    => 'Kérjük válasszon ki egy opciót...',
    ],
    'validation' => [
        'selectOptionBeforeNext' => 'Válasszon egy opciót!',
    ],
    'success' => [
        'saved' => 'Az adatok sikeresen elmentve...'
    ],
    'failed' => [
        'noPermissionToDeleteRecord' => 'Nincs jogosúltsága a törléshez!',
        'noPermissionForSomeFields' => 'Az adatok sikeresen elmentve, néhány mező kivételével, amelyekhez nincs jogosultsága. Vegye fel a kapcsolatot az adminisztrátorral.',
    ],
];
