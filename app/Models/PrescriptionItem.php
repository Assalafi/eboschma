<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id',
        'drug_id',
        'dosage',
        'frequency',
        'duration',
        'quantity',
        'instructions',
        'dispensing_status'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class, 'drug_id');
    }

    public function dispensations()
    {
        return $this->hasMany(PharmacyDispensation::class, 'prescription_item_id');
    }
}
