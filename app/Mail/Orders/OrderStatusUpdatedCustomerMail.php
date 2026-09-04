<?php

namespace App\Mail\Orders;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedCustomerMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order,
        public string $previousStatus,
        public string $newStatus,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Atualização do pedido {$this->order->order_number}");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.status_updated_customer',
            with: [
                'order' => $this->order,
                'previousStatus' => $this->previousStatus,
                'newStatus' => $this->newStatus,
            ],
        );
    }
}
