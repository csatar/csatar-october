<?php
namespace Csatar\Csatar\Updates;

use RainLab\Builder\Classes\ComponentHelper;
use Seeder;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\Troop;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\PermissionBasedAccess;
use Db;

class TestData extends Seeder
{
    public const DATA = [
        'district' => [
            'Nógrád',
            'Csík',
            'Észak-Erdély',
            'Háromszék',
        ],
        'team' => [
            'Nógrádi első próba',
            'Szent István',
            'Zöld Péter',
            'Élthes Alajos',
            'Hollósy Simon',
            'Szent György',
            'Nagyboldogasszony',
        ],
        'troop' => [
            'Madarak',
            'Virágok',
            'Madarak',
            'Virágok',
            'Halak',
        ],
        'patrol' => [
            'Sasok',
            'Hollók',
            'Farkasok',
            'Fácánok',
            'Verebek',
            'Zergék',
            'Orchideák',
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
        'contactFormSettings',
    ];

    public function run()
    {
        // districts
        $association_magyar = Association::where('name', 'Magyar Cserkészszövetség')->first();
        if (isset($association_magyar)) {
            $district_1          = District::firstOrNew([
                'name' => $this::DATA['district'][0],
                'association_id' => $association_magyar->id,
            ]);
            $district_1->address = 'Balassagyarmat, Jácint utca, 21';
            $district_1->phone   = '0123456789';
            $district_1->email   = 'erika@yahoo.com';
            $district_1->contact_name            = 'Vass Erika';
            $district_1->contact_email           = 'erika@yahoo.com';
            $district_1->leadership_presentation = 'A';
            $district_1->description = 'A';
            $district_1->save();
        }

        $association_rmcssz = Association::where('name', 'Romániai Magyar Cserkészszövetség')->first();
        if (isset($association_rmcssz)) {
            $district_2          = District::firstOrNew([
                'name' => $this::DATA['district'][1],
                'association_id' => $association_rmcssz->id,
            ]);
            $district_2->address = 'Abcde';
            $district_2->phone   = '0123456789';
            $district_2->email   = 'a@aa.com';
            $district_2->contact_name            = 'Szőcs Szilveszter';
            $district_2->contact_email           = 'a@aa.com';
            $district_2->leadership_presentation = '-';
            $district_2->description = '-';
            $district_2->save();

            $district_3          = District::firstOrNew([
                'name' => $this::DATA['district'][2],
                'association_id' => $association_rmcssz->id,
            ]);
            $district_3->address = 'Abcde';
            $district_3->phone   = '0123456789';
            $district_3->email   = 'a@aa.com';
            $district_3->contact_name            = 'Szénás Zalán';
            $district_3->contact_email           = 'a@aa.com';
            $district_3->leadership_presentation = '-';
            $district_3->description = '-';
            $district_3->save();

            $district_4          = District::firstOrNew([
                'name' => $this::DATA['district'][3],
                'association_id' => $association_rmcssz->id,
            ]);
            $district_4->address = 'Abcde';
            $district_4->phone   = '0123456789';
            $district_4->email   = 'a@aa.com';
            $district_4->contact_name            = 'Székely István';
            $district_4->contact_email           = 'a@aa.com';
            $district_4->leadership_presentation = '-';
            $district_4->description = '-';
            $district_4->save();
        }

        // teams
        if (isset($district_1)) {
            $team_1 = Team::firstOrNew([
                'name' => $this::DATA['team'][0],
                'district_id' => $district_1->id,
            ]);
            $team_1->team_number     = '1';
            $team_1->address         = 'Balassagyarmat, Ady Endre utca, 10';
            $team_1->foundation_date = '2000-06-06';
            $team_1->phone           = '0123456789';
            $team_1->email           = 'edina@yahoo.com';
            $team_1->contact_name    = 'Edina';
            $team_1->contact_email   = 'edina@yahoo.com';
            $team_1->leadership_presentation = 'A';
            $team_1->description           = 'A';
            $team_1->juridical_person_name = 'Edina';
            $team_1->juridical_person_address      = 'Balassagyarmat, Ady Endre utca, 10';
            $team_1->juridical_person_tax_number   = '06548';
            $team_1->juridical_person_bank_account = 'EM66544';
            $team_1->save();
        }

        if (isset($district_2)) {
            $team_2 = Team::firstOrNew([
                'name' => $this::DATA['team'][1],
                'district_id' => $district_2->id,
            ]);
            $team_2->team_number     = '4';
            $team_2->address         = 'Abcde';
            $team_2->foundation_date = '2000-06-18';
            $team_2->phone           = '0123456789';
            $team_2->email           = 'a@aa.com';
            $team_2->contact_name    = 'Bálint Lajos Lóránt';
            $team_2->contact_email   = 'a@aa.com';
            $team_2->leadership_presentation = '-';
            $team_2->description           = '-';
            $team_2->juridical_person_name = 'Bálint Lajos Lóránt';
            $team_2->juridical_person_address      = 'Abcde';
            $team_2->juridical_person_tax_number   = '01234';
            $team_2->juridical_person_bank_account = '01234';
            $team_2->save();

            $team_3 = Team::firstOrNew([
                'name' => $this::DATA['team'][2],
                'district_id' => $district_2->id,
            ]);
            $team_3->team_number     = '18';
            $team_3->address         = 'Abcde';
            $team_3->foundation_date = '2000-06-18';
            $team_3->phone           = '0123456789';
            $team_3->email           = 'a@aa.com';
            $team_3->contact_name    = 'Fodor Csaba';
            $team_3->contact_email   = 'a@aa.com';
            $team_3->leadership_presentation = '-';
            $team_3->description           = '-';
            $team_3->juridical_person_name = 'Fodor Csaba';
            $team_3->juridical_person_address      = 'Abcde';
            $team_3->juridical_person_tax_number   = '01234';
            $team_3->juridical_person_bank_account = '01234';
            $team_3->save();

            $team_4 = Team::firstOrNew([
                'name' => $this::DATA['team'][3],
                'district_id' => $district_2->id,
            ]);
            $team_4->team_number     = '152';
            $team_4->address         = 'Abcde';
            $team_4->foundation_date = '2000-06-18';
            $team_4->phone           = '0123456789';
            $team_4->email           = 'a@aa.com';
            $team_4->contact_name    = 'Lázár Annamária';
            $team_4->contact_email   = 'a@aa.com';
            $team_4->leadership_presentation = '-';
            $team_4->description           = '-';
            $team_4->juridical_person_name = 'Lázár Annamária';
            $team_4->juridical_person_address      = 'Abcde';
            $team_4->juridical_person_tax_number   = '01234';
            $team_4->juridical_person_bank_account = '01234';
            $team_4->save();
        }

        if (isset($district_3)) {
            $team_5 = Team::firstOrNew([
                'name' => $this::DATA['team'][4],
                'district_id' => $district_3->id,
            ]);
            $team_5->team_number     = '146';
            $team_5->address         = 'Abcde';
            $team_5->foundation_date = '2000-06-18';
            $team_5->phone           = '0123456789';
            $team_5->email           = 'a@aa.com';
            $team_5->contact_name    = 'Keresztes Annamária';
            $team_5->contact_email   = 'a@aa.com';
            $team_5->leadership_presentation = '-';
            $team_5->description           = '-';
            $team_5->juridical_person_name = 'Keresztes Annamária';
            $team_5->juridical_person_address      = 'Abcde';
            $team_5->juridical_person_tax_number   = '01234';
            $team_5->juridical_person_bank_account = '01234';
            $team_5->save();
        }

        if (isset($district_4)) {
            $team_6 = Team::firstOrNew([
                'name' => $this::DATA['team'][5],
                'district_id' => $district_4->id,
            ]);
            $team_6->team_number     = '40';
            $team_6->address         = 'Abcde';
            $team_6->foundation_date = '2000-06-18';
            $team_6->phone           = '0123456789';
            $team_6->email           = 'a@aa.com';
            $team_6->contact_name    = 'Szabó Lajos';
            $team_6->contact_email   = 'a@aa.com';
            $team_6->leadership_presentation = '-';
            $team_6->description           = '-';
            $team_6->juridical_person_name = 'Szabó Lajos';
            $team_6->juridical_person_address      = 'Abcde';
            $team_6->juridical_person_tax_number   = '01234';
            $team_6->juridical_person_bank_account = '01234';
            $team_6->save();

            $team_7 = Team::firstOrNew([
                'name' => $this::DATA['team'][6],
                'district_id' => $district_4->id,
            ]);
            $team_7->team_number     = '141';
            $team_7->address         = 'Abcde';
            $team_7->foundation_date = '2000-06-18';
            $team_7->phone           = '0123456789';
            $team_7->email           = 'a@aa.com';
            $team_7->contact_name    = 'Illyés Botond';
            $team_7->contact_email   = 'a@aa.com';
            $team_7->leadership_presentation = '-';
            $team_7->description           = '-';
            $team_7->juridical_person_name = 'Illyés Botond';
            $team_7->juridical_person_address      = 'Abcde';
            $team_7->juridical_person_tax_number   = '01234';
            $team_7->juridical_person_bank_account = '01234';
            $team_7->save();
        }

        // troops
        if (isset($team_6)) {
            $troop_1 = Troop::firstOrNew([
                'name' => $this::DATA['troop'][0],
                'team_id' => $team_6->id,
            ]);
            $troop_1->ignoreValidation = true;
            $troop_1->save();

            $troop_2 = Troop::firstOrNew([
                'name' => $this::DATA['troop'][1],
                'team_id' => $team_6->id,
            ]);
            $troop_2->ignoreValidation = true;
            $troop_2->save();
        }

        if (isset($team_7)) {
            $troop_3 = Troop::firstOrNew([
                'name' => $this::DATA['troop'][2],
                'team_id' => $team_7->id,
            ]);
            $troop_3->ignoreValidation = true;
            $troop_3->save();

            $troop_4 = Troop::firstOrNew([
                'name' => $this::DATA['troop'][3],
                'team_id' => $team_7->id,
            ]);
            $troop_4->ignoreValidation = true;
            $troop_4->save();

            $troop_5 = Troop::firstOrNew([
                'name' => $this::DATA['troop'][4],
                'team_id' => $team_7->id,
            ]);
            $troop_5->ignoreValidation = true;
            $troop_5->save();
        }

        // patrols
        if (isset($team_6)) {
            $patrol_1 = Patrol::firstOrNew([
                'name' => $this::DATA['patrol'][0],
                'team_id' => $team_6->id,
            ]);
            if (isset($troop_1)) {
                $patrol_1->troop_id = $troop_1->id;
            }

            $patrol_1->age_group_id     = $this->getFirstAgeGroupInAssociation($team_6->id);
            $patrol_1->ignoreValidation = true;
            $patrol_1->save();

            $patrol_2 = Patrol::firstOrNew([
                'name' => $this::DATA['patrol'][1],
                'team_id' => $team_6->id,
            ]);
            if (isset($troop_1)) {
                $patrol_2->troop_id = $troop_1->id;
            }

            $patrol_2->age_group_id     = $this->getFirstAgeGroupInAssociation($team_6->id);
            $patrol_2->ignoreValidation = true;
            $patrol_2->save();

            $patrol_3 = Patrol::firstOrNew([
                'name' => $this::DATA['patrol'][2],
                'team_id' => $team_6->id,
            ]);
            $patrol_3->age_group_id     = $this->getFirstAgeGroupInAssociation($team_6->id);
            $patrol_3->ignoreValidation = true;
            $patrol_3->save();
        }

        if (isset($team_7)) {
            $patrol_4 = Patrol::firstOrNew([
                'name' => $this::DATA['patrol'][3],
                'team_id' => $team_7->id,
            ]);
            if (isset($troop_3)) {
                $patrol_4->troop_id = $troop_3->id;
            }

            $patrol_4->age_group_id     = $this->getFirstAgeGroupInAssociation($team_7->id);
            $patrol_4->ignoreValidation = true;
            $patrol_4->save();

            $patrol_5 = Patrol::firstOrNew([
                'name' => $this::DATA['patrol'][4],
                'team_id' => $team_7->id,
            ]);
            if (isset($troop_3)) {
                $patrol_5->troop_id = $troop_3->id;
            }

            $patrol_5->age_group_id     = $this->getFirstAgeGroupInAssociation($team_7->id);
            $patrol_5->ignoreValidation = true;
            $patrol_5->save();

            $patrol_6 = Patrol::firstOrNew([
                'name' => $this::DATA['patrol'][5],
                'team_id' => $team_7->id,
            ]);
            $patrol_6->age_group_id     = $this->getFirstAgeGroupInAssociation($team_7->id);
            $patrol_6->ignoreValidation = true;
            $patrol_6->save();

            $patrol_7 = Patrol::firstOrNew([
                'name' => $this::DATA['patrol'][6],
                'team_id' => $team_7->id,
            ]);
            if (isset($troop_4)) {
                $patrol_7->troop_id = $troop_4->id;
            }

            $patrol_7->age_group_id     = $this->getFirstAgeGroupInAssociation($team_7->id);
            $patrol_7->ignoreValidation = true;
            $patrol_7->save();
        }

        // seed contact form settings - temp solution

        $contactFormSettings = '{"validation_type":"required","validation_error":"Az \u00fczenet kit\u00f6lt\u00e9se k\u00f6telez\u0151","validation_custom_type":"","validation_custom_pattern":"","name":"message","type":"textarea","label":"\u00dczenet","field_values":null,"field_custom_code":"","field_custom_code_twig":"0","field_custom_content":"","field_styling":"0","autofocus":"0","wrapper_css":"","label_css":"","field_css":"","field_validation":"1","validation":[{"validation_type":"required","validation_error":"Az \u00fczenet kit\u00f6lt\u00e9se k\u00f6telez\u0151","validation_custom_type":"","validation_custom_pattern":""}],"form_css_class":"","form_success_msg":"","form_error_msg":"","form_hide_after_success":"1","form_use_placeholders":"0","form_disable_browser_validation":"1","form_allow_ajax":"1","form_allow_confirm_msg":"0","form_send_confirm_msg":"","add_assets":"1","add_css_assets":"1","add_js_assets":"1","form_notes":"","send_btn_wrapper_css":"","send_btn_css_class":"","send_btn_text":"K\u00fcld\u00e9s","allow_redirect":"0","redirect_url":"\/","redirect_url_external":"0","form_fields":[{"name":"name","type":"text","label":"N\u00e9v","field_values":null,"field_custom_code":"","field_custom_code_twig":"0","field_custom_content":"","field_styling":"0","autofocus":"1","wrapper_css":"","label_css":"","field_css":"","field_validation":"1","validation":[{"validation_type":"required","validation_error":"A n\u00e9v megad\u00e1sa k\u00f6telz\u0151","validation_custom_type":"","validation_custom_pattern":""}]},{"name":"email","type":"email","label":"E-mail c\u00edm","field_values":null,"field_custom_code":"","field_custom_code_twig":"0","field_custom_content":"","field_styling":"0","autofocus":"0","wrapper_css":"","label_css":"","field_css":"","field_validation":"1","validation":[{"validation_type":"email","validation_error":"Adjon meg \u00e9rv\u00e9nyes e-mail c\u00edmet","validation_custom_type":"","validation_custom_pattern":""},{"validation_type":"required","validation_error":"Az e-mail c\u00edm megad\u00e1sa k\u00f6telez\u0151","validation_custom_type":"","validation_custom_pattern":""}]},{"name":"message","type":"textarea","label":"\u00dczenet","field_values":null,"field_custom_code":"","field_custom_code_twig":"0","field_custom_content":"","field_styling":"0","autofocus":"0","wrapper_css":"","label_css":"","field_css":"","field_validation":"1","validation":[{"validation_type":"required","validation_error":"Az \u00fczenet kit\u00f6lt\u00e9se k\u00f6telez\u0151","validation_custom_type":"","validation_custom_pattern":""}]}],"autoreply_email_field":"email","autoreply_name_field":"name","autoreply_message_field":"message","add_google_recaptcha":"1","google_recaptcha_version":"v2checkbox","google_recaptcha_site_key":"","google_recaptcha_secret_key":"","google_recaptcha_error_msg":"","google_recaptcha_wrapper_css":"","google_recaptcha_scripts_allow":"1","google_recaptcha_locale_allow":"1","add_antispam":"0","antispam_delay":null,"antispam_delay_error_msg":"","antispam_label":"","antispam_error_msg":"","add_ip_protection":"0","add_ip_protection_count":null,"add_ip_protection_error_too_many_submits":"","allow_email_queue":"0","allow_autoreply":"0","email_address_from":"","email_address_from_name":"","email_address_replyto":"","email_subject":"","email_template":"","allow_notifications":"0","notification_address_from_form":"0","notification_address_to":"","notification_template":"","ga_success_event_allow":"0","ga_success_event_gtag":"","ga_success_event_category":"","ga_success_event_action":"","ga_success_event_label":"","privacy_disable_messages_saving":"0"}';

        Db::table('system_settings')
            ->updateOrInsert(
                ['item' => 'janvince_smallcontactform_setting'],
                ['value' => $contactFormSettings],
            );

        // add all permissions to scout mandate

        $this->addAllPermissionsToScouts();

        // add read permissions to guest mandate

        $this->addReadPermissionsToGuests();
    }

    public function getFirstAgeGroupInAssociation($team_id) {
        $team = Team::find($team_id);

        if (!empty($team)) {
            return $team->district->association->ageGroups[0]->id ?? 0;
        }

        return 0;
    }

    public function addAllPermissionsToScouts() {
        $associationId = Association::where('name_abbreviation', 'RMCSSZ')->first()->id ?? null;

        if (empty($associationId)) return;

        $permissionBasedModels = PermissionBasedAccess::getAllChildClasses(); //get every model that needs permissions
        $scoutMandateTypeId    = Db::table('csatar_csatar_mandate_types')->select('id')
            ->where('association_id', $associationId)
            ->where('organization_type_model_name', '\Csatar\Csatar\Models\Scout')
            ->whereNull('deleted_at')
            ->first()->id; //get scout mandate type id

        if (empty($permissionBasedModels) || empty($scoutMandateTypeId)) return;

        foreach ($permissionBasedModels as $permissionBasedModel) {
            if ($permissionBasedModel == MandateType::MODEL_NAME_GUEST) return;

            $model          = new $permissionBasedModel();
            $fields         = $model->fillable ?? [];
            $relationArrays = ['belongsTo', 'belongsToMany', 'hasMany', 'attachOne', 'hasOne', 'morphTo', 'morphOne',
                               'morphMany', 'morphToMany', 'morphedByMany', 'attachMany', 'hasManyThrough', 'hasOneThrough'];

            foreach ($relationArrays as $relationArray) {
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
        $associationIds        = Association::all()->pluck('id')->toArray();
        $permissionBasedModels = PermissionBasedAccess::getAllChildClasses(); //get every model that needs permissions

        foreach ($associationIds as $associationId) {
            $guestMandateTypeId = Db::table('csatar_csatar_mandate_types')->select('id')
                ->where('association_id', $associationId)
                ->where('organization_type_model_name', 'GUEST')
                ->whereNull('deleted_at')
                ->first()->id; //get guest mandate type id

            if (empty($permissionBasedModels) || empty($guestMandateTypeId)) return;

            foreach ($permissionBasedModels as $permissionBasedModel) {
                $model          = new $permissionBasedModel();
                $fields         = $model->fillable ?? [];
                $relationArrays = ['belongsTo', 'belongsToMany', 'hasMany', 'attachOne', 'hasOne', 'morphTo', 'morphOne',
                                   'morphMany', 'morphToMany', 'morphedByMany', 'attachMany', 'hasManyThrough', 'hasOneThrough'];

                foreach ($relationArrays as $relationArray) {
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
