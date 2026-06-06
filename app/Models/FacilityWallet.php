<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FacilityWallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'facility_id',
        'balance',
        'total_funded',
        'total_deducted',
        'total_returned',
        'bank_name',
        'account_number',
        'account_name',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_funded' => 'decimal:2',
        'total_deducted' => 'decimal:2',
        'total_returned' => 'decimal:2',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CLOSED = 'closed';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByFacility($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }

    // Accessors
    public function getFormattedBalanceAttribute()
    {
        return '₦' . number_format($this->balance, 2);
    }

    public function getFormattedTotalFundedAttribute()
    {
        return '₦' . number_format($this->total_funded, 2);
    }

    public function getFormattedTotalDeductedAttribute()
    {
        return '₦' . number_format($this->total_deducted, 2);
    }

    public function getFormattedTotalReturnedAttribute()
    {
        return '₦' . number_format($this->total_returned, 2);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_ACTIVE => '<span class="badge bg-success">Active</span>',
            self::STATUS_SUSPENDED => '<span class="badge bg-warning">Suspended</span>',
            self::STATUS_CLOSED => '<span class="badge bg-danger">Closed</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    // Methods
    public function hasSufficientBalance($amount)
    {
        return $this->status === self::STATUS_ACTIVE && $this->balance >= $amount;
    }

    /**
     * Fund the wallet (credit).
     */
    public function fund($amount, $performedBy, $description = null)
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount;
        $this->total_funded += $amount;
        $this->save();

        return $this->transactions()->create([
            'type' => WalletTransaction::TYPE_FUNDING,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'description' => $description ?? 'Wallet funded',
            'performed_by' => $performedBy,
        ]);
    }

    /**
     * Deduct for drug stock request (debit).
     */
    public function deductForStockRequest($amount, $stockRequestId, $performedBy, $description = null)
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception("Insufficient wallet balance. Available: ₦" . number_format($this->balance, 2) . ", Required: ₦" . number_format($amount, 2));
        }

        $balanceBefore = $this->balance;
        $this->balance -= $amount;
        $this->total_deducted += $amount;
        $this->save();

        return $this->transactions()->create([
            'type' => WalletTransaction::TYPE_DRUG_STOCK_DEDUCTION,
            'amount' => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'reference' => $stockRequestId,
            'reference_type' => 'DrugStockRequest',
            'description' => $description ?? 'Drug stock request approved',
            'performed_by' => $performedBy,
        ]);
    }

    /**
     * Return 10% from dispensation (credit).
     */
    public function returnFromDispensation($amount, $dispensationId, $drugName, $drugQuantity, $drugCost, $performedBy = null)
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount;
        $this->total_returned += $amount;
        $this->save();

        return $this->transactions()->create([
            'type' => WalletTransaction::TYPE_DISPENSATION_RETURN,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'reference' => $dispensationId,
            'reference_type' => 'PharmacyDispensation',
            'drug_name' => $drugName,
            'drug_quantity' => $drugQuantity,
            'drug_cost' => $drugCost,
            'description' => "10% return from dispensing {$drugQuantity} unit(s) of {$drugName}",
            'performed_by' => $performedBy,
        ]);
    }

    // Static helpers
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_SUSPENDED => 'Suspended',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    /**
     * Get or create wallet for a facility.
     */
    public static function getForFacility($facilityId)
    {
        return self::where('facility_id', $facilityId)->first();
    }
}
