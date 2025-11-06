<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'transaction_id',
        'amount',
        'currency',
        'method',
        'status',
        'details',
        'paid_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'details' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Escopos úteis
    |--------------------------------------------------------------------------
    */

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /*
    |--------------------------------------------------------------------------
    | Acessores
    |--------------------------------------------------------------------------
    */

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsRefundedAttribute(): bool
    {
        return $this->status === 'refunded';
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos de Negócio
    |--------------------------------------------------------------------------
    */

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Se estiver vinculada a uma assinatura, pode atualizar o status dela
        if ($this->subscription && $this->subscription->status !== 'active') {
            $this->subscription->activate($this->method);
        }
    }

    public function markAsFailed(string $reason = null): void
    {
        $details = $this->details ?? [];
        if ($reason) {
            $details['failure_reason'] = $reason;
        }

        $this->update([
            'status' => 'failed',
            'details' => $details,
        ]);
    }

    public function refund(): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);
    }
}
