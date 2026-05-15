<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalDiagnosis extends Model
{
    protected $fillable = [
        'clinical_consultation_id',
        'icd_code_id',
        'diagnosis_type'
    ];

    public function consultation()
    {
        return $this->belongsTo(ClinicalConsultation::class, 'clinical_consultation_id');
    }

    public function icdCode()
    {
        return $this->belongsTo(IcdCode::class, 'icd_code_id');
    }
}
