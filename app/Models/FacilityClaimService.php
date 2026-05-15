<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityClaimService extends Model
{
    protected $fillable = [
        'facility_claim_id',
        'service_order_item_id',
        'service_type',
        'service_name',
        'service_description',
        'frequency',
        'unit_price',
        'total_price',
        'notes'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function claim()
    {
        return $this->belongsTo(FacilityClaim::class, 'facility_claim_id');
    }

    public function serviceOrderItem()
    {
        return $this->belongsTo(ServiceOrderItem::class, 'service_order_item_id');
    }
}
