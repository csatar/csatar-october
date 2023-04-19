<?php namespace Csatar\Csatar\Updates;

use Csatar\Csatar\Models\AgeGroup;
use Csatar\Csatar\Models\Allergy;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\ChronicIllness;
use Csatar\Csatar\Models\ContactSettings;
use Csatar\Csatar\Models\Currency;
use Csatar\Csatar\Models\FoodSensitivity;
use Csatar\Csatar\Models\Hierarchy;
use Csatar\Csatar\Models\LeadershipQualification;
use Csatar\Csatar\Models\LegalRelationship;
use Csatar\Csatar\Models\Locations;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\PermissionBasedAccess;
use Csatar\Csatar\Models\ProfessionalQualification;
use Csatar\Csatar\Models\Promise;
use Csatar\Csatar\Models\Religion;
use Csatar\Csatar\Models\SpecialDiet;
use Csatar\Csatar\Models\SpecialTest;
use Csatar\Csatar\Models\TShirtSize;
use Csatar\Csatar\Models\Training;
use Csatar\Forms\Models\Form;
use Seeder;
use Db;
use RainLab\User\Models\UserGroup;
use Rainlab\Location\Models\Country;

class SeederData extends Seeder
{
    public const DATA = [
        'allergy'                   => [
            'Nincs',
            'Ételintollerancia',
            'Ételallergiák',
            'Pollen alergia/Szénanátha',
            'Belélegzései allergia',
            'Rovarméreg allergia',
            'Kontakt allergia (vegyszerekre, anyagokra)',
            'Gyógyszerallergia',
            'Egyéb'
        ],
        'legalRelationship'         => [
            'Alakuló csapat tag',
            'Újonc',
            'Tag',
            'Tiszteletbeli tag',
            'Érvénytelen adat',
        ],
        'specialTest'               => [
            'Szakács',
            'Rovás',
            'Arany toll díj',
            'Tűzrakó',
            'Tájékozódó',
            'Csomózó',
            'Három sastoll',
            'Elsősegélynyújtó',
            'Gombász',
            'Helyi idegenvezető',
            'Színész',
            'Honismereti',
            'Szállásmester',
            'Nótafa',
            'Zenész',
            'Fényképész',
            'Íródeák',
        ],
        'specialDiet'               => [
            'Nem igényel különleges étrendet',
            'Sporttáplálkozás',
            'Gluténmentes',
            'Szénhidrátmentes',
            'Laktózmentes',
            'Paleo',
            'Vegán',
            'Vegetáriánus',
            'Egyéb',
        ],
        'religion'                  => [
            'Adventista',
            'Baptista',
            'Evangélikus',
            'Görög katolikus',
            'Jehova tanúi',
            'Muzulmán',
            'Ortodox',
            'Református',
            'Római katolikus',
            'Unitárius',
            'Más felekezethez tartozó'
        ],
        'tShirtSize'                => [
            '4',
            '6',
            '8',
            '10',
            '12',
            'XS',
            'S',
            'M',
            'L',
            'XL',
            'XXL',
            '3XL',
            '4XL',
            '5XL'
        ],
        'chronicIllness'            => [
            'Magas vérnyomás',
            'Szívelégtelenség',
            'Cukorbetegség',
            'Mozgásszervi betegségek',
            'Pajzsmirigy működési zavar',
            'Schizophrenia, schizotypiás és paranoid zavarok',
            'Daganatos betegség',
            'Krónikus légzési elégtelenség',
            'Veseelégtelenség',
            'HIV/SIDA',
            'Epilepszia',
            'Autizmus',
            'Mentális beteg',
            'Egyéb',
        ],
        'hierarchy'                 => [
            'RMCSSZ',
            'Körzetvezető',
            'Csapatvezető',
            'Rajvezető',
            'Őrsvezető',
            'Cserkész',
        ],
        'currency'                  => [
            'EUR',
            'HRK',
            'HUF',
            'RON',
            'RSD',
            'UAH',
        ],
        'association'               => [
            'Horvátországi magyar cserkészek',
            'Kárpátaljai Magyar Cserkészszövetség',
            'Külföldi Magyar Cserkészszövetség',
            'Magyar Cserkészszövetség',
            'Romániai Magyar Cserkészszövetség',
            'Szlovákiai Magyar Cserkészszövetség',
            'Vajdasági Magyar Cserkészszövetség',
        ],
        'foodSensitivity'           => [
            'liszt',
            'tejfehérje (kazein)',
            'tojás',
            'mogyoró',
            'szója',
            'dió',
            'kagyló',
            'eper',
            'Egyéb',
        ],
        'promise'                   => [
            'Kiscserkész igéret',
            'Cserkész fogadalom',
        ],
        'professionalQualification' => [
            'Regős',
        ],
        'leadershipQualification'   => [
            'Segédőrsvezető képzés',
            'Őrsvezető képzés',
            'Felnőtt őrsvezető képzés',
            'Segédvezető képzés',
            'Cserkész vezető',
        ], // if new leader qualification is added, and it's level is below 'Cserkész vezető', qualification_level column must be introduced, and leadership qualifications must be sorter by level
        'form'                      => [
            [
                'title' => 'Tag',
                'model' => 'Csatar\Csatar\Models\Scout',
            ],
            [
                'title' => 'Szövetség',
                'model' => 'Csatar\Csatar\Models\Association',
            ],
            [
                'title' => 'Körzet',
                'model' => 'Csatar\Csatar\Models\District',
            ],
            [
                'title' => 'Csapat',
                'model' => 'Csatar\Csatar\Models\Team',
            ],
            [
                'title' => 'Raj',
                'model' => 'Csatar\Csatar\Models\Troop',
            ],
            [
                'title' => 'Őrs',
                'model' => 'Csatar\Csatar\Models\Patrol',
            ],
            [
                'title' => 'Csapatjelentés',
                'model' => 'Csatar\Csatar\Models\TeamReport',
            ],
            [
                'title' => 'Képzés',
                'model' => 'Csatar\Csatar\Models\Training',
            ],
            [
                'title' => 'Baleseti log',
                'model' => 'Csatar\Csatar\Models\AccidentLogRecord',
            ],
            [
                'title' => 'Rólunk',
                'model' => 'Csatar\Csatar\Models\ContentPage',
            ]
        ],
        'ageGroups'                 => [
            'Romániai Magyar Cserkészszövetség' => [
                ['name' => 'Farkaskölyök', 'note' => '5-7 év'],
                ['name' => 'Kiscserkész', 'note' => '8-10 év'],
                ['name' => 'Cserkész', 'note' => '11-14 év'],
                ['name' => 'Felfedező', 'note' => '15-18 év'],
                ['name' => 'Vándor', 'note' => '19-22 év'],
                ['name' => 'Felnőtt', 'note' => '23+'],
                ['name' => 'Öregcserkész', 'note' => '50+'],
                ['name' => 'Vegyes', 'note' => ''],
            ],
            'Magyar Cserkészszövetség'          => [
                ['name' => 'Kiscserkész', 'note' => ''],
                ['name' => 'Cserkész', 'note' => ''],
                ['name' => 'Kósza', 'note' => ''],
                ['name' => 'Vándor', 'note' => ''],
                ['name' => 'Felnőtt', 'note' => ''],
                ['name' => 'Öregcserkész', 'note' => ''],
                ['name' => 'Vegyes', 'note' => ''],
            ],
            'Külföldi Magyar Cserkészszövetség' => [
                ['name' => 'Kiscserkész', 'note' => ''],
                ['name' => 'Cserkész', 'note' => ''],
                ['name' => 'Rover', 'note' => ''],
                ['name' => 'Felnőtt', 'note' => ''],
                ['name' => 'Öregcserkész', 'note' => ''],
                ['name' => 'Vegyes', 'note' => ''],
            ]
        ],
        'trainings'                 => [
            'Erdélyi VK-2021',
            'MCSZFSTVK II',
            'STVK 19/A',
        ],
        'mandateType'               => [
            'Horvátországi magyar cserkészek'      => [
                [
                    'name'                         => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name'                         => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
            'Kárpátaljai Magyar Cserkészszövetség' => [
                [
                    'name'                         => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name'                         => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
            'Külföldi Magyar Cserkészszövetség'    => [
                [
                    'name'                         => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name'                         => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
            'Magyar Cserkészszövetség'             => [
                [
                    'name'                         => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name'                         => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
            'Romániai Magyar Cserkészszövetség'    => [
                [
                    'name'                         => 'Elnök',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Association',
                    'required'                     => false,
                ],
                [
                    'name'                         => 'Ügyvezető elnök',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Association',
                    'required'                     => false,
                ],
                [
                    'name'                         => 'Mozgalmi vezető',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Association',
                    'required'                     => false,
                ],
                [
                    'name'                         => 'Szövetségi admin',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Association',
                    'required'                     => false,
                ],
                [
                    'name'                         => 'Körzetvezető',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\District',
                    'required'                     => false,
                ],
                [
                    'name'                         => 'Körzetvezető helyettes',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\District',
                    'required'                     => false,
                    'overlap_allowed'              => true,
                    'parent'                       => 'Körzetvezető',
                ],
                [
                    'name'                         => 'Csapatvezető',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Team',
                    'required'                     => false,
                ],
                [
                    'name'                         => 'Csapatvezető helyettes',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Team',
                    'required'                     => false,
                    'overlap_allowed'              => true,
                    'parent'                       => 'Csapatvezető',
                ],
                [
                    'name'                         => 'Csapat nyilvántartó',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Team',
                    'required'                     => false,
                    'parent'                       => 'Csapatvezető helyettes',
                ],
                [
                    'name'                         => 'Rajvezető',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Troop',
                    'required'                     => true,
                ],
                [
                    'name'                         => 'Rajvezető helyettes',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Troop',
                    'required'                     => false,
                    'overlap_allowed'              => true,
                    'parent'                       => 'Rajvezető',
                ],
                [
                    'name'                         => 'Őrsvezető',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Patrol',
                    'required'                     => true,
                ],
                [
                    'name'                         => 'Segédőrsvezető',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Patrol',
                    'required'                     => false,
                    'overlap_allowed'              => true,
                    'parent'                       => 'Őrsvezető',
                ],
                [
                    'name'                         => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name'                         => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
            'Szlovákiai Magyar Cserkészszövetség'  => [
                [
                    'name'                         => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name'                         => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
            'Vajdasági Magyar Cserkészszövetség'   => [
                [
                    'name'                         => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name'                         => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
        ],
        'contactSettings'           => [
            'offices'       => [
                [
                    'address' => 'Csíkszereda, Petőfi Sándor 53 sz., Hargita megye',
                ],
                [
                    'address' => 'Studium-HUB, 21-es iroda, Marosvásárhely, Bolyai utca 15 sz., Maros megye',
                ],
            ],
            'bank'          => 'OTP Bank Miercurea Ciuc',
            'bank_account'  => 'RON: RO35 OTPV 2600 0116 2186 RO01',
            'email'         => 'office[at]rmcssz.ro',
            'phone_numbers' => '+40 (723) 273 257',
        ],
        'sitesearchSettings'        => [
            'enabledOnOrgCMSpages'
        ],
        'userGroups'                => [
            ['Accident log data entry', 'dataEntry'],
            ['Accident log admin', 'admin']
        ],
        'backendUserRoles'          => [
            ['RMCSSZ', 'rmcsszOffice'],
            ['RMCSSZ', 'rmcsszKnowledgeRepository'],
            ['RMCSSZ', 'rmcsszInventory'],
        ],
        'countryNamesHungarianTranslations'      => [
            "Afghanistan" => "Afganisztán",
            "Aland Islands " => "Aland-szigetek",
            "Albania" => "Albánia",
            "Algeria" => "Algéria",
            "American Samoa" => "Amerikai Szamoa",
            "Andorra" => "Andorra",
            "Angola" => "Angola",
            "Anguilla" => "Anguilla",
            "Antarctica" => "Antarktisz",
            "Antigua and Barbuda" => "Antigua és Barbuda",
            "Argentina" => "Argentína",
            "Armenia" => "Örményország",
            "Aruba" => "Aruba",
            "Australia" => "Ausztrália",
            "Austria" => "Ausztria",
            "Azerbaijan" => "Azerbajdzsán",
            "Bahamas" => "Bahama-szigetek",
            "Bahrain" => "Bahrein",
            "Bangladesh" => "Banglades",
            "Barbados" => "Barbados",
            "Belarus" => "Fehéroroszország",
            "Belgium" => "Belgium",
            "Belize" => "Belize",
            "Benin" => "Benin",
            "Bermuda" => "Bermuda",
            "Bhutan" => "Bhután",
            "Bolivia, Plurinational State of" => "Bolívia",
            "Bonaire, Sint Eustatius and Saba" => "Bonaire, Sint Eustatius és Saba",
            "Bosnia and Herzegovina" => "Bosznia-Hercegovina",
            "Botswana" => "Botswana",
            "Bouvet Island" => "Bouvet-sziget",
            "Brazil" => "Brazília",
            "British Indian Ocean Territory" => "Brit Indiai-óceáni Terület",
            "Brunei Darussalam" => "Brunei",
            "Bulgaria" => "Bulgária",
            "Burkina Faso" => "Burkina Faso",
            "Burundi" => "Burundi",
            "Cambodia" => "Kambodzsa",
            "Cameroon" => "Kamerun",
            "Canada" => "Kanada",
            "Cape Verde" => "Zöld-foki Köztársaság",
            "Cayman Islands" => "Kajmán-szigetek",
            "Central African Republic" => "Közép-afrikai Köztársaság",
            "Chad" => "Csád",
            "Chile" => "Chile",
            "China" => "Kína",
            "Christmas Island" => "Karácsony-sziget",
            "Cocos (Keeling) Islands" => "Kókusz (Keeling) Szigetek",
            "Colombia" => "Kolumbia",
            "Comoros" => "Comore-szigetek",
            "Congo" => "Kongó",
            "Cook Islands" => "Cook-szigetek",
            "Costa Rica" => "Costa Rica",
            "Cote d'Ivoire" => "Elefántcsontpart",
            "Croatia" => "Horvátország",
            "Cuba" => "Kuba",
            "Curaçao" => "Curaçao",
            "Cyprus" => "Ciprus",
            "Czech Republic" => "Cseh Köztársaság",
            "Denmark" => "Dánia",
            "Djibouti" => "Dzsibuti",
            "Dominica" => "Dominika",
            "Dominican Republic" => "Dominikai Köztársaság",
            "Ecuador" => "Ecuador",
            "Egypt" => "Egyiptom",
            "El Salvador" => "El Salvador",
            "Equatorial Guinea" => "Egyenlítői-Guinea",
            "Eritrea" => "Eritrea",
            "Estonia" => "Észtország",
            "Ethiopia" => "Etiópia",
            "Falkland Islands (Malvinas)" => "Falkland-szigetek",
            "Faroe Islands" => "Feröer-szigetek",
            "Fiji" => "Fidzsi-szigetek",
            "Finland" => "Finnország",
            "France" => "Franciaország",
            "French Guiana" => "Francia Guyana",
            "French Polynesia" => "Francia Polinézia",
            "French Southern Territories" => "Francia Déli Területek",
            "Gabon" => "Gabon",
            "Gambia" => "Gambia",
            "Georgia" => "Grúzia",
            "Germany" => "Németország",
            "Ghana" => "Ghána",
            "Gibraltar" => "Gibraltár",
            "Greece" => "Görögország",
            "Greenland" => "Grönland",
            "Grenada" => "Grenada",
            "Guadeloupe" => "Guadeloupe",
            "Guam" => "Guam",
            "Guatemala" => "Guatemala",
            "Guernsey" => "Guernsey",
            "Guinea" => "Guinea",
            "Guinea-Bissau" => "Bissau-Guinea",
            "Guyana" => "Guyana",
            "Haiti" => "Haiti",
            "Heard Island and McDonald Islands" => "Heard-sziget és McDonald-szigetek",
            "Holy See (Vatican City State)" => "Vatikán",
            "Honduras" => "Honduras",
            "Hong Kong" => "Hongkong",
            "Hungary" => "Magyarország",
            "Iceland" => "Izland",
            "India" => "India",
            "Indonesia" => "Indonézia",
            "Iran, Islamic Republic of" => "Irán",
            "Iraq" => "Irak",
            "Ireland" => "Írország",
            "Isle of Man" => '',
            "Israel" => "Izrael",
            "Italy" => "Olaszország",
            "Jamaica" => "Jamaica",
            "Japan" => "Japán",
            "Jersey" => "Jersey",
            "Jordan" => "Jordánia",
            "Kazakhstan" => "Kazahsztán",
            "Kenya" => "Kenya",
            "Kiribati" => "Kiribati",
            "Korea, Democratic People's Republic of" => "Korea, NDK",
            "Korea, Republic of" => "Korea",
            "Kuwait" => "Kuvait",
            "Kyrgyzstan" => "Kirgizisztán",
            "Lao People's Democratic Republic" => "Laosz",
            "Latvia" => "Lettország",
            "Lebanon" => "Libanon",
            "Lesotho" => "Lesotho",
            "Liberia" => "Libéria",
            "Libyan Arab Jamahiriya" => "Líbia",
            "Liechtenstein" => "Liechtenstein",
            "Lithuania" => "Litvánia",
            "Luxembourg" => "Luxemburg",
            "Macao" => "Makaó",
            "Macedonia, the former Yugoslav Republic of" => "Macedónia",
            "Madagascar" => "Madagaszkár",
            "Malawi" => "Malawi",
            "Malaysia" => "Malajzia",
            "Maldives" => "Maldív-szigetek",
            "Mali" => "Mali",
            "Malta" => "Málta",
            "Marshall Islands" => "Marshall-szigetek",
            "Martinique" => "Martinique",
            "Mauritania" => "Mauritánia",
            "Mauritius" => "Mauritius",
            "Mayotte" => "Mayotte",
            "Mexico" => "Mexikó",
            "Micronesia, Federated States of" => "Mikronézia",
            "Moldova, Republic of" => "Moldova",
            "Monaco" => "Monaco",
            "Mongolia" => "Mongólia",
            "Montenegro" => "Montenegró",
            "Montserrat" => "Montserrat",
            "Morocco" => "Marokkó",
            "Mozambique" => "Mozambik",
            "Myanmar" => "Mianmar",
            "Namibia" => "Namíbia",
            "Nauru" => "Nauru",
            "Nepal" => "Nepál",
            "Netherlands" => "Hollandia",
            "Netherlands Antilles" => "Holland Antillák",
            "New Caledonia" => "Új-Kaledónia",
            "New Zealand" => "Új-Zéland",
            "Nicaragua" => "Nicaragua",
            "Niger" => "Niger",
            "Nigeria" => "Nigéria",
            "Niue" => "Niue",
            "Norfolk Island" => "Norfolk-sziget",
            "Northern Mariana Islands" => "Északi Mariana-szigetek",
            "Norway" => "Norvégia",
            "Oman" => "Omán",
            "Pakistan" => "Pakisztán",
            "Palau" => "Palau",
            "Palestinian Territory, Occupied" => "Palesztina",
            "Panama" => "Panama",
            "Papua New Guinea" => "Pápua Új-Guinea",
            "Paraguay" => "Paraguay",
            "Peru" => "Peru",
            "Philippines" => "Fülöp-szigetek",
            "Pitcairn" => "Pitcairn-szigetek",
            "Poland" => "Lengyelország",
            "Portugal" => "Portugália",
            "Puerto Rico" => "Puerto Rico",
            "Qatar" => "Katar",
            "Reunion" => "Réunion",
            "Romania" => "Románia",
            "Russian Federation" => "Oroszország",
            "Rwanda" => "Ruanda",
            "Saint Barthelemy" => "Saint-Barthélemy",
            "Saint Helena" => "Saint Helena",
            "Saint Kitts and Nevis" => "Saint Kitts és Nevis",
            "Saint Lucia" => "Saint Lucia",
            "Saint Martin" => "Saint-Martin",
            "Saint Pierre and Miquelon" => "Saint-Pierre és Miquelon",
            "Saint Vincent and the Grenadines" => "Saint Vincent és a Grenadine-szigetek",
            "Samoa" => "Szamoa",
            "San Marino" => "San Marino",
            "Sao Tome and Principe" => "São Tomé és Príncipe",
            "Saudi Arabia" => "Szaúd-Arábia",
            "Senegal" => "Szenegál",
            "Serbia" => "Szerbia",
            "Seychelles" => "Seychelle-szigetek",
            "Sierra Leone" => "Sierra Leone",
            "Singapore" => "Szingapúr",
            "Sint Maarten" => "Sint Maarten",
            "Slovakia" => "Szlovákia",
            "Slovenia" => "Szlovénia",
            "Solomon Islands" => "Salamon-szigetek",
            "Somalia" => "Szomália",
            "South Africa" => "Dél-Afrika",
            "South Georgia and the South Sandwich Islands" => "Déli-Georgia és Déli-Sandwich-szigetek",
            "Spain" => "Spanyolország",
            "Sri Lanka" => "Srí Lanka",
            "Sudan" => "Szudán",
            "Suriname" => "Suriname",
            "Svalbard and Jan Mayen" => "Svalbard és Jan Mayen",
            "Swaziland" => "Szváziföld",
            "Sweden" => "Svédország",
            "Switzerland" => "Svájc",
            "Syrian Arab Republic" => "Szíria",
            "Taiwan, Province of China" => "Tajvan",
            "Tajikistan" => "Tádzsikisztán",
            "Tanzania, United Republic of" => "Tanzánia",
            "Thailand" => "Thaiföld",
            "Timor-Leste" => "Kelet-Timor",
            "Togo" => "Togo",
            "Tokelau" => "Tokelau",
            "Tonga" => "Tonga",
            "Trinidad and Tobago" => "Trinidad és Tobago",
            "Tunisia" => "Tunézia",
            "Turkey" => "Törökország",
            "Turkmenistan" => "Türkmenisztán",
            "Turks and Caicos Islands" => "Turks- és Caicos-szigetek",
            "Tuvalu" => "Tuvalu",
            "Uganda" => "Uganda",
            "Ukraine" => "Ukrajna",
            "United Arab Emirates" => "Egyesült Arab Emírségek",
            "United Kingdom" => "Egyesült Királyság",
            "United States" => "Egyesült Államok",
            "United States Minor Outlying Islands" => "Egyesült Államok kisebb szigetei",
            "Uruguay" => "Uruguay",
            "Uzbekistan" => "Üzbegisztán",
            "Vanuatu" => "Vanuatu",
            "Venezuela, Bolivarian Republic of" => "Venezuela",
            "Viet Nam" => "Vietnám",
            "Virgin Islands, British" => "Brit Virgin-szigetek",
            "Virgin Islands, U.S." => "Amerikai Virgin-szigetek",
            "Wallis and Futuna" => "Wallis és Futuna",
            "Western Sahara" => "Nyugat-Szahara",
            "Yemen" => "Jemen",
            "Zambia" => "Zambia",
            "Zimbabwe" => "Zimbabwe",
        ],
        'googleCalendarParams' => [
            [
                'model' => 'Association',
                'modelName' => 'Romániai Magyar Cserkészszövetség',
                'params' => 'src=rmcssz%40gmail.com&ctz=Europe%2FBucharest&hl=hu'
            ],
            [
                'model' => 'District',
                'modelName' => 'Csík',
                'params' => 'src=uga7ch24mbb4ckfpqo3ruf912k%40group.calendar.google.com&ctz=Europe%2FBucharest&hl=hu'
            ],
            [
                'model' => 'District',
                'modelName' => 'Gyergyó',
                'params' => 'src=qq1b0l0i4unvuj4qdul780h8o8%40group.calendar.google.com&ctz=Europe%2FBucharest&hl=hu'
            ],
            [
                'model' => 'District',
                'modelName' => 'Háromszék',
                'params' => 'src=8onq5ut3tetqldh682alu37mt4%40group.calendar.google.com&ctz=Europe%2FBucharest&hl=hu'
            ],
            [
                'model' => 'District',
                'modelName' => 'Kolozsvár',
                'params' => 'src=694ek0k60e0era4vmf8bbui5vs%40group.calendar.google.com&ctz=Europe%2FBucharest&hl=hu'
            ],
            [
                'model' => 'District',
                'modelName' => 'Maros',
                'params' => 'src=fbrs4olb1skmbvsbaoov7hga8k%40group.calendar.google.com&ctz=Europe%2FBucharest&hl=hu'
            ],
            [
                'model' => 'District',
                'modelName' => 'Nagyenyed',
                'params' => 'src=btivjhutht8ucmecvcjik1lh64%40group.calendar.google.com&ctz=Europe%2FBucharest&hl=hu'
            ],
            [
                'model' => 'District',
                'modelName' => 'Udvarhely',
                'params' => 'src=cfovub3qe4jg3celfsp7ivr3ds%40group.calendar.google.com&ctz=Europe%2FBucharest&hl=hu'
            ],
        ]
    ];



    public function run()
    {
        // allergies
        foreach ($this::DATA['allergy'] as $name) {
            $allergy = Allergy::firstOrCreate([
                'name' => $name
            ]);
        }

        // legal relationships
        foreach ($this::DATA['legalRelationship'] as $name) {
            $legalRelationship = LegalRelationship::firstOrCreate([
                'name' => $name
            ]);
        }

        // special tests
        foreach ($this::DATA['specialTest'] as $name) {
            $specialTest = SpecialTest::firstOrCreate([
                'name' => $name
            ]);
        }

        // special diets
        foreach ($this::DATA['specialDiet'] as $name) {
            $specialDiet = SpecialDiet::firstOrCreate([
                'name' => $name
            ]);
        }

        // religions
        foreach ($this::DATA['religion'] as $name) {
            $religion = Religion::firstOrCreate([
                'name' => $name
            ]);
        }

        // t-shirt sizes
        foreach ($this::DATA['tShirtSize'] as $name) {
            $tshirtSize = TShirtSize::firstOrCreate([
                'name' => $name
            ]);
        }

        // chronic illnesses
        foreach ($this::DATA['chronicIllness'] as $name) {
            $chronicIllness = ChronicIllness::firstOrCreate([
                'name' => $name
            ]);
        }

        // hierarchy
        $idOfLastElement = null;
        foreach ($this::DATA['hierarchy'] as $name) {
            $hierachyItem            = Hierarchy::firstOrNew([
                'name' => $name,
            ]);
            $hierachyItem->parent_id = $idOfLastElement;
            $hierachyItem->save();

            $idOfLastElement = $hierachyItem->id;
        }

        // currencies
        foreach ($this::DATA['currency'] as $code) {
            $currency = Currency::firstOrCreate([
                'code' => $code
            ]);
        }

        // associations
        $legalRelationship1 = LegalRelationship::where('name', 'Alakuló csapat tag')->first();
        $legalRelationship2 = LegalRelationship::where('name', 'Tag')->first();
        $legalRelationship3 = LegalRelationship::where('name', 'Tiszteletbeli tag')->first();
        $legalRelationship4 = LegalRelationship::where('name', 'Újonc')->first();

        foreach ($this::DATA['association'] as $name) {
            $association = Association::firstOrNew([
                'name' => $name,
            ]);
            $association->contact_name  = $association->contact_name ?? null;
            $association->contact_email = $association->contact_email ?? null;
            $association->address       = $association->address ?? null;
            $association->leadership_presentation = $association->leadership_presentation ?? null;
            switch ($name) {
                case 'Horvátországi magyar cserkészek':
                    $association->ecset_code_suffix = $association->ecset_code_suffix ?? 'H';
                    $association->currency_id       = Currency::where('code', 'HRK')->first()->id;
                    $association->team_fee          = $association->team_fee ?? 0;
                    $association->name_abbreviation = $association->name_abbreviation ?? 'HZMCS';
                    break;
                case 'Kárpátaljai Magyar Cserkészszövetség':
                    $association->currency_id       = Currency::where('code', 'UAH')->first()->id;
                    $association->team_fee          = $association->team_fee ?? 0;
                    $association->ecset_code_suffix = $association->ecset_code_suffix ?? 'KÁ';
                    $association->name_abbreviation = $association->name_abbreviation ?? 'KáMCSSZ';
                    break;
                case 'Külföldi Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = $association->ecset_code_suffix ?? 'KÜ';
                    $association->currency_id       = Currency::where('code', 'EUR')->first()->id;
                    $association->team_fee          = $association->team_fee ?? 0;
                    $association->name_abbreviation = $association->name_abbreviation ?? 'KMCSSZ';
                    break;
                case 'Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = $association->ecset_code_suffix ?? 'M';
                    $association->currency_id       = Currency::where('code', 'HUF')->first()->id;
                    $association->team_fee          = $association->team_fee ?? 0;
                    $association->name_abbreviation = $association->name_abbreviation ?? 'MCSSZ';
                    break;
                case 'Romániai Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = $association->ecset_code_suffix ?? 'E';
                    $association->currency_id       = Currency::where('code', 'RON')->first()->id;
                    $association->team_fee          = $association->team_fee ?? 300;
                    $association->name_abbreviation = $association->name_abbreviation ?? 'RMCSSZ';
                    $association->country           = 'Románia';
                    break;
                case 'Szlovákiai Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = $association->ecset_code_suffix ?? 'F';
                    $association->currency_id       = Currency::where('code', 'EUR')->first()->id;
                    $association->team_fee          = $association->team_fee ?? 0;
                    $association->name_abbreviation = $association->name_abbreviation ?? 'SZMCS';
                    break;
                case 'Vajdasági Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = $association->ecset_code_suffix ?? 'D';
                    $association->currency_id       = Currency::where('code', 'RSD')->first()->id;
                    $association->team_fee          = $association->team_fee ?? 0;
                    $association->name_abbreviation = $association->name_abbreviation ?? 'VMCSZ';
                    break;
                default:
                    break;
            }
            $association->save();

            // associations - legal relationships pivot
            if ($association->legal_relationships->where('id', $legalRelationship1->id)->first() == null) {
                $association->legal_relationships()->attach($legalRelationship1, ['membership_fee' => 0]);
            }
            if ($association->legal_relationships->where('id', $legalRelationship2->id)->first() == null) {
                $association->legal_relationships()->attach($legalRelationship2, ['membership_fee' => 0]);
            }
            if ($association->legal_relationships->where('id', $legalRelationship3->id)->first() == null) {
                $association->legal_relationships()->attach($legalRelationship3, ['membership_fee' => 0]);
            }
            if ($association->legal_relationships->where('id', $legalRelationship4->id)->first() == null) {
                $association->legal_relationships()->attach($legalRelationship4, ['membership_fee' => 0]);
            }
            $association->save();

            // mandate types
            $mandateTypes = [];
            if (isset($this::DATA['mandateType'][$association->name])) {
                foreach ($this::DATA['mandateType'][$association->name] as $mandateType) {
                    $mandateType['association_id'] = $association->id;
                    if (isset($mandateType['parent'])) {
                        foreach ($mandateTypes as $item) {
                            if ($item->name == $mandateType['parent']) {
                                $mandateType['parent_id'] = $item->id;
                                break;
                            }
                        }
                        unset($mandateType['parent']);
                    }
                    $newMandateType           = MandateType::firstOrCreate([
                        'name' => $mandateType['name'],
                        'association_id' => $mandateType['association_id'],
                        'organization_type_model_name' => $mandateType['organization_type_model_name'],
                    ]);
                    $newMandateType->required = $mandateType['required'] ?? false;
                    $newMandateType->overlap_allowed = $mandateType['overlap_allowed'] ?? false;
                    $newMandateType->parent_id       = $mandateType['parent_id'] ?? null;
                    $newMandateType->is_vk           = $mandateType['is_vk'] ?? 0;
                    $newMandateType->save();

                    $mandateTypes[] = $newMandateType;
                }
            }

            // update the membership fee value for RMCSSZ - Member
            if ($association->name == 'Romániai Magyar Cserkészszövetség') {
                // membership fee
                $legal_relationship = $association->legal_relationships->where('id', $legalRelationship2->id)->first();
                if (isset($legal_relationship)) {
                    $legal_relationship->pivot->membership_fee = $legal_relationship->pivot->membership_fee ?? 50;
                    $legal_relationship->pivot->save();
                }
            }
        }

        // food sensitivities
        foreach ($this::DATA['foodSensitivity'] as $name) {
            $foodSensitivity = FoodSensitivity::firstOrCreate([
                'name' => $name
            ]);
        }

        // promises
        $promises = [
            'Kiscserkész igéret',
            'Cserkész fogadalom',
        ];

        foreach ($this::DATA['promise'] as $name) {
            $promise = Promise::firstOrCreate([
                'name' => $name
            ]);
        }

        // professional qualifications
        foreach ($this::DATA['professionalQualification'] as $name) {
            $professionalQualification = ProfessionalQualification::firstOrCreate([
                'name' => $name
            ]);
        }

        // leadership qualifications
        foreach ($this::DATA['leadershipQualification'] as $name) {
            $leadershipQualification = LeadershipQualification::firstOrCreate([
                'name' => $name
            ]);
        }

        // seeders for the Forms plugin
        foreach (Form::all() as $form) {
            $form->slugAttributes();
            $form->save();
        }
        foreach ($this::DATA['form'] as $form) {
            $item = Form::firstOrCreate($form);
        }

        // trainings
        foreach ($this::DATA['trainings'] as $training) {
            Training::firstOrCreate([
                'name' => $training,
            ]);
        }

        // ageGroups for associations
        foreach ($this::DATA['ageGroups'] as $associationName => $ageGroups) {
            $associationId = Association::where('name', $associationName)->first()->id ?? 0;
            foreach ($ageGroups as $ageGroup) {
                $newAgeGroup       = AgeGroup::firstOrCreate([
                    'name'           => $ageGroup['name'],
                    'association_id' => $associationId
                ]);
                $newAgeGroup->note = $newAgeGroup->note ?? $ageGroup['note'];
                $newAgeGroup->save();
            }
        }

        // userGroups
        foreach ($this::DATA['userGroups'] as $name) {
            $allergy = UserGroup::firstOrCreate([
                'name' => $name[0],
                'code' => str_slug($name[0]),
            ]);
        }

        // contact page data
        $contactSettings = ContactSettings::instance();
        foreach ($this::DATA['contactSettings'] as $contact_key => $contact_value) {
            if (($contact_key == 'offices' && !isset($contactSettings->{$contact_key})) || ($contact_key != 'offices' && empty($contactSettings->{$contact_key}))) {
                $contactSettings->{$contact_key} = $contact_value;
            }
        }
        $contactSettings->save();

        // seed site search plugin settings

        $sitesearchSettings = '{"mark_results":"1","log_queries":"0","excerpt_length":"250","log_keep_days":365,"rainlab_blog_enabled":"0","rainlab_blog_label":"Blog","rainlab_blog_page":"403","rainlab_pages_enabled":"0","rainlab_pages_label":"Page","indikator_news_enabled":"0","indikator_news_label":"News","indikator_news_posturl":"\/news","octoshop_products_enabled":"0","octoshop_products_label":"","octoshop_products_itemurl":"\/product","snipcartshop_products_enabled":"0","snipcartshop_products_label":"","jiri_jkshop_enabled":"0","jiri_jkshop_label":"","jiri_jkshop_itemurl":"\/product","radiantweb_problog_enabled":"0","radiantweb_problog_label":"Blog","arrizalamin_portfolio_enabled":"0","arrizalamin_portfolio_label":"Portfolio","arrizalamin_portfolio_url":"\/portfolio\/project","vojtasvoboda_brands_enabled":"0","vojtasvoboda_brands_label":"Brands","vojtasvoboda_brands_url":"\/brand","responsiv_showcase_enabled":"0","responsiv_showcase_label":"Showcase","responsiv_showcase_url":"\/showcase\/project","graker_photoalbums_enabled":"0","graker_photoalbums_label":"PhotoAlbums","graker_photoalbums_album_page":"403","graker_photoalbums_photo_page":"403","cms_pages_enabled":"0","cms_pages_label":"Page"}';

        Db::table('system_settings')
            ->updateOrInsert(
                ['item' => 'offline_sitesearch_settings'],
                ['value' => $sitesearchSettings],
          );

        // seed RMCSSZ Iroda backend role

        Db::table('backend_user_roles')
            ->updateOrInsert(
                ['code' => 'rmcssz-iroda'],
                [
                    'name' => 'RMCSSZ Iroda',
                    'permissions' => '{"rainlab.users.access_users":"1","rainlab.users.access_groups":"1","rainlab.users.impersonate_user":"1","pollozen.simplegallery.manage_galleries":"1","csatar.manage.data":"1","janvince.smallcontactform.access_messages":"1","janvince.smallcontactform.delete_messages":"1","janvince.smallcontactform.export_messages":"1"}'
                ],
            );

        // seed RMCSSZ Tudástáras backend role

        Db::table('backend_user_roles')
            ->updateOrInsert(
                ['code' => 'rmcssz-tudastaras'],
                [
                    'name' => 'RMCSSZ Tudástáras',
                    'permissions' => '{"backend.access_dashboard":"1","csatar.manage.knowledgerepository":"1"}'
                ],
            );

        // seed RMCSSZ Leltáros backend role

        Db::table('backend_user_roles')
            ->updateOrInsert(
                ['code' => 'rmcssz-leltaros'],
                [
                    'name' => 'RMCSSZ Leltáros',
                    'permissions' => '{"backend.access_dashboard":"1","csatar.manage.inventory":"1"}'
                ],
            );

        foreach ($this::DATA['countryNamesHungarianTranslations'] as $enCountryName => $huCountryName) {
            $country = Country::where('name', $enCountryName)->first();
            if ($country) {
                $country->setAttributeTranslated('name', $enCountryName, 'en');
                $country->setAttributeTranslated('name', $huCountryName, 'hu');
                $country->save();
            }
        }

        foreach ($this::DATA['googleCalendarParams'] as $params) {
            $class = '\Csatar\Csatar\Models\\' . $params['model'];
            $model = $class::where('name', $params['modelName'])->first();

            if ($model) {
                $model->google_calendar_id = $params['params'];
                $model->forceSave();
            }
        }
    }
}
