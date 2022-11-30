<?php return [
    'frontEnd' => [
        'authException' => 'The email address, the ID number or the password is incorrect.',
    ],
    'plugin' => [
        'name' => 'CSATÁR',
        'description' => 'Plugin for the RMCSSZ\'s CSATÁR project',
        'author' => 'CSATÁR team',
        'admin' => [
            'general' => [
                'name' => 'Name',
                'name_abbreviation' => 'Name abbreviation',
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
                'ecsetCode' => 'ID number',
                'date' => 'Date',
                'location' => 'Location',
                'qualificationCertificateNumber' => 'Képesítési Igazolás Száma',
                'training' => 'Training',
                'qualification' => 'Képzés',
                'qualificationLeader' => 'Képzésvezető',
                'relations' => 'Relations',
                'password' => 'Password',
                'password_confirmation' => 'Password confirmation',
                'organizationUnitNameWarning' => 'Organization unit name can not contain the unit type.',
                'note' => 'Note',
                'sortOrder' => 'Sort Order',
                'contentPage' => 'Content Page',
                'searchResult' => 'Search Result',
                'yes' => 'Yes',
                'no' => 'No',
                'url' => 'Hivatkozás',
                'warning' => 'Warning',
            ],
            'ageGroups' => [
                'ageGroups' => 'Age Groups',
                'numberOfPatrolsInAgeGroup' => 'Number of patrols in age group',
            ],
            'scout' => [
                'scout' => 'Scout',
                'scouts' => 'Scouts',
                'scoutData' => 'Scout data',
                'userId' => 'User Id',
                'user' => 'User Account',
                'namePrefix' => 'Name prefix',
                'familyName' => 'Family name',
                'givenName' => 'Given name',
                'nickname' => 'Nickname',
                'personalIdentificationNumber' => 'Personal identification number',
                'gender' => [
                    'gender' => 'Gender',
                    'male' => 'Male',
                    'female' => 'Female',
                    'other' => 'Other',
                ],
                'isActive' => 'Is active',
                'allergy' => 'Allergies',
                'foodSensitivity' => 'Food sensitivity',
                'legalRelationship' => 'Legal relationship',
                'chronicIllnesses' => 'Chronic illnesses',
                'specialDiet' => 'Special diet',
                'religion' => 'Religion',
                'nationality' => 'Nationality',
                'tShirtSize' => 'T-shirt size',
                'birthdate' => 'Date',
                'nameday' => 'Nameday',
                'maidenName' => 'Maiden name',
                'birthplace' => 'Location',
                'addressCountry' => 'Country',
                'addressZipcode' => 'Zipcode',
                'addressCounty' => 'County',
                'addressLocation' => 'Location',
                'addressStreet' => 'Street',
                'addressNumber' => 'Number',
                'mothersName' => 'Name',
                'mothersPhone' => 'Phone',
                'mothersEmail' => 'Email',
                'fathersName' => 'Name',
                'fathersPhone' => 'Phone',
                'fathersEmail' => 'Email',
                'legalRepresentativeName' => 'Name',
                'legalRepresentativePhone' => 'Phone',
                'legalRepresentativeEmail' => 'Email',
                'elementarySchool' => 'Elementary school',
                'primarySchool' => 'Primary school',
                'secondarySchool' => 'Secondary school',
                'postSecondarySchool' => 'Post secondary school',
                'college' => 'College',
                'university' => 'University',
                'otherTrainings' => 'Other trainings',
                'foreignLanguageKnowledge' => 'Foreign language knowledge',
                'occupation' => 'Occupation',
                'workplace' => 'Workplace',
                'comment' => 'Comment',
                'registrationForm' => 'Registration form',
                'promise' => 'Promise',
                'test' => 'Test',
                'specialTest' => 'Special test',
                'professionalQualification' => 'Professional qualification',
                'specialQualification' => 'Special qualification',
                'leadershipQualification' => 'Leadership qualification',
                'trainingQualification' => 'Training qualification',
                'allergies' => 'Allergies',
                'promises' => 'Promises',
                'tests' => 'Tests',
                'specialTests' => 'Special tests',
                'professionalQualifications' => 'Professional qualifications',
                'specialQualifications' => 'Special qualifications',
                'leadershipQualifications' => 'Leadership qualifications',
                'trainingQualifications' => 'Training qualifications',
                'foodSensitivities' => 'Food sensitivities',
                'additionalDetailsInfo' => 'Allergies, Chronic Illnesses, Food Sensitivities, Promises, Tests, Special Tests, Professional Qualifications, Special Qualifications, Leadership Qualifications and Training Qualifications can be added after the Scout has been created. Click the Create button after other information is filled.',
                'breadcrumb' => 'Scouts',
                'team' => 'Team',
                'troop' => 'Troop',
                'patrol' => 'Patrol',
                'profile_image' => 'Profile image',
                'profile_image_comment' => 'Id format (above chest), in scout uniform.',
                'sections' => [
                    'birthData' => 'Birth data',
                    'addressData' => 'Address',
                    'mothersData' => 'Mother',
                    'fathersData' => 'Father',
                    'legalRepresentativeData' => 'Legal representative',
                    'schoolData' => 'School data',
                    'occupation' => 'Occupation',
                    'otherData' => 'Other data',
                ],
                'validationExceptions' => [
                    'noTeamSelected' => 'Please select a team!',
                    'troopNotInTheTeam' => 'The selected Troop does not belong to the selected Team.',
                    'troopNotInTheTeamOrTroop' => 'The selected Patrol does not belong to the selected Team or to the selected Troop.',
                    'dateInTheFuture' => 'The selected Date is in the future.',
                    'endDateBeforeStartDate' => 'The End date cannot be before the Start date.',
                    'associationRequired' => 'The Association is required.',
                    'registrationFormRequired' => 'The Registration form is required.',
                    'dateRequiredError' => 'The Date for the %name %category is required.',
                    'locationRequiredError' => 'The Location for the %name %category is required.',
                    'qualificationCertificateNumberRequiredError' => 'The Qualification Certificate Number for the %name %category is required.',
                    'qualificationRequiredError' => 'The Qualification for the %name %category is required.',
                    'qualificationLeaderRequiredError' => 'The Qualification Leader for the %name %category is required.',
                    'mandateEndDateBeforeStartDate' => 'The End date cannot be before the Start date for the %name mandate.',
                    'dateInTheFutureError' => 'The selected Date for the %name %category is in the future.',
                    'invalidPersonalIdentificationNumber' => 'Invalid Personal Identification Number.',
                    'legalRepresentativePhoneUnderAge' => 'For scouts under legal age, phone number of one parent or legal representative must be filled.',
                ],
                'staticMessages' => [
                    'personalDataNotAccepted' => 'Please verify your personal data here!',
                ],
                'activeMandateDeleteError' => 'The Scout having the %name name has active Mandates, thus this Scout cannot be deleted.',
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
                        'testCategories' => 'Test Categories',
                        'specialTestCategories' => 'Special Test Categories',
                        'professionalQualificationCategories' => 'Professional Qualification Categories',
                        'specialQualificationCategories' => 'Special Qualification Categories',
                        'leadershipQualificationCategories' => 'Leadership Qualification Categories',
                        'trainingQualificationCategories' => 'Training Qualification Categories',
                        'trainings' => 'Trainings',
                    ],
                    'organizationSystemData' => [
                        'organizationSystemData' => 'Organization System Data',
                        'hierarchy' => 'Hierarchy',
                        'permissionsMatrix' => 'Permissions Matrix',
                    ],
                    'seederData' => [
                        'data' => 'Data',
                        'seederData' => 'Seeder data',
                        'testData' => 'Test data',
                        'importData' => 'Import scouts from ECSET',
                    ],
                ],
                'seederData' => [
                    'seederData' => 'Seeder data',
                    'testData' => 'Test data',
                    'importData' => 'Import scouts from ECSET',
                    'seederDataConfirmMessage' => 'Would you like to update the seeder data?',
                    'testDataConfirmMessage' => 'Would you like to update the test data?',
                    'dataToBeAdded' => 'The following data will be added (if doesn\'t already exist):',
                    'importDataDescription' => 'Select a .csv file, or a .zip file containing .csv files.',
                    'updateData' => 'Update data',
                    'updateDataSuccess' => 'The data has been successfully updated.',
                ],
                'permissionsMatrix' => [
                    'all' => 'All',
                    'permissionsMatrix' => 'Permissions Matrix',
                    'noRight' => 'No',
                    'hasRightWith2FactorAuth' => '2FA',
                    'hasRight' => 'Yes',
                    'own' => 'Own',
                    'notOwn' => 'Not Own',
                    'model' => 'Model',
                    'field' => 'Field',
                    'obligatory' => 'Obligatory',
                    'create' => 'Create',
                    'read' => 'Read',
                    'update' => 'Update',
                    'delete' => 'Delete',
                    'confirmSave' => 'Are you sure you want to apply the highlighted changes?',
                    'confirmCancel' => 'Are you sure you want to cancel the highlighted changes?',
                    'editPermissions' => 'Edit Permissions',
                    'managePermissions' => 'Manage Permissions',
                    'selectItems' => 'Select permission to Copy/Delete',
                    'actionSetion' => 'Action',
                    'manageAction' => 'Action',
                    'copy' => 'Copy',
                    'toSection' => 'Select destination',
                    'execute' => 'Execute',
                    'executeAndClose' => 'Execute and close',
                    'copySuccess' => 'Permissions copied successfuly.',
                    'deleteSuccess' => 'Permissions deleted successfuly.',
                    'MODEL_GENERAL' => 'GENERAL ACCESS',
                    'noPermissionChanged' => 'No permissions were changed. If there was any dropdown highlighted with yellow and you received this message, please try again or contact administrator.',
                    'notAllPermissionChanged' => 'Updated only :updated of :from for action: :action->:value vale. The following mandate permissions should have been updated: :ids .',
                    'importPermissions' => 'Import Permissions',
                    'exportPermissions' => 'Export Permissions',
                    'errorCanNotFindAssociation' => 'Can not find association with name: :associationName',
                    'errorCanNotFindMandateType' => 'Can not find mandate type with name: :mandateTypeName in association: associationName',
                    'importWarning' => 'Current values will be overwritten and lost!',
                    'exportInfoTitle' => 'In the exported csv, the following is the meaning of the values:',
                    'valueZeroNull' => '0 empty field - no access',
                    'valueOne' => '1 - access 2FA',
                    'valueTwo' => '2 - access, no 2FA needed',
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
            'contactSettings' => [
                'contactSettings' => 'Contact Settings',
                'description' => 'Data from the Contact Us page.',
                'offices' => 'Offices',
                'promptNew' => 'Add new item',
                'address' => 'Address',
                'bank' => 'Bank',
                'bankAccount' => 'Bank Account',
                'email' => 'Email',
                'phoneNumbers' => 'Phone Numbers',
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
            'form' => [
                'form' => 'Form',
            ],
            'promise' => [
                'promise' => 'Promise',
                'promises' => 'Promises',
                'breadcrumb' => 'Promises',
            ],
            'test' => [
                'test' => 'Test',
                'tests' => 'Tests',
                'breadcrumb' => 'Tests',
            ],
            'specialTest' => [
                'specialTest' => 'Special Test',
                'specialTests' => 'Special Tests',
                'breadcrumb' => 'Special Tests',
            ],
            'professionalQualification' => [
                'professionalQualification' => 'Professional Qualification',
                'professionalQualifications' => 'Professional Qualifications',
                'breadcrumb' => 'Professional Qualifications',
            ],
            'specialQualification' => [
                'specialQualification' => 'Special Qualification',
                'specialQualifications' => 'Special Qualifications',
                'breadcrumb' => 'Special Qualifications',
            ],
            'leadershipQualification' => [
                'leadershipQualification' => 'Leadership Qualification',
                'leadershipQualifications' => 'Leadership Qualifications',
                'breadcrumb' => 'Leadership Qualifications',
            ],
            'trainingQualification' => [
                'trainingQualification' => 'Training Qualification',
                'trainingQualifications' => 'Training Qualifications',
                'breadcrumb' => 'Training Qualifications',
            ],
            'hierarchy' => [
                'hierarchy' => 'Hierarchy',
                'parent' => 'Parent',
                'sortOrder' => 'Sort order',
                'breadcrumb' => 'Hierarchy',
            ],
            'organizationBase' => [
                'organizationBase' => 'Organization base',
            ],
            'association' => [
                'association' => 'Association',
                'associations' => 'Associations',
                'contactName' => 'Contact name',
                'bankAccount' => 'Bank account',
                'leadershipPresentation' => 'Leadership Presentation',
                'additionalDetailsInfo' => 'Districts, Currencies and Mandates can be added after the Association has been created. Click the Create button after other information is filled.',
                'breadcrumb' => 'Associations',
                'ecsetCode' => [
                    'suffix' => 'ID number suffix',
                ],
                'teamFee' => 'Team fee',
                'membershipFee' => 'Membership fee',
                'currency' => 'Currency',
                'personalIdentificationNumberValidator' => 'Personal Identification Number Validator',
            ],
            'district' => [
                'district' => 'District',
                'districts' => 'Districts',
                'nameSuffix' => 'district',
                'website' => 'Website',
                'description' => 'Description',
                'facebookPage' => 'Facebook page',
                'contactName' => 'Contact name',
                'leadershipPresentation' => 'Leadership presentation',
                'bankAccount' => 'Bank account',
                'breadcrumb' => 'Districts',
                'teamsInfo' => 'Teams and Mandates can be added after the District has been created. Click the Create button after other information is filled.',
                'association' => 'Association',
                'organizationUnitNameWarning' => 'The name of the district can not contain the word "district."',
                'filterOrganizationUnitNameForWords' => 'district',
            ],
            'team' => [
                'team' => 'Team',
                'teams' => 'Teams',
                'nameSuffix' => 'team',
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
                'troopsPatrolsScoutsInfo' => 'Troops, Patrols, Scouts and Mandates can be added after the Team has been created. Click the Create button after other information is filled.',
                'breadcrumb' => 'Teams',
                'teamNumberTakenError' => 'This Team number is already taken.',
                'dateInTheFutureError' => 'The selected date is in the future.',
                'organizationUnitNameWarning' => 'The name of the team can not contain the word "team."',
                'filterOrganizationUnitNameForWords' => 'team',
            ],
            'troop' => [
                'troop' => 'Troop',
                'troops' => 'Troops',
                'nameSuffix' => 'troop',
                'website' => 'Website',
                'facebookPage' => 'Facebook page',
                'team' => 'Team',
                'patrolsInfo' => 'Patrols and Mandates can be added after the Troop has been created. Click the Create button after other information is filled.',
                'breadcrumb' => 'Troops',
                'organizationUnitNameWarning' => 'The name of the troop can not contain the word "troop."',
                'filterOrganizationUnitNameForWords' => 'troop',
            ],
            'patrol' => [
                'patrol' => 'Patrol',
                'patrols' => 'Patrols',
                'nameSuffix' => 'patrol',
                'website' => 'Website',
                'facebookPage' => 'Facebook page',
                'ageGroup' => 'Age group',
                'team' => 'Team',
                'troop' => 'Troop',
                'breadcrumb' => 'Patrols',
                'mandatesInfo' => 'Mandates can be added after the Patrol has been created. Click the Create button after other information is filled.',
                'troopNotInTheTeamError' => 'The selected Troop does not belong to the selected Team.',
                'organizationUnitNameWarning' => 'The name of the patrol can not contain the word "patrol."',
                'filterOrganizationUnitNameForWords' => 'partol',
            ],
            'currency' => [
                'currency' => 'Currency',
                'currencies' => 'Currencies',
                'breadcrumb' => 'Currencies',
                'code' => 'Code',
            ],
            'teamReport' => [
                'teamReport' => 'Team report',
                'teamReports' => 'Team reports',
                'team' => 'Team',
                'year' => 'Year',
                'number_of_adult_patrols' => 'Number of adult patrols',
                'number_of_explorer_patrols' => 'Number of explorer patrols',
                'number_of_scout_patrols' => 'Number of scout patrols',
                'number_of_cub_scout_patrols' => 'Number of little scout patrols',
                'number_of_mixed_patrols' => 'Number of mixed patrols',
                'scouting_year_report' => 'Scouting year report',
                'scouting_year_report_team_camp' => 'Scouting year report (team camp)',
                'scouting_year_report_homesteading' => 'Scouting year report (homesteading)',
                'scouting_year_report_programs' => 'Scouting year report (programs)',
                'scouting_year_team_applications' => 'Scouting year team applications',
                'spiritual_leader_name' => 'Spiritual leader name',
                'spiritual_leader_religion_id' => 'Spiritual leader religion',
                'spiritual_leader_occupation' => 'Spiritual leader occupation',
                'team_fee' => 'Team fee',
                'total_amount' => 'Total amount',
                'currency' => 'Currency',
                'name' => 'Name',
                'legalRelationship' => 'Legal relationship',
                'leadershipQualification' => 'Leadership qualification',
                'membershipFee' => 'Membership fee',
                'submittedAt' => 'Submitted at',
                'approvedAt' => 'Approved at',
                'breadcrumb' => 'Team reports',
                'scoutsInfo' => 'The Scouts will be visible after the Team Report has been created. Click the Create button after other information is filled.',
                'statuses' => [
                    'notCreated' => 'Not created',
                    'created' => 'In progress',
                    'submitted' => 'Waiting for approval',
                    'approved' => 'Approved',
                ],
                'validationExceptions' => [
                    'dateInTheFuture' => 'The selected Date is in the future.',
                    'submissionDateAfterApprovalDate' => 'The Submission date cannot be after the approval date.',
                ],
            ],
            'mandateType' => [
                'mandateType' => 'Mandate type',
                'mandateTypes' => 'Mandate types',
                'mandateModels' => 'Mandate models',
                'association' => 'Association',
                'parent' => 'Parent',
                'organizationTypeModelName' => 'Organization type',
                'required' => 'Required',
                'overlapAllowed' => 'Overlap allowed',
                'scout' => 'Scout',
                'startDate' => 'Start date',
                'endDate' => 'End date',
                'breadcrumb' => 'Mandate types',
                'activeMandateDeleteError' => 'There exist active Mandates of %name type, thus this Mandate type cannot be deleted.',
                'scoutTeam' => 'Scout\'s team',
            ],
            'mandate' => [
                'mandate' => 'Mandate',
                'mandates' => 'Mandates',
                'overlappingMandateError' => 'There already exists an overlapping Mandate for the given period.',
                'requiredMandateError' => 'There is no %name Mandate set for the current moment.',
            ],
            'trainings' => [
                'trainings' => 'Trainings',
            ],
            'gallery' => [
                'gallery' => 'Gallery',
                'rules' => [
                    'nameRequired' => 'The title is required.',
                    'nameBetween'  => 'The title must be between 3-64 character.',
                    'descriptionMax' => 'The description must be maximum 255 character.',
                ]
            ],
            'permissions' => [
                'permissions' => 'Permissions',
                'allPermissionsForScout' => 'All permissions for every model and field for Scout mandate.',
                'readPermissionForGuests' => 'Read permission for guests.',
            ],
            'contactFormSettings' => [
                'contactFormSettings' => 'Contact Form Plugin Settings',
            ],
            'sitesearchSettings' => [
                'sitesearchSettings' => 'Sites Search Plugin Settings',
                'enabledOnOrgCMSpages' => 'Enabled on organization unit frontend pages',
            ],
            'userGroups' => [
                'userGroups' => 'User Groups',
                'dataEntry' => 'Accident log data entry group',
                'admin' => 'Accident log admin group',
            ],
        ],
        'component' => [
            'general' => [
                'validationExceptions' => [
                    'emailAlreadyAssigned' => 'The e-mail address is already assgined to a user account.',
                    'passwordRegex' => 'The password must be at least 8 characters long, must contain a lower and uppercase letter, a number and a special character.',
                ],
            ],
            'resetPassword' => [
                'name' => 'Reset Password',
                'description' => 'Enables restoring the user\'s password.',
            ],
            'structure' => [
                'name' => 'Organization Structure',
                'description' => 'Displays the organization structure in a tree view.',
                'properties' => [
                    'level' => [
                        'title' => 'Level',
                        'description' => 'Structure starter level.',
                    ],
                    'model_name' => [
                        'title' => 'Model Name',
                        'description' => 'Starter model name.',
                    ],
                    'model_id' => [
                        'title' => 'Model Id',
                        'description' => 'Starter model id.',
                    ],
                    'mode' => [
                        'title' => 'Display mode',
                        'description' => 'The display mode of the structure',
                        'accordion' => 'Accordion',
                        'menu' => 'Menu',
                    ],
                ],
            ],
            'logos' => [
                'name' => 'Logos',
                'description' => 'Logos and corresponding links shown in grid view.',
                'sponsors' => [
                    'title' => 'List of Sponsors',
                    'hungarianGovernment' => 'Hungarian Government',
                    'harghitaCountyCouncil' => 'Harghita County Council',
                    'communitasFoundation' => 'Communitas Foundation',
                    'toyota' => 'Toyota',
                ],
                'discounts' => [
                    'title' => 'Companies offering discounts',
                    'mormotaLand' => 'Mormota Land',
                    'tiboo' => 'Tiboo',
                    'giftyShop' => 'Gifty Shop',
                    'zergeSpecialtyStore' => 'Zerge Specialt Store',
                ],
                'partners' => [
                    'title' => 'Partners',
                    'forumOfHungarianScoutAssociations' => 'Forum of Hungarian Scout Associations',
                    'transcarpathianHungarianScoutAssociation' => 'Transcarpathian Hungarian Scout Association',
                    'hungarianScoutAssociationInExteris' => 'Hungarian Scout Association in Exteris',
                    'hungarianScoutAssociation' => 'Hungarian Scout Association',
                    'slovakHungarianScoutAssociation' => 'Slovak Hungarian Scout Association',
                    'hungarianScoutAssociationOfVojvodina' => 'Hungarian Scout Association of Vojvodina',
                    'archdiocesanYouthHeadquarters' => 'Archdiocesan Youth Headquarters',
                    'marySWayTransylvania' => 'Mary\'s Way - Transylvania',
                    'proEducatione' => 'Pro Educatione',
                    'scoutsOfRomania' => 'Scouts Of Romania',
                ],
            ],
            'teamReport' => [
                'name' => 'Team Report',
                'description' => 'Enables creating yearly reports for the teams.',
                'validationExceptions' => [
                    'teamReportAlreadyExists' => 'The team report for this team and year already exists.',
                    'teamReportCannotBeFound' => 'The team report cannot be found.',
                    'teamCannotBeFound' => 'The team cannot be found.',
                ],
            ],
            'teamReports' => [
                'name' => 'Team Reports',
                'description' => 'Lists the Team Reports of a Team.',
                'edit' => 'Edit',
                'view' => 'View',
            ],
            'checkScoutStatus' => [
                'name' => 'Check Scout Status',
                'description' => 'Returns the status of a scout, depending on the scout id from the request.',
                'scoutCode' => [
                    'title' => 'Scout Code',
                    'description' => 'Unique scout id',
                ],
                'json' => [
                    'title' => 'Json Format',
                    'description' => 'If json parameter is \'json\' return respons in json format. ',
                ],
            ],
            'createFrontendAccounts' => [
                'name' => 'Create Frontend Account',
                'description' => 'Creates a Frontend user account for an existing Scout.',
                'currentPage' => '- current page -',
                'validationExceptions' => [
                    'invalidEcsetCode' => 'Invalid ID number',
                    'emailEcsetCodeMissMatch' => 'If you don\'t have an email address or your email address is different from the registered one please contact your patrol leader!',
                    'noScoutIsSelected' => 'No Scout is selected!',
                ],
                'messages' => [
                    'scoutHasNoEmail' => ':name has no email address!',
                    'scoutAlreadyHasUserAccount' => ':name already has a user account!',
                    'userAccountCreated' => 'User account was created for :name!',
                ],
            ],
            'organizationUnitFrontend' => [
                'name' => 'Organization Unit Frontend',
                'description' => 'Display the organization unit frontend page.',
            ],
            'twoFactorAuthentication' => [
                'name' => 'Two Factor Authentication',
                'description' => 'Enables two factor authentication.',
                'twoFactorAuthFailed' => 'Authentication failed, please try again!',
                'twoFactorAuthSuccess' => 'Authentication successful, thank you!',
            ],
            'accidentLog' => [
                'accidentLog' => 'Accident Log',
                'accidentLogRecordList' => 'Accident Log Record List',
                'createdBy' => 'Created by',
                'accidentDateTime' => 'Accident date-time',
                'examinerName' => 'Examiner name',
                'instructors' => 'Instructors',
                'programName' => 'Program name',
                'programType' => 'Program type',
                'activity' => 'Activity',
                'reason' => 'Reason',
                'injuredPersonAge' => 'Injured Person Age',
                'injuredPersonGender' => 'Injured Person Gender',
                'injuredPersonName' => 'Injured Person Name',
                'injury' => 'Injury',
                'injurySeverity' => [
                    'injurySeverity' => 'Injury Severity',
                    'slight' => 'Slight',
                    'medium' => 'Medium',
                    'serious' => 'Serious',
                    'fatal' => 'Fatal',
                ],
                'skippedDaysNumber' => 'Skipped Days Number',
                'toolsUsed' => 'Tools Used',
                'transportToDoctor' => 'Transport to Doctor',
                'evacuation' => 'Evacuation',
                'personsInvolvedInCare' => 'Persons Involved in Care',
                'attachments' => 'Attachments',
                'attachmentsComment' => 'Max. five files can be uploaded',
                'attachmentsValidationException' => 'Max. five files can be uploaded',
            ],
        ],
        'oauth' => [
            'onlyExistingUsersCanLogin'         => 'At the moment, only existing users are allowed to log in with oAuth!',
            'canNotRegisterLoginWithoutEmail'   => 'We cannot log you in, because there in no email address associated with this account!',
            'canNotFindScoutWithEmail'          => 'There is no scout with the returned email address!',
            'scoutAlreadyHasUserAccount'        => 'Scout already has a user account!',
            'canNotFindUser'                    => 'We can not find your user account!',
            'userIdAndScoutUserIdMismatch'      => 'The user attached to scout doesn\'t match with the returned user!',
        ],
    ],
];
