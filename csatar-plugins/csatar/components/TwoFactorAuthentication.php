<?php
namespace Csatar\Csatar\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Classes\GoogleTwoFactorAuthentication;
use Csatar\Csatar\Models\Scout;
use Event;
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
            Event::fire('csatar.twoFA.authenticated', [Auth::user()]);
            return Redirect::to(Session::get('urlBefore403Redirect'))
                           ->with('message', Lang::get('csatar.csatar::lang.plugin.component.twoFactorAuthentication.twoFactorAuthSuccess'));
        } else {
            throw new \ValidationException(['code' => Lang::get('csatar.csatar::lang.plugin.component.twoFactorAuthentication.twoFactorAuthFailed')]);
        }
    }

    private function prepareVariables() {
        $scout           = Auth::user()->scout;
        $this->google2FA = new GoogleTwoFactorAuthentication();

        if (empty($scout->google_two_fa_secret_key) && !$scout->google_two_fa_is_activated) {
            $scout->google_two_fa_secret_key = $this->google2FA->generateSecretKey();
            $scout->ignoreValidation         = true;
            $scout->forceSave();
            $scout->refresh();
        }

        $this->userSecretKey = $scout->google_two_fa_secret_key;

        if (!$scout->google_two_fa_is_activated) {
            $this->qrCodeData = $this->google2FA->getQRCodeData('RMCSSZ', $scout->getFullName(), $this->userSecretKey, 300);
        }

        $this->is2FAuthenticated = $this->is2FAuthenticated();

        if (!$scout->google_two_fa_is_activated && $this->is2FAuthenticated) {
            $scout->google_two_fa_is_activated = true;
            $scout->forceSave();
            $scout->refresh();
        }

    }

    private function is2FAuthenticated()
    {
        return Session::get('scout.twoFA', false);
    }

}
