<?php

namespace Database\Seeders;

use App\Enums\PlanCode;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $monthlyMedium = 29.90;
        $monthlyPlus = 49.90;
        $monthlyPremium = 99.90;

        $annualMedium = round(($monthlyMedium * 12) * 0.9, 2);
        $annualPlus = round(($monthlyPlus * 12) * 0.9, 2);
        $annualPremium = round(($monthlyPremium * 12) * 0.9, 2);

        $plans = [
            [
                'code' => PlanCode::BASIC,
                'name' => 'Plano Básico',
                'monthly_price' => 24.90,
                'annual_price_total' => 238.80,
                'annual_monthly_equivalent' => 19.90,
                'products_limit' => 30,
                'product_images_limit' => 3,
                'gallery_images_limit' => 30,
                'banners_limit' => 3,
                'trial_days' => 14,
            ],
            [
                'code' => PlanCode::MEDIUM,
                'name' => 'Plano Médio',
                'monthly_price' => $monthlyMedium,
                'annual_price_total' => $annualMedium,
                'annual_monthly_equivalent' => round($annualMedium / 12, 2),
                'products_limit' => 50,
                'product_images_limit' => 3,
                'gallery_images_limit' => 50,
                'banners_limit' => 6,
                'trial_days' => 14,
            ],
            [
                'code' => PlanCode::PLUS,
                'name' => 'Plano Plus',
                'monthly_price' => $monthlyPlus,
                'annual_price_total' => $annualPlus,
                'annual_monthly_equivalent' => round($annualPlus / 12, 2),
                'products_limit' => 80,
                'product_images_limit' => 5,
                'gallery_images_limit' => 80,
                'banners_limit' => 10,
                'trial_days' => 14,
            ],
            [
                'code' => PlanCode::PREMIUM,
                'name' => 'Plano Premium',
                'monthly_price' => $monthlyPremium,
                'annual_price_total' => $annualPremium,
                'annual_monthly_equivalent' => round($annualPremium / 12, 2),
                'products_limit' => 300,
                'product_images_limit' => null,
                'gallery_images_limit' => 100,
                'banners_limit' => 20,
                'trial_days' => 14,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['code' => $plan['code']->value],
                array_merge($plan, [
                    'code' => $plan['code']->value,
                    'gateway_provider' => null,
                    'gateway_plan_ref_monthly' => null,
                    'gateway_plan_ref_annual' => null,
                    'gateway_metadata' => null,
                    'is_active' => true,
                ])
            );
        }
    }
}
