<?php namespace Csatar\Csatar\Components;

use Auth;
use Lang;
use Mail;
use Validator;
use ValidationException;
use ApplicationException;
use Cms\Classes\Page;
use RainLab\User\Components;
use RainLab\User\Models\User as UserModel;

/**
 * ResetPassword controls the password reset workflow
 *
 * When a user has forgotten their password, they are able to reset it using
 * a unique token that, sent to their email address upon request.
 */
class ResetPassword extends \RainLab\User\Components\ResetPassword
{
    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.resetPassword.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.resetPassword.description')
        ];
    }

    /**
     * Trigger the password reset email
     */
    public function onRestorePassword()
    {
        $rules = [
            'email' => 'required|email|between:6,255'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $user = UserModel::findByEmail(post('email'));
        if (!$user || $user->is_guest) {
            throw new ApplicationException(Lang::get(/*A user was not found with the given credentials.*/'rainlab.user::lang.account.invalid_user'));
        }

        $code = implode('!', [$user->id, $user->getResetPasswordCode()]);

        $link = $this->makeResetUrl($code);

        $data = [
            'name' => $user->name,
            'username' => $user->username,
            'link' => $link,
            'code' => $code
        ];

        Mail::send('csatar.csatar::mail.restore', $data, function($message) use ($user) {
            $message->to($user->email, $user->full_name);
        });
    }

    /**
     * Perform the password reset
     */
    public function onResetPassword()
    {
        $rules = [
            'code'     => 'required',
            'password' => 'required|regex:(^.*(?=.{' . UserModel::getMinPasswordLength() . ',})(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*_0-9]).*$)'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $errorFields = ['code' => Lang::get(/*Invalid activation code supplied.*/'rainlab.user::lang.account.invalid_activation_code')];

        /*
         * Break up the code parts
         */
        $parts = explode('!', post('code'));
        if (count($parts) != 2) {
            throw new ValidationException($errorFields);
        }

        list($userId, $code) = $parts;

        if (!strlen(trim($userId)) || !strlen(trim($code)) || !$code) {
            throw new ValidationException($errorFields);
        }

        if (!$user = Auth::findUserById($userId)) {
            throw new ValidationException($errorFields);
        }

        if (!$user->attemptResetPassword($code, post('password'))) {
            throw new ValidationException($errorFields);
        }

        // Check needed for compatibility with legacy systems
        if (method_exists(\RainLab\User\Classes\AuthManager::class, 'clearThrottleForUserId')) {
            Auth::clearThrottleForUserId($user->id);
        }
    }
}
