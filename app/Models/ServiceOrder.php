<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    protected $fillable = [
        'encounter_id',
        'patient_id',
        'facility_id',
        'ordered_by',
        'order_number',
        'status'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function encounter()
    {
        return $this->belongsTo(Encounter::class, 'encounter_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    public function orderedBy()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function serviceOrderItems()
    {
        return $this->hasMany(ServiceOrderItem::class, 'service_order_id');
    }
}
