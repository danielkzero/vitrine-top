<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmProfile extends Model
{
    protected $fillable = ['user_id', 'health', 'score', 'tags', 'summary', 'last_contact_at', 'next_follow_up_at'];

    protected $casts = ['tags' => 'array', 'score' => 'integer', 'last_contact_at' => 'datetime', 'next_follow_up_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
