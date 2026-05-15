<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FacilityClaim extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'claim_number',
        'encounter_id',
        'facility_id',
        'patient_id',
        'enrollee_number',
        'enrollee_type',
        'file_number',
        'patient_name',
        'boschma_no',
        'nin',
        'phone_number',
        'gender',
        'date_of_birth',
        'claim_type',
        'service_date',
        'admission_date',
        'discharge_date',
        'length_of_stay',
        'consultation_amount',
        'pharmacy_amount',
        'laboratory_amount',
        'services_amount',
        'total_amount',
        'status',
        'rejection_reason',
        'admin_notes',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'payment_reference',
        'payment_date',
        'paid_by',
        // New approval workflow fields
        'verifier_status',
        'verifier_notes',
        'verifier_updated_at',
        'verifier_id',
        'approver_status',
        'approver_notes',
        'approver_updated_at',
        'approver_id',
        'es_status',
        'es_notes',
        'es_updated_at',
        'es_id',
        'finance_status',
        'finance_notes',
        'finance_updated_at',
        'finance_id'
    ];

    protected $casts = [
        'service_date' => 'date',
        'admission_date' => 'date',
        'discharge_date' => 'date',
        'date_of_birth' => 'date',
        'consultation_amount' => 'decimal:2',
        'pharmacy_amount' => 'decimal:2',
        'laboratory_amount' => 'decimal:2',
        'services_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'payment_date' => 'date',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VERIFIED = 'verified';
    const STATUS_APPROVED = 'approved';
    const STATUS_ES_APPROVED = 'es_approved';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    // Claim type constants
    const TYPE_OUTPATIENT = 'outpatient';
    const TYPE_INPATIENT = 'inpatient';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_REFERRAL = 'referral';

    // Relationships
    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(Staff::class, 'reviewed_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(Staff::class, 'paid_by');
    }

    // Claim items
    public function consultations()
    {
        return $this->hasMany(FacilityClaimConsultation::class);
    }

    public function medications()
    {
        return $this->hasMany(FacilityClaimMedication::class);
    }

    public function services()
    {
        return $this->hasMany(FacilityClaimService::class);
    }

    public function diagnoses()
    {
        return $this->hasMany(FacilityClaimDiagnosis::class);
    }

    public function activities()
    {
        return $this->hasMany(FacilityClaimActivity::class);
    }

    public function documents()
    {
        return $this->hasMany(FacilityClaimDocument::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    // Helpers
    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_DRAFT => '<span class="badge bg-secondary text-white">Draft</span>',
            self::STATUS_SUBMITTED => '<span class="badge bg-info text-white">Submitted</span>',
            self::STATUS_VERIFIED => '<span class="badge bg-primary text-white">Verified</span>',
            self::STATUS_APPROVED => '<span class="badge bg-success text-white">Approved</span>',
            self::STATUS_ES_APPROVED => '<span class="badge bg-success text-white">ES Approved</span>',
            self::STATUS_UNDER_REVIEW => '<span class="badge bg-warning text-dark">Under Review</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger text-white">Rejected</span>',
            self::STATUS_PAID => '<span class="badge bg-success text-white">Paid</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getFormattedTotalAmountAttribute()
    {
        return '₦' . number_format($this->total_amount, 2);
    }

    // Auto-generate claim number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($claim) {
            if (empty($claim->claim_number)) {
                $claim->claim_number = 'CLM-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
            }
        });
    }
}
