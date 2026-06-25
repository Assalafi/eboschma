<?php
use App\Models\FacilityClaim;
use Illuminate\Support\Facades\DB;

$claims = FacilityClaim::with('services')->get();
$fixed = 0;
foreach ($claims as $claim) {
    $consultationAmount = 0;
    $servicesAmount = 0;
    $laboratoryAmount = 0;
    
    foreach ($claim->services as $service) {
        $amount = $service->total_price;
        
        $isLab = false;
        $isAdmin = false;
        
        // Check for admin charges
        if (in_array($service->service_type, ['specialist_review', 'nursing_care', 'bed_occupancy']) || str_contains(strtolower($service->service_name ?? ''), 'care') || str_contains(strtolower($service->service_name ?? ''), 'occupancy') || str_contains(strtolower($service->service_name ?? ''), 'review')) {
            // These are definitely admin charges based on how storeBillableItems creates them (service_order_item_id is null)
            if ($service->service_order_item_id === null) {
                $isAdmin = true;
            }
        }
        
        if (!$isAdmin) {
            // Check if service_type contains lab
            $isLab = stripos($service->service_type ?? '', 'lab') !== false;
            
            // If not, try to fetch the service item and category
            if (!$isLab && $service->serviceOrderItem && $service->serviceOrderItem->serviceItem) {
                $item = $service->serviceOrderItem->serviceItem;
                if ($item->serviceType) {
                    if (stripos($item->serviceType->name ?? '', 'lab') !== false) {
                        $isLab = true;
                    }
                    if (!$isLab && $item->serviceType->serviceCategory) {
                        if (stripos($item->serviceType->serviceCategory->name ?? '', 'lab') !== false) {
                            $isLab = true;
                        }
                    }
                }
            }
        }
        
        if ($isAdmin) {
            $consultationAmount += $amount;
        } elseif ($isLab) {
            $laboratoryAmount += $amount;
        } else {
            $servicesAmount += $amount;
        }
    }
    
    if ($claim->consultation_amount != $consultationAmount || $claim->laboratory_amount != $laboratoryAmount || $claim->services_amount != $servicesAmount) {
        $claim->consultation_amount = $consultationAmount;
        $claim->laboratory_amount = $laboratoryAmount;
        $claim->services_amount = $servicesAmount;
        // total_amount is unchanged since we just split it
        $claim->save();
        $fixed++;
    }
}
echo "Fixed {$fixed} claims for Admin Charges.\n";
