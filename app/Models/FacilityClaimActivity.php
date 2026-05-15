<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityClaimActivity extends Model
{
    protected $fillable = [
        'facility_claim_id',
        'encounter_action_id',
        'activity_type',
        'activity_description',
        'performed_at'
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function claim()
    {
        return $this->belongsTo(FacilityClaim::class, 'facility_claim_id');
    }

    public function encounterAction()
    {
        return $this->belongsTo(EncounterAction::class, 'encounter_action_id');
    }
}
