<?php
namespace Csatar\Csatar;

use App;
use Backend;
use Carbon\Carbon;
use Csatar\Csatar\Classes\Exceptions\OauthException;
use Csatar\Csatar\Classes\HistoryService;
use Csatar\Csatar\Classes\SearchProviders\ContentPageSearchProvider;
use Csatar\Csatar\Classes\SearchProviders\OrganizationSearchProvider;
use Csatar\Csatar\Classes\SearchProviders\GallerySearchProvider;
use Csatar\Csatar\Classes\Validators\CnpValidator;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\Scout;
use Db;
use Event;
use Input;
use Lang;
use Media\Classes\MediaLibrary;
use October\Rain\Exception\ApplicationException;
use PolloZen\SimpleGallery\Controllers\Gallery as SimpleGalleryController;
use RainLab\User\Models\User;
use Redirect;
use Schema;
use Session;
use System\Classes\PluginBase;
use ValidationException;
use Validator;

/**
 * csatar Plugin Information File
 */
class Plugin extends PluginBase
{
    public $runMigrations = false;

    /**
     * @var array Plugin dependencies
     */
    public $require = [
        'Flynsarmy.SocialLogin',
        'JanVince.SmallContactForm',
        'OFFLINE.GDPR',
        'OFFLINE.SiteSearch',
        'PolloZen.SimpleGallery',
        'RainLab.Translate',
        'Rainlab.User',
        'RainLab.Location',
        'Vdlp.TwoFactorAuthentication',
    ];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'csatar.csatar::lang.plugin.name',
            'description' => 'csatar.csatar::lang.plugin.description',
            'author'      => 'csatar.csatar::lang.plugin.author',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        if (!Schema::hasTable('system_plugin_versions') || $this->getPluginVersion() < 1070) {
            // if Csatar.Csatar version is lower than a specific version the below code should not run
            return;
        }

        $this->runMigrations = true;

        $this->initHistoryService();

            $this->extendUser();

        App::error(function (\October\Rain\Auth\AuthException $exception) {
            return Lang::get('csatar.csatar::lang.frontEnd.authException');
        });

        App::error(function(
            \Symfony\Component\HttpKernel\Exception\HttpException $exception) {

            if ($exception->getStatusCode() == 403) {
                Session::put('urlBefore403Redirect', Session::get('_previous.url'));
                return Redirect::to('/403');
            }
        });

        App::error(function (OauthException $exception) {
            if ($exception->getCode() == 1) {
                \Flash::warning($exception->getMessage());
                return Redirect::to('/felhasznaloi-fiok-letrehozasa');
            }

            \Flash::warning($exception->getMessage());
            return Redirect::to('/bejelentkezes');
        });

        $this->handleSocialLoginEvents();

        $this->handleExtensionOfSimpleGalleryPlugin();

        $this->handleMandteTypeIdCaching();

        $this->handleExtensionOfSiteSearchPlugin();

        Validator::extend('cnp', CnpValidator::class);
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            \Csatar\Csatar\Components\ResetPassword::class => 'resetPasswordOverRide',
            \Csatar\Csatar\Components\Structure::class => 'structure',
            \Csatar\Csatar\Components\Logos::class => 'logos',
            \Csatar\Csatar\Components\TeamReports::class => 'teamReports',
            \Csatar\Csatar\Components\TeamReport::class => 'teamReport',
            \Csatar\Csatar\Components\CheckScoutStatus::class => 'checkScoutStatus',
            \Csatar\Csatar\Components\CreateFrontendAccounts::class => 'createFrontendAccounts',
            \Csatar\Csatar\Components\OrganizationUnitFrontend::class => 'organizationUnitFrontend',
            \Csatar\Csatar\Components\CsatarGallery::class => 'csatargallery',
            \Csatar\Csatar\Components\Breadcrumb::class => 'csatarBreadcrumb',
            \Csatar\Csatar\Components\TwoFactorAuthentication::class => 'twoFactorAuthentication',
            \Csatar\Csatar\Components\AccidentLogRecordList::class => 'accidentLogRecordList',
            \Csatar\Csatar\Components\Partials::class => 'partials',
            \Csatar\Csatar\Components\ContentPageForm::class => 'contentPageForm',
            \Csatar\Csatar\Components\RecordList::class => 'recordList',
        ];
    }

    public function registerFormWidgets()
    {
        return [
            \Csatar\Forms\Widgets\TagList::class => 'taglist',
            \Csatar\Forms\Widgets\RichEditor::class => 'richeditor',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => Lang::get('csatar.csatar::lang.plugin.admin.contactSettings.contactSettings'),
                'description' => Lang::get('csatar.csatar::lang.plugin.admin.contactSettings.description'),
                'category' => Lang::get('csatar.csatar::lang.plugin.admin.contactSettings.contactSettings'),
                'icon' => 'icon-cog',
                'class' => \Csatar\Csatar\Models\ContactSettings::class,
                'order' => 500,
                'keywords' => 'contact',
                'permissions' => ['csatar.users.access_settings'],
            ]
        ];
    }

    /**
     * Registers any mail templates.
     *
     * @return array
     */
    public function registerMailTemplates()
    {
        return [
            'csatar.csatar::mail.restore',
            'csatar.csatar::mail.contactusercopy',
            'csatar.csatar::mail.contactnotification',
        ];
    }

    protected function extendUser()
    {
        if (class_exists('RainLab\User\Models\User')) {
            User::extend(function ($model) {

                $model->hasOne['scout'] = [
                    \Csatar\Csatar\Models\Scout::class
                ];

                $model->hasMany['historyRecords'] = [
                    \Csatar\Csatar\Models\History::class,
                    'key' => 'fe_user_id',
                ];

                $model->attributeNames = [
                    'password'              => Lang::get('csatar.csatar::lang.plugin.admin.general.password'),
                    'password_confirmation' => Lang::get('csatar.csatar::lang.plugin.admin.general.password_confirmation'),
                ];

                $model->bindEvent('model.beforeDelete', function () use ($model) {
                    if (isset($model->scout) || $model->historyRecords->isNotEmpty()) {
                        throw new ApplicationException(e(trans('csatar.csatar::lang.plugin.admin.general.canNotDeleteUser')));
                    }
                });
            });
        }
    }

    public function saveGuestMandateTypeIdsForEveryAssociationToSession(){

        if (empty(Session::get('guest.mandateTypeIds'))) {
            $associationIds = Association::all()->pluck('id');

            if (empty($associationIds)) {
                return;
            }

            foreach ($associationIds as $associationId) {
                MandateType::getGuestMandateTypeIdInAssociation($associationId);
            }
        }

    }

    public function registerPDFLayouts()
    {
        return [
            'csatar.csatar::pdf.layouts.teamreportlayout',
        ];
    }

    public function registerPDFTemplates()
    {
        return [
            'csatar.csatar::pdf.teamreporttemplate',
        ];
    }

    public function registerSchedule($schedule)
    {
        $schedule->call(function () {
            $scouts = Scout::where('inactivated_at', '<', Carbon::now()->subYears(5))->where('family_name', '!=', Scout::NAME_DELETED_INACTIVITY)->withTrashed()->get();
            foreach ($scouts as $scout) {
                $scout->family_name      = Scout::NAME_DELETED_INACTIVITY;
                $scout->given_name       = '';
                $scout->ignoreValidation = true;
                $scout->forceSave();
            }
        })
            ->dailyAt('00:15');
    }

    /**
     * @return void
     */
    public function initHistoryService(): void
    {
        HistoryService::init([
            '\RainLab\User\Models\User'               => [
                'extraEventListeners' => [
                    'rainlab.user.login'         => 'historyRecordEvent',
                    'rainlab.user.register'      => 'historyRecordEvent',
                    'rainlab.user.activate'      => 'historyRecordEvent',
                    'rainlab.user.deactivate'    => 'historyRecordEvent',
                    'rainlab.user.reactivate'    => 'historyRecordEvent',
                    'csatar.twoFA.authenticated' => 'historyRecordEvent',
                    'csatar.oauthRegistration'   => 'historyRecordEvent',
                    'csatar.oauthLogin'          => 'historyRecordEvent',
                ],
                'extraEvents'         => [
                    'model.auth.beforeImpersonate' => 'historyRecordEvent',
                    'model.auth.afterImpersonate'  => 'historyRecordEvent',
                ],
            ],
            '\RainLab\User\Models\UserGroup'          => null,
            '\Backend\Models\User'                    => [
                'extraEventListeners' => [
                    'backend.user.login' => 'historyRecordEvent',
                ],
            ],
            '\Backend\Models\UserGroup'               => null,
            '\Backend\Models\UserRole'                => null,
            '\PolloZen\SimpleGallery\Models\Gallery'  => null,
            '\Csatar\Csatar\Models\MandatePermission' => [
                'basicEvents'         => false,
                'relationEvents'      => false,
                'extraEventListeners' => [
                    'mandatePermission.afterSave'   => 'historyAfterSave',
                    'mandatePermission.afterDelete' => 'historyAfterDelete',
                ],
            ],
        ]);
    }

    /**
     * @return void
     * @throws OauthException
     */
    public function handleSocialLoginEvents(): void
    {
        Event::listen('flynsarmy.sociallogin.registerUser', function ($provider_details, $user_details) {

            if (empty($user_details->email)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.canNotRegisterLoginWithoutEmail'), 2);
            }

            $scout = Scout::where('email', $user_details->email)->first();

            if (empty($scout)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.canNotFindScoutWithEmail'), 3);
            }

            if (empty($scout->user_id)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.onlyExistingUsersCanLogin'), 1);
            }

            Event::fire('csatar.oauthRegistration', [$scout]);

        });

        Event::listen('flynsarmy.sociallogin.handleLogin', function (array $provider_details, array $user_details, User $user) {

            if (empty($user_details['profile']->email)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.canNotRegisterLoginWithoutEmail'), 2);
            }

            $scout = Scout::where('email', $user_details['profile']->email)->first();

            if (empty($scout)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.canNotFindScoutWithEmail'), 3);
            }

            if (empty($user)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.canNotFindUser'), 5);
            }

            // check if scout already has a user_id and if that matches or not the returned user's id
            if (!empty($scout->user_id) && $scout->user_id != $user->id) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.userIdAndScoutUserIdMismatch'), 6);
            }

            // if scout doesn't have a user_id, set the returned user's id as user_id
            if (empty($scout->user_id)) {
                $scout->user_id = $user->id;
                $scout->save();
            }

            Event::fire('csatar.oauthLogin', [$scout]);

        });
    }

    /**
     * @return void
     */
    public function handleExtensionOfSimpleGalleryPlugin(): void
    {
        if (class_exists('PolloZen\SimpleGallery\Controllers\Gallery')) {
            SimpleGalleryController::extendFormFields(function ($form, $model, $context) {
                if ($form->arrayName === 'Gallery[images]') {
                    $form->addFields([
                        'is_public' => [
                            'label'   => 'Public',
                            'type'    => 'checkbox',
                            'default' => false
                        ]
                    ]);
                }

                $form->addFields([
                    'sort_order' => [
                        'label'   => 'csatar.csatar::lang.plugin.admin.general.sortOrder',
                        'type'    => 'number',
                        'default' => 0
                    ]
                ]);
            });
        }

        if (class_exists('PolloZen\SimpleGallery\Models\Gallery')) {
            \PolloZen\SimpleGallery\Models\Gallery::extend(function ($model) {
                $model->morphTo = [
                    'model' => []
                ];
                $model->hasMany = [
                    'galleryPivot' => [
                        \Csatar\Csatar\Models\GalleryModelPivot::class,
                        'table' => 'csatar_csatar_gallery_model',
                    ],
                ];
            });
        }
    }

    /**
     * @return void
     */
    public function handleMandteTypeIdCaching(): void
    {
        Event::listen('rainlab.user.login', function ($user) {
            if (!empty($user->scout)) {
                $user->scout->saveMandateTypeIdsForEveryAssociationToSession();
            }
        });

        $this->saveGuestMandateTypeIdsForEveryAssociationToSession();
    }

    /**
     * @return void
     */
    public function handleExtensionOfSiteSearchPlugin(): void
    {
        Event::listen('offline.sitesearch.extend', function () {
            return [
                new OrganizationSearchProvider(),
                new ContentPageSearchProvider(),
                new GallerySearchProvider()
            ];
        });
    }

    public function getPluginVersion(): int
    {
        $version = \System\Models\PluginVersion::where('code', 'Csatar.Csatar')->first()->version ?? '0.0.0';
        return intval(str_replace('.', '', $version));
    }

}
