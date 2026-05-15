<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityClaimMedication extends Model
{
    protected $fillable = [
        'facility_claim_id',
        'prescription_item_id',
        'drug_name',
        'dosage',
        'quantity',
        'days',
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

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class, 'prescription_item_id');
    }
}
