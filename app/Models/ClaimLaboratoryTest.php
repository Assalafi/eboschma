<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimLaboratoryTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'test_name',
        'cost',
        'frequency',
        'claimed_amount',
        'notes'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'claimed_amount' => 'decimal:2',
        'frequency' => 'integer'
    ];

    /**
     * Get the claim that owns the laboratory test.
     */
    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    /**
     * Calculate claimed amount based on cost and frequency.
     */
    public function calculateClaimedAmount()
    {
        $this->claimed_amount = $this->cost * $this->frequency;
        return $this->claimed_amount;
    }
}
