<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'subtitle',
        'image_url',
        'order',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
