<?php 
namespace Csatar\Csatar\Components;

use Lang;
use Auth;
use Mail;
use Event;
use Flash;
use Input;
use Request;
use Redirect;
use Validator;
use ValidationException;
use ApplicationException;
use October\Rain\Auth\AuthException;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;
use RainLab\User\Models\Settings as UserSettings;
use Exception;
use Csatar\Csatar\Models\Scout;

/**
 * Creates a Frontend user account for an existing Scout
 *
 */

class CreateFrontendAccounts extends \RainLab\User\Components\Account
{
    public $permissions;

    public $messages = [
        'error' => [],
        'success' => [],
    ];

    public $scouts;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.createFrontendAccounts.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.createFrontendAccounts.description')
        ];
    }

    public function defineProperties()
    {
        return [
            'paramCode' => [
                'title'       => 'rainlab.user::lang.reset_password.code_param',
                'description' => 'rainlab.user::lang.reset_password.code_param_desc', //The page URL parameter used for the reset code
                'type'        => 'string',
                'default'     => 'code'
            ],
            'resetPage' => [
                'title'       => 'rainlab.user::lang.account.reset_page',
                'description' => 'rainlab.user::lang.account.reset_page_comment',
                'type'        => 'dropdown',
                'default'     => ''
            ],
        ];
    }

    public function prepareVars()
    {
        $this->page['user']           = $this->user();
        $this->page['canRegister']    = $this->canRegister();
        $this->page['loginAttribute'] = $this->loginAttribute();
        if (isset(Auth::user()->scout)) {
            $this->scouts = Scout::where('team_id', Auth::user()->scout->team_id)->get();
        }

    }

    public function onRun()
    {
        if ($redirect = $this->redirectForceSecure()) {
            return $redirect;
        }

        if (isset(Auth::user()->scout)) {
            $tempScout          = new Scout();
            $tempScout->team_id = Auth::user()->scout->team_id;

            if (Auth::user()->scout->getRightsForModel($tempScout)['MODEL_GENERAL']['read'] < 1) {
                \App::abort(403, 'Access denied!');
            }

            $this->permissions = Auth::user()->scout->getRightsForModel($tempScout);
        }

        $this->prepareVars();
    }

    public function getResetPageOptions()
    {
        return [
                '' => Lang::get('csatar.csatar::lang.plugin.component.createFrontendAccounts.currentPage'),
            ] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }


     // Self register with email and ID number

    public function onRegister(){

        $data = post();

        $rules = [
            'ecset_code'            => 'required',
            'email'                 => 'required|between:6,255|email|unique:users',
            'password'              => 'required|regex:(^.*(?=.{' . UserModel::getMinPasswordLength() . ',})(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*_0-9]).*$)',
            'password_confirmation' => 'required|regex:(^.*(?=.{' . UserModel::getMinPasswordLength() . ',})(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*_0-9]).*$)',
        ];

        $attributeNames = [
            'ecset_code'            => Lang::get('csatar.csatar::lang.plugin.admin.general.ecsetCode'),
            'password'              => Lang::get('csatar.csatar::lang.plugin.admin.general.password'),
            'password_confirmation' => Lang::get('csatar.csatar::lang.plugin.admin.general.password_confirmation'),
        ];

        $customMessages = [
            'email.unique' => Lang::get('csatar.csatar::lang.plugin.component.general.validationExceptions.emailAlreadyAssigned'),
            'password.regex' => Lang::get('csatar.csatar::lang.plugin.component.general.validationExceptions.passwordRegex'),
            'password_confirmation.regex' => Lang::get('csatar.csatar::lang.plugin.component.general.validationExceptions.passwordRegex'),
        ];

        $validation = Validator::make(
            $data,
            $rules,
            $customMessages,
            $attributeNames,
        );

        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $ecsetCode = $data['ecset_code'];

        try {
            if (!empty($ecsetCode)) {
                $scout = Scout::where('ecset_code', $ecsetCode)->first();
            }

            if (empty($scout) || !is_object($scout)) {
                throw new ValidationException(['ecset_code' => Lang::get('csatar.csatar::lang.plugin.component.createFrontendAccounts.validationExceptions.invalidEcsetCode')]);
            }

            if (!empty($scout) && $scout->email != $data['email']) {
                throw new ValidationException(['ecset_code' => Lang::get('csatar.csatar::lang.plugin.component.createFrontendAccounts.validationExceptions.emailEcsetCodeMissMatch')]);
            }

            if (!$this->canRegister()) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.registration_disabled'));
            }

            if ($this->isRegisterThrottled()) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.registration_throttled'));
            }

            if ($this->loginAttribute() !== UserSettings::LOGIN_USERNAME) {
                unset($rules['username']);
            }

            if ($ipAddress = Request::ip()) {
                $data['created_ip_address'] = $data['last_ip_address'] = $ipAddress;
            }

            Event::fire('rainlab.user.beforeRegister', [&$data]);

            $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
            $user = Auth::register($data, $automaticActivation);

            Event::fire('rainlab.user.register', [$user, $data]);

            $scout->user_id = $user->id;
            $scout->forceSave();

            if ($automaticActivation) {
                $fullName = $scout->getFullName();
                $data     = [
                    'name' => $fullName,
                ];

                Mail::send('csatar.csatar::mail.welcome', $data, function($message) use ($user, $fullName) {
                    $message->to($user->email, $fullName);
                });

                Auth::login($user, $this->useRememberLogin());
            }

            return Redirect::to('/tag/' . $ecsetCode);
        } catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }

    public function onCreateFrontendAccounts(){

        $scoutIds = Input::get('scouts');

        if (empty($scoutIds)) {
            throw new ValidationException(['scouts' => Lang::get('csatar.csatar::lang.plugin.component.createFrontendAccounts.validationExceptions.noScoutIsSelected')]);
        }

        $scouts = Scout::whereIn('id', $scoutIds)->get();

        foreach ($scouts as $scout) {
            if (!empty($scout->user_id)) {
                $this->messages['errors'][$scout->id] =
                    Lang::get('csatar.csatar::lang.plugin.component.createFrontendAccounts.messages.scoutAlreadyHasUserAccount',
                        ['name' => $scout->getFullName() ], 'hu');
            }

            if (!empty($scout->email) && empty($scout->user_id)) {
                $this->register($scout);
            }

            if (empty($scout->email)) {
                $this->messages['errors'][$scout->id] =
                    Lang::get('csatar.csatar::lang.plugin.component.createFrontendAccounts.messages.scoutHasNoEmail',
                        ['name' => $scout->getFullName() ]);
            }
        }

        return [
            '#messageList' => $this->renderPartial('@messages',)
        ];
    }


     // Register the user

    public function register($scout)
    {
        try {
            if (!$this->canRegister()) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.registration_disabled'));
            }

            if ($this->isRegisterThrottled()) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.registration_throttled'));
            }

            $data['email']    = $scout->email;
            $data['password'] = str_random(20);
            $data['password_confirmation'] = $data['password'];

            $rules = (new UserModel)->rules;

            if ($this->loginAttribute() !== UserSettings::LOGIN_USERNAME) {
                unset($rules['username']);
            }

            $validation = Validator::make(
                $data,
                $rules,
                $this->getValidatorMessages(),
                $this->getCustomAttributes()
            );

            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            if ($ipAddress = Request::ip()) {
                $data['created_ip_address'] = $data['last_ip_address'] = $ipAddress;
            }

            Event::fire('rainlab.user.beforeRegister', [&$data]);

            $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;

            $user = Auth::register($data, $automaticActivation);

            Event::fire('rainlab.user.register', [$user, $data]);

            $scout->user_id = $user->id;
            $scout->forceSave();

            $fullName = $scout->getFullName();

            $code = implode('!', [$user->id, $user->getResetPasswordCode()]);

            $link = $this->makeResetUrl($code);

            $data = [
                'link' => $link,
                'code' => $code
            ];

            Mail::send('csatar.csatar::mail.setpassword', $data, function($message) use ($user, $fullName) {
                $message->to($user->email, $fullName);
            });

            $this->messages['success'][$scout->id] = Lang::get('csatar.csatar::lang.plugin.component.createFrontendAccounts.messages.userAccountCreated',
                ['name' => $scout->getFullName() ]);
        } catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }

    protected function makeResetUrl($code)
    {
        $params = [
            $this->property('paramCode') => $code
        ];

        if ($pageName = $this->property('resetPage')) {
            $url = $this->pageUrl($pageName, $params);
        } else {
            $url = $this->currentPageUrl($params);
        }

        if (strpos($url, $code) === false) {
            $url .= '?reset=' . $code;
        }

        return $url;
    }

}
