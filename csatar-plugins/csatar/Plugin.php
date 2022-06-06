<?php namespace Csatar\Csatar;

use App;
use Backend;
use Event;
use Input;
use System\Classes\PluginBase;
use Validator;
use ValidationException;
use Lang;
use RainLab\User\Models\User;
use Csatar\Csatar\Models\Scout;

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

        App::error(function(\October\Rain\Auth\AuthException $exception) {
            return Lang::get('csatar.csatar::lang.frontEnd.authException');
        });
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            \Csatar\Csatar\Components\ResetPassword::class => 'resetpasswordOverRide',
            \Csatar\Csatar\Components\Structure::class => 'structure',
            \Csatar\Csatar\Components\Logos::class => 'logos',
            \Csatar\Csatar\Components\CheckScoutStatus::class => 'checkscoutstatus',
            \Csatar\Csatar\Components\CreateFrontendAccounts::class => 'CreateFrontendAccounts'
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
            'csatar.csatar::mail.restore'
        ];
    }

    protected function extendUser()
    {
        User::extend(function($model) {

            $model->hasOne['scout'] = [
                \Csatar\Csatar\Models\Scout::class
            ];

        });

        Event::listen('rainlab.user.beforeRegister', function() {

            $data = Input::all();
            $rules = ['ecset_code' => 'required'];

            $validation = Validator::make(
                $data,
                $rules
            );

            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            $ecsetCode = $data['ecset_code'];

            if(!empty($ecsetCode)){
                $scout = Scout::where('ecset_code', $ecsetCode)->first();
            }

            if(empty($scout) || !is_object($scout)){
                throw new ValidationException(['ecset_code' => 'Érvénytelen ECSET kód!']);
            }
//            dd($data['email'], $scout->email);
            if(!empty($scout) && $scout->email != $data['email']){
                throw new ValidationException(['ecset_code' => 'Az email cím és az ECSET kód nem egyezik!']);
            }

        });

        Event::listen('rainlab.user.register', function($user) {
            $ecsetCode = Input::get('ecset_code');

            if(!empty($ecsetCode)){
                $scout = Scout::where('ecset_code', $ecsetCode)->first();
                $scout->user_id = $user->id;
                $scout->save();
            }

        });

    }
}
