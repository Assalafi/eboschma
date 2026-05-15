<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'room_id',
        'is_occupied',
        'is_active',
    ];

    protected $casts = [
        'id' => 'string',
        'room_id' => 'string',
        'is_occupied' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bed) {
            if (empty($bed->id) || $bed->id === '0') {
                $bed->id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the room that owns this bed.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the ward through room.
     */
    public function ward()
    {
        return $this->hasOneThrough(Ward::class, Room::class, 'id', 'id', 'room_id', 'ward_id');
    }

    /**
     * Get the status badge HTML.
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';
    }

    /**
     * Get the occupancy badge HTML.
     */
    public function getOccupancyBadgeAttribute()
    {
        return $this->is_occupied
            ? '<span class="badge bg-warning text-dark">Occupied</span>'
            : '<span class="badge bg-info">Available</span>';
    }
}
