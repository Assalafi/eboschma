<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyDispensation extends Model
{
    protected $fillable = [
        'prescription_item_id',
        'dispensed_by',
        'quantity_dispensed',
        'cost_of_medication',
        'dispensation_date',
        'status'
    ];

    protected $casts = [
        'cost_of_medication' => 'decimal:2',
        'dispensation_date' => 'date',
    ];

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class, 'prescription_item_id');
    }

    public function dispensedBy()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }
}
