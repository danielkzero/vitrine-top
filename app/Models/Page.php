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

    public static function ensureDefaultPages($userId)
    {
        $defaultPages = [
            ['key' => 'principal', 'title' => 'Principal', 'is_active' => true, 'type' => 'simple', 'order' => 1],
            ['key' => 'catalogo', 'title' => 'Catálogo', 'is_active' => true, 'type' => 'products', 'order' => 2],
            ['key' => 'galeria', 'title' => 'Galeria', 'is_active' => false, 'type' => 'simple', 'order' => 3],
            ['key' => 'links', 'title' => 'Links', 'is_active' => false, 'type' => 'links', 'order' => 4],
            ['key' => 'sobre', 'title' => 'Sobre', 'is_active' => false, 'type' => 'simple', 'order' => 5],
            ['key' => 'avaliacoes', 'title' => 'Avaliações', 'is_active' => true, 'type' => 'reviews', 'order' => 6],
        ];

        $existingKeys = self::where('user_id', $userId)->pluck('key')->toArray();

        foreach ($defaultPages as $page) {
            if (!in_array($page['key'], $existingKeys)) {
                self::create([
                    'user_id' => $userId,
                    'key' => $page['key'],
                    'title' => $page['title'],
                    'is_active' => $page['is_active'],
                    'order' => $page['order'],
                    'content' => '',
                    'cover_image' => '',
                    'seo_title' => '',
                    'seo_description' => '',
                    'type' => $page['type']
                ]);
            }
        }
    }
}
