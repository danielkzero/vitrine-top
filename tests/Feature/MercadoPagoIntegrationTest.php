<?php

use App\Contracts\Payments\PaymentGateway;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payments\MercadoPagoGateway;
use Illuminate\Support\Facades\DB;

test('administrator can securely update mercado pago credentials', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->put(route('admin.payment-settings.update'), [
        'access_token' => 'TEST-123456789012345678901234567890',
        'public_key' => 'TEST-public-key-1234567890',
        'webhook_secret' => 'webhook-secret-1234567890',
        'sandbox' => true,
        'pix_enabled' => true,
        'credit_card_enabled' => true,
        'max_installments' => 12,
    ])->assertRedirect();

    expect(PlatformSetting::valueFor('mercado_pago_access_token'))->toBe('TEST-123456789012345678901234567890')
        ->and(DB::table('platform_settings')->where('key', 'mercado_pago_access_token')->value('value'))
        ->not->toContain('TEST-1234567890');
});

test('pix checkout stores pending payment and exposes qr code', function () {
    $user = User::factory()->create();
    $plan = Plan::create([
        'code' => 'basic', 'name' => 'Plano Básico', 'monthly_price' => 24.90,
        'annual_price_total' => 238.80, 'annual_monthly_equivalent' => 19.90,
        'products_limit' => 30, 'product_images_limit' => 3, 'gallery_images_limit' => 30,
        'banners_limit' => 3, 'trial_days' => 14, 'is_active' => true,
    ]);
    Subscription::create([
        'user_id' => $user->id, 'plan_id' => $plan->id, 'plan' => 'basic', 'price' => 24.90,
        'billing_period' => 'monthly', 'status' => 'expired',
    ]);
    PlatformSetting::setValue('mercado_pago_access_token', 'TEST-configured-token-1234567890', true);

    app()->instance(PaymentGateway::class, new class implements PaymentGateway
    {
        public function provider(): string
        {
            return 'mercado_pago';
        }

        public function createOrGetCustomer(User $user): string
        {
            return 'customer-'.$user->id;
        }

        public function createSubscription(Subscription $subscription, Plan $plan, string $paymentMethod, array $paymentData = []): array
        {
            return ['id' => '987654', 'status' => 'pending', 'status_detail' => 'pending_waiting_payment', 'qr_code' => '000201PIX-COPIA-E-COLA', 'qr_code_base64' => 'aW1hZ2Vt', 'expires_at' => now()->addMinutes(30)->toISOString()];
        }

        public function cancelSubscription(Subscription $subscription): void {}
    });

    $this->actingAs($user)->from(route('painel.billing.index'))->post(route('painel.billing.pay'), ['method' => 'pix', 'checkout_attempt_id' => fake()->uuid()])->assertRedirect(route('painel.billing.index'));

    $payment = Payment::where('transaction_id', '987654')->firstOrFail();
    expect($payment->status)->toBe('pending')
        ->and($payment->details['qr_code'])->toBe('000201PIX-COPIA-E-COLA')
        ->and($user->subscription->fresh()->status->value)->toBe('expired');
});

test('signed mercado pago webhook confirms pix and activates subscription', function () {
    $user = User::factory()->create();
    $subscription = Subscription::create(['user_id' => $user->id, 'plan' => 'basic', 'price' => 24.90, 'billing_period' => 'monthly', 'status' => 'expired']);
    Payment::create(['user_id' => $user->id, 'subscription_id' => $subscription->id, 'transaction_id' => '123456', 'amount' => 24.90, 'currency' => 'BRL', 'method' => 'pix', 'status' => 'pending']);
    PlatformSetting::setValue('mercado_pago_webhook_secret', 'secret-for-webhook-test', true);

    $gateway = Mockery::mock(MercadoPagoGateway::class);
    $gateway->shouldReceive('getPayment')->once()->with(123456)->andReturn((object) ['status' => 'approved', 'status_detail' => 'accredited']);
    app()->instance(MercadoPagoGateway::class, $gateway);

    $timestamp = (string) ((int) (microtime(true) * 1000));
    $requestId = 'request-test-123';
    $signature = hash_hmac('sha256', "id:123456;request-id:{$requestId};ts:{$timestamp};", 'secret-for-webhook-test');

    $this->withHeaders(['x-request-id' => $requestId, 'x-signature' => "ts={$timestamp},v1={$signature}"])
        ->postJson(route('payments.mercado-pago.webhook').'?data.id=123456', ['type' => 'payment', 'data' => ['id' => '123456']])
        ->assertOk();

    expect(Payment::where('transaction_id', '123456')->value('status'))->toBe('paid')
        ->and($subscription->fresh()->status->value)->toBe('active');
});

test('monthly credit card payment cannot be split into installments', function () {
    $user = User::factory()->create();
    $plan = Plan::create([
        'code' => 'basic', 'name' => 'Plano Básico', 'monthly_price' => 24.90,
        'annual_price_total' => 238.80, 'annual_monthly_equivalent' => 19.90,
        'products_limit' => 30, 'product_images_limit' => 3, 'gallery_images_limit' => 30,
        'banners_limit' => 3, 'trial_days' => 14, 'is_active' => true,
    ]);
    Subscription::create([
        'user_id' => $user->id, 'plan_id' => $plan->id, 'plan' => 'basic', 'price' => 24.90,
        'billing_period' => 'monthly', 'status' => 'expired',
    ]);

    $this->actingAs($user)->post(route('painel.billing.pay'), [
        'method' => 'credit_card',
        'token' => 'temporary-card-token',
        'payment_method_id' => 'master',
        'installments' => 2,
        'payer_email' => $user->email,
        'identification_type' => 'CPF',
        'identification_number' => '19119119100',
        'checkout_attempt_id' => fake()->uuid(),
    ])->assertSessionHasErrors('installments');

    expect(Payment::where('user_id', $user->id)->doesntExist())->toBeTrue();
});
