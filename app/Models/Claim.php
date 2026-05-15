<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\Staff;

class Claim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'authorization_code',
        'beneficiary_name',
        'boschma_id',
        'nin',
        'phone_number',
        'facility_id',
        'claim_type',
        'healthcare_provider',
        'provider_type',
        'service_date',
        'claim_amount',
        'diagnosis',
        'treatment_description',
        'additional_notes',
        'status',
        'ro_status',
        'ro_updated_at',
        'ro_updated_by',
        'e5_status',
        'e5_updated_at',
        'e5_updated_by',
        'rejection_reason',
        'payment_reference',
        'payment_date',
        'medical_report',
        'prescription',
        'receipt',
        'created_by',
        'updated_by',
        'approved_by',
        'rejected_by',
        'paid_by'
    ];

    protected $casts = [
        'service_date' => 'date',
        'ro_updated_at' => 'datetime',
        'e5_updated_at' => 'datetime',
        'payment_date' => 'date',
        'claim_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'service_date',
        'ro_updated_at',
        'e5_updated_at',
        'payment_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    // Approval status constants
    const APPROVAL_NOT_REVIEWED = '';
    const APPROVAL_APPROVED = 'approved';
    const APPROVAL_REJECTED = 'rejected';

    // Claim type constants
    const TYPE_MEDICAL = 'medical';
    const TYPE_PHARMACY = 'pharmacy';
    const TYPE_HOSPITALIZATION = 'hospitalization';
    const TYPE_DIAGNOSTIC = 'diagnostic';
    const TYPE_EMERGENCY = 'emergency';

    // Provider type constants
    const PROVIDER_HOSPITAL = 'hospital';
    const PROVIDER_CLINIC = 'clinic';
    const PROVIDER_PHARMACY = 'pharmacy';
    const PROVIDER_LABORATORY = 'laboratory';
    const PROVIDER_DIAGNOSTIC_CENTER = 'diagnostic_center';

    /**
     * Get the user who created the claim.
     */
    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Get the user who last updated the claim.
     */
    public function updater()
    {
        return $this->belongsTo(Staff::class, 'updated_by');
    }

    /**
     * Get the user who approved the claim.
     */
    public function approver()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    /**
     * Get the user who rejected the claim.
     */
    public function rejecter()
    {
        return $this->belongsTo(Staff::class, 'rejected_by');
    }

    /**
     * Get the user who marked the claim as paid.
     */
    public function payer()
    {
        return $this->belongsTo(Staff::class, 'paid_by');
    }

    /**
     * Get the RO reviewer.
     */
    public function roReviewer()
    {
        return $this->belongsTo(Staff::class, 'ro_reviewer_id');
    }

    /**
     * Get the E5 reviewer.
     */
    public function e5Reviewer()
    {
        return $this->belongsTo(Staff::class, 'e5_reviewer_id');
    }

    /**
     * Get the claim history/audit trail.
     */
    public function history()
    {
        return $this->hasMany(ClaimHistory::class);
    }

    /**
     * Get the claim notes for the claim.
     */
    public function notes()
    {
        return $this->hasMany(ClaimNote::class);
    }

    /**
     * Get the medications for the claim.
     */
    public function medications()
    {
        return $this->hasMany(ClaimMedication::class);
    }

    /**
     * Get the laboratory tests for the claim.
     */
    public function laboratoryTests()
    {
        return $this->hasMany(ClaimLaboratoryTest::class);
    }

    /**
     * Get the rendered services for the claim.
     */
    public function renderedServices()
    {
        return $this->hasMany(ClaimRenderedService::class);
    }

    /**
     * Get the documents for the claim.
     */
    public function documents()
    {
        return $this->hasMany(ClaimDocument::class);
    }

    /**
     * Get the facility for this claim.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Calculate total claim amount from all components.
     */
    public function calculateTotalAmount()
    {
        $medicationsTotal = $this->medications()->sum('claimed_amount');
        $labTestsTotal = $this->laboratoryTests()->sum('claimed_amount');
        $servicesTotal = $this->renderedServices()->sum('claimed_amount');
        
        return $medicationsTotal + $labTestsTotal + $servicesTotal;
    }

    /**
     * Recalculate and update claim amount.
     */
    public function recalculateClaimAmount()
    {
        $this->claim_amount = $this->calculateTotalAmount();
        $this->save();
        
        return $this->claim_amount;
    }

    /**
     * Scope a query to only include pending claims.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include approved claims.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope a query to only include rejected claims.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope a query to only include paid claims.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope a query to filter by claim type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('claim_type', $type);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->whereDate('service_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('service_date', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Scope a query to search by beneficiary name or BOSCHMA ID.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('beneficiary_name', 'like', "%{$search}%")
              ->orWhere('boschma_id', 'like', "%{$search}%")
              ->orWhere('authorization_code', 'like', "%{$search}%")
              ->orWhere('nin', 'like', "%{$search}%");
        });
    }

    /**
     * Generate a unique authorization code.
     */
    public static function generateAuthorizationCode()
    {
        $prefix = 'BHC';
        $year = date('Y');
        $sequence = self::count() + 1;
        
        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }

    /**
     * Check if the claim can be edited.
     */
    public function canBeEdited()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the claim can be approved.
     */
    public function canBeApproved()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the claim can be rejected.
     */
    public function canBeRejected()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the claim can be marked as paid.
     */
    public function canBePaid()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Approve the claim.
     */
    public function approve($userId = null)
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $userId;
        $this->save();
        
        $this->addHistory('approved', 'Claim approved', $userId);
    }

    /**
     * Reject the claim.
     */
    public function reject($reason, $userId = null)
    {
        $this->status = self::STATUS_REJECTED;
        $this->rejection_reason = $reason;
        $this->rejected_by = $userId;
        $this->save();
        
        $this->addHistory('rejected', "Claim rejected: {$reason}", $userId);
    }

    /**
     * Mark the claim as paid.
     */
    public function markAsPaid($paymentReference = null, $paymentDate = null, $userId = null)
    {
        $this->status = self::STATUS_PAID;
        $this->payment_reference = $paymentReference;
        $this->payment_date = $paymentDate ?: now();
        $this->paid_by = $userId;
        $this->save();
        
        $this->addHistory('paid', "Claim paid. Reference: {$paymentReference}", $userId);
    }

    /**
     * Update RO status.
     */
    public function updateRoStatus($status, $userId = null)
    {
        $this->ro_status = $status;
        $this->ro_updated_at = now();
        $this->ro_updated_by = $userId;
        $this->save();
        
        $this->addHistory('ro_reviewed', "RO status updated to: {$status}", $userId);
    }

    /**
     * Update E5 status.
     */
    public function updateE5Status($status, $userId = null)
    {
        $this->e5_status = $status;
        $this->e5_updated_at = now();
        $this->e5_updated_by = $userId;
        $this->save();
        
        $this->addHistory('e5_reviewed', "E5 status updated to: {$status}", $userId);
    }

    /**
     * Add a history record.
     */
    public function addHistory($action, $description, $userId = null)
    {
        $this->history()->create([
            'action' => $action,
            'description' => $description,
            'user_id' => $userId,
            'old_status' => $this->getOriginal('status'),
            'new_status' => $this->status,
        ]);
    }

    /**
     * Add a note to the claim.
     */
    public function addNote($content, $userId = null)
    {
        return $this->notes()->create([
            'content' => $content,
            'user_id' => $userId,
        ]);
    }

    /**
     * Get formatted claim amount.
     */
    public function getFormattedAmountAttribute()
    {
        return '₦' . number_format($this->claim_amount, 2);
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadgeAttribute()
    {
        // Check RO and E5 status for more detailed badge
        if ($this->status === 'submitted') {
            if (empty($this->ro_status)) {
                return '<span class="badge bg-warning">Pending RO Review</span>';
            } elseif ($this->ro_status === 'approved' && empty($this->e5_status)) {
                return '<span class="badge bg-primary">Pending E5 Approval</span>';
            } elseif ($this->ro_status === 'rejected') {
                return '<span class="badge bg-danger">RO Rejected</span>';
            } elseif ($this->e5_status === 'rejected') {
                return '<span class="badge bg-danger">E5 Rejected</span>';
            }
        }

        $badges = [
            self::STATUS_PENDING => '<span class="badge bg-warning">Pending</span>',
            'submitted' => '<span class="badge bg-info">Submitted</span>',
            self::STATUS_APPROVED => '<span class="badge bg-success">Approved</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger">Rejected</span>',
            self::STATUS_PAID => '<span class="badge bg-success">Paid</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get status color for UI elements.
     */
    public function getStatusColorAttribute()
    {
        if ($this->status === 'submitted') {
            if (empty($this->ro_status)) {
                return 'warning';
            } elseif ($this->ro_status === 'approved' && empty($this->e5_status)) {
                return 'primary';
            }
        }

        $colors = [
            self::STATUS_PENDING => 'warning',
            'submitted' => 'info',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_PAID => 'success',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get status icon for UI elements.
     */
    public function getStatusIconAttribute()
    {
        if ($this->status === 'submitted') {
            if (empty($this->ro_status)) {
                return 'clock';
            } elseif ($this->ro_status === 'approved' && empty($this->e5_status)) {
                return 'shield-check';
            }
        }

        $icons = [
            self::STATUS_PENDING => 'clock',
            'submitted' => 'file-text',
            self::STATUS_APPROVED => 'check-circle',
            self::STATUS_REJECTED => 'x-circle',
            self::STATUS_PAID => 'cash',
        ];

        return $icons[$this->status] ?? 'file';
    }

    /**
     * Get claim type label.
     */
    public function getClaimTypeLabelAttribute()
    {
        $types = [
            self::TYPE_MEDICAL => 'Medical Services',
            self::TYPE_PHARMACY => 'Pharmacy/Medication',
            self::TYPE_HOSPITALIZATION => 'Hospitalization',
            self::TYPE_DIAGNOSTIC => 'Diagnostic Tests',
            self::TYPE_EMERGENCY => 'Emergency Services',
        ];

        return $types[$this->claim_type] ?? 'Unknown';
    }

    /**
     * Get provider type label.
     */
    public function getProviderTypeLabelAttribute()
    {
        $types = [
            self::PROVIDER_HOSPITAL => 'Hospital',
            self::PROVIDER_CLINIC => 'Clinic',
            self::PROVIDER_PHARMACY => 'Pharmacy',
            self::PROVIDER_LABORATORY => 'Laboratory',
            self::PROVIDER_DIAGNOSTIC_CENTER => 'Diagnostic Center',
        ];

        return $types[$this->provider_type] ?? 'Unknown';
    }

    /**
     * Get approval progress percentage.
     */
    public function getApprovalProgressAttribute()
    {
        $progress = 0;
        
        if ($this->ro_status === self::APPROVAL_APPROVED) {
            $progress += 50;
        }
        
        if ($this->e5_status === self::APPROVAL_APPROVED) {
            $progress += 50;
        }
        
        return $progress;
    }

    /**
     * Check if claim is fully approved (both RO and E5).
     */
    public function isFullyApproved()
    {
        return $this->ro_status === self::APPROVAL_APPROVED && 
               $this->e5_status === self::APPROVAL_APPROVED;
    }

    /**
     * Get the next required approval step.
     */
    public function getNextApprovalStep()
    {
        if ($this->ro_status !== self::APPROVAL_APPROVED) {
            return 'RO Review';
        }
        
        if ($this->e5_status !== self::APPROVAL_APPROVED) {
            return 'E5 Review';
        }
        
        return 'Completed';
    }

    /**
     * Get all available claim types.
     */
    public static function getClaimTypes()
    {
        return [
            self::TYPE_MEDICAL => 'Medical Services',
            self::TYPE_PHARMACY => 'Pharmacy/Medication',
            self::TYPE_HOSPITALIZATION => 'Hospitalization',
            self::TYPE_DIAGNOSTIC => 'Diagnostic Tests',
            self::TYPE_EMERGENCY => 'Emergency Services',
        ];
    }

    /**
     * Get all available provider types.
     */
    public static function getProviderTypes()
    {
        return [
            self::PROVIDER_HOSPITAL => 'Hospital',
            self::PROVIDER_CLINIC => 'Clinic',
            self::PROVIDER_PHARMACY => 'Pharmacy',
            self::PROVIDER_LABORATORY => 'Laboratory',
            self::PROVIDER_DIAGNOSTIC_CENTER => 'Diagnostic Center',
        ];
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PAID => 'Paid',
        ];
    }
}
