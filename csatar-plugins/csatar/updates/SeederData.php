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
            'Nincs krónikus betegsége',
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
            'Egyéb',
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
        'mandateType' => [
            'Horvátországi magyar cserkészek' => [
                [
                    'name' => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name' => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
            'Kárpátaljai Magyar Cserkészszövetség' => [
                [
                    'name' => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name' => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
            'Külföldi Magyar Cserkészszövetség' => [
                [
                    'name' => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name' => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
            'Magyar Cserkészszövetség' => [
                [
                    'name' => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name' => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
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
                    'overlap_allowed' => true,
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
                    'overlap_allowed' => true,
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
                    'overlap_allowed' => true,
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
                    'overlap_allowed' => true,
                    'parent' => 'Őrsvezető',
                ],
                [
                    'name' => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name' => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
            'Szlovákiai Magyar Cserkészszövetség' => [
                [
                    'name' => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name' => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
            'Vajdasági Magyar Cserkészszövetség' => [
                [
                    'name' => 'Cserkész',
                    'organization_type_model_name' => '\Csatar\Csatar\Models\Scout',
                ],
                [
                    'name' => 'Látogató',
                    'organization_type_model_name' => 'GUEST',
                ],
            ],
        ],
        'permissions' => [
            'Horvátországi magyar cserkészek' => 'readPermissionForGuests',
            'Kárpátaljai Magyar Cserkészszövetség' => 'readPermissionForGuests',
            'Külföldi Magyar Cserkészszövetség' => 'readPermissionForGuests',
            'Magyar Cserkészszövetség' => 'readPermissionForGuests',
            'Romániai Magyar Cserkészszövetség' => ['allPermissionsForScout', 'readPermissionForGuests'],
            'Szlovákiai Magyar Cserkészszövetség' => 'readPermissionForGuests',
            'Vajdasági Magyar Cserkészszövetség' => 'readPermissionForGuests',
        ],
        'contactSettings' => [
            'offices' => [
                [
                    'address' => 'Csíkszereda, Petőfi Sándor 53 sz., Hargita megye',
                ],
                [
                    'address' => 'Studium-HUB, 21-es iroda, Marosvásárhely, Bolyai utca 15 sz., Maros megye',
                ],
            ],
            'bank' => 'OTP Bank Miercurea Ciuc',
            'bank_account' => 'RON: RO35 OTPV 2600 0116 2186 RO01',
            'email' => 'office[at]rmcssz.ro',
            'phone_numbers' => '+40 (723) 273 257',
        ],
        'sitesearchSettings' => [
            'enabledOnOrgCMSpages'
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
                    array_push($mandateTypes, MandateType::firstOrCreate($mandateType));
                }
            }

            // update the membership fee value for RMCSSZ - Member
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

        // contact page data
        $contactSettings = ContactSettings::instance();
        foreach ($this::DATA['contactSettings'] as $contact_key => $contact_value) {
            if (($contact_key == 'offices' && !isset($contactSettings->{$contact_key})) || ($contact_key != 'offices' && empty($contactSettings->{$contact_key}))) {
                $contactSettings->{$contact_key} = $contact_value;
            }
        }
        $contactSettings->save();

        // add all permissions to scout mandate

        $this->addAllPermissionsToScouts();

        // add read permissions to guest mandate

        $this->addReadPermissionsToGuests();

        // seed site search plugin settings

        $sitesearchSettings = '{"mark_results":"1","log_queries":"0","excerpt_length":"250","log_keep_days":365,"rainlab_blog_enabled":"1","rainlab_blog_label":"Blog","rainlab_blog_page":"403","rainlab_pages_enabled":"0","rainlab_pages_label":"Page","indikator_news_enabled":"0","indikator_news_label":"News","indikator_news_posturl":"\/news","octoshop_products_enabled":"0","octoshop_products_label":"","octoshop_products_itemurl":"\/product","snipcartshop_products_enabled":"0","snipcartshop_products_label":"","jiri_jkshop_enabled":"0","jiri_jkshop_label":"","jiri_jkshop_itemurl":"\/product","radiantweb_problog_enabled":"0","radiantweb_problog_label":"Blog","arrizalamin_portfolio_enabled":"0","arrizalamin_portfolio_label":"Portfolio","arrizalamin_portfolio_url":"\/portfolio\/project","vojtasvoboda_brands_enabled":"0","vojtasvoboda_brands_label":"Brands","vojtasvoboda_brands_url":"\/brand","responsiv_showcase_enabled":"0","responsiv_showcase_label":"Showcase","responsiv_showcase_url":"\/showcase\/project","graker_photoalbums_enabled":"0","graker_photoalbums_label":"PhotoAlbums","graker_photoalbums_album_page":"403","graker_photoalbums_photo_page":"403","cms_pages_enabled":"0","cms_pages_label":"Page"}';

        Db::table('system_settings')
          ->updateOrInsert(
              ['item' => 'offline_sitesearch_settings'],
              ['value' => $sitesearchSettings],
          );
    }

    public function addAllPermissionsToScouts() {
        $associationId = Association::where('name_abbreviation', 'RMCSSZ')->first()->id ?? null;

        if(empty($associationId)) return;

        $permissionBasedModels = PermissionBasedAccess::getAllChildClasses(); //get every model that needs permissions
        $scoutMandateTypeId = Db::table('csatar_csatar_mandate_types')->select('id')
            ->where('association_id', $associationId)
            ->where('organization_type_model_name', '\Csatar\Csatar\Models\Scout')
            ->first()->id; //get scout mandate type id

        if(empty($permissionBasedModels) || empty($scoutMandateTypeId)) return;

        foreach ($permissionBasedModels as $permissionBasedModel) {
            if($permissionBasedModel == MandateType::MODEL_NAME_GUEST) return;

            $model = new $permissionBasedModel();
            $fields = $model->fillable ?? [];
            $relationArrays = ['belongsTo', 'belongsToMany', 'hasMany', 'attachOne', 'hasOne', 'morphTo', 'morphOne',
                'morphMany', 'morphToMany', 'morphedByMany', 'attachMany', 'hasManyThrough', 'hasOneThrough'];

            foreach ($relationArrays as $relationArray){
                $fields = array_merge($fields, array_keys($model->$relationArray));
            }

            $this->filterFieldsForRealtionKeys($fields);
            //add permission for the model in general
            Db::table('csatar_csatar_mandates_permissions')
                ->updateOrInsert(
                    [ 'mandate_type_id' => $scoutMandateTypeId, 'model' => $permissionBasedModel, 'field' => 'MODEL_GENERAL', 'own' => 0],
                    [
                        'create'        => 2,
                        'read'          => 2,
                        'update'        => 2,
                        'delete'        => 2,
                    ]
                );

            //add permission for the model in general for own
            Db::table('csatar_csatar_mandates_permissions')
                ->updateOrInsert(
                    [ 'mandate_type_id' => $scoutMandateTypeId, 'model' => $permissionBasedModel, 'field' => 'MODEL_GENERAL', 'own' => 1],
                    [
                        'create'        => 2,
                        'read'          => 2,
                        'update'        => 2,
                        'delete'        => 2,
                    ]
                );


            //add permission for each attribute for general, own

            foreach ($fields as $field) {
                //add permission for the model->field
                Db::table('csatar_csatar_mandates_permissions')
                    ->updateOrInsert(
                        [ 'mandate_type_id' => $scoutMandateTypeId, 'model' => $permissionBasedModel, 'field' => $field, 'own' => 0],
                        [
                            'create'        => 2,
                            'read'          => 2,
                            'update'        => 2,
                            'delete'        => 2,
                        ]
                    );

                //add permission for the model->field for own
                Db::table('csatar_csatar_mandates_permissions')
                    ->updateOrInsert(
                        [ 'mandate_type_id' => $scoutMandateTypeId, 'model' => $permissionBasedModel, 'field' => $field, 'own' => 1],
                        [
                            'create'        => 2,
                            'read'          => 2,
                            'update'        => 2,
                            'delete'        => 2,
                        ]
                    );
            }
        }

    }

    public function addReadPermissionsToGuests() {
        $associationIds = Association::all()->pluck('id')->toArray();
        $permissionBasedModels = PermissionBasedAccess::getAllChildClasses(); //get every model that needs permissions

        foreach ($associationIds as $associationId) {
            $guestMandateTypeId = Db::table('csatar_csatar_mandate_types')->select('id')
                ->where('association_id', $associationId)
                ->where('organization_type_model_name', 'GUEST')
                ->first()->id; //get guest mandate type id

            if(empty($permissionBasedModels) || empty($guestMandateTypeId)) return;

            foreach ($permissionBasedModels as $permissionBasedModel) {

                $model = new $permissionBasedModel();
                $fields = $model->fillable ?? [];
                $relationArrays = ['belongsTo', 'belongsToMany', 'hasMany', 'attachOne', 'hasOne', 'morphTo', 'morphOne',
                    'morphMany', 'morphToMany', 'morphedByMany', 'attachMany', 'hasManyThrough', 'hasOneThrough'];

                foreach ($relationArrays as $relationArray){
                    $fields = array_merge($fields, array_keys($model->$relationArray));
                }

                $this->filterFieldsForRealtionKeys($fields);

                //add permission for the model in general
                Db::table('csatar_csatar_mandates_permissions')
                    ->updateOrInsert(
                        [ 'mandate_type_id' => $guestMandateTypeId, 'model' => $permissionBasedModel, 'field' => 'MODEL_GENERAL', 'own' => 0],
                        [
                            'read'          => 2,
                        ]
                    );

                //add permission for each attribute

                foreach ($fields as $field) {
                    //add permission for the model->field
                    Db::table('csatar_csatar_mandates_permissions')
                        ->updateOrInsert(
                            [ 'mandate_type_id' => $guestMandateTypeId, 'model' => $permissionBasedModel, 'field' => $field, 'own' => 0],
                            [
                                'read'          => 2,
                            ]
                        );
                }
            }

        }
    }

    public function filterFieldsForRealtionKeys(&$fields) {
        // filters the $fields array to remove relation key field, if relation field exists
        // for example removes: "currency_id" field if there is "currency" field in the array
        foreach ($fields as $key => $field) {
            if (substr($field, -3) === '_id') {
                $relationField = str_replace('_id', '', $field);
                if (in_array($relationField, $fields)) {
                    unset($fields[$key]);
                }
            }
        }
    }
}
