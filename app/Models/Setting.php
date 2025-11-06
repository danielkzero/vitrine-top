<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme_color',
        'text_color',
        'cover_image',
        'profile_image',
        'seo_defaults',
        'allow_name_suggestions',
    ];

    protected $casts = [
        'seo_defaults' => 'array',
        'allow_name_suggestions' => 'boolean',
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
    | Acessores e Helpers
    |--------------------------------------------------------------------------
    */

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image
            ? asset('storage/' . $this->cover_image)
            : null;
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->profile_image
            ? asset('storage/' . $this->profile_image)
            : null;
    }

    public function getSeoTitleAttribute(): ?string
    {
        return $this->seo_defaults['title'] ?? null;
    }

    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->seo_defaults['description'] ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos auxiliares
    |--------------------------------------------------------------------------
    */

    public function updateAppearance(array $data): void
    {
        $this->update([
            'theme_color' => $data['theme_color'] ?? $this->theme_color,
            'text_color' => $data['text_color'] ?? $this->text_color,
            'cover_image' => $data['cover_image'] ?? $this->cover_image,
            'profile_image' => $data['profile_image'] ?? $this->profile_image,
        ]);
    }
}
