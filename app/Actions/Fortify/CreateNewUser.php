<?php

namespace App\Actions\Fortify;

use App\Enums\BillingPeriod;
use App\Enums\PlanCode;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(private readonly PlanLimitService $planLimitService) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $plan = Plan::firstOrCreate(
                ['code' => PlanCode::BASIC->value],
                [
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
                ]
            );

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'plan' => $plan->code->value.'-trial',
                'is_active' => true,
            ]);

            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan' => $plan->code->value,
                'price' => $plan->monthly_price,
                'billing_period' => BillingPeriod::MONTHLY->value,
                'status' => SubscriptionStatus::TRIAL->value,
                'trial_starts_at' => now(),
                'trial_ends_at' => $this->planLimitService->trialEndsAt($plan),
            ]);

            return $user;
        });
    }
}
