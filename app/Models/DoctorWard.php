<?php

namespace App\Models;

use App\Models\User;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DoctorWard extends Model
{
    use HasFactory;

    protected $table = 'doctor_ward';

    protected $fillable = [
        'user_id',
        'ward_id',
        'assigned_date',
        'is_active',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'ward_id' => 'string',
        'assigned_date' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($doctorWard) {
            if (empty($doctorWard->id) || $doctorWard->id === '0') {
                $doctorWard->id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the doctor (user) for this assignment.
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the ward for this assignment.
     */
    public function ward()
    {
        return $this->belongsTo(Ward::class);
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
