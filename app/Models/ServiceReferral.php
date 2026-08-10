<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ServiceReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'encounter_id',
        'from_facility_id',
        'to_facility_id',
        'referral_type',
        'service_item_id',
        'reason',
        'status',
    ];

    // UUID configuration
    // protected $keyType = 'string';
    // public $incrementing = false;

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         if (empty($model->id)) {
    //             $model->id = (string) Str::uuid();
    //         }
    //     });
    // }

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    // Referral type constants
    const TYPE_SERVICE = 'service';
    const TYPE_PATIENT = 'patient';

    // Relationships
    public function encounter()
    {
        return $this->belongsTo(Encounter::class, 'encounter_id');
    }

    public function fromFacility()
    {
        return $this->belongsTo(Facility::class, 'from_facility_id');
    }

    public function toFacility()
    {
        return $this->belongsTo(Facility::class, 'to_facility_id');
    }

    /**
     * Get the service item for this referral
     */
    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }

    /**
     * Get the authorization for this referral
     */
    public function authorization()
    {
        return $this->hasOne(Authorization::class);
    }

    /**
     * Get the staff member who approved the referral
     */
    public function approverStaff()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Get the user who approved the referral
     */
    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the staff member who rejected the referral
     */
    public function rejecterStaff()
    {
        return $this->belongsTo(Staff::class, 'rejected_by');
    }

    /**
     * Get the user who rejected the referral
     */
    public function rejecterUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get approved by name attribute
     */
    public function getApprovedByNameAttribute()
    {
        if (!$this->approved_by) {
            return null;
        }
        if ($this->approverStaff) {
            return $this->approverStaff->fullname ?? $this->approverStaff->email;
        }
        if ($this->approverUser) {
            return $this->approverUser->name ?? $this->approverUser->email;
        }
        return null;
    }

    /**
     * Get rejected by name attribute
     */
    public function getRejectedByNameAttribute()
    {
        if (!$this->rejected_by) {
            return null;
        }
        if ($this->rejecterStaff) {
            return $this->rejecterStaff->fullname ?? $this->rejecterStaff->email;
        }
        if ($this->rejecterUser) {
            return $this->rejecterUser->name ?? $this->rejecterUser->email;
        }
        return null;
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark">Pending</span>',
            self::STATUS_ACCEPTED => '<span class="badge bg-info text-white">Accepted</span>',
            self::STATUS_COMPLETED => '<span class="badge bg-success text-white">Completed</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger text-white">Rejected</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-secondary text-white">Cancelled</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getReferralTypeLabel()
    {
        $labels = [
            self::TYPE_SERVICE => 'Service Referral',
            self::TYPE_PATIENT => 'Patient Referral',
        ];

        return $labels[$this->referral_type] ?? 'Unknown';
    }
}
