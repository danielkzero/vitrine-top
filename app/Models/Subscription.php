<?php

namespace App\Models;

use App\Enums\BillingPeriod;
use App\Enums\PaymentGatewayProvider;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'plan',
        'price',
        'billing_period',
        'trial_starts_at',
        'trial_ends_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'next_billing_at',
        'status',
        'payment_method',
        'gateway_provider',
        'gateway_subscription_id',
        'gateway_customer_id',
        'gateway_metadata',
    ];

    protected $casts = [
        'billing_period' => BillingPeriod::class,
        'status' => SubscriptionStatus::class,
        'gateway_provider' => PaymentGatewayProvider::class,
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'date',
        'current_period_starts_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'next_billing_at' => 'date',
        'gateway_metadata' => 'array',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function planModel()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE->value);
    }

    public function scopeTrial($query)
    {
        return $query->where('status', SubscriptionStatus::TRIAL->value);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', SubscriptionStatus::EXPIRED->value);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE;
    }

    public function getIsTrialAttribute(): bool
    {
        return $this->status === SubscriptionStatus::TRIAL && $this->trial_ends_at?->isFuture();
    }

    public function getDaysLeftAttribute(): ?int
    {
        if ($this->trial_ends_at && $this->status === SubscriptionStatus::TRIAL) {
            return now()->diffInDays($this->trial_ends_at, false);
        }

        if ($this->next_billing_at && $this->status === SubscriptionStatus::ACTIVE) {
            return now()->diffInDays($this->next_billing_at, false);
        }

        return null;
    }

    public function getIsExpiredAttribute(): bool
    {
        if ($this->trial_ends_at && now()->greaterThan($this->trial_ends_at) && $this->status === SubscriptionStatus::TRIAL) {
            return true;
        }

        if ($this->next_billing_at && now()->greaterThan($this->next_billing_at) && $this->status === SubscriptionStatus::ACTIVE) {
            return true;
        }

        return $this->status === SubscriptionStatus::EXPIRED;
    }

    public function startTrial(int $days = 14): void
    {
        $this->update([
            'trial_starts_at' => now(),
            'trial_ends_at' => now()->addDays($days),
            'status' => SubscriptionStatus::TRIAL->value,
        ]);
    }

    public function activate(string $method = 'pix'): void
    {
        $isAnnual = $this->billing_period instanceof BillingPeriod
            ? $this->billing_period === BillingPeriod::ANNUAL
            : $this->billing_period === BillingPeriod::ANNUAL->value;

        $nextBilling = $isAnnual ? now()->addYear() : now()->addMonth();

        $this->update([
            'status' => SubscriptionStatus::ACTIVE->value,
            'payment_method' => $method,
            'next_billing_at' => $nextBilling,
            'current_period_starts_at' => now(),
            'current_period_ends_at' => $nextBilling,
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => SubscriptionStatus::CANCELLED->value]);
    }

    public function markExpired(): void
    {
        $this->update(['status' => SubscriptionStatus::EXPIRED->value]);
    }
}
