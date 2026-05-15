<?php

use Illuminate\Support\Facades\Route;
use App\Models\Drug;
use App\Models\DrugStock;

Route::get('/debug-drugs', function () {
    echo "Database: " . config('database.default') . "<br>";
    echo "Drugs count: " . Drug::count() . "<br>";
    
    $drugs = Drug::take(5)->get();
    foreach ($drugs as $drug) {
        echo "Drug: " . $drug->name . " - Facility: " . $drug->facility_id . " - Status: " . $drug->status . " - Price: " . $drug->unit_price . "<br>";
    }
    
    echo "<br>DrugStocks count: " . DrugStock::count() . "<br>";
    
    $user = auth()->guard('web')->user();
    if ($user) {
        echo "Current user facility: " . $user->facility_id . "<br>";
        
        $facilityDrugs = Drug::where('facility_id', $user->facility_id)->get();
        echo "Facility drugs count: " . $facilityDrugs->count() . "<br>";
    }
    
    echo "<br>All drug columns: " . implode(', ', array_keys(Drug::first()->getAttributes())) . "<br>";
});
