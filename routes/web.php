<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\StudentController;
// use App\Http\Controllers\CourseController;
// use App\Http\Controllers\SessionController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\CivilServantController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\EhrReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route::post('/update-session', [SessionController::class, 'updateSession']);

// ─── Twilio Public Webhook Routes (no auth — Twilio must reach these) ─────────
Route::post('/twilio/voice/inbound', [\App\Http\Controllers\TwilioVoiceController::class, 'inboundTwiml'])->name('twilio.voice.inbound');
Route::get('/twilio/voice/outbound-twiml', [\App\Http\Controllers\TwilioVoiceController::class, 'outboundTwiml'])->name('twilio.voice.outbound-twiml');
Route::post('/twilio/webhook/call-status', [\App\Http\Controllers\TwilioVoiceController::class, 'callStatusCallback'])->name('twilio.webhook.call-status');
Route::get('/twilio/lookup', [\App\Http\Controllers\TwilioVoiceController::class, 'lookupBeneficiary'])->name('twilio.lookup');
Route::post('/twilio/voice/client-outbound', [\App\Http\Controllers\TwilioVoiceController::class, 'clientOutboundTwiml'])->name('twilio.voice.client-outbound');
// ─────────────────────────────────────────────────────────────────────────────

// Protected Routes
Route::middleware(['auth:staff,web'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Facility routes (Protected by permissions)
    Route::prefix('facilities')->name('facilities.')->group(function () {
        Route::get('/', [App\Http\Controllers\FacilityController::class, 'index'])->name('index')->middleware('permission:facility.view,staff');
        Route::get('/create', [App\Http\Controllers\FacilityController::class, 'create'])->name('create')->middleware('permission:facility.create,staff');
        Route::post('/', [App\Http\Controllers\FacilityController::class, 'store'])->name('store')->middleware('permission:facility.create,staff');
        
        // Upload and bulk operations (must be before {facility} routes)
        Route::get('/upload/form', [App\Http\Controllers\FacilityController::class, 'uploadForm'])->name('upload.form')->middleware('permission:facility.create,staff');
        Route::post('/upload/excel', [App\Http\Controllers\FacilityController::class, 'uploadExcel'])->name('upload.excel')->middleware('permission:facility.create,staff');
        Route::get('/download/template', [App\Http\Controllers\FacilityController::class, 'downloadTemplate'])->name('download.template')->middleware('permission:facility.view,staff');
        Route::delete('/bulk-delete', [App\Http\Controllers\FacilityController::class, 'bulkDelete'])->name('bulk-delete')->middleware('permission:facility.delete,staff');
        Route::post('/bulk-delete', [App\Http\Controllers\FacilityController::class, 'bulkDelete'])->name('bulk-delete.post')->middleware('permission:facility.delete,staff');
        
        // Individual facility routes (must be after specific routes)
        Route::get('/{facility}', [App\Http\Controllers\FacilityController::class, 'show'])->name('show')->middleware('permission:facility.view,staff');
        Route::get('/{facility}/edit', [App\Http\Controllers\FacilityController::class, 'edit'])->name('edit')->middleware('permission:facility.edit,staff');
        Route::put('/{facility}', [App\Http\Controllers\FacilityController::class, 'update'])->name('update')->middleware('permission:facility.edit,staff');
        Route::delete('/{facility}', [App\Http\Controllers\FacilityController::class, 'destroy'])->name('destroy')->middleware('permission:facility.delete,staff');
    });
    
    // Drugs Management (Protected by permissions)
    Route::prefix('drugs')->name('drugs.')->group(function () {
        Route::get('/', [App\Http\Controllers\DrugsController::class, 'index'])->name('index')->middleware('auth:staff', 'permission:drugs.view,staff');
        Route::get('/create', [App\Http\Controllers\DrugsController::class, 'create'])->name('create')->middleware('permission:drugs.create,staff');
        Route::post('/', [App\Http\Controllers\DrugsController::class, 'store'])->name('store')->middleware('permission:drugs.create,staff');
        Route::get('/{drug}', [App\Http\Controllers\DrugsController::class, 'show'])->name('show')->middleware('auth:staff', 'permission:drugs.view,staff');
        Route::get('/{drug}/edit', [App\Http\Controllers\DrugsController::class, 'edit'])->name('edit')->middleware('permission:drugs.edit,staff');
        Route::put('/{drug}', [App\Http\Controllers\DrugsController::class, 'update'])->name('update')->middleware('permission:drugs.edit,staff');
        Route::delete('/{drug}', [App\Http\Controllers\DrugsController::class, 'destroy'])->name('destroy')->middleware('permission:drugs.delete,staff');
        
        // Bulk operations
        Route::get('/bulk/create', [App\Http\Controllers\DrugsController::class, 'bulkCreate'])->name('bulk.create')->middleware('permission:drugs.create,staff');
        Route::post('/bulk/store', [App\Http\Controllers\DrugsController::class, 'bulkStore'])->name('bulk.store')->middleware('permission:drugs.create,staff');
        Route::delete('/bulk/delete', [App\Http\Controllers\DrugsController::class, 'bulkDelete'])->name('bulk.delete')->middleware('permission:drugs.delete,staff');
        
        // Import/Export
        Route::post('/import', [App\Http\Controllers\DrugsController::class, 'import'])->name('import')->middleware('permission:drugs.create,staff');
        Route::get('/export', [App\Http\Controllers\DrugsController::class, 'export'])->name('export')->middleware('auth:staff', 'permission:drugs.view,staff');
        Route::get('/download/drugs-template', [App\Http\Controllers\DrugsController::class, 'downloadTemplate'])->name('download.template'); // Temporarily removed middleware for testing
        
        // Toggle status
        Route::post('/{drug}/toggle-status', [App\Http\Controllers\DrugsController::class, 'toggleStatus'])->name('toggle.status')->middleware('permission:drugs.edit,staff');
        
        // Stock details
        Route::get('/{drug}/stock-details', [App\Http\Controllers\DrugsController::class, 'getStockDetails'])->name('stock.details')->middleware('auth:staff', 'permission:drugs.view,staff');
    });
    
    // Program Management (Protected by permissions)
    Route::prefix('programs')->name('programs.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProgramController::class, 'index'])->name('index')->middleware('permission:program.view,staff');
        Route::get('/create', [App\Http\Controllers\ProgramController::class, 'create'])->name('create')->middleware('permission:program.create,staff');
        Route::post('/', [App\Http\Controllers\ProgramController::class, 'store'])->name('store')->middleware('permission:program.create,staff');
        Route::get('/{program}', [App\Http\Controllers\ProgramController::class, 'show'])->name('show')->middleware('permission:program.view,staff');
        Route::get('/{program}/edit', [App\Http\Controllers\ProgramController::class, 'edit'])->name('edit')->middleware('permission:program.edit,staff');
        Route::put('/{program}', [App\Http\Controllers\ProgramController::class, 'update'])->name('update')->middleware('permission:program.edit,staff');
        Route::delete('/{program}', [App\Http\Controllers\ProgramController::class, 'destroy'])->name('destroy')->middleware('permission:program.delete,staff');
    });
    
    // Student Management
    // Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    // Route::get('/student-registration', [StudentController::class, 'create'])->name('students.create');
    // Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    // Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    // Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    // Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    
    // Course Management
    // Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    // Route::get('/course-registration', [CourseController::class, 'create'])->name('courses.create');
    // Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    // Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    // Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    // Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    // User Management Routes
    Route::resource('staff', \App\Http\Controllers\Admin\StaffController::class);
    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    Route::resource('permissions', \App\Http\Controllers\PermissionController::class);
    Route::post('/permissions/bulk/store', [\App\Http\Controllers\PermissionController::class, 'bulkStore'])->name('permissions.bulk.store');
    
    // Services Management (Protected by permissions)
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ServicesController::class, 'index'])->name('index')->middleware('permission:services.view,staff');
        Route::get('/create', [\App\Http\Controllers\ServicesController::class, 'create'])->name('create')->middleware('permission:services.create,staff');
        Route::post('/', [\App\Http\Controllers\ServicesController::class, 'store'])->name('store')->middleware('permission:services.create,staff');
        Route::get('/bulk/create', [\App\Http\Controllers\ServicesController::class, 'bulkCreate'])->name('bulk.create')->middleware('permission:services.create,staff');
        Route::post('/bulk/store', [\App\Http\Controllers\ServicesController::class, 'bulkStore'])->name('bulk.store')->middleware('permission:services.create,staff');
        
        // Specific routes must come before general ones
        Route::get('/{service}/edit', [\App\Http\Controllers\ServicesController::class, 'edit'])->name('edit')->middleware('permission:services.edit,staff');
        Route::get('/{service}', [\App\Http\Controllers\ServicesController::class, 'show'])->name('show')->middleware('permission:services.view,staff');
        Route::put('/{service}', [\App\Http\Controllers\ServicesController::class, 'update'])->name('update')->middleware('permission:services.edit,staff');
        Route::delete('/{service}', [\App\Http\Controllers\ServicesController::class, 'destroy'])->name('destroy')->middleware('permission:services.delete,staff');
    });

    // Laboratory Tests Management
    Route::prefix('laboratory-tests')->name('laboratory-tests.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LaboratoryTestsController::class, 'index'])->name('index')->middleware('permission:laboratory-tests.view,staff');
        Route::get('/create', [\App\Http\Controllers\LaboratoryTestsController::class, 'create'])->name('create')->middleware('permission:laboratory-tests.create,staff');
        Route::post('/', [\App\Http\Controllers\LaboratoryTestsController::class, 'store'])->name('store')->middleware('permission:laboratory-tests.create,staff');
        Route::get('/bulk/create', [\App\Http\Controllers\LaboratoryTestsController::class, 'bulkCreate'])->name('bulk.create')->middleware('permission:laboratory-tests.create,staff');
        Route::post('/bulk/store', [\App\Http\Controllers\LaboratoryTestsController::class, 'bulkStore'])->name('bulk.store')->middleware('permission:laboratory-tests.create,staff');
        Route::get('/{test}/edit', [\App\Http\Controllers\LaboratoryTestsController::class, 'edit'])->name('edit')->middleware('permission:laboratory-tests.edit,staff');
        Route::put('/{test}', [\App\Http\Controllers\LaboratoryTestsController::class, 'update'])->name('update')->middleware('permission:laboratory-tests.edit,staff');
        Route::delete('/{test}', [\App\Http\Controllers\LaboratoryTestsController::class, 'destroy'])->name('destroy')->middleware('permission:laboratory-tests.delete,staff');
        Route::get('/upload', [\App\Http\Controllers\LaboratoryTestsController::class, 'upload'])->name('upload')->middleware('permission:laboratory-tests.create,staff');
        Route::post('/import', [\App\Http\Controllers\LaboratoryTestsController::class, 'import'])->name('import')->middleware('permission:laboratory-tests.create,staff');
        Route::get('/export', [\App\Http\Controllers\LaboratoryTestsController::class, 'export'])->name('export')->middleware('permission:laboratory-tests.view,staff');
        Route::get('/template', [\App\Http\Controllers\LaboratoryTestsController::class, 'downloadTemplate'])->name('template')->middleware('permission:laboratory-tests.create,staff');
    });

    // ICD Codes Management
    Route::prefix('icd-codes')->name('icd-codes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\IcdCodesController::class, 'index'])->name('index')->middleware('permission:icd-codes.view,staff');
        Route::get('/create', [\App\Http\Controllers\IcdCodesController::class, 'create'])->name('create')->middleware('permission:icd-codes.create,staff');
        Route::post('/', [\App\Http\Controllers\IcdCodesController::class, 'store'])->name('store')->middleware('permission:icd-codes.create,staff');
        Route::get('/bulk/create', [\App\Http\Controllers\IcdCodesController::class, 'bulkCreate'])->name('bulk.create')->middleware('permission:icd-codes.create,staff');
        Route::post('/bulk/store', [\App\Http\Controllers\IcdCodesController::class, 'bulkStore'])->name('bulk.store')->middleware('permission:icd-codes.create,staff');
        Route::get('/{code}/edit', [\App\Http\Controllers\IcdCodesController::class, 'edit'])->name('edit')->middleware('permission:icd-codes.edit,staff');
        Route::put('/{code}', [\App\Http\Controllers\IcdCodesController::class, 'update'])->name('update')->middleware('permission:icd-codes.edit,staff');
        Route::delete('/{code}', [\App\Http\Controllers\IcdCodesController::class, 'destroy'])->name('destroy')->middleware('permission:icd-codes.delete,staff');
        Route::get('/upload', [\App\Http\Controllers\IcdCodesController::class, 'upload'])->name('upload')->middleware('permission:icd-codes.create,staff');
        Route::post('/import', [\App\Http\Controllers\IcdCodesController::class, 'import'])->name('import')->middleware('permission:icd-codes.create,staff');
        Route::get('/export', [\App\Http\Controllers\IcdCodesController::class, 'export'])->name('export')->middleware('permission:icd-codes.view,staff');
        Route::get('/template', [\App\Http\Controllers\IcdCodesController::class, 'downloadTemplate'])->name('template')->middleware('permission:icd-codes.create,staff');
    });

    // Service Categories Management
    Route::prefix('service-categories')->name('service-categories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ServiceCategoryController::class, 'index'])->name('index')->middleware('permission:service-categories.view,staff');
        Route::get('/create', [\App\Http\Controllers\ServiceCategoryController::class, 'create'])->name('create')->middleware('permission:service-categories.create,staff');
        Route::post('/', [\App\Http\Controllers\ServiceCategoryController::class, 'store'])->name('store')->middleware('permission:service-categories.create,staff');
        Route::get('/{id}', [\App\Http\Controllers\ServiceCategoryController::class, 'show'])->name('show')->middleware('permission:service-categories.view,staff');
        Route::get('/{id}/edit', [\App\Http\Controllers\ServiceCategoryController::class, 'edit'])->name('edit')->middleware('permission:service-categories.edit,staff');
        Route::put('/{id}', [\App\Http\Controllers\ServiceCategoryController::class, 'update'])->name('update')->middleware('permission:service-categories.edit,staff');
        Route::delete('/{id}', [\App\Http\Controllers\ServiceCategoryController::class, 'destroy'])->name('destroy')->middleware('permission:service-categories.delete,staff');
    });

    // Service Types Management
    Route::prefix('service-types')->name('service-types.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ServiceTypeController::class, 'index'])->name('index')->middleware('permission:service-types.view,staff');
        Route::get('/create', [\App\Http\Controllers\ServiceTypeController::class, 'create'])->name('create')->middleware('permission:service-types.create,staff');
        Route::post('/', [\App\Http\Controllers\ServiceTypeController::class, 'store'])->name('store')->middleware('permission:service-types.create,staff');
        Route::get('/by-category', [\App\Http\Controllers\ServiceTypeController::class, 'getServiceTypesByCategory'])->name('by-category');
        Route::get('/{id}', [\App\Http\Controllers\ServiceTypeController::class, 'show'])->name('show')->middleware('permission:service-types.view,staff');
        Route::get('/{id}/edit', [\App\Http\Controllers\ServiceTypeController::class, 'edit'])->name('edit')->middleware('permission:service-types.edit,staff');
        Route::put('/{id}', [\App\Http\Controllers\ServiceTypeController::class, 'update'])->name('update')->middleware('permission:service-types.edit,staff');
        Route::delete('/{id}', [\App\Http\Controllers\ServiceTypeController::class, 'destroy'])->name('destroy')->middleware('permission:service-types.delete,staff');
    });

    // Service Items Management
    Route::prefix('service-items')->name('service-items.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ServiceItemController::class, 'index'])->name('index')->middleware('permission:service-items.view,staff');
        Route::get('/create', [\App\Http\Controllers\ServiceItemController::class, 'create'])->name('create')->middleware('permission:service-items.create,staff');
        Route::post('/', [\App\Http\Controllers\ServiceItemController::class, 'store'])->name('store')->middleware('permission:service-items.create,staff');
        Route::get('/{id}/edit', [\App\Http\Controllers\ServiceItemController::class, 'edit'])->name('edit')->middleware('permission:service-items.edit,staff');
        Route::put('/{id}', [\App\Http\Controllers\ServiceItemController::class, 'update'])->name('update')->middleware('permission:service-items.edit,staff');
        Route::delete('/{id}', [\App\Http\Controllers\ServiceItemController::class, 'destroy'])->name('destroy')->middleware('permission:service-items.delete,staff');
        Route::get('/by-type', [\App\Http\Controllers\ServiceItemController::class, 'getServiceItemsByType'])->name('by-type');
    });

    // Staff Positions Management (Facility Staff Positions)
    Route::prefix('staff-positions')->name('staff-positions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\StaffPositionsController::class, 'index'])->name('index')->middleware('permission:staff-positions.view,staff');
        Route::get('/create', [\App\Http\Controllers\StaffPositionsController::class, 'create'])->name('create')->middleware('permission:staff-positions.create,staff');
        Route::post('/', [\App\Http\Controllers\StaffPositionsController::class, 'store'])->name('store')->middleware('permission:staff-positions.create,staff');
        Route::get('/bulk/create', [\App\Http\Controllers\StaffPositionsController::class, 'bulkCreate'])->name('bulk.create')->middleware('permission:staff-positions.create,staff');
        Route::post('/bulk/store', [\App\Http\Controllers\StaffPositionsController::class, 'bulkStore'])->name('bulk.store')->middleware('permission:staff-positions.create,staff');
        Route::get('/{position}/edit', [\App\Http\Controllers\StaffPositionsController::class, 'edit'])->name('edit')->middleware('permission:staff-positions.edit,staff');
        Route::put('/{position}', [\App\Http\Controllers\StaffPositionsController::class, 'update'])->name('update')->middleware('permission:staff-positions.edit,staff');
        Route::delete('/{position}', [\App\Http\Controllers\StaffPositionsController::class, 'destroy'])->name('destroy')->middleware('permission:staff-positions.delete,staff');
    });

    // Facility Staff Management
    Route::prefix('facility-staff')->name('facility-staff.')->group(function () {
        Route::get('/', [\App\Http\Controllers\FacilityStaffController::class, 'index'])->name('index')->middleware('permission:facility-staff.view,staff');
        Route::get('/create', [\App\Http\Controllers\FacilityStaffController::class, 'create'])->name('create')->middleware('permission:facility-staff.create,staff');
        Route::post('/', [\App\Http\Controllers\FacilityStaffController::class, 'store'])->name('store')->middleware('permission:facility-staff.create,staff');
        Route::get('/{staff}/edit', [\App\Http\Controllers\FacilityStaffController::class, 'edit'])->name('edit')->middleware('permission:facility-staff.edit,staff');
        Route::put('/{staff}', [\App\Http\Controllers\FacilityStaffController::class, 'update'])->name('update')->middleware('permission:facility-staff.edit,staff');
        Route::delete('/{staff}', [\App\Http\Controllers\FacilityStaffController::class, 'destroy'])->name('destroy')->middleware('permission:facility-staff.delete,staff');
    });

    // Wards Management
    Route::prefix('wards')->name('wards.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WardController::class, 'index'])->name('index')->middleware('permission:wards.view,staff');
        Route::get('/create', [\App\Http\Controllers\WardController::class, 'create'])->name('create')->middleware('permission:wards.create,staff');
        Route::post('/', [\App\Http\Controllers\WardController::class, 'store'])->name('store')->middleware('permission:wards.create,staff');
        Route::get('/{ward}/edit', [\App\Http\Controllers\WardController::class, 'edit'])->name('edit')->middleware('permission:wards.edit,staff');
        Route::put('/{ward}', [\App\Http\Controllers\WardController::class, 'update'])->name('update')->middleware('permission:wards.edit,staff');
        Route::delete('/{ward}', [\App\Http\Controllers\WardController::class, 'destroy'])->name('destroy')->middleware('permission:wards.delete,staff');
        Route::get('/by-facility', [\App\Http\Controllers\WardController::class, 'getByFacility'])->name('by-facility');
    });

    // Rooms Management
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [\App\Http\Controllers\RoomController::class, 'index'])->name('index')->middleware('permission:rooms.view,staff');
        Route::get('/create', [\App\Http\Controllers\RoomController::class, 'create'])->name('create')->middleware('permission:rooms.create,staff');
        Route::post('/', [\App\Http\Controllers\RoomController::class, 'store'])->name('store')->middleware('permission:rooms.create,staff');
        Route::get('/{room}/edit', [\App\Http\Controllers\RoomController::class, 'edit'])->name('edit')->middleware('permission:rooms.edit,staff');
        Route::put('/{room}', [\App\Http\Controllers\RoomController::class, 'update'])->name('update')->middleware('permission:rooms.edit,staff');
        Route::delete('/{room}', [\App\Http\Controllers\RoomController::class, 'destroy'])->name('destroy')->middleware('permission:rooms.delete,staff');
        Route::get('/by-ward', [\App\Http\Controllers\RoomController::class, 'getByWard'])->name('by-ward');
    });

    // Beds Management
    Route::prefix('beds')->name('beds.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BedController::class, 'index'])->name('index')->middleware('permission:beds.view,staff');
        Route::get('/create', [\App\Http\Controllers\BedController::class, 'create'])->name('create')->middleware('permission:beds.create,staff');
        Route::post('/', [\App\Http\Controllers\BedController::class, 'store'])->name('store')->middleware('permission:beds.create,staff');
        Route::get('/{bed}/edit', [\App\Http\Controllers\BedController::class, 'edit'])->name('edit')->middleware('permission:beds.edit,staff');
        Route::put('/{bed}', [\App\Http\Controllers\BedController::class, 'update'])->name('update')->middleware('permission:beds.edit,staff');
        Route::delete('/{bed}', [\App\Http\Controllers\BedController::class, 'destroy'])->name('destroy')->middleware('permission:beds.delete,staff');
        Route::get('/by-room', [\App\Http\Controllers\BedController::class, 'getByRoom'])->name('by-room');
    });

    // Nurse Ward Assignments
    Route::prefix('nurse-ward')->name('nurse-ward.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NurseWardController::class, 'index'])->name('index')->middleware('permission:nurse-ward.view,staff');
        Route::get('/create', [\App\Http\Controllers\NurseWardController::class, 'create'])->name('create')->middleware('permission:nurse-ward.create,staff');
        Route::post('/', [\App\Http\Controllers\NurseWardController::class, 'store'])->name('store')->middleware('permission:nurse-ward.create,staff');
        Route::get('/{assignment}/edit', [\App\Http\Controllers\NurseWardController::class, 'edit'])->name('edit')->middleware('permission:nurse-ward.edit,staff');
        Route::put('/{assignment}', [\App\Http\Controllers\NurseWardController::class, 'update'])->name('update')->middleware('permission:nurse-ward.edit,staff');
        Route::delete('/{assignment}', [\App\Http\Controllers\NurseWardController::class, 'destroy'])->name('destroy')->middleware('permission:nurse-ward.delete,staff');
        Route::get('/nurses-by-facility', [\App\Http\Controllers\NurseWardController::class, 'getNursesByFacility'])->name('nurses-by-facility');
    });

    // Doctor Ward Assignments
    Route::prefix('doctor-ward')->name('doctor-ward.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DoctorWardController::class, 'index'])->name('index')->middleware('permission:doctor-ward.view,staff');
        Route::get('/create', [\App\Http\Controllers\DoctorWardController::class, 'create'])->name('create')->middleware('permission:doctor-ward.create,staff');
        Route::post('/', [\App\Http\Controllers\DoctorWardController::class, 'store'])->name('store')->middleware('permission:doctor-ward.create,staff');
        Route::get('/{assignment}/edit', [\App\Http\Controllers\DoctorWardController::class, 'edit'])->name('edit')->middleware('permission:doctor-ward.edit,staff');
        Route::put('/{assignment}', [\App\Http\Controllers\DoctorWardController::class, 'update'])->name('update')->middleware('permission:doctor-ward.edit,staff');
        Route::delete('/{assignment}', [\App\Http\Controllers\DoctorWardController::class, 'destroy'])->name('destroy')->middleware('permission:doctor-ward.delete,staff');
        Route::get('/doctors-by-facility', [\App\Http\Controllers\DoctorWardController::class, 'getDoctorsByFacility'])->name('doctors-by-facility');
    });

    // Beneficiary Categories Management
    Route::resource('beneficiary-categories', \App\Http\Controllers\BeneficiaryCategoryController::class)->except(['show'])->middleware('permission:beneficiary.view,staff');

    // Facility Services Management
    Route::prefix('facility-services')->name('facility-services.')->group(function () {
        Route::get('/', [\App\Http\Controllers\FacilityServiceController::class, 'index'])->name('index')->middleware('permission:facility-services.view,staff');
        Route::get('/create', [\App\Http\Controllers\FacilityServiceController::class, 'create'])->name('create')->middleware('permission:facility-services.create,staff');
        Route::post('/', [\App\Http\Controllers\FacilityServiceController::class, 'store'])->name('store')->middleware('permission:facility-services.create,staff');
        Route::post('/{id}/toggle', [\App\Http\Controllers\FacilityServiceController::class, 'toggle'])->name('toggle')->middleware('permission:facility-services.edit,staff');
        Route::delete('/{id}', [\App\Http\Controllers\FacilityServiceController::class, 'destroy'])->name('destroy')->middleware('permission:facility-services.delete,staff');
        Route::post('/bulk-delete', [\App\Http\Controllers\FacilityServiceController::class, 'bulkDelete'])->name('bulk-delete')->middleware('permission:facility-services.delete,staff');
        Route::get('/types-by-category', [\App\Http\Controllers\FacilityServiceController::class, 'getTypesByCategory'])->name('types-by-category');
        Route::get('/assigned', [\App\Http\Controllers\FacilityServiceController::class, 'getAssignedServices'])->name('assigned');
    });
    
    // Beneficiary Management (Protected by permissions)
    
    Route::get('beneficiaries/verify', [BeneficiaryController::class, 'verify'])->name('beneficiaries.verify')->middleware('permission:beneficiary.view,staff');
    Route::get('beneficiaries/verify-nin/{nin}', [BeneficiaryController::class, 'verifyNin'])->name('beneficiaries.verify-nin')->middleware('permission:beneficiary.create|beneficiary.edit,staff');
    Route::get('beneficiaries/verify-dp/{dpNo}', [BeneficiaryController::class, 'verifyDp'])->name('beneficiaries.verify-dp')->middleware('permission:beneficiary.create|beneficiary.edit,staff');
    Route::post('beneficiaries/bulk-action', [BeneficiaryController::class, 'bulkAction'])->name('beneficiaries.bulk-action')->middleware('permission:beneficiary.delete,staff');
    Route::patch('beneficiaries/{beneficiary}/update-status', [BeneficiaryController::class, 'updateStatus'])->name('beneficiaries.update-status')->middleware('permission:beneficiary.approve,staff');
    Route::post('beneficiaries/{beneficiary}/convert-program', [BeneficiaryController::class, 'convertProgram'])->name('beneficiaries.convert-program')->middleware('permission:beneficiary.edit,staff');
    Route::get('beneficiaries/{beneficiary}/pdf', [App\Http\Controllers\BeneficiaryController::class, 'downloadPdf'])->name('beneficiaries.pdf')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/{beneficiary}/id-card', [App\Http\Controllers\BeneficiaryController::class, 'generateIdCard'])->name('beneficiaries.id-card')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/{beneficiary}/id-card/download', [App\Http\Controllers\BeneficiaryController::class, 'downloadIdCard'])->name('beneficiaries.id-card.download')->middleware('permission:beneficiary.export,staff');
    Route::get('bulk-id-cards', [BeneficiaryController::class, 'bulkIdCards'])->name('beneficiaries.bulk-id-cards')->middleware('permission:beneficiary.export,staff');
    Route::post('beneficiaries/bulk-id-cards/start', [BeneficiaryController::class, 'startBulkIdCardGeneration'])->name('beneficiaries.bulk-id-cards.start')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/bulk-id-cards/status/{jobId}', [BeneficiaryController::class, 'getBulkIdCardJobStatus'])->name('beneficiaries.bulk-id-cards.status')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/bulk-id-cards/download/{jobId}', [BeneficiaryController::class, 'downloadBulkIdCardFile'])->name('beneficiaries.bulk-id-cards.download-file')->middleware('permission:beneficiary.export,staff');
    Route::post('beneficiaries/bulk-id-cards/cancel/{jobId}', [BeneficiaryController::class, 'cancelBulkIdCardJob'])->name('beneficiaries.bulk-id-cards.cancel')->middleware('permission:beneficiary.export,staff');
    Route::delete('beneficiaries/bulk-id-cards/delete/{jobId}', [BeneficiaryController::class, 'deleteBulkIdCardJob'])->name('beneficiaries.bulk-id-cards.delete')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/bulk-id-cards/jobs', [BeneficiaryController::class, 'getBulkIdCardJobs'])->name('beneficiaries.bulk-id-cards.jobs')->middleware('permission:beneficiary.export,staff');
    
    // Legacy routes for backward compatibility
    Route::post('beneficiaries/bulk-id-cards/generate', [BeneficiaryController::class, 'generateBulkIdCards'])->name('beneficiaries.bulk-id-cards.generate')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/bulk-id-cards/download/{filename}', [BeneficiaryController::class, 'downloadBulkIdCards'])->name('beneficiaries.bulk-id-cards.download')->middleware('permission:beneficiary.export,staff');
    Route::post('beneficiaries/save-section', [BeneficiaryController::class, 'saveSection'])->name('beneficiaries.saveSection')->middleware('permission:beneficiary.create|beneficiary.edit,staff');
    Route::get('beneficiaries/{beneficiary}/load-data', [BeneficiaryController::class, 'loadData'])->name('beneficiaries.loadData')->middleware('permission:beneficiary.view,staff');
    
    // Beneficiary Upload (bulk import)
    Route::get('beneficiaries/upload/form', [BeneficiaryController::class, 'uploadForm'])->name('beneficiaries.upload.form')->middleware('permission:beneficiary.create,staff');
    Route::post('beneficiaries/upload/excel', [BeneficiaryController::class, 'uploadExcel'])->name('beneficiaries.upload.excel')->middleware('permission:beneficiary.create,staff');
    Route::get('beneficiaries/download/template', [BeneficiaryController::class, 'downloadTemplate'])->name('beneficiaries.download.template')->middleware('permission:beneficiary.create,staff');
    
    // API route for facilities by LGA
    Route::get('api/facilities/by-lga', function(\Illuminate\Http\Request $request) {
        $lga = $request->get('lga');
        $facilities = \App\Models\Facility::where('lga', $lga)->orderBy('name')->get(['id', 'name', 'ward', 'type']);
        return response()->json(['facilities' => $facilities]);
    })->name('api.facilities.by-lga')->middleware('auth:staff');
    
    // API route for wards by facility
    Route::get('api/facilities/{facilityId}/wards', function($facilityId) {
        $wards = \App\Models\Ward::where('facility_id', $facilityId)->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return response()->json($wards);
    })->name('api.facilities.wards')->middleware('auth:staff');
    
    // Beneficiary CRUD - Order matters! Specific routes before parameterized routes
    Route::middleware(['permission:beneficiary.view,staff'])->group(function () {
        Route::get('beneficiaries', [BeneficiaryController::class, 'index'])->name('beneficiaries.index');
    });
    Route::middleware(['permission:beneficiary.create,staff'])->group(function () {
        Route::get('beneficiaries/create', [BeneficiaryController::class, 'create'])->name('beneficiaries.create');
        Route::post('beneficiaries', [BeneficiaryController::class, 'store'])->name('beneficiaries.store');
    });
    Route::middleware(['permission:beneficiary.edit,staff'])->group(function () {
        Route::get('beneficiaries/{beneficiary}/edit', [BeneficiaryController::class, 'edit'])->name('beneficiaries.edit');
        Route::put('beneficiaries/{beneficiary}', [BeneficiaryController::class, 'update'])->name('beneficiaries.update');
    });
    Route::middleware(['permission:beneficiary.view,staff'])->group(function () {
        Route::get('beneficiaries/{beneficiary}', [BeneficiaryController::class, 'show'])->name('beneficiaries.show');
    });
    Route::middleware(['permission:beneficiary.delete,staff'])->group(function () {
        Route::delete('beneficiaries/{beneficiary}', [BeneficiaryController::class, 'destroy'])->name('beneficiaries.destroy');
    });
    
    // Civil Servant Management
    Route::delete('civil-servants/bulk-delete', [CivilServantController::class, 'bulkDelete'])->name('civil-servants.bulk-delete');
    Route::get('civil-servants/upload/form', [CivilServantController::class, 'uploadForm'])->name('civil-servants.upload.form');
    Route::post('civil-servants/upload/excel', [CivilServantController::class, 'uploadExcel'])->name('civil-servants.upload.excel');
    Route::post('civil-servants/upload/bvn', [CivilServantController::class, 'uploadBvn'])->name('civil-servants.upload.bvn');
    Route::get('civil-servants/download/template', [CivilServantController::class, 'downloadTemplate'])->name('civil-servants.download.template');
    Route::get('civil-servants/download/bvn-template', [CivilServantController::class, 'downloadBvnTemplate'])->name('civil-servants.download.bvn-template');
    Route::resource('civil-servants', CivilServantController::class);
    
    // Reports Management (Protected by permissions)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index')->middleware('permission:report.view|crm.view,staff');
        Route::get('/export', [ReportsController::class, 'exportDashboard'])->name('export')->middleware('permission:report.view|crm.view,staff');
        Route::get('/enumerators', [ReportsController::class, 'enumerators'])->name('enumerators')->middleware('permission:report.view|crm.view,staff');
        Route::get('/enumerators/export', [ReportsController::class, 'exportEnumerators'])->name('enumerators.export')->middleware('permission:report.view|crm.view,staff');
        Route::get('/enumerators/{id}/enrollments', [ReportsController::class, 'enumeratorEnrollments'])->name('enumerators.enrollments')->middleware('permission:report.view|crm.view,staff');
        Route::get('/enumerators/{id}/enrollments/export', [ReportsController::class, 'exportEnumeratorEnrollments'])->name('enumerators.enrollments.export')->middleware('permission:report.view|crm.view,staff');
        Route::get('/facilities', [ReportsController::class, 'facilities'])->name('facilities')->middleware('permission:report.view|crm.view,staff');
        Route::get('/facilities/export', [ReportsController::class, 'exportFacilities'])->name('facilities.export')->middleware('permission:report.view|crm.view,staff');
        Route::get('/facilities/{id}/enrollments', [ReportsController::class, 'facilityEnrollments'])->name('facilities.show')->middleware('permission:report.view|crm.view,staff');
        Route::get("/beneficiaries", [ReportsController::class, "beneficiaries"])->name("beneficiaries")->middleware("permission:report.view|crm.view,staff");
        Route::get("/beneficiaries/export", [ReportsController::class, "exportBeneficiaries"])->name("beneficiaries.export")->middleware("permission:report.view|crm.view,staff");
        Route::get('/pharmacy-stock', [ReportsController::class, 'pharmacyStock'])->name('pharmacy_stock')->middleware('permission:report.view|crm.view,staff');
        Route::get('/enrollments', [ReportsController::class, 'enrollments'])->name('enrollments')->middleware('permission:report.view|crm.view,staff');
        Route::get('/enrollments/export', [ReportsController::class, 'exportEnrollments'])->name('enrollments.export')->middleware('permission:report.view|crm.view,staff');
        Route::get('/enrollments/export/month/{month}', [ReportsController::class, 'exportMonthlyEnrollments'])->name('enrollments.export.month')->middleware('permission:report.view|crm.view,staff');
        Route::get('/enrollments/export/category/{category}', [ReportsController::class, 'exportCategoryEnrollments'])->name('enrollments.export.category')->middleware('permission:report.view|crm.view,staff');
        Route::get('/enrollments/export/status/{status}', [ReportsController::class, 'exportStatusEnrollments'])->name('enrollments.export.status')->middleware('permission:report.view|crm.view,staff');
        Route::get('/crm/reports', [ReportsController::class, 'crm'])->name('crm')->middleware('permission:report.view|crm.view,staff');
        Route::get('/crm/reports/export', [ReportsController::class, 'crmExport'])->name('crm.export')->middleware('permission:report.view|crm.view,staff');
        Route::get('/crm/reports/print', [ReportsController::class, 'crmPrint'])->name('crm.print')->middleware('permission:report.view|crm.view,staff');
        Route::get('/crm/reports/{ticket}', [ReportsController::class, 'crmTicketDetail'])->name('crm.ticket.detail')->middleware('permission:report.view|crm.view,staff');
        Route::get('/crm/reports/category/breakdown', [ReportsController::class, 'crmCategoryBreakdown'])->name('crm.category.breakdown')->middleware('permission:report.view|crm.view,staff');
        Route::get('/crm/reports/status/breakdown', [ReportsController::class, 'crmStatusBreakdown'])->name('crm.status.breakdown')->middleware('permission:report.view|crm.view,staff');
        Route::get('/crm/reports/department/breakdown', [ReportsController::class, 'crmDepartmentBreakdown'])->name('crm.department.breakdown')->middleware('permission:report.view|crm.view,staff');

        // EHR Activity Reports
        Route::get('/ehr', [EhrReportController::class, 'index'])->name('ehr')->middleware('permission:report.view|crm.view,staff');
        Route::get('/ehr/export', [EhrReportController::class, 'export'])->name('ehr.export')->middleware('permission:report.view|crm.view,staff');
        Route::get('/ehr/drilldown', [EhrReportController::class, 'drilldown'])->name('ehr.drilldown')->middleware('permission:report.view|crm.view,staff');
    });
    
    // Contributions Management (Protected by permissions)
    Route::get('contributions/download-template', [App\Http\Controllers\ContributionController::class, 'downloadTemplate'])->name('contributions.download-template')->middleware('permission:contribution.view,staff');
    Route::post('contributions/import', [App\Http\Controllers\ContributionController::class, 'import'])->name('contributions.import')->middleware('permission:contribution.import,staff');
    
    Route::middleware(['permission:contribution.view,staff'])->group(function () {
        Route::get('contributions', [App\Http\Controllers\ContributionController::class, 'index'])->name('contributions.index');
        Route::get('contributions/{contribution}', [App\Http\Controllers\ContributionController::class, 'show'])->name('contributions.show');
    });
    Route::middleware(['permission:contribution.create,staff'])->group(function () {
        Route::get('contributions/create', [App\Http\Controllers\ContributionController::class, 'create'])->name('contributions.create');
        Route::post('contributions', [App\Http\Controllers\ContributionController::class, 'store'])->name('contributions.store');
    });
    Route::middleware(['permission:contribution.edit,staff'])->group(function () {
        Route::get('contributions/{contribution}/edit', [App\Http\Controllers\ContributionController::class, 'edit'])->name('contributions.edit');
        Route::put('contributions/{contribution}', [App\Http\Controllers\ContributionController::class, 'update'])->name('contributions.update');
    });
    Route::middleware(['permission:contribution.delete,staff'])->group(function () {
        Route::delete('contributions/{contribution}', [App\Http\Controllers\ContributionController::class, 'destroy'])->name('contributions.destroy');
    });
    
    // Contribution Upload Management
    Route::get('contribution-uploads/{id}/process', [App\Http\Controllers\ContributionUploadController::class, 'process'])->name('contribution-uploads.process');
    Route::get('contribution-uploads/{id}/progress', [App\Http\Controllers\ContributionUploadController::class, 'progress'])->name('contribution-uploads.progress');
    Route::get('contribution-uploads/{id}/errors', [App\Http\Controllers\ContributionUploadController::class, 'showErrors'])->name('contribution-uploads.errors');
    Route::resource('contribution-uploads', App\Http\Controllers\ContributionUploadController::class);
    
    // Activity Logs
    Route::get('/activity-logs', function () {
        return view('admin.activity_logs', ['page' => 'admin.activity_logs']);
    })->name('activity-logs.index');
    
    // Settings Page
    Route::get('/settings', function () {
        return view('admin.settings', ['page' => 'admin.settings']);
    })->name('settings.index');
    
    // Staff Management (Protected by permission middleware)
    Route::middleware(['permission:staff.view,staff'])->group(function () {
        Route::resource('staff', App\Http\Controllers\Admin\StaffController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    });
    
    Route::middleware(['permission:staff.create,staff'])->group(function () {
        Route::get('staff/create', [App\Http\Controllers\Admin\StaffController::class, 'create'])->name('staff.create');
        Route::post('staff', [App\Http\Controllers\Admin\StaffController::class, 'store'])->name('staff.store');
    });
    
    Route::middleware(['permission:staff.edit,staff'])->group(function () {
        Route::get('staff/{staff}/edit', [App\Http\Controllers\Admin\StaffController::class, 'edit'])->name('staff.edit');
        Route::put('staff/{staff}', [App\Http\Controllers\Admin\StaffController::class, 'update'])->name('staff.update');
    });
    
    Route::middleware(['permission:staff.delete,staff'])->group(function () {
        Route::delete('staff/{staff}', [App\Http\Controllers\Admin\StaffController::class, 'destroy'])->name('staff.destroy');
    });
    
    // Role & Permission Management (Protected by permission middleware)
    Route::middleware(['permission:role.view,staff'])->group(function () {
        Route::resource('roles', App\Http\Controllers\Admin\RoleController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    });
    
    Route::middleware(['permission:role.create,staff'])->group(function () {
        Route::get('roles/create', [App\Http\Controllers\Admin\RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
    });
    
    Route::middleware(['permission:role.edit,staff'])->group(function () {
        Route::get('roles/{role}/edit', [App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
    });
    
    Route::middleware(['permission:role.delete,staff'])->group(function () {
        Route::delete('roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
    });
    
    // CRM / Customer Care Routes
    Route::middleware(['permission:crm.view,staff'])->group(function () {
        Route::get('crm', [CrmController::class, 'index'])->name('crm.index');
        Route::get('crm/create', [CrmController::class, 'create'])->name('crm.create');
        Route::get('crm/search-beneficiary', [CrmController::class, 'searchBeneficiary'])->name('crm.search-beneficiary');
        Route::get('crm/beneficiary/{id}/profile', [CrmController::class, 'getBeneficiaryProfile'])
            ->name('crm.beneficiary.profile')
            ->where('id', '.*');
        Route::get('crm/facility-activity', [CrmController::class, 'facilityActivity'])->name('crm.facility-activity');
        Route::get('crm/active-staff', [CrmController::class, 'activeStaff'])->name('crm.active-staff');
        Route::get('crm/active-users', [CrmController::class, 'activeUsers'])->name('crm.active-users');
        Route::get('crm/live-presence', [CrmController::class, 'livePresence'])->name('crm.live-presence');
        Route::post('crm/heartbeat', [CrmController::class, 'heartbeat'])->name('crm.heartbeat');
        Route::get('crm/ehr-activity', [CrmController::class, 'ehrActivity'])->name('crm.ehr-activity');
        Route::get('crm/{ticket}', [CrmController::class, 'show'])->name('crm.show');
        Route::get('crm/{ticket}/edit', [CrmController::class, 'edit'])->name('crm.edit');
        Route::get('crm/stats', [CrmController::class, 'stats'])->name('crm.stats');
        Route::get('crm/download-attachment/{replyId}', [CrmController::class, 'downloadAttachment'])->name('crm.download.attachment');
        Route::get('crm/validate-boschma-no/{boschmaNo}', [CrmController::class, 'validateBoschmaNo'])
        ->name('crm.validate.boschma_no')
        ->where('boschmaNo', '.*'); // Allow any characters including slashes
        
        // Twilio Voice Routes
        Route::get('twilio/status', [\App\Http\Controllers\TwilioVoiceController::class, 'status'])->name('twilio.voice.status');
        Route::get('twilio/token', [\App\Http\Controllers\TwilioVoiceController::class, 'generateToken'])->name('twilio.voice.token');
        Route::post('twilio/call', [\App\Http\Controllers\TwilioVoiceController::class, 'makeCall'])->name('twilio.voice.call');
        Route::post('crm/twilio/sms', [CrmController::class, 'sendCustomSms'])->name('crm.twilio.sms');
    });
    
    Route::middleware(['permission:crm.create,staff'])->group(function () {
        Route::post('crm', [CrmController::class, 'store'])->name('crm.store');
        Route::post('crm/{ticket}/reply', [CrmController::class, 'addReply'])->name('crm.reply');
        Route::post('crm/{ticket}/mark-completed', [CrmController::class, 'markCompleted'])->name('crm.mark.completed');
        Route::post('crm/tickets/{ticket}/mark-viewed', [CrmController::class, 'markViewed'])->name('crm.tickets.mark-viewed');
        Route::post('crm/replies/{reply}/mark-read', [CrmController::class, 'markReplyAsRead'])->name('crm.replies.mark-read');
        Route::get('crm/notifications/api', [CrmController::class, 'getNotifications'])->name('crm.notifications.get');
        Route::post('crm/notifications/{notification}/read', [CrmController::class, 'markNotificationAsRead'])->name('crm.notifications.read');
        Route::post('crm/notifications/read-all', [CrmController::class, 'markAllNotificationsAsRead'])->name('crm.notifications.read-all');
        Route::get('crm/notifications', [CrmController::class, 'viewAllNotifications'])->name('crm.notifications.index');
    });
    
    Route::middleware(['permission:crm.edit,staff'])->group(function () {
        Route::put('crm/{ticket}', [CrmController::class, 'update'])->name('crm.update');
        Route::patch('crm/{ticket}/status', [CrmController::class, 'updateStatus'])->name('crm.update.status');
    });
    
    Route::middleware(['permission:crm.delete,staff'])->group(function () {
        Route::delete('crm/{ticket}', [CrmController::class, 'destroy'])->name('crm.destroy');
    });
    
    // Referral System Routes
    Route::middleware(['permission:referral.view,staff'])->group(function () {
        Route::get('referrals', [ReferralController::class, 'index'])->name('referrals.index');
        Route::get('referrals/{referral}', [ReferralController::class, 'show'])->name('referrals.show');
        Route::get('referrals/{referral}/print', [ReferralController::class, 'print'])->name('referrals.print');
        Route::get('referrals/analytics', [ReferralController::class, 'analytics'])->name('referrals.analytics');
        Route::get('referrals/settings', [ReferralController::class, 'settings'])->name('referrals.settings');
        Route::get('referrals/export', [ReferralController::class, 'export'])->name('referrals.export');
        Route::get('referrals/analytics/export', [ReferralController::class, 'exportAnalytics'])->name('referrals.analytics.export');
        Route::get('referrals/search', [ReferralController::class, 'search'])->name('referrals.search');
        Route::get('referrals/validate-referrer/{email}', [ReferralController::class, 'validateReferrer'])->name('referrals.validate.referrer');
        Route::get('referrals/validate-referred/{email}', [ReferralController::class, 'validateReferred'])->name('referrals.validate.referred');
        Route::get('referrals/download-document/{referral}/{document}', [ReferralController::class, 'downloadDocument'])->name('referrals.download.document');
    });
    
    Route::middleware(['permission:referral.create,staff'])->group(function () {
        Route::get('referrals/create', [ReferralController::class, 'create'])->name('referrals.create');
        Route::post('referrals', [ReferralController::class, 'store'])->name('referrals.store');
        Route::post('referrals/{referral}/approve', [ReferralController::class, 'approve'])->name('referrals.approve');
        Route::post('referrals/{referral}/reject', [ReferralController::class, 'reject'])->name('referrals.reject');
        Route::post('referrals/{referral}/complete', [ReferralController::class, 'complete'])->name('referrals.complete');
        Route::post('referrals/{referral}/pay-commission', [ReferralController::class, 'payCommission'])->name('referrals.pay-commission');
        Route::post('referrals/{referral}/send-reminder', [ReferralController::class, 'sendReminder'])->name('referrals.send-reminder');
        Route::post('referrals/bulk-approve', [ReferralController::class, 'bulkApprove'])->name('referrals.bulk-approve');
        Route::post('referrals/bulk-reject', [ReferralController::class, 'bulkReject'])->name('referrals.bulk-reject');
        Route::post('referrals/bulk-delete', [ReferralController::class, 'bulkDelete'])->name('referrals.bulk-delete');
    });
    
    Route::middleware(['permission:referral.edit,staff'])->group(function () {
        Route::get('referrals/{referral}/edit', [ReferralController::class, 'edit'])->name('referrals.edit');
        Route::put('referrals/{referral}', [ReferralController::class, 'update'])->name('referrals.update');
        Route::patch('referrals/{referral}/status', [ReferralController::class, 'updateStatus'])->name('referrals.update.status');
        Route::patch('referrals/{referral}/commission-status', [ReferralController::class, 'updateCommissionStatus'])->name('referrals.update.commission-status');
        Route::post('referrals/{referral}/assign', [ReferralController::class, 'assign'])->name('referrals.assign');
        Route::post('referrals/{referral}/unassign', [ReferralController::class, 'unassign'])->name('referrals.unassign');
        Route::post('referrals/{referral}/add-note', [ReferralController::class, 'addNote'])->name('referrals.add-note');
    });
    
    Route::middleware(['permission:referral.delete,staff'])->group(function () {
        Route::delete('referrals/{referral}', [ReferralController::class, 'destroy'])->name('referrals.destroy');
    });
    
    Route::middleware(['permission:referral.settings,staff'])->group(function () {
        Route::put('referrals/settings', [ReferralController::class, 'updateSettings'])->name('referrals.settings.update');
        Route::post('referrals/settings/reset', [ReferralController::class, 'resetSettings'])->name('referrals.settings.reset');
        Route::post('referrals/settings/test-email', [ReferralController::class, 'testEmail'])->name('referrals.settings.test-email');
    });

    // Claims Routes - IMPORTANT: Specific routes MUST come before dynamic {id} routes
    
    // Specific routes (no dynamic segments)
    Route::get('claims', [ClaimController::class, 'index'])->name('claims.index')->middleware('permission:claim.view');
    Route::get('claims/create', [ClaimController::class, 'create'])->name('claims.create')->middleware('permission:claim.create');
    Route::get('claims/export', [ClaimController::class, 'export'])->name('claims.export')->middleware('permission:claim.view');
    Route::get('claims/bulk-upload', [ClaimController::class, 'bulkUpload'])->name('claims.bulk.upload')->middleware('permission:claim.edit');
    Route::get('claims/template', [ClaimController::class, 'downloadTemplate'])->name('claims.template')->middleware('permission:claim.edit');
    Route::get('claims/analytics', [ClaimController::class, 'analytics'])->name('claims.analytics')->middleware('permission:claim.edit');
    Route::get('claims/analytics/facility-report', [ClaimController::class, 'facilityReport'])->name('claims.analytics.facility-report')->middleware('permission:claim.view');
    Route::get('claims/audit-report', [ClaimController::class, 'auditReport'])->name('claims.audit.report')->middleware('permission:claim.edit');
    Route::get('claims/export-audit-trail', [ClaimController::class, 'exportAuditTrail'])->name('claims.audit.export')->middleware('permission:claim.edit');
    Route::get('claims/notifications', [ClaimController::class, 'notifications'])->name('claims.notifications')->middleware('permission:claim.edit');
    Route::get('claims/notifications/count', [ClaimController::class, 'getNotificationCount'])->name('claims.notifications.count')->middleware('permission:claim.edit');
    Route::get('claims/alerts', [ClaimController::class, 'alerts'])->name('claims.alerts')->middleware('permission:claim.edit');
    Route::get('claims/master-data/drugs', [ClaimController::class, 'getDrugs'])->name('claims.master.drugs')->middleware('permission:claim.edit');
    Route::get('claims/master-data/laboratory-tests', [ClaimController::class, 'getLaboratoryTests'])->name('claims.master.laboratory-tests')->middleware('permission:claim.edit');
    Route::get('claims/master-data/services', [ClaimController::class, 'getServices'])->name('claims.master.services')->middleware('permission:claim.edit');
    
    // Approval workflow routes
    Route::get('claims/ro-review', [ClaimController::class, 'roReview'])->name('claims.ro-review')->middleware('permission:claim.edit');
    Route::get('claims/e5-review', [ClaimController::class, 'e5Review'])->name('claims.e5-review')->middleware('permission:claim.edit');
    Route::post('claims/bulk-approve', [ClaimController::class, 'bulkApprove'])->name('claims.bulk-approve')->middleware('permission:claim.edit');
    Route::post('claims/bulk-payment', [ClaimController::class, 'bulkPayment'])->name('claims.bulk-payment')->middleware('permission:claim.edit');
    
    // API Routes for patient search (claims)
    Route::get('api/patients/search', [ClaimController::class, 'searchBeneficiaries'])->name('api.patients.search')->middleware('permission:claim.edit');
    Route::get('api/facilities/{facilityId}/claims/review', [ClaimController::class, 'facilityShow'])->name('api.facilities.claims.review')->middleware('permission:claim.view');
    
    // Routes with path segments before {id}
    Route::get('claims/facility/{facilityId}', [ClaimController::class, 'facilityShow'])->name('claims.facility.show')->middleware('permission:claim.view');
    Route::get('claims/facility-claim/{claimId}', [ClaimController::class, 'showFacilityClaim'])->name('claims.facility-claim.show')->middleware('permission:claim.view');
    Route::get('claims/facility-claim/{claimId}/download-pdf', [ClaimController::class, 'downloadFacilityClaimPdf'])->name('claims.facility-claim.download-pdf')->middleware('permission:claim.view');
    Route::post('claims/facility-claim/{claimId}/update-item', [ClaimController::class, 'updateFacilityClaimItem'])->name('claims.facility-claim.update-item')->middleware('permission:claim.edit-items,staff');
    Route::post('claims/facility-claim/{claimId}/delete-item', [ClaimController::class, 'deleteFacilityClaimItem'])->name('claims.facility-claim.delete-item')->middleware('permission:claim.edit-items,staff');
    Route::post('claims/facility-claim/{claimId}/add-medication', [ClaimController::class, 'addMedicationToFacilityClaim'])->name('claims.facility-claim.add-medication')->middleware('permission:claim.edit-items,staff');
    Route::post('claims/facility-claim/{claimId}/add-service', [ClaimController::class, 'addServiceToFacilityClaim'])->name('claims.facility-claim.add-service')->middleware('permission:claim.edit-items,staff');
    Route::post('claims/facility-claims/batch-approve', [ClaimController::class, 'batchApproveFacilityClaims'])->name('claims.facility-claims.batch-approve')->middleware('permission:claim.view');
    Route::get('claims/facility-claim/{claimId}/download', [ClaimController::class, 'downloadFacilityClaim'])->name('claims.facility.download')->middleware('permission:claim.view');
    Route::get('claims/{facility}/list', [ClaimController::class, 'facilityList'])->name('claims.facility.list')->middleware('permission:claim.view');
    
    // Dynamic {id} routes - MUST come AFTER all specific routes
    Route::middleware(['permission:claim.view'])->group(function () {
        Route::get('claims/{id}/print', [ClaimController::class, 'print'])->name('claims.print');
        Route::get('claims/{id}/download', [ClaimController::class, 'download'])->name('claims.download');
        Route::get('claims/{id}/audit-trail', [ClaimController::class, 'auditTrail'])->name('claims.audit.trail');
        Route::get('claims/{id}', [ClaimController::class, 'show'])->name('claims.show');
        Route::post('claims/{id}/approve', [ClaimController::class, 'approve'])->name('claims.approve');
        Route::post('claims/{id}/reject', [ClaimController::class, 'reject'])->name('claims.reject');
    });

    Route::middleware(['permission:claim.create'])->group(function () {
        Route::post('claims', [ClaimController::class, 'store'])->name('claims.store');
    });

    Route::middleware(['permission:claim.edit'])->group(function () {
        Route::get('claims/{id}/edit', [ClaimController::class, 'edit'])->name('claims.edit');
        Route::put('claims/{id}', [ClaimController::class, 'update'])->name('claims.update');
        Route::post('claims/{id}/pay', [ClaimController::class, 'markAsPaid'])->name('claims.pay');

        // RO and E5 approval routes
        Route::post('claims/{id}/ro-approve', [ClaimController::class, 'roApprove'])->name('claims.ro.approve');
        Route::post('claims/{id}/ro-reject', [ClaimController::class, 'roReject'])->name('claims.ro.reject');
        Route::post('claims/{id}/review', [ClaimController::class, 'review'])->name('claims.review');
        Route::post('claims/{id}/e5-approve', [ClaimController::class, 'e5Approve'])->name('claims.e5-approve');
        Route::post('claims/{id}/add-note', [ClaimController::class, 'addNote'])->name('claims.add-note');

        // Claim component management routes
        // Medications
        Route::post('claims/{claim}/medications', [ClaimController::class, 'storeMedication'])->name('claims.medications.store');
        Route::get('claims/{claim}/medications/{medication}', [ClaimController::class, 'getMedication'])->name('claims.medications.get');
        Route::put('claims/{claim}/medications/{medication}', [ClaimController::class, 'updateMedication'])->name('claims.medications.update');
        Route::delete('claims/{claim}/medications/{medication}', [ClaimController::class, 'deleteMedication'])->name('claims.medications.delete');

        // Laboratory Tests
        Route::post('claims/{claim}/laboratory', [ClaimController::class, 'storeLaboratory'])->name('claims.laboratory.store');
        Route::get('claims/{claim}/laboratory/{test}', [ClaimController::class, 'getLaboratory'])->name('claims.laboratory.get');
        Route::put('claims/{claim}/laboratory/{test}', [ClaimController::class, 'updateLaboratory'])->name('claims.laboratory.update');
        Route::delete('claims/{claim}/laboratory/{test}', [ClaimController::class, 'deleteLaboratory'])->name('claims.laboratory.delete');

        // Rendered Services
        Route::post('claims/{claim}/services', [ClaimController::class, 'storeService'])->name('claims.services.store');
        Route::get('claims/{claim}/services/{service}', [ClaimController::class, 'getService'])->name('claims.services.get');
        Route::put('claims/{claim}/services/{service}', [ClaimController::class, 'updateService'])->name('claims.services.update');
        Route::delete('claims/{claim}/services/{service}', [ClaimController::class, 'deleteService'])->name('claims.services.delete');

        // Documents
        Route::post('claims/{claim}/documents/upload', [ClaimController::class, 'uploadDocuments'])->name('claims.documents.upload');
        Route::delete('claims/{claim}/documents/{document}', [ClaimController::class, 'deleteDocument'])->name('claims.documents.delete');

        // Bulk upload routes
        Route::post('claims/bulk-upload', [ClaimController::class, 'processBulkUpload'])->name('claims.bulk.upload.process');

        // Analytics and reporting routes
        Route::post('claims/generate-report', [ClaimController::class, 'generateReport'])->name('claims.generate.report');

        // Audit trail routes
        Route::post('claims/{id}/add-audit-note', [ClaimController::class, 'addAuditNote'])->name('claims.audit.add-note');

        // Notifications and alerts routes
        Route::post('claims/notifications/{id}/read', [ClaimController::class, 'markNotificationRead'])->name('claims.notifications.read');
        Route::post('claims/notifications/read-all', [ClaimController::class, 'markAllNotificationsRead'])->name('claims.notifications.read-all');
        Route::delete('claims/notifications/{id}', [ClaimController::class, 'deleteNotification'])->name('claims.notifications.delete');
    });

    Route::middleware(['permission:claim.delete'])->group(function () {
        Route::delete('claims/{id}', [ClaimController::class, 'destroy'])->name('claims.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Facility Staff Routes
|--------------------------------------------------------------------------
|
| These routes are for facility staff who use the users table for authentication
| and have their own separate dashboard and functionality.
|
*/

Route::prefix('facility')->name('facility.')->group(function () {
    // Authentication Routes
    Route::get('/login', [\App\Http\Controllers\Facility\FacilityAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Facility\FacilityAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [\App\Http\Controllers\Facility\FacilityAuthController::class, 'logout'])->name('logout');
    
    // Protected Routes (require authentication)
    Route::middleware(['auth:web'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Facility\FacilityDashboardController::class, 'index'])->name('dashboard');
        
        // Profile (placeholder routes for future implementation)
        Route::get('/profile', function () {
            return view('facility.profile');
        })->name('profile');
        
        Route::get('/settings', function () {
            return view('facility.settings');
        })->name('settings');
        
        // Patients Management
        Route::get('/patients', [\App\Http\Controllers\Facility\PatientsController::class, 'index'])->name('patients.index');
        Route::get('/patients/{id}', [\App\Http\Controllers\Facility\PatientsController::class, 'show'])->name('patients.show');
        Route::get('/beneficiary/{id}', [\App\Http\Controllers\Facility\PatientsController::class, 'showBeneficiary'])->name('beneficiary.show');
        
        // Facility Staff Management
        Route::get('/staff', [\App\Http\Controllers\Facility\FacilityStaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [\App\Http\Controllers\Facility\FacilityStaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [\App\Http\Controllers\Facility\FacilityStaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{id}/edit', [\App\Http\Controllers\Facility\FacilityStaffController::class, 'edit'])->name('staff.edit');
        Route::post('/stafff/{id}', [\App\Http\Controllers\Facility\FacilityStaffController::class, 'update'])->name('staff.updatef');
        Route::delete('/staff/{id}', [\App\Http\Controllers\Facility\FacilityStaffController::class, 'destroy'])->name('staff.destroy');
        
        // Nurse Ward Assignments
        Route::get('/nurse-ward', [\App\Http\Controllers\Facility\FacilityNurseWardController::class, 'index'])->name('nurse-ward.index');
        Route::get('/nurse-ward/create', [\App\Http\Controllers\Facility\FacilityNurseWardController::class, 'create'])->name('nurse-ward.create');
        Route::post('/nurse-ward', [\App\Http\Controllers\Facility\FacilityNurseWardController::class, 'store'])->name('nurse-ward.store');
        Route::get('/nurse-ward/{id}/edit', [\App\Http\Controllers\Facility\FacilityNurseWardController::class, 'edit'])->name('nurse-ward.edit');
        Route::put('/nurse-ward/{id}', [\App\Http\Controllers\Facility\FacilityNurseWardController::class, 'update'])->name('nurse-ward.update');
        Route::delete('/nurse-ward/{id}', [\App\Http\Controllers\Facility\FacilityNurseWardController::class, 'destroy'])->name('nurse-ward.destroy');
        
        // Doctor Ward Assignments
        Route::get('/doctor-ward', [\App\Http\Controllers\DoctorWardController::class, 'index'])->name('doctor-ward.index');
        Route::get('/doctor-ward/create', [\App\Http\Controllers\DoctorWardController::class, 'create'])->name('doctor-ward.create');
        Route::post('/doctor-ward', [\App\Http\Controllers\DoctorWardController::class, 'store'])->name('doctor-ward.store');
        Route::get('/doctor-ward/{id}/edit', [\App\Http\Controllers\DoctorWardController::class, 'edit'])->name('doctor-ward.edit');
        Route::put('/doctor-ward/{id}', [\App\Http\Controllers\DoctorWardController::class, 'update'])->name('doctor-ward.update');
        Route::delete('/doctor-ward/{id}', [\App\Http\Controllers\DoctorWardController::class, 'destroy'])->name('doctor-ward.destroy');
        
        // Facility Services Management
        Route::get('/services', [\App\Http\Controllers\Facility\FacilityServiceController::class, 'index'])->name('services.index');
        Route::get('/services/create', [\App\Http\Controllers\Facility\FacilityServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [\App\Http\Controllers\Facility\FacilityServiceController::class, 'store'])->name('services.store');
        Route::post('/services/{id}/toggle', [\App\Http\Controllers\Facility\FacilityServiceController::class, 'toggle'])->name('services.toggle');
        Route::delete('/services/{id}', [\App\Http\Controllers\Facility\FacilityServiceController::class, 'destroy'])->name('services.destroy');
        Route::get('/services/types-by-category', [\App\Http\Controllers\Facility\FacilityServiceController::class, 'getTypesByCategory'])->name('services.types-by-category');
        
        // Pharmacy Management
        Route::get('/pharmacy', [\App\Http\Controllers\Facility\PharmacyController::class, 'index'])->name('pharmacy.index');
        Route::get('/pharmacy/create', [\App\Http\Controllers\Facility\PharmacyController::class, 'create'])->name('pharmacy.create');
        Route::post('/pharmacy', [\App\Http\Controllers\Facility\PharmacyController::class, 'store'])->name('pharmacy.store');
        Route::get('/pharmacy/{id}/edit', [\App\Http\Controllers\Facility\PharmacyController::class, 'edit'])->name('pharmacy.edit');
        Route::put('/pharmacy/{id}', [\App\Http\Controllers\Facility\PharmacyController::class, 'update'])->name('pharmacy.update');
        Route::delete('/pharmacy/{id}', [\App\Http\Controllers\Facility\PharmacyController::class, 'destroy'])->name('pharmacy.destroy');
        Route::get('/pharmacy/stock', [\App\Http\Controllers\Facility\PharmacyController::class, 'stockForm'])->name('pharmacy.stock');
        Route::post('/pharmacy/stock', [\App\Http\Controllers\Facility\PharmacyController::class, 'updateStock'])->name('pharmacy.stock.update');
        Route::get('/pharmacy/low-stock', [\App\Http\Controllers\Facility\PharmacyController::class, 'lowStock'])->name('pharmacy.low-stock');
        
        // Bulk Drug Creation
        Route::get('/pharmacy/bulk-create', [\App\Http\Controllers\Facility\PharmacyController::class, 'bulkCreate'])->name('pharmacy.bulk-create');
        Route::post('/pharmacy/bulk-store', [\App\Http\Controllers\Facility\PharmacyController::class, 'bulkStore'])->name('pharmacy.bulk-store');
        
        // Excel Import/Export
        Route::get('/pharmacy/import', [\App\Http\Controllers\Facility\PharmacyController::class, 'importForm'])->name('pharmacy.import');
        Route::post('/pharmacy/import', [\App\Http\Controllers\Facility\PharmacyController::class, 'import'])->name('pharmacy.import.store');
        Route::get('/pharmacy/export', [\App\Http\Controllers\Facility\PharmacyController::class, 'export'])->name('pharmacy.export');
        Route::get('/pharmacy/download-template', [\App\Http\Controllers\Facility\PharmacyController::class, 'downloadTemplate'])->name('pharmacy.download-template');
        
        // Stock Management (New System)
        Route::get('/pharmacy/stock-requests', [\App\Http\Controllers\Facility\PharmacyController::class, 'stockRequests'])->name('pharmacy.stock-requests');
        Route::get('/pharmacy/stock-requests/create', [\App\Http\Controllers\Facility\PharmacyController::class, 'createStockRequest'])->name('pharmacy.stock-requests.create');
        Route::post('/pharmacy/stock-requests', [\App\Http\Controllers\Facility\PharmacyController::class, 'storeStockRequest'])->name('pharmacy.stock-requests.store');
        Route::get('/pharmacy/stock-requests/bulk', [\App\Http\Controllers\Facility\PharmacyController::class, 'bulkStockRequest'])->name('pharmacy.stock-requests.bulk');
        Route::post('/pharmacy/stock-requests/bulk', [\App\Http\Controllers\Facility\PharmacyController::class, 'storeBulkStockRequest'])->name('pharmacy.stock-requests.bulk.store');
        Route::get('/pharmacy/stock-requests/{id}', [\App\Http\Controllers\Facility\PharmacyController::class, 'showStockRequest'])->name('pharmacy.stock-requests.show');
        Route::get('/pharmacy/stock-requests/{id}/edit', [\App\Http\Controllers\Facility\PharmacyController::class, 'editStockRequest'])->name('pharmacy.stock-requests.edit');
        Route::put('/pharmacy/stock-requests/{id}', [\App\Http\Controllers\Facility\PharmacyController::class, 'updateStockRequest'])->name('pharmacy.stock-requests.update');
        Route::get('/pharmacy/{id}/stock-details', [\App\Http\Controllers\Facility\PharmacyController::class, 'stockDetails'])->name('pharmacy.stock-details');
        Route::get('/pharmacy/{id}/stock-level', [\App\Http\Controllers\Facility\PharmacyController::class, 'getDrugStock'])->name('pharmacy.get-stock');
        
        // Patient History (Encounters)
        Route::get('/encounters', [\App\Http\Controllers\Facility\FacilityClaimController::class, 'index'])->name('encounters.index');
        Route::get('/encounters/{id}', [\App\Http\Controllers\Facility\FacilityClaimController::class, 'showEncounter'])->name('encounters.show');
        
        // Claims Management
        Route::get('/claims', [\App\Http\Controllers\Facility\FacilityClaimController::class, 'claims'])->name('claims.list');
        Route::get('/claims/billable-items', [\App\Http\Controllers\Facility\FacilityClaimController::class, 'billableItems'])->name('claims.billable');
        Route::post('/claims/store-billable', [\App\Http\Controllers\Facility\FacilityClaimController::class, 'storeFromBillable'])->name('claims.store-billable');
        Route::get('/claims/create/{encounter}', [\App\Http\Controllers\Facility\FacilityClaimController::class, 'create'])->name('claims.create');
        Route::post('/claims', [\App\Http\Controllers\Facility\FacilityClaimController::class, 'store'])->name('claims.store');
        Route::get('/claims/{id}', [\App\Http\Controllers\Facility\FacilityClaimController::class, 'show'])->name('claims.show');
        Route::get('/claims/{id}/edit', [\App\Http\Controllers\Facility\FacilityClaimController::class, 'edit'])->name('claims.edit');
        Route::put('/claims/{id}', [\App\Http\Controllers\Facility\FacilityClaimController::class, 'update'])->name('claims.update');
        Route::delete('/claims/{id}', [\App\Http\Controllers\Facility\FacilityClaimController::class, 'destroy'])->name('claims.destroy');
        
        // Referrals Management
        Route::get('/referrals', [\App\Http\Controllers\Facility\FacilityReferralController::class, 'index'])->name('referrals.index');
        Route::get('/referrals/create/{encounter}', [\App\Http\Controllers\Facility\FacilityReferralController::class, 'create'])->name('referrals.create');
        Route::post('/referrals', [\App\Http\Controllers\Facility\FacilityReferralController::class, 'store'])->name('referrals.store');
        Route::get('/referrals/{id}', [\App\Http\Controllers\Facility\FacilityReferralController::class, 'show'])->name('referrals.show');
        Route::post('/referrals/{id}/update-status', [\App\Http\Controllers\Facility\FacilityReferralController::class, 'updateStatus'])->name('referrals.update-status');
    });
});

// Drug Store (Central Inventory)
Route::prefix('drug-store')->name('drug-store.')->middleware('auth:staff')->group(function () {
    Route::get('/', [\App\Http\Controllers\DrugStoreController::class, 'index'])->name('index')->middleware('permission:drug-store.view,staff');
    Route::get('/stock-in', [\App\Http\Controllers\DrugStoreController::class, 'stockInForm'])->name('stock-in-form')->middleware('permission:drug-store.stock-in,staff');
    Route::post('/stock-in', [\App\Http\Controllers\DrugStoreController::class, 'stockIn'])->name('stock-in')->middleware('permission:drug-store.stock-in,staff');
    Route::get('/{drugId}', [\App\Http\Controllers\DrugStoreController::class, 'show'])->name('show')->middleware('permission:drug-store.view,staff');
    Route::get('/stock/{id}/edit', [\App\Http\Controllers\DrugStoreController::class, 'edit'])->name('edit')->middleware('permission:drug-store.stock-in,staff');
    Route::put('/stock/{id}', [\App\Http\Controllers\DrugStoreController::class, 'update'])->name('update')->middleware('permission:drug-store.stock-in,staff');
    Route::delete('/stock/{id}', [\App\Http\Controllers\DrugStoreController::class, 'destroy'])->name('destroy')->middleware('permission:drug-store.stock-in,staff');
});

// Drug Stock Request Management (Boschma Admin)
Route::prefix('drug-stock-requests')->name('drug-stock-requests.')->group(function () {
    Route::get('/', [\App\Http\Controllers\DrugStockRequestController::class, 'index'])->name('index')->middleware('auth:staff', 'permission:drug-stock-requests.view,staff');
    Route::get('/facility/{facilityId}', [\App\Http\Controllers\DrugStockRequestController::class, 'facilityRequests'])->name('facility-requests')->middleware('auth:staff', 'permission:drug-stock-requests.view,staff');
    Route::get('/create', [\App\Http\Controllers\DrugStockRequestController::class, 'create'])->name('create')->middleware('permission:drug-stock-requests.create,staff');
    Route::post('/', [\App\Http\Controllers\DrugStockRequestController::class, 'store'])->name('store')->middleware('permission:drug-stock-requests.create,staff');
    Route::get('/{id}', [\App\Http\Controllers\DrugStockRequestController::class, 'show'])->name('show')->middleware('auth:staff', 'permission:drug-stock-requests.view,staff');
    Route::post('/{id}/update-items', [\App\Http\Controllers\DrugStockRequestController::class, 'updateItems'])->name('update-items')->middleware('auth:staff', 'permission:drug-stock-requests.edit,staff');
    Route::get('/{id}/edit', [\App\Http\Controllers\DrugStockRequestController::class, 'edit'])->name('edit')->middleware('permission:drug-stock-requests.edit,staff');
    Route::put('/{id}', [\App\Http\Controllers\DrugStockRequestController::class, 'update'])->name('update')->middleware('permission:drug-stock-requests.edit,staff');
    Route::post('/{id}/approve', [\App\Http\Controllers\DrugStockRequestController::class, 'approve'])->name('approve')->middleware('permission:drug-stock-requests.approve,staff');
    Route::post('/{id}/reject', [\App\Http\Controllers\DrugStockRequestController::class, 'reject'])->name('reject')->middleware('permission:drug-stock-requests.reject,staff');
    Route::post('/bulk-approve', [\App\Http\Controllers\DrugStockRequestController::class, 'bulkApprove'])->name('bulk-approve')->middleware('permission:drug-stock-requests.approve,staff');
    Route::post('/bulk-reject', [\App\Http\Controllers\DrugStockRequestController::class, 'bulkReject'])->name('bulk-reject')->middleware('permission:drug-stock-requests.reject,staff');
    Route::get('/{id}/dispense', [\App\Http\Controllers\DrugStockRequestController::class, 'dispenseForm'])->name('dispense-form')->middleware('permission:drug-stock-requests.dispense,staff');
    Route::post('/{id}/dispense', [\App\Http\Controllers\DrugStockRequestController::class, 'dispense'])->name('dispense')->middleware('permission:drug-stock-requests.dispense,staff');
});

// Facility Wallets Management
Route::prefix('wallets')->name('wallets.')->middleware('auth:staff')->group(function () {
    Route::get('/', [\App\Http\Controllers\WalletController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\WalletController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\WalletController::class, 'store'])->name('store');
    Route::get('/{id}', [\App\Http\Controllers\WalletController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [\App\Http\Controllers\WalletController::class, 'edit'])->name('edit');
    Route::put('/{id}', [\App\Http\Controllers\WalletController::class, 'update'])->name('update');
    Route::get('/{id}/fund', [\App\Http\Controllers\WalletController::class, 'fundForm'])->name('fund-form');
    Route::post('/{id}/fund', [\App\Http\Controllers\WalletController::class, 'fund'])->name('fund');
    Route::get('/transactions/{id}/edit', [\App\Http\Controllers\WalletController::class, 'editFund'])->name('edit-fund');
    Route::put('/transactions/{id}', [\App\Http\Controllers\WalletController::class, 'updateFund'])->name('update-fund');
    Route::get('/check-balance/{facilityId}/{programId}', [\App\Http\Controllers\WalletController::class, 'checkBalance'])->name('check-balance');
});

// Debug route to check drugs
Route::get('/debug-drugs', function () {
    echo "Database: " . config('database.default') . "<br>";
    echo "Drugs count: " . \App\Models\Drug::count() . "<br>";
    
    $drugs = \App\Models\Drug::take(5)->get();
    foreach ($drugs as $drug) {
        echo "Drug: " . $drug->name . " - Facility: " . $drug->facility_id . " - Status: '" . $drug->status . "' - Price: " . $drug->unit_price . "<br>";
    }
    
    echo "<br>DrugStocks count: " . \App\Models\DrugStock::count() . "<br>";
    
    $user = auth()->guard('web')->user();
    if ($user) {
        echo "Current user facility: " . $user->facility_id . "<br>";
        
        $facilityDrugs = \App\Models\Drug::where('facility_id', $user->facility_id)->get();
        echo "Facility drugs count: " . $facilityDrugs->count() . "<br>";
        
        $allAvailableDrugs = \App\Models\Drug::where('facility_id', $user->facility_id)
                                         ->orWhere('facility_id', 0)
                                         ->get();
        echo "Available drugs (facility + 0): " . $allAvailableDrugs->count() . "<br>";
    } else {
        echo "No authenticated user<br>";
    }
    
    echo "<br>All drug columns: " . implode(', ', array_keys(\App\Models\Drug::first()->getAttributes())) . "<br>";
});
