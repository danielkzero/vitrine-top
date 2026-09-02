<?php

namespace App\Http\Controllers\Dashboard;

use App\Contracts\Payments\PaymentGateway;
use App\Enums\PaymentGatewayProvider;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Services\AccountStatusService;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use MercadoPago\Exceptions\MPApiException;
use Throwable;

class BillingController extends Controller
{
    public function __construct(
        private readonly AccountStatusService $accountStatusService,
        private readonly PlanLimitService $planLimitService,
        private readonly PaymentGateway $paymentGateway,
    ) {}

    public function index(Request $request)
    {
        return $this->renderBillingPage($request, false);
    }

    public function required(Request $request)
    {
        return $this->renderBillingPage($request, true);
    }

    public function choosePlan(Request $request)
    {
        $data = $request->validate([
            'plan_code' => ['nullable', 'in:basic,medium,plus,premium'],
            'billing_period' => ['required', 'in:monthly,annual'],
        ]);

        $user = $request->user();
        $status = $this->accountStatusService->sync($user);
        $subscription = $status['subscription'];
        $currentPlan = $this->planLimitService->getPlanForUser($user);
        $planCode = $data['plan_code'] ?? $this->resolvePlanCode($currentPlan);
        $plan = Plan::where('code', $planCode)->where('is_active', true)->firstOrFail();

        $price = $data['billing_period'] === 'annual'
            ? (float) $plan->annual_price_total
            : (float) $plan->monthly_price;

        $subscription->update([
            'plan_id' => $plan->id,
            'plan' => $plan->code->value,
            'billing_period' => $data['billing_period'],
            'price' => $price,
        ]);

        return back()->with('success', 'Plano atualizado. Agora finalize o pagamento.');
    }

    public function pay(Request $request)
    {
        $data = $request->validate([
            'method' => ['required', 'in:pix,credit_card'],
            'token' => ['required_if:method,credit_card', 'nullable', 'string', 'max:200'],
            'payment_method_id' => ['required_if:method,credit_card', 'nullable', 'string', 'max:50'],
            'issuer_id' => ['nullable', 'string', 'max:50'],
            'installments' => ['required_if:method,credit_card', 'nullable', 'integer', 'min:1', 'max:'.(int) PlatformSetting::valueFor('mercado_pago_max_installments', '12')],
            'payer_email' => ['required_if:method,credit_card', 'nullable', 'email:rfc', 'max:255'],
            'identification_type' => ['required_if:method,credit_card', 'nullable', 'string', 'max:20'],
            'identification_number' => ['required_if:method,credit_card', 'nullable', 'string', 'max:30'],
            'checkout_attempt_id' => ['required', 'uuid'],
        ]);

        $user = $request->user();
        $status = $this->accountStatusService->sync($user);
        $subscription = $status['subscription']->loadMissing('planModel');
        $plan = $this->planLimitService->getPlanForUser($user);

        if ($data['method'] === 'credit_card'
            && $subscription->billing_period->value === 'monthly'
            && (int) $data['installments'] !== 1) {
            throw ValidationException::withMessages([
                'installments' => 'O plano mensal deve ser pago à vista. Parcelamento disponível somente no plano anual.',
            ]);
        }

        if (! $subscription->gateway_provider) {
            $subscription->update([
                'gateway_provider' => PaymentGatewayProvider::MERCADO_PAGO->value,
                'gateway_customer_id' => $this->paymentGateway->createOrGetCustomer($user),
            ]);
        }

        try {
            $gatewayPayment = $this->paymentGateway->createSubscription($subscription, $plan, $data['method'], $data);
        } catch (MPApiException $exception) {
            $response = $exception->getApiResponse()->getContent();
            report($exception);
            logger()->error('Mercado Pago recusou a criação do pagamento.', [
                'status' => $exception->getStatusCode(),
                'response' => $response,
            ]);

            throw ValidationException::withMessages([
                'method' => $this->mercadoPagoErrorMessage($response),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['method' => $exception->getMessage()]);
        }

        $localStatus = match ($gatewayPayment['status']) {
            'approved' => 'paid',
            'rejected', 'cancelled' => 'failed',
            'refunded', 'charged_back' => 'refunded',
            default => 'pending',
        };

        $payment = Payment::updateOrCreate(['transaction_id' => $gatewayPayment['id']], [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => $gatewayPayment['total_paid_amount'] ?? $subscription->price,
            'currency' => 'BRL',
            'method' => $data['method'],
            'status' => $localStatus,
            'details' => [
                'provider' => $this->paymentGateway->provider(),
                'plan_code' => $this->resolvePlanCode($plan),
                'billing_period' => $subscription->billing_period->value,
                'status_detail' => $gatewayPayment['status_detail'],
                'qr_code' => $gatewayPayment['qr_code'],
                'qr_code_base64' => $gatewayPayment['qr_code_base64'],
                'expires_at' => $gatewayPayment['expires_at'],
                'payment_method_id' => $gatewayPayment['payment_method_id'] ?? null,
                'installments' => $gatewayPayment['installments'] ?? 1,
                'installment_amount' => $gatewayPayment['installment_amount'] ?? $subscription->price,
                'total_paid_amount' => $gatewayPayment['total_paid_amount'] ?? $subscription->price,
                'card_last_four_digits' => $gatewayPayment['card_last_four_digits'] ?? null,
            ],
            'paid_at' => $gatewayPayment['status'] === 'approved' ? now() : null,
        ]);

        if ($localStatus === 'paid') {
            $payment->markAsPaid();
        }

        $message = $data['method'] === 'pix'
            ? 'PIX gerado. Use o QR Code ou o código copia e cola para pagar.'
            : ($gatewayPayment['status'] === 'approved' ? 'Pagamento aprovado.' : 'Pagamento enviado para análise.');

        return back()->with('success', $message);
    }

    public function destroyAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['password' => 'Senha incorreta.']);
        }

        auth()->logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Conta removida com sucesso.');
    }

    private function renderBillingPage(Request $request, bool $forceBlockedView)
    {
        $user = $request->user();
        $status = $this->accountStatusService->sync($user);
        $subscription = $status['subscription']->loadMissing('planModel');
        $currentPlan = $this->planLimitService->getPlanForUser($user);
        $isBlocked = $forceBlockedView || ! $status['allowed'];
        $now = now();

        $payments = Payment::where('user_id', $user->id)
            ->latest()
            ->take(20)
            ->get(['id', 'amount', 'currency', 'method', 'status', 'details', 'created_at', 'paid_at'])
            ->map(fn (Payment $payment) => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'method' => $payment->method,
                'status' => $payment->status,
                'installments' => (int) ($payment->details['installments'] ?? 1),
                'installment_amount' => (float) ($payment->details['installment_amount'] ?? $payment->amount),
                'status_detail' => $payment->details['remote_status_detail'] ?? $payment->details['status_detail'] ?? null,
                'created_at' => optional($payment->created_at)?->toDateTimeString(),
                'paid_at' => optional($payment->paid_at)?->toDateTimeString(),
            ]);

        $pendingPix = Payment::where('user_id', $user->id)->where('method', 'pix')->where('status', 'pending')->latest()->first();

        $plans = Plan::where('is_active', true)
            ->orderBy('monthly_price')
            ->get([
                'id',
                'code',
                'name',
                'monthly_price',
                'annual_price_total',
                'annual_monthly_equivalent',
                'products_limit',
                'product_images_limit',
                'gallery_images_limit',
                'banners_limit',
                'trial_days',
            ])
            ->map(fn (Plan $plan) => [
                'id' => $plan->id,
                'code' => $plan->code->value,
                'name' => $plan->name,
                'monthly' => (float) $plan->monthly_price,
                'annual_total' => (float) $plan->annual_price_total,
                'annual_monthly_equivalent' => (float) $plan->annual_monthly_equivalent,
                'limits' => [
                    'products' => $plan->products_limit,
                    'product_images' => $plan->product_images_limit,
                    'gallery_images' => $plan->gallery_images_limit,
                    'banners' => $plan->banners_limit,
                ],
                'trial_days' => $plan->trial_days,
            ]);

        return Inertia::render('Billing/Required', [
            'is_blocked' => $isBlocked,
            'subscription' => [
                'id' => $subscription->id,
                'status' => $subscription->status->value,
                'billing_period' => $subscription->billing_period->value,
                'trial_ends_at' => optional($subscription->trial_ends_at)?->toDateString(),
                'trial_days_left' => $subscription->trial_ends_at ? $now->diffInDays($subscription->trial_ends_at, false) : null,
                'next_billing_at' => optional($subscription->next_billing_at)?->toDateString(),
                'price' => (float) $subscription->price,
                'plan_code' => $this->resolvePlanCode($currentPlan),
                'plan_name' => $currentPlan->name,
            ],
            'current_plan_limits' => [
                'products' => $currentPlan->products_limit,
                'product_images' => $currentPlan->product_images_limit,
                'gallery_images' => $currentPlan->gallery_images_limit,
                'banners' => $currentPlan->banners_limit,
            ],
            'plan_catalog' => $plans,
            'payment_gateway' => [
                'provider' => $this->paymentGateway->provider(),
                'supports_pix' => PlatformSetting::valueFor('mercado_pago_pix_enabled', '1') === '1',
                'supports_credit_card' => PlatformSetting::valueFor('mercado_pago_credit_card_enabled', '1') === '1'
                    && filled(PlatformSetting::valueFor('mercado_pago_public_key', config('services.mercado_pago.public_key'))),
                'supports_boleto' => false,
                'configured' => filled(PlatformSetting::valueFor('mercado_pago_access_token', config('services.mercado_pago.access_token'))),
                'public_key' => PlatformSetting::valueFor('mercado_pago_public_key', config('services.mercado_pago.public_key')),
                'max_installments' => (int) PlatformSetting::valueFor('mercado_pago_max_installments', '12'),
            ],
            'pix_payment' => $pendingPix ? [
                'id' => $pendingPix->id,
                'qr_code' => $pendingPix->details['qr_code'] ?? null,
                'qr_code_base64' => $pendingPix->details['qr_code_base64'] ?? null,
                'expires_at' => $pendingPix->details['expires_at'] ?? null,
            ] : null,
            'payments' => $payments,
        ]);
    }

    private function resolvePlanCode(mixed $plan): string
    {
        if ($plan instanceof Plan) {
            $code = $plan->code;

            if (is_object($code) && property_exists($code, 'value')) {
                return (string) $code->value;
            }

            return (string) $code;
        }

        if (is_object($plan) && property_exists($plan, 'code')) {
            $code = $plan->code;
            if (is_object($code) && property_exists($code, 'value')) {
                return (string) $code->value;
            }

            return (string) $code;
        }

        return is_string($plan) ? $plan : 'basic';
    }

    private function mercadoPagoErrorMessage(array $response): string
    {
        $cause = $response['cause'][0]['description'] ?? $response['cause'][0]['code'] ?? null;
        $message = $cause ?: ($response['message'] ?? $response['error'] ?? null);

        return $message
            ? 'Mercado Pago: '.$message
            : 'O Mercado Pago não aceitou os dados do pagamento. Confira as credenciais e tente novamente.';
    }
}
