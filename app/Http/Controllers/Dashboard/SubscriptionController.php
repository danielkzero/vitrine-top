<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\BillingPeriod;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Dashboard\BaseController;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends BaseController
{
    public function __construct(private readonly PlanLimitService $planLimitService)
    {
        parent::__construct();
    }

    public function plans()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('monthly_price')
            ->get()
            ->map(fn (Plan $plan) => [
                'id' => $plan->id,
                'code' => $plan->code->value,
                'name' => $plan->name,
                'monthly_price' => (float) $plan->monthly_price,
                'annual_price_total' => (float) $plan->annual_price_total,
                'annual_monthly_equivalent' => (float) $plan->annual_monthly_equivalent,
                'limits' => [
                    'products' => $plan->products_limit,
                    'product_images' => $plan->product_images_limit,
                    'gallery_images' => $plan->gallery_images_limit,
                    'banners' => $plan->banners_limit,
                ],
                'trial_days' => $plan->trial_days,
            ]);

        return Inertia::render('Dashboard/Subscriptions/Plans', [
            'plans' => $plans,
            'trial_days' => 14,
        ]);
    }

    public function index(Request $request)
    {
        $subscriptions = Subscription::where('user_id', $this->user->id)
            ->with('planModel')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return $this->json(['subscriptions' => $subscriptions]);
        }

        return Inertia::render('Dashboard/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function show(Subscription $subscription)
    {
        $this->authorizeOwnership($subscription);

        return $this->json(['subscription' => $subscription->load('planModel')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plan_code' => ['required', 'in:basic,medium,plus,premium'],
            'billing_period' => ['required', 'in:monthly,annual'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);

        $plan = Plan::where('code', $data['plan_code'])->where('is_active', true)->firstOrFail();
        $price = $data['billing_period'] === BillingPeriod::ANNUAL->value
            ? (float) $plan->annual_monthly_equivalent
            : (float) $plan->monthly_price;

        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'plan' => $plan->code->value,
            'price' => $price,
            'billing_period' => $data['billing_period'],
            'payment_method' => $data['payment_method'] ?? null,
            'status' => SubscriptionStatus::TRIAL->value,
            'trial_starts_at' => now(),
            'trial_ends_at' => $this->planLimitService->trialEndsAt($plan),
        ]);

        return $this->json([
            'message' => 'Assinatura criada com sucesso.',
            'subscription' => $subscription->load('planModel'),
        ], 201);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $this->authorizeOwnership($subscription);

        $data = $request->validate([
            'plan_code' => ['required', 'in:basic,medium,plus,premium'],
            'billing_period' => ['required', 'in:monthly,annual'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:trial,active,past_due,expired,cancelled'],
        ]);

        $plan = Plan::where('code', $data['plan_code'])->where('is_active', true)->firstOrFail();
        $price = $data['billing_period'] === BillingPeriod::ANNUAL->value
            ? (float) $plan->annual_monthly_equivalent
            : (float) $plan->monthly_price;

        $subscription->update([
            'plan_id' => $plan->id,
            'plan' => $plan->code->value,
            'price' => $price,
            'billing_period' => $data['billing_period'],
            'payment_method' => $data['payment_method'] ?? null,
            'status' => $data['status'],
        ]);

        return $this->json([
            'message' => 'Assinatura atualizada com sucesso.',
            'subscription' => $subscription->fresh()->load('planModel'),
        ]);
    }

    public function destroy(Subscription $subscription)
    {
        $this->authorizeOwnership($subscription);

        $subscription->delete();

        return $this->json(['message' => 'Assinatura removida com sucesso.']);
    }

    public function cancel(Subscription $subscription)
    {
        $this->authorizeOwnership($subscription);

        $subscription->cancel();

        return $this->json(['message' => 'Assinatura cancelada.']);
    }

    protected function authorizeOwnership(Subscription $subscription)
    {
        if ($subscription->user_id !== $this->user->id) {
            abort(403, 'Esta assinatura não pertence ao usuário autenticado.');
        }
    }
}
