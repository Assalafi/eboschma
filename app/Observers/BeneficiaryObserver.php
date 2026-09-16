<?php

namespace App\Observers;

use App\Models\Beneficiary;
use App\Models\BeneficiaryFacilityChange;
use Illuminate\Support\Facades\Log;

class BeneficiaryObserver
{
    /**
     * Record a facility change whenever a beneficiary's facility_id or
     * alt_facility_id is modified (mobile app, admin panel, imports, etc.).
     */
    public function updating(Beneficiary $beneficiary): void
    {
        $facilityChanged = $beneficiary->isDirty('facility_id');
        $altFacilityChanged = $beneficiary->isDirty('alt_facility_id');

        if (!$facilityChanged && !$altFacilityChanged) {
            return;
        }

        try {
            BeneficiaryFacilityChange::create([
                'beneficiary_id' => $beneficiary->id,
                'boschma_no' => $beneficiary->boschma_no,
                'old_facility_id' => $beneficiary->getOriginal('facility_id'),
                'new_facility_id' => $beneficiary->facility_id,
                'old_alt_facility_id' => $beneficiary->getOriginal('alt_facility_id'),
                'new_alt_facility_id' => $beneficiary->alt_facility_id,
                'changed_by' => auth('staff')->id() ?? auth()->id(),
                'changed_via' => $this->detectSource(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Never let audit logging break the actual update
            Log::error('Failed to record beneficiary facility change: ' . $e->getMessage(), [
                'beneficiary_id' => $beneficiary->id,
            ]);
        }
    }

    /**
     * Best-effort detection of which interface triggered the change.
     */
    private function detectSource(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, 'mobile')) {
            return 'mobile';
        }
        if ($routeName && str_contains($routeName, 'beneficiaries')) {
            return 'admin';
        }

        return $routeName ?: 'unknown';
    }
}
