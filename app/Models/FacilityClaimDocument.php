<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityClaimDocument extends Model
{
    protected $fillable = [
        'facility_claim_id',
        'document_type',
        'document_name',
        'file_path',
        'file_size',
        'uploaded_by'
    ];

    public function claim()
    {
        return $this->belongsTo(FacilityClaim::class, 'facility_claim_id');
    }
}
