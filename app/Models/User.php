<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'surname',
        'business_name',
        'slug',
        'avatar',
        'logo_base64',
        'background_image',
        'subtitle',
        'theme_color',
        'email',
        'password',
        'address',
        'city',
        'state',
        'zip',
        'phone_primary',
        'plan',
        'billing_customer_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * ===============================
     * Relacionamentos
     * ===============================
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function settings()
    {
        return $this->hasOne(Setting::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function banners()
    {
        return $this->hasMany(Banner::class);
    }

    /**
     * ===============================
     * Acessores e Mutadores
     * ===============================
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->slug) && !empty($user->business_name)) {
                $user->slug = str()->slug($user->business_name);
            }
        });
    }
}
