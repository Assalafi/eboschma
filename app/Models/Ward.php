<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'facility_id',
        'is_active',
    ];

    protected $casts = [
        'id' => 'string',
        'facility_id' => 'string',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ward) {
            if (empty($ward->id) || $ward->id === '0') {
                $ward->id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the facility that owns this ward.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Get the rooms in this ward.
     */
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the nurse assignments for this ward.
     */
    public function nurseAssignments()
    {
        return $this->hasMany(NurseWard::class);
    }

    /**
     * Get the nurses assigned to this ward.
     */
    public function nurses()
    {
        return $this->belongsToMany(User::class, 'nurse_ward', 'ward_id', 'user_id')
            ->withPivot('assigned_date', 'is_active')
            ->withTimestamps();
    }

    /**
     * Get the count of rooms in this ward.
     */
    public function getRoomsCountAttribute()
    {
        return $this->rooms()->count();
    }

    /**
     * Get the count of beds in this ward.
     */
    public function getBedsCountAttribute()
    {
        return $this->rooms()->withCount('beds')->get()->sum('beds_count');
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
