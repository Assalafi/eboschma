<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityClaimDiagnosis extends Model
{
    protected $fillable = [
        'facility_claim_id',
        'diagnosis_id',
        'icd_code',
        'diagnosis_type',
        'diagnosis_description'
    ];

    public function claim()
    {
        return $this->belongsTo(FacilityClaim::class, 'facility_claim_id');
    }

    public function diagnosis()
    {
        return $this->belongsTo(ClinicalDiagnosis::class, 'diagnosis_id');
    }
}
