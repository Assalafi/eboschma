<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ward_id',
        'is_active',
    ];

    protected $casts = [
        'id' => 'string',
        'ward_id' => 'string',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($room) {
            if (empty($room->id) || $room->id === '0') {
                $room->id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the ward that owns this room.
     */
    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    /**
     * Get the beds in this room.
     */
    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    /**
     * Get the facility through ward.
     */
    public function facility()
    {
        return $this->hasOneThrough(Facility::class, Ward::class, 'id', 'id', 'ward_id', 'facility_id');
    }

    /**
     * Get the count of beds in this room.
     */
    public function getBedsCountAttribute()
    {
        return $this->beds()->count();
    }

    /**
     * Get the count of occupied beds.
     */
    public function getOccupiedBedsCountAttribute()
    {
        return $this->beds()->where('is_occupied', true)->count();
    }

    /**
     * Get the count of available beds.
     */
    public function getAvailableBedsCountAttribute()
    {
        return $this->beds()->where('is_occupied', false)->where('is_active', true)->count();
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
}
