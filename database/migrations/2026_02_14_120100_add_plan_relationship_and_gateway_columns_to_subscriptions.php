<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_id')
                ->nullable()
                ->after('user_id')
                ->constrained('plans')
                ->nullOnDelete();

            $table->timestamp('trial_starts_at')->nullable()->after('billing_period');
            $table->timestamp('current_period_starts_at')->nullable()->after('trial_ends_at');
            $table->timestamp('current_period_ends_at')->nullable()->after('next_billing_at');
            $table->string('gateway_provider')->nullable()->after('payment_method');
            $table->string('gateway_customer_id')->nullable()->after('gateway_subscription_id');
            $table->json('gateway_metadata')->nullable()->after('gateway_customer_id');
        });

        if (Schema::hasTable('plans')) {
            $plansByCode = DB::table('plans')->pluck('id', 'code');
            if ($plansByCode->isNotEmpty()) {
                DB::table('subscriptions')
                    ->whereNull('plan_id')
                    ->orderBy('id')
                    ->chunkById(200, function ($subscriptions) use ($plansByCode) {
                        foreach ($subscriptions as $subscription) {
                            $legacy = (string) ($subscription->plan ?? '');
                            $mappedCode = match ($legacy) {
                                'basic', 'medium', 'plus', 'premium' => $legacy,
                                'pro', 'pro-monthly', 'pro-annual' => 'basic',
                                default => 'basic',
                            };

                            $planId = $plansByCode->get($mappedCode) ?? $plansByCode->get('basic');
                            if ($planId) {
                                DB::table('subscriptions')
                                    ->where('id', $subscription->id)
                                    ->update(['plan_id' => $planId]);
                            }
                        }
                    });
            }
        }
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn([
                'trial_starts_at',
                'current_period_starts_at',
                'current_period_ends_at',
                'gateway_provider',
                'gateway_customer_id',
                'gateway_metadata',
            ]);
        });
    }
};

