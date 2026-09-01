<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\Orders\OrderCreatedStoreMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderCreatedStoreEmail implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order->loadMissing('user');

        if (!$order->user?->email) {
            return;
        }

        Mail::to($order->user->email)->send(new OrderCreatedStoreMail($order));
    }
}
