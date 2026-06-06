<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference',
        'reference_type',
        'drug_name',
        'drug_quantity',
        'drug_cost',
        'description',
        'performed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'drug_cost' => 'decimal:2',
        'drug_quantity' => 'integer',
    ];

    // Type constants
    const TYPE_FUNDING = 'funding';
    const TYPE_DRUG_STOCK_DEDUCTION = 'drug_stock_deduction';
    const TYPE_DISPENSATION_RETURN = 'dispensation_return';
    const TYPE_ADJUSTMENT = 'adjustment';

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
    public function wallet()
    {
        return $this->belongsTo(FacilityWallet::class, 'wallet_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        $prefix = $this->amount >= 0 ? '+' : '';
        return $prefix . '₦' . number_format(abs($this->amount), 2);
    }

    public function getAmountColorAttribute()
    {
        return $this->amount >= 0 ? 'text-success' : 'text-danger';
    }

    public function getTypeBadgeAttribute()
    {
        $badges = [
            self::TYPE_FUNDING => '<span class="badge bg-success">Funding</span>',
            self::TYPE_DRUG_STOCK_DEDUCTION => '<span class="badge bg-danger">Stock Deduction</span>',
            self::TYPE_DISPENSATION_RETURN => '<span class="badge bg-info">10% Return</span>',
            self::TYPE_ADJUSTMENT => '<span class="badge bg-warning">Adjustment</span>',
        ];

        return $badges[$this->type] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getTypeIconAttribute()
    {
        $icons = [
            self::TYPE_FUNDING => 'ti-wallet',
            self::TYPE_DRUG_STOCK_DEDUCTION => 'ti-minus',
            self::TYPE_DISPENSATION_RETURN => 'ti-receipt-refund',
            self::TYPE_ADJUSTMENT => 'ti-settings',
        ];

        return $icons[$this->type] ?? 'ti-help';
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCredits($query)
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeDebits($query)
    {
        return $query->where('amount', '<', 0);
    }

    // Static methods
    public static function getTypes()
    {
        return [
            self::TYPE_FUNDING => 'Funding',
            self::TYPE_DRUG_STOCK_DEDUCTION => 'Drug Stock Deduction',
            self::TYPE_DISPENSATION_RETURN => 'Dispensation Return (10%)',
            self::TYPE_ADJUSTMENT => 'Manual Adjustment',
        ];
    }
}
