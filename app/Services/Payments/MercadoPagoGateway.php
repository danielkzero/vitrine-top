<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGateway;
use App\Enums\PaymentGatewayProvider;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use LogicException;

class MercadoPagoGateway implements PaymentGateway
{
    public function provider(): string
    {
        return PaymentGatewayProvider::MERCADO_PAGO->value;
    }

    public function createOrGetCustomer(User $user): string
    {
        if (!empty($user->billing_customer_id)) {
            return (string) $user->billing_customer_id;
        }

        // Placeholder para futura integração com API real do gateway.
        return 'mp-customer-pending-'.$user->id;
    }

    public function createSubscription(Subscription $subscription, Plan $plan, string $paymentMethod): array
    {
        throw new LogicException('Gateway Mercado Pago ainda não implementado.');
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        throw new LogicException('Cancelamento no gateway ainda não implementado.');
    }
}
