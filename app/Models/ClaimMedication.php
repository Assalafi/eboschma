<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimMedication extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'medication_name',
        'cost',
        'frequency',
        'days',
        'claimed_amount',
        'notes'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'claimed_amount' => 'decimal:2',
        'frequency' => 'integer',
        'days' => 'integer'
    ];

    /**
     * Get the claim that owns the medication.
     */
    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    /**
     * Calculate claimed amount based on cost, frequency, and days.
     */
    public function calculateClaimedAmount()
    {
        $this->claimed_amount = $this->cost * $this->frequency * $this->days;
        return $this->claimed_amount;
    }
}
