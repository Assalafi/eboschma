<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityClaimConsultation extends Model
{
    protected $fillable = [
        'facility_claim_id',
        'consultation_id',
        'diagnosis_code',
        'diagnosis_description',
        'consultation_notes',
        'amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function claim()
    {
        return $this->belongsTo(FacilityClaim::class, 'facility_claim_id');
    }

    public function consultation()
    {
        return $this->belongsTo(ClinicalConsultation::class, 'consultation_id');
    }
}
