<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeneficiaryFacilityChange extends Model
{
    protected $fillable = [
        'beneficiary_id',
        'boschma_no',
        'old_facility_id',
        'new_facility_id',
        'old_alt_facility_id',
        'new_alt_facility_id',
        'changed_by',
        'changed_via',
        'ip_address',
        'user_agent',
    ];

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id');
    }

    public function oldFacility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'old_facility_id');
    }

    public function newFacility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'new_facility_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'changed_by');
    }

    /**
     * Human-readable summary of the change.
     */
    public function getSummaryAttribute(): string
    {
        $from = $this->oldFacility->name ?? ('Facility ' . ($this->old_facility_id ?? 'N/A'));
        $to = $this->newFacility->name ?? ('Facility ' . ($this->new_facility_id ?? 'N/A'));

        return $from . ' → ' . $to;
    }
}
