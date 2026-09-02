<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmNote extends Model
{
    protected $fillable = ['user_id', 'author_id', 'type', 'content', 'occurred_at'];

    protected $casts = ['occurred_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
