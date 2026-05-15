<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $fillable = [
        'clinical_consultation_id',
        'prescription_number',
        'status',
        'prescription_date'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'prescription_date' => 'date',
    ];

    public function consultation()
    {
        return $this->belongsTo(ClinicalConsultation::class, 'clinical_consultation_id');
    }

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class, 'prescription_id');
    }
}
