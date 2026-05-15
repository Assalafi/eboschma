<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Encounter extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id',
        'facility_id',
        'program_id',
        'visit_date',
        'nature_of_visit',
        'mode_of_entry',
        'reason_for_visit',
        'officer_in_charge_id',
        'status',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
    ];

    // Status constants
    const STATUS_REGISTERED = 'Registered';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_CANCELLED = 'Cancelled';

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function officerInCharge()
    {
        return $this->belongsTo(User::class, 'officer_in_charge_id');
    }

    public function consultations()
    {
        return $this->hasMany(ClinicalConsultation::class);
    }

    public function actions()
    {
        return $this->hasMany(EncounterAction::class);
    }

    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class);
    }

    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class);
    }

    public function admission()
    {
        return $this->hasOne(Admission::class)->where('is_active', true);
    }

    public function facilityClaim()
    {
        return $this->hasOne(FacilityClaim::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeWithoutClaim($query)
    {
        return $query->doesntHave('facilityClaim');
    }

    public function scopeReadyForClaim($query)
    {
        return $query->completed()->withoutClaim();
    }
}
