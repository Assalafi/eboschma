<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalConsultation extends Model
{
    protected $fillable = [
        'encounter_id',
        'doctor_id',
        'presenting_complaints',
        'history_of_present_illness',
        'physical_examination',
        'investigation_required',
        'investigation_note',
        'clinical_note',
        'status'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'investigation_required' => 'boolean',
    ];

    public function encounter()
    {
        return $this->belongsTo(Encounter::class, 'encounter_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function diagnoses()
    {
        return $this->hasMany(ClinicalDiagnosis::class, 'clinical_consultation_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'clinical_consultation_id');
    }
}
