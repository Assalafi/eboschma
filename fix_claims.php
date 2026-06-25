<?php
use App\Models\FacilityClaim;

$claims = FacilityClaim::with('services')->get();
$fixed = 0;
foreach ($claims as $claim) {
    $servicesAmount = 0;
    $laboratoryAmount = 0;
    
    foreach ($claim->services as $service) {
        $amount = $service->total_price;
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
        
        if ($isLab) {
            $laboratoryAmount += $amount;
        } else {
            $servicesAmount += $amount;
        }
    }
    
    if ($claim->laboratory_amount != $laboratoryAmount || $claim->services_amount != $servicesAmount) {
        $claim->laboratory_amount = $laboratoryAmount;
        $claim->services_amount = $servicesAmount;
        // total_amount is unchanged since we just split it
        $claim->save();
        $fixed++;
    }
}
echo "Fixed {$fixed} claims.\n";
