<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'ticket_id',
        'type',
        'title',
        'message',
        'read',
        'read_at'
    ];

    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\Staff::class, 'user_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function markAsRead()
    {
        $this->read = true;
        $this->read_at = now();
        $this->save();
    }

    public function getTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
