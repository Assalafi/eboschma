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
    Route::resource('staff', \App\Http\Controllers\StaffController::class);
    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    Route::resource('permissions', \App\Http\Controllers\PermissionController::class);
    
    // Beneficiary Management (Protected by permissions)
    
    Route::get('beneficiaries/verify', [BeneficiaryController::class, 'verify'])->name('beneficiaries.verify')->middleware('permission:beneficiary.view,staff');
    Route::get('beneficiaries/verify-nin/{nin}', [BeneficiaryController::class, 'verifyNin'])->name('beneficiaries.verify-nin')->middleware('permission:beneficiary.create|beneficiary.edit,staff');
    Route::get('beneficiaries/verify-dp/{dpNo}', [BeneficiaryController::class, 'verifyDp'])->name('beneficiaries.verify-dp')->middleware('permission:beneficiary.create|beneficiary.edit,staff');
    Route::post('beneficiaries/bulk-action', [BeneficiaryController::class, 'bulkAction'])->name('beneficiaries.bulk-action')->middleware('permission:beneficiary.delete,staff');
    Route::patch('beneficiaries/{beneficiary}/update-status', [BeneficiaryController::class, 'updateStatus'])->name('beneficiaries.update-status')->middleware('permission:beneficiary.approve,staff');
    Route::get('beneficiaries/{beneficiary}/pdf', [App\Http\Controllers\BeneficiaryController::class, 'downloadPdf'])->name('beneficiaries.pdf')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/{beneficiary}/id-card', [App\Http\Controllers\BeneficiaryController::class, 'generateIdCard'])->name('beneficiaries.id-card')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/{beneficiary}/id-card/download', [App\Http\Controllers\BeneficiaryController::class, 'downloadIdCard'])->name('beneficiaries.id-card.download')->middleware('permission:beneficiary.export,staff');
    Route::get('bulk-id-cards', [BeneficiaryController::class, 'bulkIdCards'])->name('beneficiaries.bulk-id-cards')->middleware('permission:beneficiary.export,staff');
    Route::post('beneficiaries/bulk-id-cards/start', [BeneficiaryController::class, 'startBulkIdCardGeneration'])->name('beneficiaries.bulk-id-cards.start')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/bulk-id-cards/status/{jobId}', [BeneficiaryController::class, 'getBulkIdCardJobStatus'])->name('beneficiaries.bulk-id-cards.status')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/bulk-id-cards/download/{jobId}', [BeneficiaryController::class, 'downloadBulkIdCardFile'])->name('beneficiaries.bulk-id-cards.download-file')->middleware('permission:beneficiary.export,staff');
    Route::post('beneficiaries/bulk-id-cards/cancel/{jobId}', [BeneficiaryController::class, 'cancelBulkIdCardJob'])->name('beneficiaries.bulk-id-cards.cancel')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/bulk-id-cards/jobs', [BeneficiaryController::class, 'getBulkIdCardJobs'])->name('beneficiaries.bulk-id-cards.jobs')->middleware('permission:beneficiary.export,staff');
    
    // Legacy routes for backward compatibility
    Route::post('beneficiaries/bulk-id-cards/generate', [BeneficiaryController::class, 'generateBulkIdCards'])->name('beneficiaries.bulk-id-cards.generate')->middleware('permission:beneficiary.export,staff');
    Route::get('beneficiaries/bulk-id-cards/download/{filename}', [BeneficiaryController::class, 'downloadBulkIdCards'])->name('beneficiaries.bulk-id-cards.download')->middleware('permission:beneficiary.export,staff');
    Route::post('beneficiaries/save-section', [BeneficiaryController::class, 'saveSection'])->name('beneficiaries.saveSection')->middleware('permission:beneficiary.create|beneficiary.edit,staff');
    Route::get('beneficiaries/{beneficiary}/load-data', [BeneficiaryController::class, 'loadData'])->name('beneficiaries.loadData')->middleware('permission:beneficiary.view,staff');
    
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
    Route::get('civil-servants/download/template', [CivilServantController::class, 'downloadTemplate'])->name('civil-servants.download.template');
    Route::resource('civil-servants', CivilServantController::class);
    
    // Reports Management (Protected by permissions)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index')->middleware('permission:report.view,staff');
        Route::get('/export', [ReportsController::class, 'exportDashboard'])->name('export')->middleware('permission:report.view,staff');
        Route::get('/enumerators', [ReportsController::class, 'enumerators'])->name('enumerators')->middleware('permission:report.view,staff');
        Route::get('/enumerators/export', [ReportsController::class, 'exportEnumerators'])->name('enumerators.export')->middleware('permission:report.view,staff');
        Route::get('/enumerators/{id}/enrollments', [ReportsController::class, 'enumeratorEnrollments'])->name('enumerators.enrollments')->middleware('permission:report.view,staff');
        Route::get('/enumerators/{id}/enrollments/export', [ReportsController::class, 'exportEnumeratorEnrollments'])->name('enumerators.enrollments.export')->middleware('permission:report.view,staff');
        Route::get('/facilities', [ReportsController::class, 'facilities'])->name('facilities')->middleware('permission:report.view,staff');
        Route::get('/facilities/export', [ReportsController::class, 'exportFacilities'])->name('facilities.export')->middleware('permission:report.view,staff');
        Route::get('/facilities/{id}/enrollments', [ReportsController::class, 'facilityEnrollments'])->name('facilities.show')->middleware('permission:report.view,staff');
        Route::get('/enrollments', [ReportsController::class, 'enrollments'])->name('enrollments')->middleware('permission:report.view,staff');
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
});
