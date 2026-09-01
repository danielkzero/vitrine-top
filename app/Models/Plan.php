<?php

namespace App\Models;

use App\Enums\PlanCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'monthly_price',
        'annual_price_total',
        'annual_monthly_equivalent',
        'products_limit',
        'product_images_limit',
        'gallery_images_limit',
        'banners_limit',
        'trial_days',
        'gateway_provider',
        'gateway_plan_ref_monthly',
        'gateway_plan_ref_annual',
        'gateway_metadata',
        'is_active',
    ];

    protected $casts = [
        'code' => PlanCode::class,
        'monthly_price' => 'decimal:2',
        'annual_price_total' => 'decimal:2',
        'annual_monthly_equivalent' => 'decimal:2',
        'products_limit' => 'integer',
        'product_images_limit' => 'integer',
        'gallery_images_limit' => 'integer',
        'banners_limit' => 'integer',
        'trial_days' => 'integer',
        'gateway_metadata' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function isUnlimitedProductImages(): bool
    {
        return $this->product_images_limit === null;
    }
}

