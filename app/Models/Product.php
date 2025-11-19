<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'code',
        'name',
        'description',
        'price',
        'discount_price',
        'stock',
        'is_public',
        'allow_whatsapp',
        'cover_image',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_public' => 'boolean',
        'allow_whatsapp' => 'boolean',
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


    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity', 'price')->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Acessores, Mutadores e Eventos
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        // Gera código automático se não existir
        static::creating(function ($product) {
            if (empty($product->code)) {
                $product->code = strtoupper(Str::random(8));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos auxiliares
    |--------------------------------------------------------------------------
    */

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2, ',', '.');
    }

    public function getPublicUrlAttribute()
    {
        return url("/produto/{$this->id}-" . Str::slug($this->name));
    }
}
