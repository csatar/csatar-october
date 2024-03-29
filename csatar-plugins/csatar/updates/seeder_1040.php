<?php
namespace Csatar\Csatar\Updates;

use Seeder;
use Db;

class Seeder1040 extends Seeder
{

    public function run()
    {
        $contactFormSettings = [
            'validation_type' => 'required',
            'validation_error' => 'Az üzenet kitöltése kötelező',
            'validation_custom_type' => '',
            'validation_custom_pattern' => '',
            'name' => 'message',
            'type' => 'textarea',
            'label' => 'Üzenet',
            'field_values' => null,
            'field_custom_code' => '',
            'field_custom_code_twig' => '0',
            'field_custom_content' => '',
            'field_styling' => '0',
            'autofocus' => '0',
            'wrapper_css' => '',
            'label_css' => '',
            'field_css' => '',
            'field_validation' => '1',
            'validation' => [
                [
                    'validation_type' => 'required',
                    'validation_error' => 'Az üzenet kitöltése kötelező',
                    'validation_custom_type' => '',
                    'validation_custom_pattern' => '',
                ],
            ],
            'form_css_class' => '',
            'form_success_msg' => 'Üzenet elküldve!',
            'form_error_msg' => 'Hiba történt, kérjük ellenőrízze a pirossal kijelölt mezőket!',
            'form_hide_after_success' => '1',
            'form_use_placeholders' => '0',
            'form_disable_browser_validation' => '1',
            'form_allow_ajax' => '1',
            'form_allow_confirm_msg' => '0',
            'form_send_confirm_msg' => '',
            'add_assets' => '0',
            'add_css_assets' => '1',
            'add_js_assets' => '1',
            'form_notes' => '',
            'send_btn_wrapper_css' => '',
            'send_btn_css_class' => 'btn btn-sm rounded btn-primary mt-2',
            'send_btn_text' => 'Küldés',
            'allow_redirect' => '0',
            'redirect_url' => '/',
            'redirect_url_external' => '0',
            'form_fields' => [
                [
                    'name' => 'name',
                    'type' => 'text',
                    'label' => 'Név',
                    'field_values' => null,
                    'field_custom_code' => '',
                    'field_custom_code_twig' => '0',
                    'field_custom_content' => '',
                    'field_styling' => '0',
                    'autofocus' => '1',
                    'wrapper_css' => '',
                    'label_css' => '',
                    'field_css' => '',
                    'field_validation' => '1',
                    'validation' => [
                        [
                            'validation_type' => 'required',
                            'validation_error' => 'A név megadása kötelző',
                            'validation_custom_type' => '',
                            'validation_custom_pattern' => '',
                        ],
                    ],
                ],
                [
                    'name' => 'email',
                    'type' => 'email',
                    'label' => 'E-mail cím',
                    'field_values' => null,
                    'field_custom_code' => '',
                    'field_custom_code_twig' => '0',
                    'field_custom_content' => '',
                    'field_styling' => '0',
                    'autofocus' => '0',
                    'wrapper_css' => '',
                    'label_css' => '',
                    'field_css' => '',
                    'field_validation' => '1',
                    'validation' => [
                        [
                            'validation_type' => 'email',
                            'validation_error' => 'Adjon meg érvényes e-mail címet',
                            'validation_custom_type' => '',
                            'validation_custom_pattern' => '',
                        ],
                        ['validation_type' => 'required',
                            'validation_error' => 'Az e-mail cím megadása kötelező',
                            'validation_custom_type' => '',
                            'validation_custom_pattern' => '',
                        ],
                    ],
                ],
                [
                    'name' => 'message',
                    'type' => 'textarea',
                    'label' => 'Üzenet',
                    'field_values' => null,
                    'field_custom_code' => '',
                    'field_custom_code_twig' => '0',
                    'field_custom_content' => '',
                    'field_styling' => '0',
                    'autofocus' => '0',
                    'wrapper_css' => '',
                    'label_css' => '',
                    'field_css' => '',
                    'field_validation' => '1',
                    'validation' => [
                        [
                            'validation_type' => 'required',
                            'validation_error' => 'Az üzenet kitöltése kötelező',
                            'validation_custom_type' => '',
                            'validation_custom_pattern' => '',
                        ],
                    ],
                ],
            ],
            'autoreply_email_field' => 'email',
            'autoreply_name_field' => 'name',
            'autoreply_message_field' => 'message',
            'add_google_recaptcha' => '0',
            'google_recaptcha_version' => 'v2checkbox',
            'google_recaptcha_site_key' => '',
            'google_recaptcha_secret_key' => '',
            'google_recaptcha_error_msg' => '',
            'google_recaptcha_wrapper_css' => '',
            'google_recaptcha_scripts_allow' => '1',
            'google_recaptcha_locale_allow' => '1',
            'add_antispam' => '0',
            'antispam_delay' => null,
            'antispam_delay_error_msg' => '',
            'antispam_label' => '',
            'antispam_error_msg' => '',
            'add_ip_protection' => '0',
            'add_ip_protection_count' => null,
            'add_ip_protection_error_too_many_submits' => '',
            'allow_email_queue' => '0',
            'allow_autoreply' => '1',
            'email_address_from' => 'csatar@rmcssz.ro',
            'email_address_from_name' => 'CSATÁR',
            'email_address_replyto' => '',
            'email_subject' => '',
            'email_template' => 'csatar.csatar::mail.contactusercopy',
            'allow_notifications' => '1',
            'notification_address_from_form' => '0',
            'notification_address_to' => 'csatar@rmcssz.ro',
            'notification_template' => 'csatar.csatar::mail.contactnotification',
            'ga_success_event_allow' => '0',
            'ga_success_event_gtag' => '',
            'ga_success_event_category' => '',
            'ga_success_event_action' => '',
            'ga_success_event_label' => '',
            'privacy_disable_messages_saving' => '0',
        ];

        Db::table('system_settings')
            ->updateOrInsert(
                ['item' => 'janvince_smallcontactform_settings'],
                ['value' => json_encode($contactFormSettings)]
            );
    }

}
