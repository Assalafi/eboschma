<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileEnrollmentController;

/*
|--------------------------------------------------------------------------
| Mobile API Routes
|--------------------------------------------------------------------------
|
| API routes for the BOSCHMA Enumerators Mobile App
| 
*/

// Public routes (no authentication required)
Route::post('/mobile/login', [MobileAuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication
    Route::post('/mobile/logout', [MobileAuthController::class, 'logout']);
    Route::get('/mobile/profile', [MobileAuthController::class, 'profile']);
    Route::post('/mobile/update-password', [MobileAuthController::class, 'updatePassword']);
    
    // Programs & Facilities
    Route::get('/mobile/programs', [MobileEnrollmentController::class, 'getPrograms']);
    Route::get('/mobile/facilities', [MobileEnrollmentController::class, 'getFacilities']);
    Route::get('/mobile/civil-servants', [MobileEnrollmentController::class, 'getCivilServants']);
    Route::get('/mobile/spouses', [MobileEnrollmentController::class, 'getSpouses']);
    Route::get('/mobile/children', [MobileEnrollmentController::class, 'getChildren']);
    Route::get('/mobile/beneficiaries-list', [MobileEnrollmentController::class, 'getBeneficiariesList']);
    Route::get('/mobile/enumerators', [MobileEnrollmentController::class, 'getEnumerators']);
    
    // Search
    Route::get('/mobile/search', [MobileEnrollmentController::class, 'search']);
    
    // Verification
    Route::post('/mobile/verify-nin', [MobileEnrollmentController::class, 'verifyNin']);
    Route::post('/mobile/verify-dp', [MobileEnrollmentController::class, 'verifyDpNumber']);
    
    // Beneficiaries
    Route::get('/mobile/beneficiaries', [MobileEnrollmentController::class, 'getBeneficiaries']);
    Route::get('/mobile/beneficiaries/{id}', [MobileEnrollmentController::class, 'getBeneficiary']);
    Route::get('/mobile/beneficiaries/{id}/full', [MobileEnrollmentController::class, 'getBeneficiaryFull']); // Get full data for editing
    Route::post('/mobile/beneficiaries', [MobileEnrollmentController::class, 'createBeneficiary']);
    Route::post('/mobile/beneficiaries/upload', [MobileEnrollmentController::class, 'uploadBeneficiary']); // Upload with files
    Route::put('/mobile/beneficiaries/{id}', [MobileEnrollmentController::class, 'updateBeneficiary']);
    Route::put('/mobile/beneficiaries/{id}/update', [MobileEnrollmentController::class, 'updateBeneficiaryFull']); // Full update with CRUD
    Route::delete('/mobile/beneficiaries/{id}', [MobileEnrollmentController::class, 'deleteBeneficiary']);
    
    // Dashboard
    Route::get('/mobile/dashboard/stats', [MobileEnrollmentController::class, 'getDashboardStats']);
});
