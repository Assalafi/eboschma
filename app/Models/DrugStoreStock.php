<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DrugStoreStock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'drug_id',
        'program_id',
        'batch_number',
        'expiry_date',
        'quantity_received',
        'quantity_remaining',
        'quantity_dispensed',
        'unit_cost',
        'supplier',
        'notes',
        'status',
        'stocked_by',
        'stocked_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity_received' => 'integer',
        'quantity_remaining' => 'integer',
        'quantity_dispensed' => 'integer',
        'unit_cost' => 'decimal:2',
        'stocked_at' => 'datetime',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

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
    const STATUS_ACTIVE = 'active';
    const STATUS_DEPLETED = 'depleted';
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

    public function stockedBy()
    {
        return $this->belongsTo(User::class, 'stocked_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('quantity_remaining', '>', 0);
    }

    public function scopeByDrug($query, $drugId)
    {
        return $query->where('drug_id', $drugId);
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expiry_date', '>', now());
    }

    // Accessors
    public function getFormattedUnitCostAttribute()
    {
        return '₦' . number_format($this->unit_cost, 2);
    }

    public function getFormattedTotalValueAttribute()
    {
        return '₦' . number_format($this->quantity_remaining * $this->unit_cost, 2);
    }

    public function getTotalValueAttribute()
    {
        return $this->quantity_remaining * $this->unit_cost;
    }

    public function getExpiryStatusAttribute()
    {
        $days = now()->diffInDays($this->expiry_date, false);

        if ($days < 0) {
            return ['status' => 'expired', 'badge' => '<span class="badge bg-red">Expired</span>', 'text' => 'Expired'];
        } elseif ($days <= 30) {
            return ['status' => 'near-expiry', 'badge' => '<span class="badge bg-orange">Expires Soon</span>', 'text' => 'Expires in ' . $days . ' days'];
        } else {
            return ['status' => 'good', 'badge' => '<span class="badge bg-green">Good</span>', 'text' => 'Expires in ' . $days . ' days'];
        }
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_ACTIVE => '<span class="badge bg-success">Active</span>',
            self::STATUS_DEPLETED => '<span class="badge bg-secondary">Depleted</span>',
            self::STATUS_EXPIRED => '<span class="badge bg-danger">Expired</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    // Methods
    public function deductQuantity($quantity)
    {
        if ($quantity > $this->quantity_remaining) {
            throw new \Exception("Insufficient stock in batch {$this->batch_number}. Available: {$this->quantity_remaining}, Requested: {$quantity}");
        }

        $this->quantity_remaining -= $quantity;
        $this->quantity_dispensed += $quantity;

        if ($this->quantity_remaining == 0) {
            $this->status = self::STATUS_DEPLETED;
        }

        $this->save();
        return $this;
    }

    // Static helpers
    public static function getAvailableQuantity($drugId)
    {
        return self::where('drug_id', $drugId)
            ->where('status', self::STATUS_ACTIVE)
            ->where('quantity_remaining', '>', 0)
            ->where('expiry_date', '>', now())
            ->sum('quantity_remaining');
    }

    public static function getAvailableBatches($drugId)
    {
        return self::where('drug_id', $drugId)
            ->where('status', self::STATUS_ACTIVE)
            ->where('quantity_remaining', '>', 0)
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    public static function deductFromStore($drugId, $quantity)
    {
        $remaining = $quantity;
        $batches = self::getAvailableBatches($drugId);

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $deduct = min($remaining, $batch->quantity_remaining);
            $batch->deductQuantity($deduct);
            $remaining -= $deduct;
        }

        if ($remaining > 0) {
            throw new \Exception("Insufficient store stock. Short by {$remaining} units.");
        }

        return true;
    }

    public static function getStoreSummary()
    {
        return self::selectRaw('
                drug_id,
                SUM(quantity_received) as total_received,
                SUM(quantity_remaining) as total_remaining,
                SUM(quantity_dispensed) as total_dispensed,
                COUNT(*) as total_batches,
                SUM(CASE WHEN status = "active" AND quantity_remaining > 0 AND expiry_date > NOW() THEN quantity_remaining ELSE 0 END) as available_qty,
                SUM(CASE WHEN expiry_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) AND expiry_date > NOW() AND quantity_remaining > 0 THEN quantity_remaining ELSE 0 END) as near_expiry_qty,
                SUM(CASE WHEN expiry_date <= NOW() THEN quantity_remaining ELSE 0 END) as expired_qty,
                MIN(CASE WHEN status = "active" AND quantity_remaining > 0 AND expiry_date > NOW() THEN unit_cost END) as min_unit_cost,
                MAX(CASE WHEN status = "active" AND quantity_remaining > 0 AND expiry_date > NOW() THEN unit_cost END) as max_unit_cost
            ')
            ->groupBy('drug_id')
            ->get();
    }
}
