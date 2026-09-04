<?php

namespace App\Services;

use App\Enums\BillingPeriod;
use App\Enums\PlanCode;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class AccountStatusService
{
    public function __construct(private readonly PlanLimitService $planLimitService) {}

    public function sync(User $user): array
    {
        $subscription = $user->subscription;

        if (! $subscription) {
            $subscription = $this->createTrialSubscription($user);
            $user->setRelation('subscription', $subscription);
        }

        if ($subscription->status === SubscriptionStatus::TRIAL
            && $subscription->trial_ends_at
            && now()->greaterThan($subscription->trial_ends_at)) {
            $subscription->update(['status' => SubscriptionStatus::EXPIRED->value]);
            $subscription->refresh();
        }

        if ($subscription->status === SubscriptionStatus::ACTIVE
            && $subscription->next_billing_at
            && now()->greaterThan($subscription->next_billing_at)) {
            $subscription->update(['status' => SubscriptionStatus::PAST_DUE->value]);
            $subscription->refresh();
        }

        $isTrialActive = $subscription->status === SubscriptionStatus::TRIAL
            && $subscription->trial_ends_at
            && now()->lessThanOrEqualTo($subscription->trial_ends_at);

        $isSubscriptionActive = $subscription->status === SubscriptionStatus::ACTIVE
            && (! $subscription->next_billing_at || now()->lessThanOrEqualTo($subscription->next_billing_at));

        $isAllowed = $isTrialActive || $isSubscriptionActive;

        if ((bool) $user->is_active !== $isAllowed) {
            $user->update(['is_active' => $isAllowed]);
        }

        return [
            'allowed' => $isAllowed,
            'subscription' => $subscription->fresh(['planModel']),
        ];
    }

    private function createTrialSubscription(User $user): Subscription
    {
        $plan = Plan::where('code', PlanCode::BASIC->value)->first();
        if (! $plan) {
            $plan = Plan::create([
                'code' => PlanCode::BASIC->value,
                'name' => 'Plano Básico',
                'monthly_price' => 24.90,
                'annual_price_total' => 238.80,
                'annual_monthly_equivalent' => 19.90,
                'products_limit' => 30,
                'product_images_limit' => 3,
                'gallery_images_limit' => 30,
                'banners_limit' => 3,
                'trial_days' => 14,
                'is_active' => true,
            ]);
        }

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'plan' => $plan->code->value,
            'price' => $plan->monthly_price,
            'billing_period' => BillingPeriod::MONTHLY->value,
            'status' => SubscriptionStatus::TRIAL->value,
            'trial_starts_at' => now(),
            'trial_ends_at' => $this->planLimitService->trialEndsAt($plan),
        ]);
    }
}
