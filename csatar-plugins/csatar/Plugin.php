<?php namespace Csatar\Csatar;

use App;
use Backend;
use Csatar\Csatar\Classes\Exceptions\OauthException;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\Scout;
use Event;
use Input;
use Lang;
use RainLab\User\Models\User;
use Redirect;
use Session;
use System\Classes\PluginBase;
use ValidationException;
use Validator;

/**
 * csatar Plugin Information File
 */
class Plugin extends PluginBase
{
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
        $this->extendUser();

        App::error(function (\October\Rain\Auth\AuthException $exception) {
            return Lang::get('csatar.csatar::lang.frontEnd.authException');
        });

        App::error(function(
            \Symfony\Component\HttpKernel\Exception\HttpException $exception) {

            if($exception->getStatusCode() == 403) {
                return Redirect::to('/403');
            }
        });

        App::error(function (OauthException $exception) {
            if($exception->getCode() == 1) {
                \Flash::warning($exception->getMessage());
                return Redirect::to('/felhasznaloi-fiok-letrehozasa');
            }

            \Flash::warning($exception->getMessage());
            return Redirect::to('/bejelentkezes');
        });

        Event::listen('flynsarmy.sociallogin.registerUser', function ($provider_details, $user_details) {

            if(empty($user_details->email)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.canNotRegisterLoginWithoutEmail'), 2);
            }

            $scout = Scout::where('email', $user_details->email)->first();

            if(empty($scout)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.canNotFindScoutWithEmail'), 3);
            }

            if(empty($scout->user_id)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.onlyExistingUsersCanLogin'), 1);
            }

            if(!empty($scout->user_id)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.onlyExistingUsersCanLogin'), 4);
            }

        });

        Event::listen('flynsarmy.sociallogin.handleLogin', function (array $provider_details, array $user_details, User $user) {

            if(empty($user_details['profile']->email)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.canNotRegisterLoginWithoutEmail'), 2);
            }

            $scout = Scout::where('email', $user_details['profile']->email)->first();

            if(empty($scout)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.canNotFindScoutWithEmail'), 3);
            }

            if(empty($user)) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.canNotFindUser'), 5);
            }

            //check if scout already has a user_id and if that matches or not the returned user's id
            if(!empty($scout->user_id) && $scout->user_id != $user->id) {
                throw new OAuthException(Lang::get('csatar.csatar::lang.plugin.oauth.userIdAndScoutUserIdMismatch'), 6);
            }

            //if scout doesn't have a user_id, set the returned user's id as user_id
            if(empty($scout->user_id)) {
                $scout->user_id = $user->id;
                $scout->save();
            }

        });

        Event::listen('rainlab.user.login', function($user) {
            if(!empty($user->scout)){
                $user->scout->saveMandateTypeIdsForEveryAssociationToSession();
            }
        });

        $this->saveGuestMandateTypeIdsForEveryAssociationToSession();
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
        User::extend(function($model) {

            $model->hasOne['scout'] = [
                \Csatar\Csatar\Models\Scout::class
            ];

            $model->attributeNames = [
                'password'              => Lang::get('csatar.csatar::lang.plugin.admin.general.password'),
                'password_confirmation' => Lang::get('csatar.csatar::lang.plugin.admin.general.password_confirmation'),
            ];

        });
    }

    public function saveGuestMandateTypeIdsForEveryAssociationToSession(){

        if(empty(Session::get('guest.mandateTypeIds'))) {
            $associationIds = Association::all()->pluck('id');

            if(empty($associationIds)){
                return;
            }

            foreach($associationIds as $associationId){
                MandateType::getGuestMandateTypeIdInAssociation($associationId);
            }
        }

    }
}
