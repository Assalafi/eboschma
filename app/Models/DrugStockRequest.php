<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DrugStockRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'facility_id',
        'program_id',
        'drug_id',
        'quantity_requested',
        'estimated_cost',
        'reason',
        'notes',
        'status',
        'priority',
        'requested_by',
        'approved_by',
        'dispensed_by',
        'requested_at',
        'approved_at',
        'dispensed_at',
        'rejection_reason',
        'out_of_stock_items',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'quantity_requested' => 'integer',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'dispensed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'out_of_stock_items' => 'array',
    ];

    protected $dates = [
        'requested_at',
        'approved_at',
        'dispensed_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DISPENSED = 'dispensed';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Relationships
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the drug for this request (for single requests).
     */
    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    /**
     * Get the items for this bulk request.
     */
    public function items()
    {
        return $this->hasMany(DrugStockRequestItem::class, 'stock_request_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dispensedBy()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function drugStocks()
    {
        return $this->hasMany(DrugStock::class, 'request_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeDispensed($query)
    {
        return $query->where('status', self::STATUS_DISPENSED);
    }

    public function scopeByFacility($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeForBoschmaAdmin($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark">Pending</span>',
            self::STATUS_APPROVED => '<span class="badge bg-success text-white">Approved</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger text-white">Rejected</span>',
            self::STATUS_DISPENSED => '<span class="badge bg-info text-white">Dispensed</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getPriorityBadgeAttribute()
    {
        $badges = [
            self::PRIORITY_LOW => '<span class="badge bg-secondary text-white">Low</span>',
            self::PRIORITY_MEDIUM => '<span class="badge bg-primary text-white">Medium</span>',
            self::PRIORITY_HIGH => '<span class="badge" style="background-color: #fd7e14; color: white;">High</span>',
            self::PRIORITY_URGENT => '<span class="badge bg-danger text-white">Urgent</span>',
        ];

        return $badges[$this->priority] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getFormattedEstimatedCostAttribute()
    {
        return '₦' . number_format($this->estimated_cost, 2);
    }

    public function getFormattedQuantityAttribute()
    {
        return number_format($this->quantity_requested);
    }

    // Methods
    public function canBeEdited()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeApproved()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeRejected()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeDispensed()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function approve($approvedBy, $notes = null)
    {
        if (!$this->canBeApproved()) {
            throw new \Exception('Request cannot be approved');
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approvedBy;
        $this->approved_at = now();
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "Approval notes: " . $notes;
        }
        $this->save();

        return $this;
    }

    public function reject($rejectedBy, $reason)
    {
        if (!$this->canBeRejected()) {
            throw new \Exception('Request cannot be rejected');
        }

        $this->status = self::STATUS_REJECTED;
        $this->rejection_reason = $reason;
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "Rejection notes: " . $reason;
        }
        $this->save();

        return $this;
    }

    public function dispense($dispensedBy, $drugStockData)
    {
        if (!$this->canBeDispensed()) {
            throw new \Exception('Request cannot be dispensed');
        }

        \DB::transaction(function () use ($dispensedBy, $drugStockData) {
            // Create drug stock records
            foreach ($drugStockData as $stockData) {
                DrugStock::create([
                    'drug_id' => $this->drug_id,
                    'facility_id' => $this->facility_id,
                    'request_id' => $this->id,
                    'batch_number' => $stockData['batch_number'],
                    'expiry_date' => $stockData['expiry_date'],
                    'quantity_received' => $stockData['quantity_received'],
                    'quantity_remaining' => $stockData['quantity_received'],
                    'unit_cost' => $stockData['unit_cost'],
                    'supplier' => $stockData['supplier'],
                    'notes' => $stockData['notes'] ?? null,
                    'stocked_by' => $dispensedBy,
                    'stocked_at' => now(),
                    'status' => 'dispensed',
                    'dispensed_by' => $dispensedBy,
                    'dispensed_at' => now(),
                ]);
            }

            // Update request status
            $this->status = self::STATUS_DISPENSED;
            $this->dispensed_by = $dispensedBy;
            $this->dispensed_at = now();
            $this->save();
        });

        return $this;
    }

    // Static methods
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_DISPENSED => 'Dispensed',
        ];
    }

    public static function getPriorities()
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }
}
