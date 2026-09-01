<?php

namespace Database\Factories;

use App\Enums\BillingPeriod;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plan = Plan::query()->where('code', 'basic')->first();

        return [
            'user_id' => User::factory(),
            'plan_id' => $plan?->id,
            'plan' => $plan?->code?->value ?? 'basic',
            'price' => $plan?->monthly_price ?? 24.90,
            'billing_period' => BillingPeriod::MONTHLY->value,
            'status' => SubscriptionStatus::TRIAL->value,
            'trial_starts_at' => now(),
            'trial_ends_at' => now()->addDays($plan?->trial_days ?? 14),
        ];
    }
}
