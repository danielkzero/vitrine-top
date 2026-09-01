<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Console\Command;

class CheckSubscriptionStatus extends Command
{
    protected $signature = 'subscriptions:check-status';

    protected $description = 'Atualiza status de assinatura e ativa/desativa contas conforme pagamento';

    public function handle(): void
    {
        $today = now()->startOfDay();

        Subscription::where('status', SubscriptionStatus::TRIAL->value)
            ->whereDate('trial_ends_at', '<', $today)
            ->update(['status' => SubscriptionStatus::EXPIRED->value]);

        Subscription::where('status', SubscriptionStatus::ACTIVE->value)
            ->whereDate('next_billing_at', '<', $today)
            ->update(['status' => SubscriptionStatus::PAST_DUE->value]);

        Subscription::whereIn('status', [
            SubscriptionStatus::EXPIRED->value,
            SubscriptionStatus::PAST_DUE->value,
            SubscriptionStatus::CANCELLED->value,
        ])
            ->with('user')
            ->get()
            ->each(function (Subscription $subscription) {
                if ($subscription->user && $subscription->user->is_active) {
                    $subscription->user->update(['is_active' => false]);
                }
            });

        Subscription::whereIn('status', [
            SubscriptionStatus::TRIAL->value,
            SubscriptionStatus::ACTIVE->value,
        ])
            ->with('user')
            ->get()
            ->each(function (Subscription $subscription) use ($today) {
                if (!$subscription->user) {
                    return;
                }

                if ($subscription->status === SubscriptionStatus::TRIAL && $subscription->trial_ends_at && $subscription->trial_ends_at->lt($today)) {
                    return;
                }

                if ($subscription->status === SubscriptionStatus::ACTIVE && $subscription->next_billing_at && $subscription->next_billing_at->lt($today)) {
                    return;
                }

                if (!$subscription->user->is_active) {
                    $subscription->user->update(['is_active' => true]);
                }
            });

        $this->info('Status de assinaturas atualizado.');
    }
}
