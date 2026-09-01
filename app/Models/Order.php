<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_id',
        'product_id',
        'shipping_address_id',
        'status',
        'subtotal',
        'customer_name',
        'total',
        'contact',
        'payment_method',
        'shipping_method',
        'notes',
        'address_snapshot',
        'message',
        'placed_at',
        'canceled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'address_snapshot' => 'array',
            'placed_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'shipping_address_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getProductNameAttribute(): string
    {
        return $this->product?->name ?? 'Pedido geral';
    }

    public function getWhatsappMessageAttribute(): string
    {
        $text = "Ola, me interessei pelo produto: *{$this->product_name}*";

        if ($this->message) {
            $text .= "\nMensagem: {$this->message}";
        }

        return urlencode($text);
    }
}
