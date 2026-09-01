<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\Orders\OrderCreatedCustomerMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderCreatedCustomerEmail implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order->loadMissing('customer');

        if (!$order->customer?->email) {
            return;
        }

        Mail::to($order->customer->email)->send(new OrderCreatedCustomerMail($order));
    }
}
