<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'title',
        'icon',
        'is_active',
        'order',
        'content',
        'cover_image',
        'seo_title',
        'seo_description',
        'type'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'content' => 'array',
    ];

    /*
     * |--------------------------------------------------------------------------
     * | Relacionamentos
     * |--------------------------------------------------------------------------
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /*
     * |--------------------------------------------------------------------------
     * | Acessores, Mutadores e Eventos
     * |--------------------------------------------------------------------------
     */

    protected static function booted()
    {
        static::creating(function ($page) {
            // Gera automaticamente a chave única se não for informada
            if (empty($page->key)) {
                $page->key = Str::slug($page->title) . '-' . Str::random(4);
            }
        });
    }

    public function getExcerptAttribute()
    {
        return Str::limit(strip_tags($this->content), 150);
    }

    public function getPublicUrlAttribute()
    {
        return url("/pagina/{$this->key}");
    }

    /*
     * |--------------------------------------------------------------------------
     * | Escopos personalizados
     * |--------------------------------------------------------------------------
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
