<?php

namespace App\Models;

use App\Notifications\VerifyEmailCustom;
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
        'logo_path',
        'background_path',
        'description',
        'subtitle',
        'theme_color',
        'email',
        'password',
        'address',
        'city',
        'state',
        'zip',
        'phone_primary',
        'whatsapp',
        'plan',
        'billing_customer_id',
        'is_active',
        'is_admin',
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
            'is_admin' => 'boolean',
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

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentPlan(): ?Plan
    {
        $subscription = $this->subscription;

        if (! $subscription) {
            return null;
        }

        if ($subscription->relationLoaded('planModel') && $subscription->planModel) {
            return $subscription->planModel;
        }

        if ($subscription->plan_id) {
            return Plan::find($subscription->plan_id);
        }

        return null;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function banners()
    {
        return $this->hasMany(Banner::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function pageViews()
    {
        return $this->hasMany(PageView::class);
    }

    public function productViews()
    {
        return $this->hasMany(ProductView::class);
    }

    public function crmProfile()
    {
        return $this->hasOne(CrmProfile::class);
    }

    public function crmNotes()
    {
        return $this->hasMany(CrmNote::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * ===============================
     * Acessores e Mutadores
     * ===============================
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->slug) && ! empty($user->business_name)) {
                $user->slug = str()->slug($user->business_name);
            }
        });
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailCustom);
    }
}
