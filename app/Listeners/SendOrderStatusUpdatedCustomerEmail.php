<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Mail\Orders\OrderStatusUpdatedCustomerMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdatedCustomerEmail implements ShouldQueue
{
    public function handle(OrderStatusUpdated $event): void
    {
        $order = $event->order->loadMissing('customer');

        if (!$order->customer?->email) {
            return;
        }

        Mail::to($order->customer->email)->send(
            new OrderStatusUpdatedCustomerMail($order, $event->previousStatus, $event->newStatus)
        );
    }
}
