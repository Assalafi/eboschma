<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DrugStock extends Model
{
    use HasFactory, SoftDeletes;

    
    protected $fillable = [
        'drug_id',
        'facility_id',
        'program_id',
        'batch_number',
        'expiry_date',
        'quantity_received',
        'quantity_remaining',
        'unit_cost',
        'supplier',
        'notes',
        'status',
        'request_id',
        'approved_by',
        'approved_at',
        'dispensed_by',
        'dispensed_at',
        'rejection_reason',
        'stocked_by',
        'stocked_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity_received' => 'integer',
        'quantity_remaining' => 'integer',
        'unit_cost' => 'decimal:2',
        'approved_at' => 'datetime',
        'dispensed_at' => 'datetime',
        'stocked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'expiry_date',
        'approved_at',
        'dispensed_at',
        'stocked_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DISPENSED = 'dispensed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    // Relationships
    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function request()
    {
        return $this->belongsTo(DrugStockRequest::class, 'request_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dispensedBy()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function stockedBy()
    {
        return $this->belongsTo(User::class, 'stocked_by');
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

    public function scopeDispensed($query)
    {
        return $query->where('status', self::STATUS_DISPENSED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
                    ->orWhere('status', self::STATUS_EXPIRED);
    }

    public function scopeNearExpiry($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now());
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity_remaining', '>', 0)
                    ->where('status', self::STATUS_DISPENSED);
    }

    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('quantity_remaining', '>', 0)
                    ->where('quantity_remaining', '<=', $threshold)
                    ->where('status', self::STATUS_DISPENSED);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_remaining', '=', 0)
                    ->orWhere('status', self::STATUS_EXPIRED);
    }

    public function scopeByFacility($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }

    public function scopeByDrug($query, $drugId)
    {
        return $query->where('drug_id', $drugId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_PENDING => '<span class="badge bg-yellow-lt text-yellow">Pending</span>',
            self::STATUS_APPROVED => '<span class="badge bg-green-lt text-green">Approved</span>',
            self::STATUS_DISPENSED => '<span class="badge bg-blue-lt text-blue">Dispensed</span>',
            self::STATUS_REJECTED => '<span class="badge bg-red-lt text-red">Rejected</span>',
            self::STATUS_EXPIRED => '<span class="badge bg-red-lt text-red">Expired</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getFormattedUnitCostAttribute()
    {
        return '₦' . number_format($this->unit_cost, 2);
    }

    public function getFormattedQuantityReceivedAttribute()
    {
        return number_format($this->quantity_received);
    }

    public function getFormattedQuantityRemainingAttribute()
    {
        return number_format($this->quantity_remaining);
    }

    public function getTotalValueAttribute()
    {
        return $this->quantity_remaining * $this->unit_cost;
    }

    public function getFormattedTotalValueAttribute()
    {
        return '₦' . number_format($this->getTotalValueAttribute(), 2);
    }

    public function getDaysUntilExpiryAttribute()
    {
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getExpiryStatusAttribute()
    {
        $days = $this->getDaysUntilExpiryAttribute();
        
        if ($days < 0) {
            return ['status' => 'expired', 'badge' => '<span class="badge bg-red">Expired</span>', 'text' => 'Expired'];
        } elseif ($days <= 30) {
            return ['status' => 'near-expiry', 'badge' => '<span class="badge bg-orange">Expires Soon</span>', 'text' => 'Expires in ' . $days . ' days'];
        } else {
            return ['status' => 'good', 'badge' => '<span class="badge bg-green">Good</span>', 'text' => 'Expires in ' . $days . ' days'];
        }
    }

    // Methods
    public function isExpired()
    {
        return $this->expiry_date->isPast();
    }

    public function isNearExpiry($days = 30)
    {
        return $this->expiry_date->lte(now()->addDays($days)) && !$this->isExpired();
    }

    public function isExpiringSoon($days = 30)
    {
        return $this->isNearExpiry($days);
    }

    public function isInStock()
    {
        return $this->quantity_remaining > 0 && $this->status === self::STATUS_DISPENSED;
    }

    public function isLowStock($threshold = 10)
    {
        return $this->quantity_remaining > 0 && $this->quantity_remaining <= $threshold && $this->status === self::STATUS_DISPENSED;
    }

    public function isOutOfStock()
    {
        return $this->quantity_remaining == 0 || $this->status === self::STATUS_EXPIRED;
    }

    public function consumeQuantity($quantity, $reason = 'Dispensed')
    {
        if ($quantity > $this->quantity_remaining) {
            throw new \Exception('Insufficient stock available');
        }

        $this->quantity_remaining -= $quantity;
        
        if ($this->quantity_remaining == 0) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . 
                         "Stock depleted on " . now()->format('Y-m-d H:i:s') . ". Reason: " . $reason;
        }

        $this->save();

        return $this;
    }

    public function adjustQuantity($newQuantity, $reason = 'Stock adjustment')
    {
        if ($newQuantity < 0) {
            throw new \Exception('Quantity cannot be negative');
        }

        $oldQuantity = $this->quantity_remaining;
        $this->quantity_remaining = $newQuantity;
        
        $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . 
                     "Stock adjusted on " . now()->format('Y-m-d H:i:s') . 
                     " from {$oldQuantity} to {$newQuantity}. Reason: " . $reason;
        
        $this->save();

        return $this;
    }

    // Static methods
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_DISPENSED => 'Dispensed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
        ];
    }

    public static function getStockLevels()
    {
        return [
            'in_stock' => 'In Stock',
            'low_stock' => 'Low Stock',
            'out_of_stock' => 'Out of Stock',
            'expired' => 'Expired',
            'near_expiry' => 'Near Expiry',
        ];
    }

    // Query helpers
    public static function getFacilityStockSummary($facilityId)
    {
        return self::byFacility($facilityId)
            ->selectRaw('
                drug_id,
                SUM(CASE WHEN status = ? AND quantity_remaining > 0 THEN quantity_remaining ELSE 0 END) as total_quantity,
                SUM(CASE WHEN status = ? AND quantity_remaining > 0 AND expiry_date <= ? THEN quantity_remaining ELSE 0 END) as near_expiry_quantity,
                SUM(CASE WHEN status = ? AND quantity_remaining > 0 AND expiry_date > ? THEN quantity_remaining ELSE 0 END) as good_quantity,
                SUM(CASE WHEN status = ? OR quantity_remaining = 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                COUNT(*) as total_batches
            ', [
                self::STATUS_DISPENSED,
                self::STATUS_DISPENSED,
                now()->addDays(30),
                self::STATUS_DISPENSED,
                now()->addDays(30),
                self::STATUS_EXPIRED
            ])
            ->groupBy('drug_id')
            ->with('drug')
            ->get();
    }
}
