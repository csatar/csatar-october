<?php namespace Csatar\Csatar\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Classes\GoogleTwoFactorAuthentication;
use Csatar\Csatar\Models\Scout;
use Input;
use Lang;
use Redirect;
use Session;

class TwoFactorAuthentication extends ComponentBase
{
    private $userSecretKey;
    private $google2FA;
    public $qrCodeData;
    public $is2FAuthenticated;
    public $activated2FA;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.twoFactorAuthentication.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.twoFactorAuthentication.description'),
        ];
    }

    public function onRun()
    {
        if (!Auth::user()) {
            return;
        }

        if (!isset(Auth::user()->scout)) {
            return;
        }

        $this->prepareVariables();
    }

    public function onVerifyCode()
    {
        $this->prepareVariables();

        if ($this->google2FA->verifyKey($this->userSecretKey, Input::get('code'))) {
            Session::forget('scout.rightsForModels');
            Session::put('scout.twoFA', true);
            return Redirect::to(Session::get('urlBefore403Redirect'))
                           ->with('message', Lang::get('csatar.csatar::lang.plugin.component.twoFactorAuthentication.twoFactorAuthSuccess'));
        } else {
            throw new \ValidationException(['code' => Lang::get('csatar.csatar::lang.plugin.component.twoFactorAuthentication.twoFactorAuthFailed')]);
        }
    }

    private function prepareVariables() {
        $scout = Scout::find(Auth::user()->scout->id);
        $this->google2FA = new GoogleTwoFactorAuthentication();

        $this->activated2FA = !empty($scout->google_two_fa_secret_key);
        if (!$this->activated2FA) {
            $scout->google_two_fa_secret_key = $this->google2FA->generateSecretKey();
            $scout->ignoreValidation = true;
            $scout->forceSave();
        }

        $this->userSecretKey = $scout->google_two_fa_secret_key;
        $this->qrCodeData = $this->google2FA->getQRCodeData('RMCSSZ', $scout->getFullName(), $this->userSecretKey, 300);
        $this->is2FAuthenticated = $this->is2FAuthenticated();
    }

    private function is2FAuthenticated()
    {
        return Session::get('scout.twoFA', false);
    }
}
