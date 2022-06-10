<?php return [
    'frontEnd' => [
        'authException' => 'Az e-mail cím, az ECSET kód vagy a jelszó téves!'
    ],
    'plugin' => [
        'name' => 'CSATÁR',
        'description' => 'Plugin for the RMCSSZ\'s CSATÁR project',
        'author' => 'CSATÁR team',
        'admin' => [
            'general' => [
                'name' => 'Name',
                'email' => 'Email',
                'contactEmail' => 'Contact email',
                'phone' => 'Phone',
                'address' => 'Address',
                'comment' => 'Comment',
                'id' => 'Id',
                'createdAt' => 'Created at',
                'updatedAt' => 'Updated at',
                'deletedAt' => 'Deleted at',
                'select' => 'Select...',
                'logo' => 'Logo',
                'coordinates' => 'Coordinates',
                'ecsetCode' => 'ECSET code',
                'date' => 'Dátum',
                'location' => 'Helyszín',
                'qualificationCertificateNumber' => 'Képesítési Igazolás Száma',
                'qualification' => 'Képzés',
                'qualificationLeader' => 'Képzésvezető',
                'relations' => 'Relations',
            ],
            'scout' => [
                'scout' => 'Scout',
                'scouts' => 'Scouts',
                'scoutData' => 'Scout data',
                'userId' => 'User Id',
                'namePrefix' => 'Név előtag',
                'familyName' => 'Family name',
                'givenName' => 'Given name',
                'nickname' => 'Becenév',
                'personalIdentificationNumber' => 'Personal identification number',
                'gender' => [
                    'gender' => 'Gender',
                    'male' => 'Male',
                    'female' => 'Female',
                ],
                'isActive' => 'Is active',
                'allergy' => 'Allergies',
                'foodSensitivity' => 'Ételérzékenység',
                'legalRelationship' => 'Legal relationship',
                'chronicIllnesses' => 'Chronic illnesses',
                'specialDiet' => 'Special diet',
                'religion' => 'Religion',
                'nationality' => 'Nemzetiség',
                'tShirtSize' => 'T-shirt size',
                'birthdate' => 'Születési dátum',
                'nameday' => 'Névnap',
                'maidenName' => 'Születési/leánykori név',
                'birthplace' => 'Születési hely',
                'addressCountry' => 'Cím - ország',
                'addressZipcode' => 'Cím - irányítószám',
                'addressCounty' => 'Cím - megye',
                'addressLocation' => 'Cím - település',
                'addressStreet' => 'Cím - utca',
                'addressNumber' => 'Cím - házszám',
                'mothersName' => 'Anyja neve',
                'mothersPhone' => 'Anyja telefonszáma',
                'mothersEmail' => 'Anyja e-mail címe',
                'fathersName' => 'Apja neve',
                'fathersPhone' => 'Apja telefonszáma',
                'fathersEmail' => 'Apja e-mail címe',
                'legalRepresentativeName' => 'Törvényes képviselő neve',
                'legalRepresentativePhone' => 'Törvényes képviselő telefonszáma',
                'legalRepresentativeEmail' => 'Törvényes képviselő e-mail címe',
                'elementarySchool' => 'Elemi iskola',
                'primarySchool' => 'Általános iskola',
                'secondarySchool' => 'Középiskola',
                'postSecondarySchool' => 'Posztliceális iskola',
                'college' => 'Főiskola',
                'university' => 'Egyetem',
                'otherTrainings' => 'Egyéb képzések',
                'foreignLanguageKnowledge' => 'Idegen nyelvismeret',
                'occupation' => 'Foglalkozás',
                'workplace' => 'Munkahely',
                'comment' => 'Megjegyzés',
                'registrationForm' => 'Bejelentkezési és nyilvántartási lap',
                'promise' => 'Fogadalom, ígéret',
                'test' => 'Próba',
                'specialTest' => 'Különpróba',
                'professionalQualification' => 'Szakági képesítés',
                'specialQualification' => 'Szakági különpróba',
                'leadershipQualification' => 'Vezetői képesítés',
                'trainingQualification' => 'Kiképzői képesítés',
                'allergies' => 'Allergies',
                'promises' => 'Fogadalmak, ígéretek',
                'tests' => 'Próbák',
                'specialTests' => 'Különpróbák',
                'professionalQualifications' => 'Szakági képesítések',
                'specialQualifications' => 'Szakági különpróbák',
                'leadershipQualifications' => 'Vezetői képesítések',
                'trainingQualifications' => 'Kiképzői képesítések',
                'foodSensitivities' => 'Ételérzékenységekek',
                'additionalDetailsInfo' => 'Allergák, Ételérékenységek, Fogadalmak, Próbák, Különpróbák, Szakági képesítések, Szakági különpróbák, Vezetői képesítések and Kiképzői képesítések hozzáadása a Cserkész létrehozása után lehetséges. Miután a többi adatot kitöltötted, kattints a Létrehozás gombra.',
                'breadcrumb' => 'Scouts',
                'team' => 'Team',
                'troop' => 'Troop',
                'patrol' => 'Patrol',
                'sections' => [
                    'birthData' => 'Születési adatok',
                    'addressData' => 'Cím',
                    'mothersData' => 'Anyja adatai',
                    'fathersData' => 'Apja adatai',
                    'legalRepresentativeData' => 'Törvényes képviselő adatai',
                    'schoolData' => 'Tanulmányok',
                    'occupation' => 'Foglalkozás',
                    'otherData' => 'Egyéb adatok',
                ],
                'validationExceptions' => [
                    'noTeamSelected' => 'Please select a team!',
                    'troopNotInTheTeam' => 'The selected Troop does not belong to the selected Team.',
                    'troopNotInTheTeamOrTroop' => 'The selected Patrol does not belong to the selected Team or to the selected Troop.',
                    'dateInTheFuture' => 'A Dátum nem lehet a jövőben.',
                    'registrationFormRequired' => 'A Bejelentkezési és nyilvántartási lap kötelező.',
                    'dateRequiredError' => 'A Dátum megadása a %name %category esetén kötelező.',
                    'locationRequiredError' => 'A Helyszín megadása a %name %category esetén kötelező.',
                    'qualificationCertificateNumberRequiredError' => 'A Képesítési Igazolás Számának megadása a %name %category esetén kötelező.',
                    'qualificationRequiredError' => 'A Képzés megadása a %name %category esetén kötelező.',
                    'qualificationLeaderRequiredError' => 'A Képzésvezető megadása a %name %category esetén kötelező.',
                    'dateInTheFutureError' => 'A Dátum a %name %category esetén nem lehet a jövőben.',
                ]
            ],
            'admin' => [
                'menu' => [
                    'scout' => 'Scout',
                    'scoutSystemData' => [
                        'scoutSystemData' => 'Scout System Data',
                        'legalRelationshipCategories' => 'Legal Relationship Categories',
                        'chronicIllnessCategories' => 'Chronic Illness Categories',
                        'allergyCategories' => 'Allergy Categories',
                        'foodSensitivityCategories' => 'Ételérzékenység típusok',
                        'specialDietCategories' => 'Special Diet Categories',
                        'religionCategories' => 'Religion Categories',
                        'tShirtSizeCategories' => 'T-Shirt Size Categories',
                        'promiseCategories' => 'Fogadalom, ígéret típusok',
                        'testCategories' => 'Próba típusok',
                        'specialTestCategories' => 'Különpróba típusok',
                        'professionalQualificationCategories' => 'Szakági képesítés típusok',
                        'specialQualificationCategories' => 'Szakági különpróba típusok',
                        'leadershipQualificationCategories' => 'Vezetői képesítés típusok',
                        'trainingQualificationCategories' => 'Kiképzői képesítés típusok',
                    ],
                    'organizationSystemData' => [
                        'organizationSystemData' => 'Organization System Data',
                        'hierarchy' => 'Hierarchy',
                    ],
                ],
            ],
            'allergy' => [
                'allergy' => 'Allergy',
                'allergies' => 'Allergies',
                'breadcrumb' => 'Allergies',
            ],
            'foodSensitivity' => [
                'foodSensitivity' => 'Ételérzékenység',
                'foodSensitivities' => 'Ételérzékenységek',
                'breadcrumb' => 'Ételérzékenységek',
            ],
            'chronicIllness' => [
                'chronicIllness' => 'Chronic Illness',
                'chronicIllnesses' => 'Chronic Illnesses',
                'breadcrumb' => 'Chronic Illnesses',
            ],
            'legalRelationship' => [
                'legalRelationship' => 'Legal Relationship',
                'legalRelationships' => 'Legal Relationships',
                'sortOrder' => 'Sort order',
                'breadcrumb' => 'Legal Relationships',
            ],
            'religion' => [
                'religion' => 'Religion',
                'religions' => 'Religions',
                'breadcrumb' => 'Religions',
            ],
            'specialDiet' => [
                'specialDiet' => 'Special Diet',
                'specialDiets' => 'Special Diets',
                'breadcrumb' => 'Special Diets',
            ],
            'tShirtSize' => [
                'tShirtSize' => 'T-Shirt Size',
                'tShirtSizes' => 'T-Shirt Sizes',
                'breadcrumb' => 'T-Shirt Sizes',
            ],
            'promise' => [
                'promise' => 'Fogadalom, ígéret',
                'promises' => 'Fogadalmak, ígéretek',
                'breadcrumb' => 'Fogadalmak, ígéretek',
            ],
            'test' => [
                'test' => 'Próba',
                'tests' => 'Próbák',
                'breadcrumb' => 'Próbák',
            ],
            'specialTest' => [
                'specialTest' => 'Különpróba',
                'specialTests' => 'Különpróbák',
                'breadcrumb' => 'Különpróbák',
            ],
            'professionalQualification' => [
                'professionalQualification' => 'Szakági képesítés',
                'professionalQualifications' => 'Szakági képesítések',
                'breadcrumb' => 'Szakági képesítések',
            ],
            'specialQualification' => [
                'specialQualification' => 'Szakági különpróba',
                'specialQualifications' => 'Szakági különpróbák',
                'breadcrumb' => 'Szakági különpróbák',
            ],
            'leadershipQualification' => [
                'leadershipQualification' => 'Vezetői képesítés',
                'leadershipQualifications' => 'Vezetői képesítések',
                'breadcrumb' => 'Vezetői képesítések',
            ],
            'trainingQualification' => [
                'trainingQualification' => 'Kiképzői képesítés',
                'trainingQualifications' => 'Kiképzői képesítések',
                'breadcrumb' => 'Kiképzői képesítések',
            ],
            'hierarchy' => [
                'hierarchy' => 'Hierarchy',
                'parent' => 'Parent',
                'sortOrder' => 'Sort order',
                'breadcrumb' => 'Hierarchy',
            ],
            'association' => [
                'association' => 'Association',
                'associations' => 'Associations',
                'contactName' => 'Contact name',
                'bankAccount' => 'Bank account',
                'leadershipPresentation' => 'Leadership Presentation',
                'districtsInfo' => 'Districts can be added after the Association has been created. Click the Create button after other information is filled.',
                'breadcrumb' => 'Associations',
                'ecsetCode' => [
                    'suffix' => 'ECSET code suffix',
                ],
            ],
            'district' => [
                'district' => 'District',
                'districts' => 'Districts',
                'website' => 'Website',
                'description' => 'Description',
                'facebookPage' => 'Facebook page',
                'contactName' => 'Contact name',
                'leadershipPresentation' => 'Leadership presentation',
                'bankAccount' => 'Bank account',
                'breadcrumb' => 'Districts',
                'teamsInfo' => 'Teams can be added after the District has been created. Click the Create button after other information is filled.',
                'association' => 'Association',
            ],
            'team' => [
                'team' => 'Team',
                'teams' => 'Teams',
                'teamNumber' => 'Team number',
                'foundationDate' => 'Foundation date',
                'website' => 'Website',
                'facebookPage' => 'Facebook page',
                'contactName' => 'Contact name',
                'history' => 'History',
                'leadershipPresentation' => 'Leadership presentation',
                'description' => 'Description',
                'juridicalPersonName' => 'Juridical person name',
                'juridicalPersonAddress' => 'Juridical person address',
                'juridicalPersonTaxNumber' => 'Juridical person tax number',
                'juridicalPersonBankAccount' => 'Juridical person bank account',
                'homeSupplierName' => 'Home supplier name',
                'district' => 'District',
                'troopsPatrolsScoutsInfo' => 'Troops, Patrols and Scouts can be added after the Team has been created. Click the Create button after other information is filled.',
                'breadcrumb' => 'Teams',
                'teamNumberTakenError' => 'This Team number is already taken.',
                'dateInTheFutureError' => 'A dátum nem lehet a jövőben.',
            ],
            'troop' => [
                'troop' => 'Troop',
                'troops' => 'Troops',
                'website' => 'Website',
                'facebookPage' => 'Facebook page',
                'troopLeaderName' => 'Troop leader name',
                'troopLeaderPhone' => 'Troop leader phone',
                'troopLeaderEmail' => 'Troop leader email',
                'team' => 'Team',
                'patrolsInfo' => 'Patrols can be added after the Troop has been created. Click the Create button after other information is filled.',
                'breadcrumb' => 'Troops',
            ],
            'patrol' => [
                'patrol' => 'Patrol',
                'patrols' => 'Patrols',
                'website' => 'Website',
                'facebookPage' => 'Facebook page',
                'patrolLeaderName' => 'Patrol leader name',
                'patrolLeaderPhone' => 'Patrol leader phone',
                'patrolLeaderEmail' => 'Patrol leader email',
                'ageGroup' => 'Age group',
                'team' => 'Team',
                'troop' => 'Troop',
                'breadcrumb' => 'Patrols',
                'troopNotInTheTeamError' => 'The selected Troop does not belong to the selected Team.',
            ],
        ],
        'component' => [
            'resetPassword' => [
                'name' => 'Reset Password',
                'description' => 'Enables restoring the user\'s password.',
            ],
            'structure' => [
                'name' => 'Organization Structure',
                'description' => 'Displays the organization structure in a tree view.',
            ],
            'logos' => [
                'name' => 'Logók',
                'description' => 'Logók és a hozzájuk tartózó hivatkozások rács-nézetben való megjelenítése.',
                'sponsors' => [
                    'title' => 'Támogatók listája',
                    'hungarianGovernment' => 'Magyar Kormány',
                    'harghitaCountyCouncil' => 'Hargita Megye Tanácsa',
                    'communitasFoundation' => 'Communitas Alapítvány',
                    'toyota' => 'Toyota',
                ],
               'discounts' => [
                    'title' => 'Kedvezményeket kínáló cégek',
                    'mormotaLand' => 'Mormota Land',
                    'tiboo' => 'Tiboo',
                    'giftyShop' => 'Gifty Shop',
                    'zergeSpecialtyStore' => 'Zerge Szakbolt',
                ],
                'partners' => [
                    'title' => 'Partnerek',
                    'forumOfHungarianScoutAssociations' => 'Magyar Cserkészszövetségek Fóruma',
                    'transcarpathianHungarianScoutAssociation' => 'Kárpátaljai Magyar Cserkészszövetség',
                    'hungarianScoutAssociationInExteris' => 'Külföldi Magyar Cserkészszövetség',
                    'hungarianScoutAssociation' => 'Magyar Cserkészszövetség',
                    'slovakHungarianScoutAssociation' => 'Szlovákiai Magyar Cserkészszövetség',
                    'hungarianScoutAssociationOfVojvodina' => 'Vajdasági Magyar Cserkészszövetség',
                    'archdiocesanYouthHeadquarters' => 'Főegyházmegyei Ifjúsági Főlelkészség',
                    'marySWayTransylvania' => 'Mária út - Erdély',
                    'proEducatione' => 'Pro Educatione',
                    'scoutsOfRomania' => 'Románia Cserkészei',
                ],
            ]
        ]
    ]
];
