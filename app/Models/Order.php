<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'customer_name',
        'contact',
        'message',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    // Dono da vitrine
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Produto relacionado (pode ser nulo)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Acessores & Helpers
    |--------------------------------------------------------------------------
    */

    // Exemplo: nome do produto ou "Pedido geral"
    public function getProductNameAttribute(): string
    {
        return $this->product?->name ?? 'Pedido geral';
    }

    // Mensagem para WhatsApp formatada
    public function getWhatsappMessageAttribute(): string
    {
        $text = "Olá, me interessei pelo produto: *{$this->product_name}*";
        if ($this->message) {
            $text .= "\nMensagem: {$this->message}";
        }
        return urlencode($text);
    }
}
