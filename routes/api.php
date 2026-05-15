<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BeneficiaryApiController;
use App\Http\Controllers\Api\CivilServantApiController;
use App\Http\Controllers\Api\OcrController;
use App\Http\Controllers\Api\BackgroundRemovalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Mobile App API Routes
require __DIR__.'/api_mobile.php';

// NIN Uniqueness Verification (for beneficiary mobile app - no auth required)
Route::post('/mobile/verify-nin-uniqueness', [BeneficiaryApiController::class, 'verifyNinUniqueness']);

// OCR API Routes (for web platform - no auth required)
Route::prefix('ocr')->group(function () {
    Route::post('/verify-nin', [OcrController::class, 'verifyNin']);
    Route::post('/test', [OcrController::class, 'testOcr']); // For testing OCR functionality
});

// Background Removal API Routes (for web platform - no auth required)
Route::prefix('background-removal')->group(function () {
    Route::post('/remove', [BackgroundRemovalController::class, 'removeBackground']);
    Route::get('/test', [BackgroundRemovalController::class, 'test']); // For testing service availability
});

// Facilities API Route - separate from beneficiaries to avoid conflicts

// Beneficiary API Routes
Route::prefix('beneficiaries')->group(function () {

Route::get('/facilities', [BeneficiaryApiController::class, 'facilities']);
    // Search and filter endpoints - specific routes before parameterized ones
    Route::get('/search/{query}', [BeneficiaryApiController::class, 'search']);
    Route::get('/dp-search/{dpNo}', [BeneficiaryApiController::class, 'searchByDpNo']);
    Route::get('/filter/category/{category}', [BeneficiaryApiController::class, 'filterByCategory']);
    Route::get('/filter/status/{status}', [BeneficiaryApiController::class, 'filterByStatus']);
    Route::get('/filter/facility/{facilityId}', [BeneficiaryApiController::class, 'filterByFacility']);
    
    // Public routes (if needed)
    Route::get('/', [BeneficiaryApiController::class, 'index']);
    Route::post('/', [BeneficiaryApiController::class, 'store']);
    
    // Parameterized routes - MUST be after specific routes
    Route::get('/{beneficiary}', [BeneficiaryApiController::class, 'show']);
    Route::put('/{beneficiary}', [BeneficiaryApiController::class, 'update']);
    Route::patch('/{beneficiary}', [BeneficiaryApiController::class, 'update']);
    Route::delete('/{beneficiary}', [BeneficiaryApiController::class, 'destroy']);
    
    // Additional utility endpoints
    Route::get('/{beneficiary}/children', [BeneficiaryApiController::class, 'children']);
    Route::get('/{beneficiary}/spouse', [BeneficiaryApiController::class, 'spouse']);
    
    // File upload endpoints
    Route::post('/{beneficiary}/photo', [BeneficiaryApiController::class, 'uploadPhoto']);
    Route::post('/{beneficiary}/spouse/photo', [BeneficiaryApiController::class, 'uploadSpousePhoto']);
    Route::post('/{beneficiary}/children/{child}/photo', [BeneficiaryApiController::class, 'uploadChildPhoto']);
    Route::post('/{beneficiary}/children/{child}/birth-certificate', [BeneficiaryApiController::class, 'uploadChildBirthCertificate']);
});

// Civil Servant Authentication API Routes
Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/verify', [CivilServantApiController::class, 'verify']);
    Route::post('/create-account', [CivilServantApiController::class, 'createAccount']);
    Route::post('/login', [CivilServantApiController::class, 'login']);
    Route::post('/verify-for-reset', [CivilServantApiController::class, 'verifyForReset']);
    Route::post('/reset-password', [CivilServantApiController::class, 'resetPassword']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [CivilServantApiController::class, 'profile']);
        Route::get('/dashboard', [CivilServantApiController::class, 'dashboard']);
        Route::post('/logout', [CivilServantApiController::class, 'logout']);
    });
});
