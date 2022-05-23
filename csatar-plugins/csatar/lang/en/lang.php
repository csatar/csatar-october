<?php return [
    'frontEnd' => [
        'authException' => 'The email address, the ECSET code or the password is incorrect.'
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
                'date' => 'Date',
                'location' => 'Location',
                'relations' => 'Relations',
            ],
            'scout' => [
                'scout' => 'Scout',
                'scouts' => 'Scouts',
                'scoutData' => 'Scout data',
                'userId' => 'User Id',
                'familyName' => 'Family name',
                'givenName' => 'Given name',
                'personalIdentificationNumber' => 'Personal identification number',
                'gender' => [
                    'gender' => 'Gender',
                    'male' => 'Male',
                    'female' => 'Female',
                ],
                'isActive' => 'Is active',
                'allergy' => 'Allergies',
                'foodSensitivity' => 'Food sensitivity',
                'legalRelationship' => 'Legal relationship',
                'chronicIllnesses' => 'Chronic illnesses',
                'specialDiet' => 'Special diet',
                'religion' => 'Religion',
                'tShirtSize' => 'T-shirt size',
                'promise' => 'Promise',
                'allergies' => 'Allergies',
                'promises' => 'Promises',
                'foodSensitivities' => 'Food sensitivities',
                'additionalDetailsInfo' => 'Allergies, Food Sensitivities, Promises can be added after the Scout has been created. Click the Create button after other information is filled.',
                'breadcrumb' => 'Scouts',
                'team' => 'Team',
                'troop' => 'Troop',
                'patrol' => 'Patrol',
                'validationExceptions' => [
                    'noTeamSelected' => 'Please select a team!',
                    'troopNotInTheTeam' => 'The selected Troop does not belong to the selected Team.',
                    'troopNotInTheTeamOrTroop' => 'The selected Patrol does not belong to the selected Team or to the selected Troop.',
                    'dateRequiredError' => 'The Date for the %name %category is required.',
                    'locationRequiredError' => 'The Location for the %name %category is required.',
                    'dateInTheFutureError' => 'The selected Date for the %name %category is in the future.',
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
                        'foodSensitivityCategories' => 'Food Sensitivity Categories',
                        'specialDietCategories' => 'Special Diet Categories',
                        'religionCategories' => 'Religion Categories',
                        'tShirtSizeCategories' => 'T-Shirt Size Categories',
                        'promiseCategories' => 'Promise Categories',
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
                'foodSensitivity' => 'Food Sensitivity',
                'foodSensitivities' => 'Food Sensitivities',
                'breadcrumb' => 'Food Sensitivities',
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
                'promise' => 'Promise',
                'promises' => 'Promises',
                'breadcrumb' => 'Promises',
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
                'dateInTheFutureError' => 'The selected date is in the future.',
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
                'name' => 'Logos',
                'description' => 'Logos and corresponding links shown in grid view.',
                'sponsors' => [
                    'title' => 'List of Sponsors', // 'Támogatók listája'
                    'hungarianGovernment' => 'Hungarian Government', // 'Magyar Kormány'
                    'harghitaCountyCouncil' => 'Harghita County Council', // 'Hargita Megye Tanácsa'
                    'communitasFoundation' => 'Communitas Foundation', // 'Communitas Alapítvány'
                    'toyota' => 'Toyota', // 'Toyota'
                ],
                'discounts' => [
                    'title' => 'Companies offering discounts', // 'Kedvezményeket kínáló cégek'
                    'mormotaLand' => 'Mormota Land', // 'Mormota Land'
                    'tiboo' => 'Tiboo', // 'Tiboo'
                    'giftyShop' => 'Gifty Shop', // 'Gifty Shop'
                    'zergeSpecialtyStore' => 'Zerge Specialt Store', // 'Zerge Szakbolt'
                ],
                'partners' => [
                    'title' => 'Partners', // 'Partnerek'
                    'forumOfHungarianScoutAssociations' => 'Forum of Hungarian Scout Associations', // 'Magyar Cserkészszövetségek Fóruma'
                    'transcarpathianHungarianScoutAssociation' => 'Transcarpathian Hungarian Scout Association', // 'Kárpátaljai Magyar Cserkészszövetség'
                    'hungarianScoutAssociation' => 'Hungarian Scout Association', // 'Magyar Cserkészszövetség'
                    'slovakHungarianScoutAssociation' => 'Slovak Hungarian Scout Association', // 'Szlovákiai Magyar Cserkészszövetség'
                    'hungarianScoutAssociationOfVojvodina' => 'Hungarian Scout Association of Vojvodina', // 'Vajdasági Magyar Cserkészszövetség'
                    'archdiocesanYouthHeadquarters' => 'Archdiocesan Youth Headquarters', // 'Főegyházmegyei Ifjúsági Főlelkészség'
                    'marySWayTransylvania' => 'Mary\'s Way - Transylvania', // 'Mária út - Erdély'
                    'proEducatione' => 'Pro Educatione', // 'Pro Educatione'
                    'scoutsOfRomania' => 'Scouts Of Romania', // 'Románia Cserkészei'
                ],
            ],
        ],
    ],
];
