<?php

namespace App\Services;

use App\Enums\BillingPeriod;
use App\Enums\PlanCode;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PlanLimitService
{
    public function getPlanForUser(User $user): Plan
    {
        $subscription = $user->subscription;
        if (! $subscription) {
            return $this->getDefaultPlan();
        }

        if ($subscription->relationLoaded('planModel') && $subscription->planModel) {
            return $this->applyCustomLimits($subscription->planModel, $subscription);
        }

        if ($subscription->plan_id) {
            $plan = Plan::find($subscription->plan_id);
            if ($plan) {
                $this->syncSubscriptionPlanCode($subscription, $plan);

                return $this->applyCustomLimits($plan, $subscription);
            }
        }

        $legacyCode = $this->mapLegacyPlanToCode((string) ($subscription->plan ?? ''));
        $plan = Plan::where('code', $legacyCode)->first();

        if ($plan) {
            $this->syncSubscriptionPlan($subscription, $plan);

            return $this->applyCustomLimits($plan, $subscription);
        }

        return $plan ?? $this->getDefaultPlan();
    }

    private function applyCustomLimits(Plan $plan, Subscription $subscription): Plan
    {
        if (! $subscription->custom_plan_name) {
            return $plan;
        }

        $customPlan = $plan->replicate();
        $customPlan->forceFill([
            'name' => $subscription->custom_plan_name,
            'monthly_price' => $subscription->price,
            'annual_monthly_equivalent' => $subscription->price,
            'products_limit' => $subscription->custom_products_limit ?? $plan->products_limit,
            'product_images_limit' => $subscription->custom_product_images_limit,
            'gallery_images_limit' => $subscription->custom_gallery_images_limit ?? $plan->gallery_images_limit,
            'banners_limit' => $subscription->custom_banners_limit ?? $plan->banners_limit,
        ]);

        return $customPlan;
    }

    public function ensureCanAddProducts(User $user, int $currentCount, int $newCount): void
    {
        $plan = $this->getPlanForUser($user);
        if (($currentCount + $newCount) > $plan->products_limit) {
            throw ValidationException::withMessages([
                'produtos' => "Limite de produtos atingido. Máximo: {$plan->products_limit}.",
            ]);
        }
    }

    public function ensureProductImagesWithinLimit(User $user, int $imagesCount): void
    {
        $plan = $this->getPlanForUser($user);
        $limit = $plan->product_images_limit;

        if ($limit !== null && $imagesCount > $limit) {
            throw ValidationException::withMessages([
                'produtos' => "Cada produto pode ter no máximo {$limit} imagens.",
            ]);
        }
    }

    public function ensureGalleryWithinLimit(User $user, int $galleryImagesCount): void
    {
        $plan = $this->getPlanForUser($user);
        if ($galleryImagesCount > $plan->gallery_images_limit) {
            throw ValidationException::withMessages([
                'page' => "Limite de fotos na galeria atingido. Máximo: {$plan->gallery_images_limit}.",
            ]);
        }
    }

    public function ensureCanAddBanner(User $user, int $currentCount): void
    {
        $plan = $this->getPlanForUser($user);
        if ($currentCount >= $plan->banners_limit) {
            throw ValidationException::withMessages([
                'image' => "Limite de banners atingido. Você pode ter no máximo {$plan->banners_limit} banners.",
            ]);
        }
    }

    public function trialEndsAt(Plan $plan): \DateTimeInterface
    {
        return now()->addDays($plan->trial_days);
    }

    public function priceFor(Subscription $subscription, Plan $plan): float
    {
        $isAnnual = $subscription->billing_period instanceof BillingPeriod
            ? $subscription->billing_period === BillingPeriod::ANNUAL
            : $subscription->billing_period === BillingPeriod::ANNUAL->value;

        return $isAnnual
            ? (float) $plan->annual_monthly_equivalent
            : (float) $plan->monthly_price;
    }

    private function getDefaultPlan(): Plan
    {
        $plan = Plan::where('code', PlanCode::BASIC->value)->first();
        if ($plan) {
            return $plan;
        }

        return new Plan([
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

    private function mapLegacyPlanToCode(string $legacyPlan): string
    {
        return match ($legacyPlan) {
            'basic', 'medium', 'plus', 'premium' => $legacyPlan,
            'pro', 'pro-monthly', 'pro-annual' => PlanCode::BASIC->value,
            default => PlanCode::BASIC->value,
        };
    }

    private function syncSubscriptionPlan(Subscription $subscription, Plan $plan): void
    {
        $updates = [];

        if ((int) $subscription->plan_id !== (int) $plan->id) {
            $updates['plan_id'] = $plan->id;
        }

        $code = $this->extractPlanCode($plan);
        if ((string) ($subscription->plan ?? '') !== $code) {
            $updates['plan'] = $code;
        }

        if (! empty($updates)) {
            $subscription->forceFill($updates)->save();
        }
    }

    private function syncSubscriptionPlanCode(Subscription $subscription, Plan $plan): void
    {
        $code = $this->extractPlanCode($plan);
        if ((string) ($subscription->plan ?? '') !== $code) {
            $subscription->forceFill(['plan' => $code])->save();
        }
    }

    private function extractPlanCode(Plan $plan): string
    {
        $code = $plan->code;

        if (is_object($code) && property_exists($code, 'value')) {
            return (string) $code->value;
        }

        return (string) $code;
    }
}
