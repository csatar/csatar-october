<?php return [
    'frontEnd' => [
        'authException' => 'Az e-mail cím, az Igazolványszám vagy a jelszó téves!',
    ],
    'plugin' => [
        'name' => 'CSATÁR',
        'description' => 'Plugin az RMCSSZ CSATÁR alkalmazás számára',
        'author' => 'CSATÁR csapat',
        'admin' => [
            'general' => [
                'name' => 'Név',
                'name_abbreviation' => 'Név rövidítése',
                'email' => 'E-mail cím',
                'contactEmail' => 'Kapcsolattartó e-mail címe',
                'phone' => 'Telefonszám',
                'address' => 'Cím',
                'comment' => 'Megjegyzés',
                'id' => 'Azonosító',
                'createdAt' => 'Létrehozás ideje',
                'updatedAt' => 'Módosítás ideje',
                'deletedAt' => 'Törlés ideje',
                'select' => 'Válassz...',
                'logo' => 'Logó',
                'coordinates' => 'Koordináták',
                'ecsetCode' => 'Igazolványszám',
                'date' => 'Dátum',
                'location' => 'Helyszín',
                'qualificationCertificateNumber' => 'Képesítési Igazolás Száma',
                'training' => 'Képzés',
                'qualification' => 'Képzés',
                'qualificationLeader' => 'Képzésvezető',
                'relations' => 'Kapcsolatok',
                'password' => 'Jelszó',
                'password_confirmation' => 'Jelszó megerősítés',
                'organizationUnitNameWarning' => 'A szervezeti egység neve nem tartalmazhatja a szervezeti egység megnevezését.',
                'note' => 'Megjegyzés',
                'sortOrder' => 'Sorszám',
                'contentPage' => 'Bemutatkozó oldal',
                'searchResult' => 'Keresés eredménye',
                'yes' => 'Igen',
                'no' => 'Nem',
                'url' => 'URL',
                'warning' => 'FIGYELEM',
                'status' => 'Státusz',
                'active' => 'Aktív',
                'inActive' => 'Inaktív',
                'inactivationWarning' => 'Figyelem! Ha a státusz aktívról bármilyen típusú inaktív státuszra változik, a szervezeti egység alá tartozó összes szervezeti egység és cserkész státusza is inaktívá válik, valamint lejár az ezekhez tartozó összes mebízatás!'
            ],
            'ageGroups' => [
                'ageGroups' => 'Korosztályok',
                'numberOfPatrolsInAgeGroup' => 'Őrsök száma a korosztályban',
            ],
            'scout' => [
                'scout' => 'Cserkész',
                'scouts' => 'Cserkészek',
                'scoutData' => 'Cserkész adatai',
                'userId' => 'Felhasználó azonosítója',
                'user' => 'Felhasználói fiók',
                'namePrefix' => 'Név előtag',
                'familyName' => 'Családnév',
                'givenName' => 'Keresztnév',
                'nickname' => 'Becenév',
                'personalIdentificationNumber' => 'Személyi szám',
                'gender' => [
                    'gender' => 'Nem',
                    'male' => 'Férfi',
                    'female' => 'Nő',
                    'other' => 'Egyéb',
                ],
                'isActive' => 'Aktív',
                'allergy' => 'Allergia',
                'foodSensitivity' => 'Ételérzékenység',
                'legalRelationship' => 'Jogviszony',
                'chronicIllnesses' => 'Krónikus betegségek',
                'specialDiet' => 'Különleges étrend',
                'religion' => 'Vallás',
                'nationality' => 'Nemzetiség',
                'tShirtSize' => 'Póló mérete',
                'birthdate' => 'Dátum',
                'nameday' => 'Névnap',
                'maidenName' => 'Születési/leánykori név',
                'birthplace' => 'Hely',
                'addressCountry' => 'Ország',
                'addressZipcode' => 'Irányítószám',
                'addressCounty' => 'Megye',
                'addressLocation' => 'Település',
                'addressStreet' => 'Utca',
                'addressNumber' => 'Házszám',
                'mothersName' => 'Név',
                'mothersPhone' => 'Telefonszám',
                'mothersEmail' => 'E-mail cím',
                'fathersName' => 'Név',
                'fathersPhone' => 'Telefonszám',
                'fathersEmail' => 'E-mail cím',
                'legalRepresentativeName' => 'Név',
                'legalRepresentativePhone' => 'Telefonszám',
                'legalRepresentativeEmail' => 'E-mail cím',
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
                'allergies' => 'Allergiák',
                'promises' => 'Fogadalmak, ígéretek',
                'tests' => 'Próbák',
                'specialTests' => 'Különpróbák',
                'professionalQualifications' => 'Szakági képesítések',
                'specialQualifications' => 'Szakági különpróbák',
                'leadershipQualifications' => 'Vezetői képesítések',
                'trainingQualifications' => 'Kiképzői képesítések',
                'foodSensitivities' => 'Ételérzékenységekek',
                'additionalDetailsInfo' => 'Allergák, Krónikus Betegségek, Ételérékenységek, Fogadalmak, Próbák, Különpróbák, Szakági képesítések, Szakági különpróbák, Vezetői képesítések és Kiképzői képesítések hozzáadása a Cserkész létrehozása után lehetséges. Miután a többi adatot kitöltötted, kattints a Létrehozás gombra.',
                'breadcrumb' => 'Cserkészek',
                'team' => 'Csapat',
                'troop' => 'Raj',
                'patrol' => 'Őrs',
                'profile_image' => 'Profilkép',
                'profile_image_comment' => 'Igazolványkép formátumban (mellkastól felfele), "cserkészdíszben".',
                'sections' => [
                    'birthData' => 'Születési adatok',
                    'addressData' => 'Cím',
                    'mothersData' => 'Anya',
                    'fathersData' => 'Apa',
                    'legalRepresentativeData' => 'Törvényes képviselő',
                    'schoolData' => 'Tanulmányok',
                    'occupation' => 'Foglalkozás',
                    'otherData' => 'Egyéb adatok',
                ],
                'validationExceptions' => [
                    'noTeamSelected' => 'Válassz egy csapatot!',
                    'troopNotInTheTeam' => 'A kiválasztott Raj nem tartózik a kiválasztott Csapathoz.',
                    'troopNotInTheTeamOrTroop' => 'A kiválasztott Őrs nem tartózik a kiválasztott Csapathoz vagy Rajhoz.',
                    'dateInTheFuture' => 'A Dátum nem lehet a jövőben.',
                    'endDateBeforeStartDate' => 'A végső időpont nem lehet a kezdeti időpont előtt.',
                    'associationRequired' => 'A Szövetséget kötelező megadni.',
                    'registrationFormRequired' => 'A Bejelentkezési és nyilvántartási lap kötelező.',
                    'dateRequiredError' => 'A Dátum megadása a %name %category esetén kötelező.',
                    'locationRequiredError' => 'A Helyszín megadása a %name %category esetén kötelező.',
                    'qualificationCertificateNumberRequiredError' => 'A Képesítési Igazolás Számának megadása a %name %category esetén kötelező.',
                    'qualificationRequiredError' => 'A Képzés megadása a %name %category esetén kötelező.',
                    'qualificationLeaderRequiredError' => 'A Képzésvezető megadása a %name %category esetén kötelező.',
                    'mandateEndDateBeforeStartDate' => 'A végső időpont nem lehet a kezdeti időpont előtt a %name megbízatás esetén.',
                    'dateInTheFutureError' => 'A Dátum a %name %category esetén nem lehet a jövőben.',
                    'invalidPersonalIdentificationNumber' => 'Érvénytelen személyi szám.',
                    'legalRepresentativePhoneUnderAge' => 'Kiskorú cserkés esetén kötelező megadni az egyik szülő vagy törvényes képviselő telefonszámát.',
                ],
                'staticMessages' => [
                    'personalDataNotAccepted' => 'Kérlek ellenőrizd, hogy helyesek-e a személyes adataid itt!',
                ],
                'activeMandateDeleteError' => 'A(z) %name nevű Tagnak létezik aktív Megbízatása, így ez a Tag nem törölhető.',
                'scoutTeam' => 'Tag csapata',
                'inactivationWarning' => 'Figyelem! Ha a státusz aktívról inaktívra változik, a cserkész összes megbízatása lejár!'
            ],
            'admin' => [
                'menu' => [
                    'scout' => 'Cserkész',
                    'scoutSystemData' => [
                        'scoutSystemData' => 'Cserkész rendszeradatok',
                        'legalRelationshipCategories' => 'Jogviszony típusok',
                        'chronicIllnessCategories' => 'Krónikus betegségek',
                        'allergyCategories' => 'Allergiák',
                        'foodSensitivityCategories' => 'Ételérzékenység típusok',
                        'specialDietCategories' => 'Különleges étrendek',
                        'religionCategories' => 'Vallások',
                        'tShirtSizeCategories' => 'Póló méretek',
                        'promiseCategories' => 'Fogadalom, ígéret típusok',
                        'testCategories' => 'Próba típusok',
                        'specialTestCategories' => 'Különpróba típusok',
                        'professionalQualificationCategories' => 'Szakági képesítés típusok',
                        'specialQualificationCategories' => 'Szakági különpróba típusok',
                        'leadershipQualificationCategories' => 'Vezetői képesítés típusok',
                        'trainingQualificationCategories' => 'Kiképzői képesítés típusok',
                        'trainings' => 'Képzések',
                    ],
                    'organizationSystemData' => [
                        'organizationSystemData' => 'Szervezeti rendszeradatok',
                        'hierarchy' => 'Hierarchia',
                        'permissionsMatrix' => 'Jogosultság Mátrix',
                    ],
                    'seederData' => [
                        'data' => 'Adatok',
                        'seederData' => 'Alapértelmezett adatok',
                        'testData' => 'Teszt adatok',
                        'importData' => 'ECSET taglista importálása',
                    ],
                ],
                'seederData' => [
                    'seederData' => 'Alapértelmezett adatok',
                    'testData' => 'Teszt adatok',
                    'importData' => 'ECSET taglista importálása',
                    'seederDataConfirmMessage' => 'Szeretnéd frissíteni az alapértelmezett adatokat?',
                    'testDataConfirmMessage' => 'Szeretnéd frissíteni a teszt adatokat?',
                    'dataToBeAdded' => 'A következő adatok lesznek hozzáadva (ha már nem voltak felvéve):',
                    'importDataDescription' => 'Adj meg egy .csv fájlt, vagy egy .csv fájlokat tartalmazó .zip fájlt.',
                    'updateData' => 'Adatok frissítése',
                    'updateDataSuccess' => 'Az adatok frissítve lettek.',
                ],
                'permissionsMatrix' => [
                    'all' => 'Összes',
                    'permissionsMatrix' => 'Jogosultság Matrix',
                    'noRight' => 'Nem',
                    'hasRightWith2FactorAuth' => '2FA',
                    'hasRight' => 'Igen',
                    'own' => 'Saját',
                    'notOwn' => 'Nem Saját',
                    'model' => 'Modell',
                    'field' => 'Mező',
                    'obligatory' => 'Kötelező',
                    'create' => 'Létrehozás',
                    'read' => 'Olvasás',
                    'update' => 'Módosítás',
                    'delete' => 'Törlés',
                    'confirmSave' => 'Biztos benne, hogy menteni szeretné a kijelölt változtatásokat?',
                    'confirmCancel' => 'Biztos benne, hogy visszavonja a kijelölt változtatásokat?',
                    'editPermissions' => 'Jogosultságok módosítása',
                    'managePermissions' => 'Jogosultságok mendzselése',
                    'selectItems' => 'Válassza ki a másolandó/törlendő jogosultságokat',
                    'actionSetion' => 'Action',
                    'manageAction' => 'Művelet',
                    'copy' => 'Másolás',
                    'toSection' => 'Válassza ki, hogy hová szeretné másolni a fent kiválasztott jogosultságokat',
                    'execute' => 'Végrehajtás',
                    'executeAndClose' => 'Végrehajtás és bezárás',
                    'copySuccess' => 'Jogosultságok sikeresen átmásolva.',
                    'deleteSuccess' => 'Jogosultságok törölve.',
                    'MODEL_GENERAL' => 'ÁLTALÁNOS HOZZÁFÉRÉS',
                    'noPermissionChanged' => 'A jogosultságok nem változtak. Ha volt sárgával kijelölt legördülő mező és mégis ezt az üzentet kaptad, próbáld újra vagy szólj a rendszergazdának.',
                    'notAllPermissionChanged' => 'Csak :updated jogosultságot sikerült elmenteni a :from-ból a következő műveletre és értékre: :action->:value. A következők jogosultságok kellett volna változzanak: :ids.',
                    'importPermissions' => 'Jogosultságok importálása',
                    'exportPermissions' => 'Jogosultságok exportálása',
                    'errorCanNotFindAssociation' => 'Nem található ":associationName" nevű szövetség!',
                    'errorCanNotFindMandateType' => 'Nem található ":mandateTypeName" nevű megbízatás típus a ":associationName" nevű szövetségben!',
                    'importWarning' => 'A jelenlegi értékek visszavonhatatlanul el fognak veszni!',
                    'exportInfoTitle' => 'Az exportált csv-ben, az következő az értékek jelentése:',
                    'valueZeroNull' => '0 vagy üres mező - nincs hozzáférés',
                    'valueOne' => '1 - hozzáférés csak 2FA-val',
                    'valueTwo' => '2 - hozzáférés 2FA nélkül is',
                ],
            ],
            'allergy' => [
                'allergy' => 'Allergia',
                'allergies' => 'Allergiák',
                'breadcrumb' => 'Allergiák',
            ],
            'foodSensitivity' => [
                'foodSensitivity' => 'Ételérzékenység',
                'foodSensitivities' => 'Ételérzékenységek',
                'breadcrumb' => 'Ételérzékenységek',
            ],
            'contactSettings' => [
                'contactSettings' => 'Kapcsolat beállítások',
                'description' => 'Adatok a Kapcsolat oldalról.',
                'offices' => 'Irodák',
                'promptNew' => 'Új hozzáadása',
                'address' => 'Cím',
                'bank' => 'Bank',
                'bankAccount' => 'Bankszámla',
                'email' => 'E-mail cím',
                'phoneNumbers' => 'Telefonszámok',
            ],
            'chronicIllness' => [
                'chronicIllness' => 'Krónikus betegség',
                'chronicIllnesses' => 'Krónikus betegségek',
                'breadcrumb' => 'Krónikus betegségek',
            ],
            'legalRelationship' => [
                'legalRelationship' => 'Jogviszony',
                'legalRelationships' => 'Jogviszonyok',
                'sortOrder' => 'Sorrend',
                'breadcrumb' => 'Jogviszonyok',
            ],
            'religion' => [
                'religion' => 'Vallás',
                'religions' => 'Vallások',
                'breadcrumb' => 'Vallások',
            ],
            'specialDiet' => [
                'specialDiet' => 'Különleges étrend',
                'specialDiets' => 'Különleges étrendek',
                'breadcrumb' => 'Különleges étrendek',
            ],
            'tShirtSize' => [
                'tShirtSize' => 'Pólóméret',
                'tShirtSizes' => 'Pólóméretek',
                'breadcrumb' => 'Pólóméretek',
            ],
            'form' => [
                'form' => 'Űrlap',
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
                'hierarchy' => 'Hierarchia',
                'parent' => 'Szülő',
                'sortOrder' => 'Sorrend',
                'breadcrumb' => 'Hierarchia',
            ],
            'organizationBase' => [
                'organizationBase' => 'Általános szervezeti egység',
            ],
            'association' => [
                'association' => 'Szövetség',
                'associations' => 'Szövetségek',
                'contactName' => 'Kapcsolattartó neve',
                'bankAccount' => 'Bankszámla',
                'leadershipPresentation' => 'Vezetőség bemutatása',
                'additionalDetailsInfo' => 'Körzetek, Pénznemek és Megbízatások hozzáadása a Szövetség létrehozása után lehetséges. Miután a többi adatot kitöltötted, kattints a Létrehozás gombra.',
                'breadcrumb' => 'Szövetségek',
                'ecsetCode' => [
                    'suffix' => 'Igazolványszám utótag',
                ],
                'teamFee' => 'Csapat fenntartói díj',
                'membershipFee' => 'Tagdíj értéke',
                'currency' => 'Pénznem',
                'personalIdentificationNumberValidator' => 'Személyi szám hitelesítő',
            ],
            'district' => [
                'district' => 'Körzet',
                'districts' => 'Körzetek',
                'nameSuffix' => 'körzet',
                'website' => 'Weboldal',
                'description' => 'Leírás',
                'facebookPage' => 'Facebook oldal',
                'contactName' => 'Kapcsolattartó neve',
                'leadershipPresentation' => 'Vezetőség bemutatása',
                'bankAccount' => 'Bankszámla',
                'breadcrumb' => 'Körzetek',
                'teamsInfo' => 'Csapatok és Megbízatások hozzáadása a Körzet létrehozása után lehetséges. Miután a többi adatot kitöltötted, kattints a Létrehozás gombra.',
                'association' => 'Szövetség',
                'organizationUnitNameWarning' => 'A körzet neve nem tartalmazhatja a "körzet" szót.',
                'filterOrganizationUnitNameForWords' => 'körzet, korzet',
            ],
            'team' => [
                'team' => 'Csapat',
                'teams' => 'Csapatok',
                'nameSuffix' => 'cserkészcsapat',
                'teamNumber' => 'Csapatszám',
                'foundationDate' => 'Alapítás dátuma',
                'website' => 'Weboldal',
                'facebookPage' => 'Facebook oldal',
                'contactName' => 'Kapcsolattartó neve',
                'history' => 'Csapat története',
                'leadershipPresentation' => 'Vezetőség bemutatása',
                'description' => 'Leírás',
                'juridicalPersonName' => 'Jogi személy neve',
                'juridicalPersonAddress' => 'Jogi személy címe',
                'juridicalPersonTaxNumber' => 'Jogi személy adószáma',
                'juridicalPersonBankAccount' => 'Jogi személy bankszámlája',
                'homeSupplierName' => 'Otthoni beszállító neve',
                'district' => 'Körzet',
                'troopsPatrolsScoutsInfo' => 'Rajok, Őrsök, Cserkészek és Megbízatások hozzáadása a Csapat létrehozása után lehetséges. Miután a többi adatot kitöltötted, kattints a Létrehozás gombra.',
                'breadcrumb' => 'Csapatok',
                'teamNumberTakenError' => 'Ez a csapatszám már foglalt.',
                'dateInTheFutureError' => 'A dátum nem lehet a jövőben.',
                'organizationUnitNameWarning' => 'A csapat neve nem tartalmazhatja a "csapat" szót.',
                'filterOrganizationUnitNameForWords' => 'cserkészcsapat, csapat',
                'active' => 'Aktív',
                'inActive' => 'Inaktív',
                'suspended' => 'Szünetelő',
                'forming' => 'Alakuló/újraalakuló',
            ],
            'troop' => [
                'troop' => 'Raj',
                'troops' => 'Rajok',
                'nameSuffix' => 'raj',
                'website' => 'Weboldal',
                'facebookPage' => 'Facebook oldal',
                'team' => 'Csapat',
                'patrolsInfo' => 'Őrsök és Megbízatások hozzáadása a Raj létrehozása után lehetséges. Miután a többi adatot kitöltötted, kattints a Létrehozás gombra.',
                'breadcrumb' => 'Rajok',
                'organizationUnitNameWarning' => 'A raj neve nem tartalmazhatja a "raj" szót.',
                'filterOrganizationUnitNameForWords' => 'raj',
            ],
            'patrol' => [
                'patrol' => 'Őrs',
                'patrols' => 'Őrsök',
                'nameSuffix' => 'őrs',
                'website' => 'Weboldal',
                'facebookPage' => 'Facebook oldal',
                'ageGroup' => 'Korosztály',
                'team' => 'Csapat',
                'troop' => 'Raj',
                'breadcrumb' => 'Őrsök',
                'mandatesInfo' => 'Megbízatások hozzáadása az Őrs létrehozása után lehetséges. Miután a többi adatot kitöltötted, kattints a Létrehozás gombra.',
                'troopNotInTheTeamError' => 'A kiválasztott Raj nem tartózik a kiválasztott Csapathoz.',
                'organizationUnitNameWarning' => 'Az őrs neve nem tartalmazhatja az "őrs" szót.',
                'filterOrganizationUnitNameForWords' => 'őrs, örs, ors',
                'gender' => [
                    'mixed' => 'Vegyes',
                ],
            ],
            'currency' => [
                'currency' => 'Pénznem',
                'currencies' => 'Pénznemek',
                'breadcrumb' => 'Pénznemek',
                'code' => 'Kód',
            ],
            'teamReport' => [
                'teamReport' => 'Csapatjelentés',
                'teamReports' => 'Csapatjelentések',
                'team' => 'Csapat',
                'year' => 'Év',
                'number_of_adult_patrols' => 'Felnőtt őrsök száma',
                'number_of_explorer_patrols' => 'Felfedező őrsök száma',
                'number_of_scout_patrols' => 'Cserkész őrsök száma',
                'number_of_cub_scout_patrols' => 'Kiscserkész őrsök száma',
                'number_of_mixed_patrols' => 'Vegyes őrsök száma',
                'scouting_year_report' => 'Előző cserkérkészév beszámoló',
                'scouting_year_report_team_camp' => 'Előző cserkérkészév beszámoló (csapat tábor)',
                'scouting_year_report_homesteading' => 'Előző cserkérkészév beszámoló (tanyázás)',
                'scouting_year_report_programs' => 'Előző cserkészév beszámoló (programok)',
                'scouting_year_team_applications' => 'Előző cserkészév csapat pályázatai',
                'spiritual_leader_name' => 'Csapat lelki vezetője',
                'spiritual_leader_religion_id' => 'Csapat lelki vezetőjének felekezete',
                'spiritual_leader_occupation' => 'Csapat lelki vezetőjének foglalkozás',
                'team_fee' => 'Csapatfenntartói járulék',
                'total_amount' => 'Befizetendő összeg',
                'currency' => 'Pénznem',
                'name' => 'Név',
                'legalRelationship' => 'Jogviszony',
                'leadershipQualification' => 'Vezetői képesítés',
                'membershipFee' => 'Tagdíj értéke',
                'submittedAt' => 'Beküldés ideje',
                'approvedAt' => 'Elfogadás ideje',
                'breadcrumb' => 'Csapatjelentések',
                'scoutsInfo' => 'A Csapatjelentés létrehozása után, a csapathoz tartózó cserkészek is láthatóak lesznek. Töltsd ki a kötelező mezőket, majd kattints a Létrehozás gombra.',
                'statuses' => [
                    'notCreated' => 'Nincs létrehozva',
                    'created' => 'Szerkesztés alatt',
                    'submitted' => 'Elfogadásra vár',
                    'approved' => 'Elfogadva',
                ],
                'validationExceptions' => [
                    'dateInTheFuture' => 'A Dátum nem lehet a jövőben.',
                    'submissionDateAfterApprovalDate' => 'A Beküldés ideje nem lehet az Elfogadás ideje után.',
                ],
            ],
            'mandateType' => [
                'mandateType' => 'Megbízatás típus',
                'mandateTypes' => 'Megbízatás típusok',
                'mandateModels' => 'Megbízatás modellek',
                'association' => 'Szövetség',
                'parent' => 'Szülő',
                'organizationTypeModelName' => 'Szervezeti egység',
                'required' => 'Kötelező',
                'overlapAllowed' => 'Átfedés megengedett',
                'scout' => 'Tag',
                'startDate' => 'Kezdete',
                'endDate' => 'Vége',
                'breadcrumb' => 'Megbízatás típusok',
                'activeMandateDeleteError' => 'Létezik %name típusú aktív Megbízatás, így ez a Megbízatás típus nem törölhető.',
            ],
            'mandate' => [
                'mandate' => 'Megbízatás',
                'mandates' => 'Megbízatások',
                'overlappingMandateError' => 'Már létezik Megbízatás a megadott periódusra.',
                'requiredMandateError' => 'Jelenleg nincs %name Megbízatás beállítva.',
            ],
            'trainings' => [
                'trainings' => 'Képzések',
            ],
            'gallery' => [
                'gallery' => 'Galéria',
                'rules' => [
                    'nameRequired' => 'A cím megadása kötelező.',
                    'nameBetween'  => 'A cím 3 - 64 karakter között kell legyen.',
                    'descriptionMax' => 'A leírás maximum 255 karakter lehet',
                ]
            ],
            'permissions' => [
                'permissions' => 'Jogosultságok',
                'allPermissionsForScout' => 'Minden jogosultság a Cserkész megbízatáshoz, az összes model összes mezőjéhez.',
                'readPermissionForGuests' => 'Olvasási jog vendégeknek.',
            ],
            'contactFormSettings' => [
                'contactFormSettings' => 'Kapcsolat űrlap plugin beállítások',
            ],
            'sitesearchSettings' => [
                'sitesearchSettings' => 'Sites Search plugin beállítások',
                'enabledOnOrgCMSpages' => 'Bekapcsolva a szervezeti egységek frontend oldalain.',
            ],
            'userGroups' => [
                'userGroups' => 'Felhasználó csoportok',
                'dataEntry' => 'Baleseti log adatbevívó',
                'admin' => 'Baleseti log adminisztrátor',
            ],
        ],
        'component' => [
            'general' => [
                'validationExceptions' => [
                    'emailAlreadyAssigned' => 'Ez az e-mail cím már felhasználói fiókhoz van rendelve.',
                    'passwordRegex' => 'A jelszó kell tartalmazzon legalább 8 karaktert, kis-, és nagybetűt, valamint számot vagy szimbólumot.',
                ],
            ],
            'resetPassword' => [
                'name' => 'Jelszó visszaállítása',
                'description' => 'A felhasználó jelszavának a visszaállítását teszi lehetővé.',
            ],
            'structure' => [
                'name' => 'Szervezeti struktúra',
                'description' => 'Fa nézetben jeleníti meg a szervezeti struktúrát.',
                'properties' => [
                    'level' => [
                        'title' => 'Szint',
                        'description' => 'Struktúra kezdő szintje.',
                    ],
                    'model_name' => [
                        'title' => 'Model neve',
                        'description' => 'Kezdő model név.',
                    ],
                    'model_id' => [
                        'title' => 'Model Id',
                        'description' => 'Kezdő model id.',
                    ],
                    'mode' => [
                        'title' => 'Megjelenítési mód',
                        'description' => 'A szervezeti struktúra megjelenítéi módja',
                        'accordion' => 'Akkordion',
                        'menu' => 'Menü',
                    ],
                ],
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
            ],
            'teamReport' => [
                'name' => 'Csapatjelentés',
                'description' => 'Éves csapatjelentés létrehozását teszi lehetővé a csapatok számára.',
                'validationExceptions' => [
                    'teamReportAlreadyExists' => 'Már létezik csapatjelentés e csapat számára, erre az évre.',
                    'teamReportCannotBeFound' => 'A csapatjelentés nem található.',
                    'teamCannotBeFound' => 'A csapat nem található.',
                ],
            ],
            'teamReports' => [
                'name' => 'Csapatjelentések',
                'description' => 'Csapat csapatjelentéseinek listázása.',
                'edit' => 'Módosítás',
                'view' => 'Megtekintés',
            ],
            'checkScoutStatus' => [
                'name' => 'Cserkész állapotának ellenőrzése',
                'description' => 'A cserkész állapotát téríti vissza a Kérésből származó cserkészazonosító alapján.',
                'scoutCode' => [
                    'title' => 'Cserkész azonosítója',
                    'description' => 'Egyedi cserkész azonosító',
                ],
                'json' => [
                    'title' => 'JSON formátum',
                    'description' => 'Ha a JSON formátum értéke \'json\', akkor a választ JSON formátumban téríti vissza.',
                ],
            ],
            'createFrontendAccounts' => [
                'name' => 'Frontend felhasználó létrehozása',
                'description' => 'Lehetővé teszi frontend felhasználó létrehozását.',
                'currentPage' => '- jelenlegi oldal -',
                'validationExceptions' => [
                    'invalidEcsetCode' => 'Érvénytelen Igazolványszám',
                    'emailEcsetCodeMissMatch' => 'Ha nincs email címed, vagy nem egyezik meg a rendszerben levővel, vedd fel a kapcsolatot az őrsvezetőddel.',
                    'noScoutIsSelected' => 'Nincs tag kiválasztva!',
                ],
                'messages' => [
                    'scoutHasNoEmail' => ':name nem rendelkezik e-mail címmel!',
                    'scoutAlreadyHasUserAccount' => ':name már rendelkezik felhasználói fiókkal!',
                    'userAccountCreated' => ':name cserkésznek létrejött a felhasználói fiókja!',
                ],
            ],
            'organizationUnitFrontend' => [
                'name' => 'Szervezeti Egység Frontend',
                'description' => 'Megyjeleníti egy szerevezeti egység frontend oldalát.',
            ],
            'twoFactorAuthentication' => [
                'name' => 'Két faktoros hitelesítése',
                'description' => 'Két faktoros hitelesítése plugin.',
                'twoFactorAuthFailed' => 'A hitelesítés sikertelen, kérlek próbáld újra!',
                'twoFactorAuthSuccess' => 'A hitelesítés sikeres, köszönjük!',
                'reset' => 'Két faktoros hitelesítés visszaállítása',
                'resetSuccess' => 'A két faktoros hitelesítés skeresen vissza lett állítva. A felhasználónak a hitelesítő alkalmazásból is törölnie kell a fióokot.',
            ],
            'accidentLog' => [
                'accidentLog' => 'Baleseti napló',
                'createdBy' => 'Lérehozta',
                'accidentDateTime' => 'Baleset dátum és időpont',
                'examinerName' => 'Vizsgálatot végző neve',
                'instructors' => 'Oktatók',
                'programName' => 'Program neve',
                'programType' => 'Program típusa',
                'activity' => 'Tevékenység',
                'reason' => 'Ok',
                'injuredPersonAge' => 'Sérült/ beteg életkora',
                'injuredPersonGender' => 'Sérült/ beteg neme',
                'injuredPersonName' => 'Sérült/ beteg neve',
                'injury' => 'Sérülés/betegség megnevezése',
                'injurySeverity' => [
                    'injurySeverity' => 'Eset súlyossága',
                    'slight' => 'Enyhe',
                    'medium' => 'Közepes',
                    'serious' => 'Súlyos',
                    'fatal' => 'Végzetes',
                ],
                'skippedDaysNumber' => 'Kiesett programnapok száma',
                'toolsUsed' => 'Használt eszközök',
                'transportToDoctor' => 'Orvoshoz szállítás',
                'evacuation' => 'Evakuáció',
                'personsInvolvedInCare' => 'Ellátásba bevont személyek',
                'attachments' => 'Csatolt fájlok',
                'attachmentsComment' => 'Maximum öt fájlt lehet feltölteni',
                'attachmentsValidationException' => 'Maximum öt fájlt lehet feltölteni',
                'created_by' => 'Created by',
            ],
        ],
        'oauth' => [
            'onlyExistingUsersCanLogin' => 'Jelenleg csak létező felhasználók léphetnek be oAuth-al!',
            'canNotRegisterLoginWithoutEmail' => 'Nem tudunk beléptetni, mert ehhez a fiókhoz nincs email cím rendelve!',
            'canNotFindScoutWithEmail' => 'Nem található cserkész a visszatérített e-mail címmel!',
            'scoutAlreadyHasUserAccount' => 'A cserkész már rendelkezik felhasználói fiókkal!',
            'canNotFindUser' => 'A felhasználói fiók nem található!',
            'userIdAndScoutUserIdMismatch' => 'A cserkészhez csatolt- és a visszatérített felhasználói fiók nem egyezik!',
        ],
    ],
];
