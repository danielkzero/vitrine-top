<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGateway;
use App\Enums\PaymentGatewayProvider;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\Subscription;
use App\Models\User;
use LogicException;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoGateway implements PaymentGateway
{
    public function provider(): string
    {
        return PaymentGatewayProvider::MERCADO_PAGO->value;
    }

    public function createOrGetCustomer(User $user): string
    {
        if (! empty($user->billing_customer_id)) {
            return (string) $user->billing_customer_id;
        }

        return 'vitrine-user-'.$user->id;
    }

    public function createSubscription(Subscription $subscription, Plan $plan, string $paymentMethod, array $paymentData = []): array
    {
        if (! in_array($paymentMethod, ['pix', 'credit_card'], true)) {
            throw new LogicException('Método de pagamento não suportado.');
        }

        $this->configure();
        $options = new RequestOptions;
        $idempotencyKey = 'subscription-'.$subscription->id.'-'.$paymentData['checkout_attempt_id'];
        $options->setCustomHeaders(["X-Idempotency-Key: {$idempotencyKey}"]);

        $payload = [
            'transaction_amount' => (float) $subscription->price,
            'description' => 'Assinatura '.$plan->name.' - Vitrine',
            'payment_method_id' => $paymentMethod === 'pix' ? 'pix' : $paymentData['payment_method_id'],
            'external_reference' => 'subscription:'.$subscription->id,
            'payer' => [
                'email' => $paymentData['payer_email'] ?? $subscription->user->email,
                'first_name' => $subscription->user->name,
            ],
        ];

        $notificationUrl = route('payments.mercado-pago.webhook');
        if ($this->isPublicHttpsUrl($notificationUrl)) {
            $payload['notification_url'] = $notificationUrl;
        }

        if ($paymentMethod === 'pix') {
            $payload['date_of_expiration'] = now()->addMinutes(30)->format('Y-m-d\TH:i:s.vP');
        } else {
            $payload += [
                'token' => $paymentData['token'],
                'installments' => $subscription->billing_period->value === 'monthly'
                    ? 1
                    : (int) $paymentData['installments'],
                'issuer_id' => $paymentData['issuer_id'] ?? null,
            ];
            $payload['payer']['identification'] = [
                'type' => $paymentData['identification_type'],
                'number' => preg_replace('/\D/', '', $paymentData['identification_number']),
            ];
        }

        $payment = (new PaymentClient)->create($payload, $options);

        $transactionData = $payment->point_of_interaction?->transaction_data;

        return [
            'id' => (string) $payment->id,
            'status' => (string) $payment->status,
            'status_detail' => $payment->status_detail,
            'qr_code' => $transactionData?->qr_code,
            'qr_code_base64' => $transactionData?->qr_code_base64,
            'expires_at' => $payment->date_of_expiration,
            'payment_method_id' => $payment->payment_method_id,
            'installments' => (int) ($payment->installments ?: 1),
            'transaction_amount' => (float) $payment->transaction_amount,
            'installment_amount' => (float) ($payment->transaction_details?->installment_amount ?: $payment->transaction_amount),
            'total_paid_amount' => (float) ($payment->transaction_details?->total_paid_amount ?: $payment->transaction_amount),
            'card_last_four_digits' => $payment->card?->last_four_digits,
        ];
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        throw new LogicException('Cancelamento no gateway ainda não implementado.');
    }

    public function getPayment(int $paymentId): object
    {
        $this->configure();

        return (new PaymentClient)->get($paymentId);
    }

    private function configure(): void
    {
        $accessToken = PlatformSetting::valueFor('mercado_pago_access_token', config('services.mercado_pago.access_token'));
        if (! $accessToken) {
            throw new LogicException('Mercado Pago não configurado. Informe o Access Token na área administrativa.');
        }

        MercadoPagoConfig::setAccessToken($accessToken);
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);
    }

    private function isPublicHttpsUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (parse_url($url, PHP_URL_SCHEME) !== 'https' || ! is_string($host)) {
            return false;
        }

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        return filter_var($host, FILTER_VALIDATE_IP) === false
            || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}
