<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DrugStockRequestItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'stock_request_id',
        'drug_id',
        'quantity_requested',
        'estimated_cost',
        'priority',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
    ];

    /**
     * Get the stock request that owns the item.
     */
    public function stockRequest()
    {
        return $this->belongsTo(DrugStockRequest::class, 'stock_request_id');
    }

    /**
     * Get the drug for this request item.
     */
    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    /**
     * Get formatted quantity.
     */
    public function getFormattedQuantityAttribute()
    {
        return number_format($this->quantity_requested);
    }

    /**
     * Get formatted estimated cost.
     */
    public function getFormattedEstimatedCostAttribute()
    {
        return '₦' . number_format($this->estimated_cost, 2);
    }

    /**
     * Get priority badge HTML.
     */
    public function getPriorityBadgeAttribute()
    {
        $badges = [
            'low' => '<span class="badge bg-blue">Low</span>',
            'medium' => '<span class="badge bg-warning">Medium</span>',
            'high' => '<span class="badge bg-orange">High</span>',
            'urgent' => '<span class="badge bg-danger">Urgent</span>',
        ];

        return $badges[$this->priority] ?? $badges['medium'];
    }
}
