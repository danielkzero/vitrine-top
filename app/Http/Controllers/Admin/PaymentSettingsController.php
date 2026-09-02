<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'access_token' => ['nullable', 'string', 'min:20', 'max:500'],
            'public_key' => ['nullable', 'string', 'max:500'],
            'webhook_secret' => ['nullable', 'string', 'max:500'],
            'sandbox' => ['required', 'boolean'],
            'pix_enabled' => ['required', 'boolean'],
            'credit_card_enabled' => ['required', 'boolean'],
            'max_installments' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        foreach (['access_token', 'public_key', 'webhook_secret'] as $key) {
            if (! empty($data[$key])) {
                PlatformSetting::setValue("mercado_pago_{$key}", $data[$key], true);
            }
        }
        PlatformSetting::setValue('mercado_pago_sandbox', $data['sandbox'] ? '1' : '0');
        PlatformSetting::setValue('mercado_pago_pix_enabled', $data['pix_enabled'] ? '1' : '0');
        PlatformSetting::setValue('mercado_pago_credit_card_enabled', $data['credit_card_enabled'] ? '1' : '0');
        PlatformSetting::setValue('mercado_pago_max_installments', (string) $data['max_installments']);

        return back()->with('success', 'Configurações do Mercado Pago atualizadas.');
    }
}
