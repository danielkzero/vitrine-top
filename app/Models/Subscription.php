<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan',
        'price',
        'billing_period',
        'trial_ends_at',
        'next_billing_at',
        'status',
        'payment_method',
        'gateway_subscription_id',
    ];

    protected $casts = [
        'trial_ends_at' => 'date',
        'next_billing_at' => 'date',
        'price' => 'decimal:2',
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

    /*
    |--------------------------------------------------------------------------
    | Escopos úteis
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /*
    |--------------------------------------------------------------------------
    | Acessores e Lógicas de Status
    |--------------------------------------------------------------------------
    */

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsTrialAttribute(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at?->isFuture();
    }

    public function getDaysLeftAttribute(): ?int
    {
        if ($this->trial_ends_at && $this->status === 'trial') {
            return now()->diffInDays($this->trial_ends_at, false);
        }

        if ($this->next_billing_at && $this->status === 'active') {
            return now()->diffInDays($this->next_billing_at, false);
        }

        return null;
    }

    public function getIsExpiredAttribute(): bool
    {
        if ($this->trial_ends_at && now()->greaterThan($this->trial_ends_at) && $this->status === 'trial') {
            return true;
        }

        if ($this->next_billing_at && now()->greaterThan($this->next_billing_at) && $this->status === 'active') {
            return true;
        }

        return $this->status === 'expired';
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos de Negócio
    |--------------------------------------------------------------------------
    */

    public function startTrial(int $days = 7): void
    {
        $this->update([
            'trial_ends_at' => now()->addDays($days),
            'status' => 'trial',
        ]);
    }

    public function activate(string $method = 'pix'): void
    {
        $nextBilling = $this->billing_period === 'annual'
            ? now()->addYear()
            : now()->addMonth();

        $this->update([
            'status' => 'active',
            'payment_method' => $method,
            'next_billing_at' => $nextBilling,
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function markExpired(): void
    {
        $this->update(['status' => 'expired']);
    }
}
