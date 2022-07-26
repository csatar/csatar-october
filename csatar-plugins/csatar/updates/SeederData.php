<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\Allergy;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\ChronicIllness;
use Csatar\Csatar\Models\Currency;
use Csatar\Csatar\Models\FoodSensitivity;
use Csatar\Csatar\Models\Hierarchy;
use Csatar\Csatar\Models\LeadershipQualification;
use Csatar\Csatar\Models\LegalRelationship;
use Csatar\Csatar\Models\Mandate;
use Csatar\Csatar\Models\ProfessionalQualification;
use Csatar\Csatar\Models\Promise;
use Csatar\Csatar\Models\Religion;
use Csatar\Csatar\Models\SpecialDiet;
use Csatar\Csatar\Models\SpecialTest;
use Csatar\Csatar\Models\TShirtSize;
use Csatar\Forms\Models\Form;
use Csatar\Csatar\Models\Training;
use Csatar\Csatar\Models\AgeGroup;

class SeederData extends Seeder
{
    public const DATA = [
        'allergy' => [
            'Ételintollerancia',
            'Ételallergiák',
            'Pollen alergia/Szénanátha',
            'Belélegzései allergia',
            'Rovarméreg allergia',
            'Kontakt allergia (vegyszerekre, anyagokra)',
            'Gyógyszerallergia',
            'Egyéb'
        ],
        'legalRelationship' => [
            'Alakuló csapat tag',
            'Újonc',
            'Tag',
            'Tiszteletbeli tag'
        ],
        'specialTest' => [
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
        'specialDiet' => [
            'Sporttáplálkozás',
            'Gluténmentes',
            'Szénhidrátmentes',
            'Laktózmentes',
            'Paleo',
            'Vegán',
            'Vegetáriánus',
        ],
        'religion' => [
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
        'tShirtSize' => [
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
        'chronicIllness' => [
            'Magas vérnyomás',
            'Szívelégtelenség',
            'Allergia',
            'Cukorbetegség',
            'Mozgásszervi betegségek',
            'Pajzsmirigy működési zavar',
            'Schizophrenia, schizotypiás és paranoid zavarok',
            'Daganatos betegség',
            'Krónikus légzési elégtelenség',
            'Veseelégtelenség',
            'HIV/SIDA',
        ],
        'hierarchy' => [
            'RMCSSZ',
            'Körzetvezető',
            'Csapatvezető',
            'Rajvezető',
            'Őrsvezető',
            'Cserkész',
        ],
        'currency' => [
            'EUR',
            'HRK',
            'HUF',
            'RON',
            'RSD',
            'UAH',
        ],
        'association' => [
            'Horvátországi magyar cserkészek',
            'Kárpátaljai Magyar Cserkészszövetség',
            'Külföldi Magyar Cserkészszövetség',
            'Magyar Cserkészszövetség',
            'Romániai Magyar Cserkészszövetség',
            'Szlovákiai Magyar Cserkészszövetség',
            'Vajdasági Magyar Cserkészszövetség',
        ],
        'foodSensitivity' => [
            'liszt',
            'tejfehérje (kazein)',
            'tojás',
            'mogyoró',
            'szója',
            'dió',
            'kagyló',
            'eper',
        ],
        'promise' => [
            'Kiscserkész igéret',
            'Cserkész fogadalom',
        ],
        'professionalQualification' => [
            'Regős',
        ],
        'leadershipQualification' => [
            'Segédőrsvezető képzés',
            'Őrsvezető képzés',
            'Felnőtt őrsvezető képzés',
            'Segédvezető képzés',
            'Cserkész vezető',
        ],
        'form' => [
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
            ]
        ],
        'ageGroups' => [
            'Romániai Magyar Cserkészszövetség' => [
                [ 'name' => 'Farkaskölyök', 'note' => '5-7 év' ],
                [ 'name' => 'Kiscserkész', 'note' => '8-10 év' ],
                [ 'name' => 'Cserkész', 'note' => '11-14 év' ],
                [ 'name' => 'Felfedező', 'note' => '15-18 év' ],
                [ 'name' => 'Vándor', 'note' => '19-22 év' ],
                [ 'name' => 'Felnőtt', 'note' => '23+' ],
                [ 'name' => 'Öregcserkész', 'note' => '50+' ],
                [ 'name' => 'Vegyes', 'note' => ''],
            ],
            'Magyar Cserkészszövetség' => [
                [ 'name' => 'Kiscserkész', 'note' => ''],
                [ 'name' => 'Cserkész', 'note' => ''],
                [ 'name' => 'Kósza', 'note' => ''],
                [ 'name' => 'Vándor', 'note' => ''],
                [ 'name' => 'Felnőtt', 'note' => ''],
                [ 'name' => 'Öregcserkész', 'note' => ''],
                [ 'name' => 'Vegyes', 'note' => ''],
            ],
            'Külföldi Magyar Cserkészszövetség' => [
                [ 'name' => 'Kiscserkész', 'note' => ''],
                [ 'name' => 'Cserkész', 'note' => ''],
                [ 'name' => 'Rover', 'note' => ''],
                [ 'name' => 'Felnőtt', 'note' => ''],
                [ 'name' => 'Öregcserkész', 'note' => ''],
                [ 'name' => 'Vegyes', 'note' => ''],
            ]
        ],
        'trainings' => [
            'Erdélyi VK-2021',
            'MCSZFSTVK II',
            'STVK 19/A',
        ],
        'mandate' => [
            'Romániai Magyar Cserkészszövetség' => [
                [
                    'name' => 'Elnök',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Association',
                    'required' => false,
                ],
                [
                    'name' => 'Ügyvezető elnök',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Association',
                    'required' => false,
                ],
                [
                    'name' => 'Mozgalmi vezető',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Association',
                    'required' => false,
                ],
                [
                    'name' => 'Szövetségi admin',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Association',
                    'required' => false,
                ],
                [
                    'name' => 'Körzetvezető',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\District',
                    'required' => false,
                ],
                [
                    'name' => 'Körzetvezető helyettes',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\District',
                    'required' => false,
                    'parent' => 'Körzetvezető',
                ],
                [
                    'name' => 'Csapatvezető',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Team',
                    'required' => false,
                ],
                [
                    'name' => 'Csapatvezető helyettes',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Team',
                    'required' => false,
                    'parent' => 'Csapatvezető',
                ],
                [
                    'name' => 'Csapat nyilvántartó',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Team',
                    'required' => false,
                    'parent' => 'Csapatvezető helyettes',
                ],
                [
                    'name' => 'Rajvezető',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Troop',
                    'required' => true,
                ],
                [
                    'name' => 'Rajvezető helyettes',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Troop',
                    'required' => false,
                    'parent' => 'Rajvezető',
                ],
                [
                    'name' => 'Őrsvezető',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Patrol',
                    'required' => true,
                ],
                [
                    'name' => 'Őrsvezető helyettes',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Patrol',
                    'required' => false,
                    'parent' => 'Őrsvezető',
                ],
            ],
        ],
    ];

    public function run()
    {
        // allergies
        foreach($this::DATA['allergy'] as $name) {
            $allergy = Allergy::firstOrCreate([
                'name' => $name
            ]);
        }

        // legal relationships
        foreach($this::DATA['legalRelationship'] as $name) {
            $legalRelationship = LegalRelationship::firstOrCreate([
                'name' => $name
            ]);
        }

        // special tests
        foreach($this::DATA['specialTest'] as $name) {
            $specialTest = SpecialTest::firstOrCreate([
                'name' => $name
            ]);
        }

        // special diets
        foreach($this::DATA['specialDiet'] as $name) {
            $specialDiet = SpecialDiet::firstOrCreate([
                'name' => $name
            ]);
        }

        // religions
        foreach($this::DATA['religion'] as $name) {
            $religion = Religion::firstOrCreate([
                'name' => $name
            ]);
        }

        // t-shirt sizes
        foreach($this::DATA['tShirtSize'] as $name) {
            $tshirtSize = TShirtSize::firstOrCreate([
                'name' => $name
            ]);
        }

        // chronic illnesses
        foreach($this::DATA['chronicIllness'] as $name) {
            $chronicIllness = ChronicIllness::firstOrCreate([
                'name' => $name
            ]);
        }

        // hierarchy
        $idOfLastElement = null;
        foreach($this::DATA['hierarchy'] as $name) {
            $hierachyItem = Hierarchy::firstOrNew([
                'name' => $name,
            ]);
            $hierachyItem->parent_id = $idOfLastElement;
            $hierachyItem->save();

            $idOfLastElement = $hierachyItem->id;
        }

        // currencies
        foreach($this::DATA['currency'] as $code) {
            $currency = Currency::firstOrCreate([
                'code' => $code
            ]);
        }

        // associations
        $legalRelationship1 = LegalRelationship::where('name', 'Alakuló csapat tag')->first();
        $legalRelationship2 = LegalRelationship::where('name', 'Tag')->first();

        foreach($this::DATA['association'] as $name) {
            $association = Association::firstOrNew([
                'name' => $name,
            ]);
            $association->contact_name = 'Abcde';
            $association->contact_email = 'ab@ab.ab';
            $association->address = 'Abcde';
            $association->leadership_presentation = 'A';
            switch ($name) {
                case 'Horvátországi magyar cserkészek':
                    $association->ecset_code_suffix = 'H';
                    $association->currency_id = Currency::where('code', 'HRK')->first()->id;
                    $association->team_fee = 0;
                    $association->name_abbreviation = 'HZMCS';
                    break;
                case 'Kárpátaljai Magyar Cserkészszövetség':
                    $association->currency_id = Currency::where('code', 'UAH')->first()->id;
                    $association->team_fee = 0;
                    $association->ecset_code_suffix = 'KÁ';
                    $association->name_abbreviation = 'KáMCSSZ';
                    break;
                case 'Külföldi Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = 'KÜ';
                    $association->currency_id = Currency::where('code', 'EUR')->first()->id;
                    $association->team_fee = 0;
                    $association->name_abbreviation = 'KMCSSZ';
                    break;
                case 'Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = 'M';
                    $association->currency_id = Currency::where('code', 'HUF')->first()->id;
                    $association->team_fee = 0;
                    $association->name_abbreviation = 'MCSSZ';
                    break;
                case 'Romániai Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = 'E';
                    $association->currency_id = Currency::where('code', 'RON')->first()->id;
                    $association->team_fee = 300;
                    $association->name_abbreviation = 'RMCSSZ';
                    break;
                case 'Szlovákiai Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = 'F';
                    $association->currency_id = Currency::where('code', 'EUR')->first()->id;
                    $association->team_fee = 0;
                    $association->name_abbreviation = 'SZMCS';
                    break;
                case 'Vajdasági Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = 'D';
                    $association->currency_id = Currency::where('code', 'RSD')->first()->id;
                    $association->team_fee = 0;
                    $association->name_abbreviation = 'VMCSZ';
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
            $association->save();

            // mandates
            $mandates = [];
            if (isset($this::DATA['mandate'][$association->name])) {
                foreach ($this::DATA['mandate'][$association->name] as $mandate) {
                    $mandate['association_id'] = $association->id;
                    if (isset($mandate['parent'])) {
                        foreach ($mandates as $item) {
                            if ($item->name == $mandate['parent']) {
                                $mandate['parent_id'] = $item->id;
                                break;
                            }
                        }
                        unset($mandate['parent']);
                    }
                    array_push($mandates, Mandate::firstOrCreate($mandate));
                }
            }

            // update the membership fee value and add mandates for RMCSSZ - Member
            if ($association->name == 'Romániai Magyar Cserkészszövetség') {
                // membership fee
                $legal_relationship = $association->legal_relationships->where('id', $legalRelationship2->id)->first();
                if (isset($legal_relationship)) {
                    $legal_relationship->pivot->membership_fee = 50;
                    $legal_relationship->pivot->save();
                }
            }
        }

        // food sensitivities
        foreach($this::DATA['foodSensitivity'] as $name) {
            $foodSensitivity = FoodSensitivity::firstOrCreate([
                'name' => $name
            ]);
        }

        // promises
        $promises = [
            'Kiscserkész igéret',
            'Cserkész fogadalom',
        ];

        foreach($this::DATA['promise'] as $name) {
            $promise = Promise::firstOrCreate([
                'name' => $name
            ]);
        }

        // professional qualifications
        foreach($this::DATA['professionalQualification'] as $name) {
            $professionalQualification = ProfessionalQualification::firstOrCreate([
                'name' => $name
            ]);
        }

        // leadership qualifications
        foreach($this::DATA['leadershipQualification'] as $name) {
            $leadershipQualification = LeadershipQualification::firstOrCreate([
                'name' => $name
            ]);
        }

        // seeders for the Forms plugin
        foreach(Form::all() as $form) {
            $form->slugAttributes();
            $form->save();
        }
        foreach($this::DATA['form'] as $form) {
            $item = Form::firstOrCreate($form);
        }

        // trainings
        foreach($this::DATA['trainings'] as $training) {
            Training::firstOrCreate([
                'name' => $training,
            ]);
        }

        // ageGroups for associations
        foreach($this::DATA['ageGroups'] as $associationName => $ageGroups) {
            $associationId = Association::where('name', $associationName)->first()->id ?? 0;
            foreach($ageGroups as $ageGroup) {
                $newAgeGroup = AgeGroup::firstOrCreate([
                    'name' => $ageGroup['name'],
                    'association_id' => $associationId
                ]);
                $newAgeGroup->note = $ageGroup['note'];
                $newAgeGroup->save();
            }
        }
    }
}
