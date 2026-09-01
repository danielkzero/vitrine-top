<?php

namespace App\Http\Controllers\Dashboard;

use App\Contracts\Payments\PaymentGateway;
use App\Enums\PaymentGatewayProvider;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\AccountStatusService;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function __construct(
        private readonly AccountStatusService $accountStatusService,
        private readonly PlanLimitService $planLimitService,
        private readonly PaymentGateway $paymentGateway,
    ) {
    }

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
            ? (float) $plan->annual_monthly_equivalent
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
            'method' => ['required', 'in:pix,credit_card,boleto'],
        ]);

        $user = $request->user();
        $status = $this->accountStatusService->sync($user);
        $subscription = $status['subscription']->loadMissing('planModel');
        $plan = $this->planLimitService->getPlanForUser($user);

        if (!$subscription->gateway_provider) {
            $subscription->update([
                'gateway_provider' => PaymentGatewayProvider::MERCADO_PAGO->value,
                'gateway_customer_id' => $this->paymentGateway->createOrGetCustomer($user),
            ]);
        }

        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => $subscription->price,
            'currency' => 'BRL',
            'method' => $data['method'],
            'status' => 'paid',
            'details' => [
                'provider' => $this->paymentGateway->provider(),
                'plan_code' => $this->resolvePlanCode($plan),
                'billing_period' => $subscription->billing_period->value,
            ],
            'paid_at' => now(),
        ]);

        $payment->markAsPaid();
        $this->accountStatusService->sync($user->fresh());

        return redirect()->route('painel.index')->with('success', 'Pagamento confirmado com sucesso.');
    }

    public function destroyAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('password'), $user->password)) {
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
        $isBlocked = $forceBlockedView || !$status['allowed'];
        $now = now();

        $payments = Payment::where('user_id', $user->id)
            ->latest()
            ->take(20)
            ->get(['id', 'amount', 'currency', 'method', 'status', 'created_at', 'paid_at'])
            ->map(fn (Payment $payment) => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'method' => $payment->method,
                'status' => $payment->status,
                'created_at' => optional($payment->created_at)?->toDateTimeString(),
                'paid_at' => optional($payment->paid_at)?->toDateTimeString(),
            ]);

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
                'supports_pix' => true,
                'supports_credit_card' => true,
            ],
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
}
