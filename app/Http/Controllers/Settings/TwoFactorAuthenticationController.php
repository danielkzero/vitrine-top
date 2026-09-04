<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class TwoFactorAuthenticationController extends Controller
{
    public function show(TwoFactorAuthenticationRequest $request): Response|RedirectResponse
    {
        if ($this->requiresPasswordConfirmation($request)) {
            return redirect()->guest(route('password.confirm'));
        }

        $request->ensureStateIsValid();

        return Inertia::render('settings/TwoFactor', [
            'twoFactorEnabled' => $request->user()->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => Features::optionEnabled(
                Features::twoFactorAuthentication(),
                'confirm'
            ),
        ]);
    }

    private function requiresPasswordConfirmation(TwoFactorAuthenticationRequest $request): bool
    {
        if (! Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            return false;
        }

        $confirmedAt = (int) $request->session()->get('auth.password_confirmed_at', 0);

        return (time() - $confirmedAt) > (int) config('auth.password_timeout', 10800);
    }
}
