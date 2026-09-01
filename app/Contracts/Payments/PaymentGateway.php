<?php

namespace App\Contracts\Payments;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

interface PaymentGateway
{
    public function provider(): string;

    public function createOrGetCustomer(User $user): string;

    public function createSubscription(Subscription $subscription, Plan $plan, string $paymentMethod): array;

    public function cancelSubscription(Subscription $subscription): void;
}

