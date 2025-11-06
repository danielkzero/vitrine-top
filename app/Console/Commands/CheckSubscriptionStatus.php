<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;

class CheckSubscriptionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica e atualiza o status das assinaturas expiradas ou pendentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $today = Carbon::now();

        Subscription::where('status', 'trial')
            ->whereDate('trial_ends_at', '<', $today)
            ->update(['status' => 'expired']);

        Subscription::where('status', 'active')
            ->whereDate('next_billing_at', '<', $today)
            ->update(['status' => 'past_due']);

        $this->info('Verificação de assinaturas concluída com sucesso!');
    }
}
