<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminDemoClientsSeeder extends Seeder
{
    public const CLIENTS = [
        ['Ana Martins', 'Ateliê Aurora', 'aurora', 'active', 'medium', 39.90, 24, null],
        ['Bruno Costa', 'Casa do Café', 'casa-cafe', 'trial', 'basic', 24.90, 5, null],
        ['Camila Rocha', 'Bella Acessórios', 'bella-acessorios', 'trial', 'plus', 59.90, 2, null],
        ['Diego Almeida', 'Tech do Dia', 'tech-do-dia', 'past_due', 'premium', 99.90, -3, null],
        ['Elisa Nunes', 'Jardim da Elisa', 'jardim-elisa', 'expired', 'basic', 24.90, -12, null],
        ['Felipe Santos', 'Empório Natural', 'emporio-natural', 'active', 'plus', 59.90, 18, null],
        ['Gabriela Melo', 'Mundo Kids', 'mundo-kids', 'cancelled', 'medium', 39.90, -20, null],
        ['Henrique Lima', 'Parceiro Especial', 'parceiro-especial', 'active', 'premium', 149.90, 45, 'Plano Parceiro'],
    ];

    public function run(): void
    {
        foreach (self::CLIENTS as $index => [$name, $store, $slug, $status, $planCode, $price, $daysLeft, $customName]) {
            $plan = Plan::where('code', $planCode)->firstOrFail();
            $user = User::updateOrCreate(
                ['email' => "cliente.demo{$index}@vitrine.test"],
                [
                    'name' => $name,
                    'business_name' => $store,
                    'slug' => $slug,
                    'description' => 'Conta fictícia criada para demonstração do painel administrativo.',
                    'email_verified_at' => now(),
                    'password' => 'password',
                    'whatsapp' => '+552499900'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                    'is_active' => ! in_array($status, ['expired', 'cancelled'], true),
                    'is_admin' => false,
                    'plan' => $planCode,
                ],
            );

            $isTrial = $status === 'trial';
            Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id' => $plan->id,
                    'plan' => $planCode,
                    'custom_plan_name' => $customName,
                    'custom_products_limit' => $customName ? 250 : null,
                    'custom_product_images_limit' => $customName ? 12 : null,
                    'custom_gallery_images_limit' => $customName ? 150 : null,
                    'custom_banners_limit' => $customName ? 20 : null,
                    'price' => $price,
                    'billing_period' => $index % 3 === 0 ? 'annual' : 'monthly',
                    'status' => $status,
                    'status_changed_at' => now()->subDays(in_array($status, ['expired', 'cancelled', 'past_due'], true) ? abs($daysLeft) : 3 + $index),
                    'trial_starts_at' => $isTrial ? now()->subDays(14 - max(0, $daysLeft)) : now()->subMonths(4),
                    'trial_ends_at' => $isTrial ? now()->addDays($daysLeft) : now()->subMonths(3),
                    'current_period_starts_at' => $status === 'active' ? now()->subDays(12) : null,
                    'current_period_ends_at' => $status === 'active' ? now()->addDays($daysLeft) : null,
                    'next_billing_at' => $status === 'active' ? now()->addDays($daysLeft) : ($status === 'past_due' ? now()->addDays($daysLeft) : null),
                    'payment_method' => $index % 2 === 0 ? 'pix' : 'credit_card',
                ],
            );
        }

        $this->command?->info('Oito clientes fictícios foram criados ou atualizados.');
    }
}
