<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'whatsapp',
        'customer_name',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Escopos personalizados
    |--------------------------------------------------------------------------
    */

    // Apenas avaliações aprovadas
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Apenas pendentes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Apenas rejeitadas
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /*
    |--------------------------------------------------------------------------
    | Acessores úteis
    |--------------------------------------------------------------------------
    */

    public function getStarsAttribute()
    {
        return str_repeat('⭐', $this->rating);
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }
}
