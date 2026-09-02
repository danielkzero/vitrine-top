<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = ['number', 'user_id', 'assigned_to', 'subject', 'category', 'priority', 'status', 'last_activity_at', 'resolved_at'];

    protected $casts = ['last_activity_at' => 'datetime', 'resolved_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id');
    }
}
