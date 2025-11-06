<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Illuminate\Support\Facades\Auth;

class TwoFactorAuthenticationController extends Controller
{
    protected TwoFactorAuthenticationProvider $provider;

    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Exibe a tela de gerenciamento da autenticação em duas etapas.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Settings/TwoFactor', [
            'enabled' => !is_null($user->two_factor_secret),
            'qrCode' => $this->getQrCode($user),
            'recoveryCodes' => $this->getRecoveryCodes($user),
        ]);
    }

    /**
     * Ativa o 2FA para o usuário autenticado.
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_secret) {
            return back()->with('error', 'A autenticação em duas etapas já está ativada.');
        }

        $secret = $this->provider->generateSecretKey();
        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode(collect(range(1, 8))->map(fn() => str()->random(10))->toArray())),
        ])->save();

        return back()->with('success', 'Autenticação em duas etapas ativada com sucesso!');
    }

    /**
     * Desativa o 2FA para o usuário autenticado.
     */
    public function disable(Request $request)
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return back()->with('success', 'Autenticação em duas etapas desativada.');
    }

    /**
     * Gera o QR Code (Google Authenticator) se o 2FA estiver ativo.
     */
    protected function getQrCode($user)
    {
        if (!$user->two_factor_secret) {
            return null;
        }

        $otpUrl = $this->provider->qrCodeUrl(
            config('app.name'),
            $user->email,
            decrypt($user->two_factor_secret)
        );

        return $otpUrl;
    }

    /**
     * Retorna os códigos de recuperação descriptografados.
     */
    protected function getRecoveryCodes($user)
    {
        if (!$user->two_factor_recovery_codes) {
            return [];
        }

        return json_decode(decrypt($user->two_factor_recovery_codes), true);
    }
}
