<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimHistory;
use App\Models\ClaimNote;
use App\Models\ClaimMedication;
use App\Models\ClaimLaboratoryTest;
use App\Models\ClaimRenderedService;
use App\Models\ClaimDocument;
use App\Models\Staff;
use App\Models\Drug;
use App\Models\LaboratoryTest;
use App\Models\Service;
use App\Models\Beneficiary;
use App\Models\Spouse;
use App\Models\Child;
use App\Models\Patient;
use App\Models\Facility;
use App\Imports\ClaimsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Exports\ClaimsExport;

class ClaimController extends Controller
{
    /**
     * Display a listing of facilities with claims.
     */
    public function index(Request $request)
    {
        // Get facilities with claims statistics - using facility_claims as base
        $facilities = DB::table('facility_claims as fc')
            ->join('facilities as f', 'fc.facility_id', '=', 'f.id')
            ->whereNull('fc.deleted_at')
            ->select([
                'f.name as facility_name',
                'f.id as facility_id',
                DB::raw('COUNT(DISTINCT fc.id) as total_claims'),
                DB::raw('SUM(CASE WHEN fc.status = "submitted" THEN 1 ELSE 0 END) as pending_claims'),
                DB::raw('SUM(CASE WHEN fc.status = "approved" THEN 1 ELSE 0 END) as approved_claims'),
                DB::raw('SUM(CASE WHEN fc.status = "rejected" THEN 1 ELSE 0 END) as rejected_claims'),
                DB::raw('SUM(CASE WHEN fc.status = "paid" THEN 1 ELSE 0 END) as paid_claims'),
                DB::raw('COALESCE(SUM(fc.total_amount), 0) as total_value'),
                DB::raw('COALESCE(SUM(CASE WHEN fc.status = "approved" OR fc.status = "paid" THEN fc.total_amount ELSE 0 END), 0) as approved_value'),
                DB::raw('MAX(fc.service_date) as latest_service_date'),
                DB::raw('MAX(fc.created_at) as latest_claim_date'),
                DB::raw('SUM(CASE WHEN fc.status = "submitted" OR fc.status = "under_review" THEN 1 ELSE 0 END) as ro_pending'),
                DB::raw('SUM(CASE WHEN fc.status = "under_review" THEN 1 ELSE 0 END) as ro_approved'),
                DB::raw('0 as ro_rejected'),
                DB::raw('SUM(CASE WHEN fc.status = "under_review" THEN 1 ELSE 0 END) as e5_pending'),
                DB::raw('0 as e5_approved'),
                DB::raw('0 as e5_rejected'),
            ])
            ->groupBy('f.id', 'f.name')
            ->orderBy('latest_claim_date', 'desc')
            ->get()
            ->map(function ($facility) {
                $facility->latest_claim_date = $facility->latest_claim_date ? Carbon::parse($facility->latest_claim_date) : now()->subMonths(6);
                $facility->latest_service_date = $facility->latest_service_date ? Carbon::parse($facility->latest_service_date) : null;
                $facility->number_of_patients = DB::table('facility_claims')
                    ->where('facility_id', $facility->facility_id)
                    ->distinct('patient_id')
                    ->count('patient_id');
                return $facility;
            });

        // Get overall statistics for approval workflow from facility_claims
        $stats = [
            'ro_pending' => DB::table('facility_claims')
                                ->whereNull('deleted_at')
                                ->where('status', 'submitted')
                                ->where(function($q) {
                                    $q->whereNull('ro_status')->orWhere('ro_status', '');
                                })
                                ->count(),
            'e5_pending' => DB::table('facility_claims')
                                ->whereNull('deleted_at')
                                ->where('ro_status', 'approved')
                                ->where(function($q) {
                                    $q->whereNull('e5_status')->orWhere('e5_status', '');
                                })
                                ->count(),
            'approved' => DB::table('facility_claims')->whereNull('deleted_at')->where('status', 'approved')->count(),
            'paid' => DB::table('facility_claims')->whereNull('deleted_at')->where('status', 'paid')->count(),
            'total_claims' => DB::table('facility_claims')->whereNull('deleted_at')->count(),
            'total_value' => DB::table('facility_claims')->whereNull('deleted_at')->sum('total_amount'),
            'this_month_claims' => DB::table('facility_claims')
                                       ->whereNull('deleted_at')
                                       ->whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->count(),
            'this_month_value' => DB::table('facility_claims')
                                       ->whereNull('deleted_at')
                                       ->whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->sum('total_amount'),
        ];

        // Get recent activity from facility_claims
        $recentActivity = DB::table('facility_claims as fc')
            ->join('facilities as f', 'fc.facility_id', '=', 'f.id')
            ->whereNull('fc.deleted_at')
            ->select('fc.*', 'f.name as facility_name')
            ->orderBy('fc.updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($claim) {
                $action = 'Updated';
                if ($claim->status === 'pending' || $claim->status === 'submitted') {
                    $action = 'Submitted';
                } elseif ($claim->status === 'approved') {
                    $action = 'Approved';
                } elseif ($claim->status === 'rejected') {
                    $action = 'Rejected';
                } elseif ($claim->status === 'paid') {
                    $action = 'Paid';
                }
                
                return [
                    'time' => \Carbon\Carbon::parse($claim->updated_at)->diffForHumans(),
                    'title' => $action . ' - ' . ($claim->claim_number ?? 'CLM-'.$claim->id),
                    'description' => ($claim->patient_name ?? 'Patient') . ' - ₦' . number_format($claim->total_amount ?? 0, 2),
                    'type' => $claim->status === 'approved' ? 'success' : ($claim->status === 'rejected' ? 'danger' : 'primary'),
                ];
            });

        // Get recent claims for display from facility_claims
        $recentClaims = DB::table('facility_claims as fc')
            ->join('facilities as f', 'fc.facility_id', '=', 'f.id')
            ->whereNull('fc.deleted_at')
            ->select(
                'fc.*', 
                'f.name as healthcare_provider',
                'fc.patient_name as beneficiary_name',
                'fc.boschma_no as boschma_id',
                'fc.claim_number as authorization_code',
                'fc.total_amount as claim_amount'
            )
            ->orderBy('fc.created_at', 'desc')
            ->paginate(20);

        return view('claims.index', compact('facilities', 'stats', 'recentActivity', 'recentClaims'));
    }

    /**
     * Show the form for creating a new claim.
     */
    public function create()
    {
        $facilities = Facility::select('id', 'name', 'type', 'lga', 'ward')
                              ->orderBy('name')
                              ->get();
        
        return view('claims.create', compact('facilities'));
    }

    /**
     * Store a newly created claim in storage.
     */
    public function store(Request $request)
    {
        // Debug logging
        \Log::info('🚀 Claim store method started');
        \Log::info('📋 Request data:', $request->all());
        \Log::info('📋 Request files:', $request->allFiles());
        
        try {
            $validated = $request->validate([
                'patient_id' => 'required|string|max:255',
                'facility_id' => 'required|integer|exists:facilities,id',
                'authorization_code' => 'required|string|max:100',
                'service_date' => 'required|date|before_or_equal:today',
                'diagnosis' => 'required|string|max:1000',
                'claim_amount' => 'required|numeric|min:0|max:1000000',
                'operation_sheets.*' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:5120',
                'prescription_sheets.*' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:5120',
                'other_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
            ]);
            
            \Log::info('✅ Validation passed');
            \Log::info('📦 Validated data:', $validated);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Validation failed:');
            \Log::error('  - Errors:', $e->errors());
            \Log::error('  - Request data:', $request->all());
            throw $e;
        }

        // Get patient information
        \Log::info('🔍 Looking up patient with ID: ' . $validated['patient_id']);
        $patient = Patient::find($validated['patient_id']);
        if (!$patient) {
            \Log::error('❌ Patient not found with ID: ' . $validated['patient_id']);
            return back()->withErrors(['patient_id' => 'Selected patient not found']);
        }
        
        \Log::info('✅ Patient found: ' . $patient->enrollee_number . ' (' . $patient->enrollee_type . ')');

        // Get patient details based on enrollee type
        \Log::info('🔍 Getting patient details for enrollee type: ' . $patient->enrollee_type);
        $patientDetails = null;
        switch ($patient->enrollee_type) {
            case 'beneficiary':
                $patientDetails = Beneficiary::where('boschma_no', $patient->enrollee_number)->first();
                break;
            case 'spouse':
                $patientDetails = Spouse::where('boschma_no', $patient->enrollee_number)->first();
                break;
            case 'child':
                $patientDetails = Child::where('boschma_no', $patient->enrollee_number)->first();
                break;
        }

        if (!$patientDetails) {
            \Log::error('❌ Patient details not found for enrollee: ' . $patient->enrollee_number);
            return back()->withErrors(['patient_id' => 'Patient details not found']);
        }
        
        \Log::info('✅ Patient details found: ' . ($patientDetails->fullname ?? $patientDetails->name));

        // Get facility information
        \Log::info('🔍 Looking up facility with ID: ' . $validated['facility_id']);
        $facility = Facility::find($validated['facility_id']);
        if (!$facility) {
            \Log::error('❌ Facility not found with ID: ' . $validated['facility_id']);
            return back()->withErrors(['facility_id' => 'Selected facility not found']);
        }
        
        \Log::info('✅ Facility found: ' . $facility->name . ' (' . $facility->type . ')');

        // Map facility type to accepted ENUM values
        $providerTypeMap = [
            'Primary Care' => 'clinic',
            'Secondary' => 'hospital',
            'Tertiary' => 'hospital',
            'hospital' => 'hospital',
            'clinic' => 'clinic',
            'pharmacy' => 'pharmacy',
            'laboratory' => 'laboratory',
            'diagnostic_center' => 'diagnostic_center',
        ];
        
        $mappedProviderType = $providerTypeMap[$facility->type] ?? 'clinic';

        // Prepare claim data using facility_id reference
        $claimData = [
            'authorization_code' => $validated['authorization_code'],
            'service_date' => $validated['service_date'],
            'diagnosis' => $validated['diagnosis'],
            'claim_amount' => $validated['claim_amount'],
            'status' => 'pending', // Use string instead of constant
            'created_by' => auth()->id(),
            // Add patient info for reference (existing fields)
            'beneficiary_name' => $patientDetails->fullname ?? $patientDetails->name,
            'boschma_id' => $patient->enrollee_number,
            'nin' => $patientDetails->nin ?? '',
            'phone_number' => $patientDetails->phone_no ?? $patientDetails->phone ?? '',
            // Add facility info using proper facility_id reference
            'facility_id' => $facility->id,
            'healthcare_provider' => $facility->name,
            'provider_type' => $mappedProviderType,
        ];

        \Log::info('🔄 Starting database transaction');
        DB::beginTransaction();
        try {
            \Log::info('📝 Creating claim with data:', $claimData);
            $claim = Claim::create($claimData);
            \Log::info('✅ Claim created with ID: ' . $claim->id);

            // Handle medications
            if ($request->has('medications')) {
                $medications = json_decode($request->input('medications'), true);
                \Log::info('💊 Processing ' . count($medications) . ' medications');
                foreach ($medications as $med) {
                    ClaimMedication::create([
                        'claim_id' => $claim->id,
                        'medication_name' => $med['name'],
                        'cost' => $med['cost'],
                        'frequency' => $med['quantity'],
                        'days' => $med['days'],
                        'claimed_amount' => $med['total'],
                    ]);
                }
            }

            // Handle laboratory tests
            if ($request->has('laboratories')) {
                $laboratories = json_decode($request->input('laboratories'), true);
                \Log::info('🔬 Processing ' . count($laboratories) . ' laboratory tests');
                foreach ($laboratories as $lab) {
                    ClaimLaboratoryTest::create([
                        'claim_id' => $claim->id,
                        'test_name' => $lab['name'],
                        'cost' => $lab['price'],
                        'frequency' => $lab['frequency'],
                        'claimed_amount' => $lab['total'],
                    ]);
                }
            }

            // Handle services
            if ($request->has('services')) {
                $services = json_decode($request->input('services'), true);
                \Log::info('🏥 Processing ' . count($services) . ' services');
                foreach ($services as $service) {
                    ClaimRenderedService::create([
                        'claim_id' => $claim->id,
                        'service_name' => $service['name'],
                        'cost' => $service['price'],
                        'frequency' => $service['frequency'],
                        'claimed_amount' => $service['total'],
                    ]);
                }
            }

            // Handle document uploads
            if ($request->hasFile('operation_sheets')) {
                $operationSheets = $request->file('operation_sheets');
                \Log::info('📄 Processing ' . count($operationSheets) . ' operation sheets');
                foreach ($operationSheets as $file) {
                    $path = $file->store('claims/operation_sheets', 'public');
                    ClaimDocument::create([
                        'claim_id' => $claim->id,
                        'document_type' => 'operation_sheet',
                        'document_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            if ($request->hasFile('prescription_sheets')) {
                $prescriptionSheets = $request->file('prescription_sheets');
                \Log::info('📄 Processing ' . count($prescriptionSheets) . ' prescription sheets');
                foreach ($prescriptionSheets as $file) {
                    $path = $file->store('claims/prescription_sheets', 'public');
                    ClaimDocument::create([
                        'claim_id' => $claim->id,
                        'document_type' => 'prescription_sheet',
                        'document_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            if ($request->hasFile('other_documents')) {
                $otherDocuments = $request->file('other_documents');
                \Log::info('📄 Processing ' . count($otherDocuments) . ' other documents');
                foreach ($otherDocuments as $file) {
                    $path = $file->store('claims/other_documents', 'public');
                    ClaimDocument::create([
                        'claim_id' => $claim->id,
                        'document_type' => 'other',
                        'document_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Add history record
            $claim->addHistory('created', 'Claim created successfully', auth()->id());
            \Log::info('📝 History record added');

            DB::commit();
            \Log::info('✅ Database transaction committed successfully');

            \Log::info('🎉 Claim creation completed successfully! Redirecting to claim show page.');
            return redirect()->route('claims.show', $claim->id)
                           ->with('success', 'Claim created successfully with authorization code: ' . $claim->authorization_code);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ Exception occurred during claim creation:');
            \Log::error('  - Error message: ' . $e->getMessage());
            \Log::error('  - File: ' . $e->getFile());
            \Log::error('  - Line: ' . $e->getLine());
            \Log::error('  - Trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error creating claim: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified claim.
     */
    public function show($id)
    {
        $claim = Claim::with(['creator', 'updater', 'approver', 'rejecter', 'payer', 
                             'roReviewer', 'e5Reviewer', 'history.user', 'notes.user',
                             'medications', 'laboratoryTests', 'renderedServices', 'documents'])
                     ->findOrFail($id);
        
        return view('claims.show', compact('claim'));
    }

    /**
     * Show the form for editing the specified claim.
     */
    public function edit($id)
    {
        $claim = Claim::findOrFail($id);
        
        if (!$claim->canBeEdited() && !auth()->user()->hasRole(['admin', 'claims_manager'])) {
            return redirect()->route('claims.show', $claim->id)
                           ->with('error', 'This claim cannot be edited as it has already been processed.');
        }

        $claimTypes = Claim::getClaimTypes();
        $providerTypes = Claim::getProviderTypes();

        return view('claims.edit', compact('claim', 'claimTypes', 'providerTypes'));
    }

    /**
     * Update the specified claim in storage.
     */
    public function update(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        
        if (!$claim->canBeEdited() && !auth()->user()->hasRole(['admin', 'claims_manager'])) {
            return redirect()->route('claims.show', $claim->id)
                           ->with('error', 'This claim cannot be edited as it has already been processed.');
        }

        $validated = $request->validate([
            'beneficiary_name' => 'required|string|max:255',
            'boschma_id' => 'required|string|max:50',
            'nin' => 'nullable|string|size:11',
            'phone_number' => 'nullable|string|max:20',
            'claim_type' => ['required', Rule::in(array_keys(Claim::getClaimTypes()))],
            'healthcare_provider' => 'required|string|max:255',
            'provider_type' => ['required', Rule::in(array_keys(Claim::getProviderTypes()))],
            'service_date' => 'required|date|before_or_equal:today',
            'claim_amount' => 'required|numeric|min:0|max:1000000',
            'diagnosis' => 'nullable|string|max:1000',
            'treatment_description' => 'nullable|string|max:1000',
            'additional_notes' => 'nullable|string|max:1000',
            'medical_report' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'prescription' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'change_reason' => 'required|string|max:500',
        ]);

        // Admin/manager can change status
        if (auth()->user()->hasRole(['admin', 'claims_manager'])) {
            $validated['status'] = $request->input('status', $claim->status);
            $validated['ro_status'] = $request->input('ro_status', $claim->ro_status);
            $validated['e5_status'] = $request->input('e5_status', $claim->e5_status);
            $validated['rejection_reason'] = $request->input('rejection_reason');
            $validated['payment_reference'] = $request->input('payment_reference');
            $validated['payment_date'] = $request->input('payment_date') ? Carbon::parse($request->input('payment_date')) : null;
        }

        $validated['updated_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $oldStatus = $claim->status;
            $claim->update($validated);

            // Handle file uploads
            if ($request->hasFile('medical_report')) {
                if ($claim->medical_report) {
                    Storage::disk('public')->delete($claim->medical_report);
                }
                $claim->medical_report = $request->file('medical_report')->store('claims/medical_reports', 'public');
                $claim->save();
            }
            
            if ($request->hasFile('prescription')) {
                if ($claim->prescription) {
                    Storage::disk('public')->delete($claim->prescription);
                }
                $claim->prescription = $request->file('prescription')->store('claims/prescriptions', 'public');
                $claim->save();
            }
            
            if ($request->hasFile('receipt')) {
                if ($claim->receipt) {
                    Storage::disk('public')->delete($claim->receipt);
                }
                $claim->receipt = $request->file('receipt')->store('claims/receipts', 'public');
                $claim->save();
            }

            // Add history record
            $claim->addHistory('updated', 'Claim updated: ' . $validated['change_reason'], auth()->id());

            // Handle status changes
            if ($oldStatus !== $claim->status) {
                switch ($claim->status) {
                    case Claim::STATUS_APPROVED:
                        $claim->approve(auth()->id());
                        break;
                    case Claim::STATUS_REJECTED:
                        $claim->reject($validated['rejection_reason'], auth()->id());
                        break;
                    case Claim::STATUS_PAID:
                        $claim->markAsPaid($validated['payment_reference'], $validated['payment_date'], auth()->id());
                        break;
                }
            }

            DB::commit();

            return redirect()->route('claims.show', $claim->id)
                           ->with('success', 'Claim updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error updating claim: ' . $e->getMessage());
        }
    }

    /**
     * Display claims for a specific facility.
     */
    public function facilityList(Request $request, $facility)
    {
        // Get facility by ID or name (for backward compatibility)
        $facilityModel = null;
        if (is_numeric($facility)) {
            $facilityModel = Facility::find($facility);
        } else {
            $facilityModel = Facility::where('name', $facility)->first();
        }
        
        if (!$facilityModel) {
            return redirect()->route('claims.index')->with('error', 'Facility not found');
        }

        $claims = Claim::where('facility_id', $facilityModel->id)
                       ->with(['creator', 'updater', 'facility'])
                       ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $claims->where('status', $request->status);
        }
        
        if ($request->filled('claim_type')) {
            $claims->where('claim_type', $request->claim_type);
        }
        
        if ($request->filled('date_from')) {
            $claims->whereDate('service_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $claims->whereDate('service_date', '<=', $request->date_to);
        }
        
        if ($request->filled('search')) {
            $claims->search($request->search);
        }

        $claims = $claims->paginate(15);

        // Get facility statistics using facility_id
        $statistics = [
            'total_claims' => Claim::where('facility_id', $facilityModel->id)->count(),
            'pending_claims' => Claim::where('facility_id', $facilityModel->id)->pending()->count(),
            'approved_claims' => Claim::where('facility_id', $facilityModel->id)->approved()->count(),
            'rejected_claims' => Claim::where('facility_id', $facilityModel->id)->rejected()->count(),
            'paid_claims' => Claim::where('facility_id', $facilityModel->id)->paid()->count(),
            'total_value' => Claim::where('facility_id', $facilityModel->id)->sum('claim_amount'),
        ];

        return view('claims.facility-list', compact('claims', 'facility', 'statistics', 'facilityModel'));
    }

    /**
     * Approve the specified claim.
     * Super-admin and admin can perform ALL workflow actions.
     */
    public function approve(Request $request, $id)
    {
        // First try to find facility claim
        $claim = DB::table('facility_claims')->where('id', $id)->first();
        
        if ($claim) {
            $approvalType = $request->input('approval_type'); // 'verifier', 'approver', 'es', 'finance'
            $notes = $request->input('notes');
            // Try staff guard first, then fall back to default guard
            $user = auth('staff')->user() ?: auth()->user();
            $isSuperAdmin = $user && ($user->hasRole('Super Admin') || $user->hasRole('admin'));

            try {
                $updateData = [];
                $staffId = $user->id ?? null;
                
                switch ($approvalType) {
                    case 'verifier':
                        if (!$isSuperAdmin && !$user->can('claim.verify')) {
                            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                        }
                        $updateData = [
                            'verifier_status' => 'approved',
                            'verifier_notes' => $notes,
                            'verifier_updated_at' => now(),
                            'verifier_id' => $staffId,
                            'status' => 'verified',
                        ];
                        break;
                        
                    case 'approver':
                        if (!$isSuperAdmin && !$user->can('claim.approve')) {
                            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                        }
                        $updateData = [
                            'approver_status' => 'approved',
                            'approver_notes' => $notes,
                            'approver_updated_at' => now(),
                            'approver_id' => $staffId,
                            'status' => 'approved',
                        ];
                        break;
                        
                    case 'es':
                        if (!$isSuperAdmin && !$user->can('claim.es-approve')) {
                            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                        }
                        $updateData = [
                            'es_status' => 'approved',
                            'es_notes' => $notes,
                            'es_updated_at' => now(),
                            'es_id' => $staffId,
                            'status' => 'es_approved',
                        ];
                        break;
                        
                    case 'finance':
                        if (!$isSuperAdmin && !$user->can('claim.finance-approve')) {
                            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                        }
                        $updateData = [
                            'finance_status' => 'paid',
                            'finance_notes' => $notes,
                            'finance_updated_at' => now(),
                            'finance_id' => $staffId,
                            'payment_date' => now(),
                            'paid_by' => $staffId,
                            'status' => 'paid'
                        ];
                        break;
                        
                    default:
                        return response()->json(['success' => false, 'message' => 'Invalid approval type']);
                }
                
                DB::table('facility_claims')->where('id', $id)->update($updateData);

                if ($request->expectsJson()) {
                    return response()->json(['success' => true, 'message' => 'Claim approved successfully']);
                }
                return back()->with('success', 'Claim approved successfully');
            } catch (\Exception $e) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
                }
                return back()->with('error', 'Error: ' . $e->getMessage());
            }
        }
        
        // Fallback to old claims system
        $claim = Claim::findOrFail($id);
        
        if (!$claim->canBeApproved()) {
            return response()->json(['success' => false, 'message' => 'This claim cannot be approved.']);
        }

        try {
            $claim->approve(auth()->id());
            return response()->json(['success' => true, 'message' => 'Claim approved successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error approving claim: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject the specified claim.
     * Rejection goes back to the PREVIOUS level only (not reject everything).
     * Comments are required. The previous level can then re-approve forward.
     */
    public function reject(Request $request, $id)
    {
        // First try to find facility claim
        $claim = DB::table('facility_claims')->where('id', $id)->first();
        
        if ($claim) {
            $rejectionReason = $request->input('rejection_reason');
            $rejectStage = $request->input('reject_stage'); // which stage is rejecting

            if (empty($rejectionReason)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Rejection comments are required'], 400);
                }
                return back()->with('error', 'Rejection comments are required');
            }

            // Permission check
            // Try staff guard first, then fall back to default guard
            $user = auth('staff')->user() ?: auth()->user();
            $isSuperAdmin = $user && ($user->hasRole('Super Admin') || $user->hasRole('admin'));

            try {
                $updateData = [];
                $staffId = $user->id ?? null;

                // Determine which stage is rejecting and send back to previous level
                switch ($rejectStage) {
                    case 'verifier':
                        if (!$isSuperAdmin && !$user->can('claim.verify')) {
                            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                        }
                        // Verifier rejects → goes back to submitted (facility must resubmit)
                        $updateData = [
                            'verifier_status' => 'rejected',
                            'verifier_notes' => $rejectionReason,
                            'verifier_updated_at' => now(),
                            'verifier_id' => $staffId,
                            'status' => 'submitted', // back to submitted
                        ];
                        break;

                    case 'approver':
                        if (!$isSuperAdmin && !$user->can('claim.approve')) {
                            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                        }
                        // Approver rejects → goes back to verifier
                        $updateData = [
                            'approver_status' => 'rejected',
                            'approver_notes' => $rejectionReason,
                            'approver_updated_at' => now(),
                            'approver_id' => $staffId,
                            'verifier_status' => 'pending',
                            'verifier_notes' => null,
                            'verifier_updated_at' => null,
                            'verifier_id' => null,
                            'status' => 'submitted',
                        ];
                        break;

                    case 'es':
                        if (!$isSuperAdmin && !$user->can('claim.es-approve')) {
                            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                        }
                        // ES rejects → goes back to approver
                        $updateData = [
                            'es_status' => 'rejected',
                            'es_notes' => $rejectionReason,
                            'es_updated_at' => now(),
                            'es_id' => $staffId,
                            'approver_status' => 'pending',
                            'approver_notes' => null,
                            'approver_updated_at' => null,
                            'approver_id' => null,
                            'status' => 'verified',
                        ];
                        break;

                    case 'finance':
                        if (!$isSuperAdmin && !$user->can('claim.finance-approve')) {
                            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                        }
                        // Finance rejects → goes back to ES
                        $updateData = [
                            'finance_status' => 'rejected',
                            'finance_notes' => $rejectionReason,
                            'finance_updated_at' => now(),
                            'finance_id' => $staffId,
                            'es_status' => 'pending',
                            'es_notes' => null,
                            'es_updated_at' => null,
                            'es_id' => null,
                            'status' => 'approved',
                        ];
                        break;

                    default:
                        return response()->json(['success' => false, 'message' => 'Invalid rejection stage']);
                }

                DB::table('facility_claims')->where('id', $id)->update($updateData);

                if ($request->expectsJson()) {
                    return response()->json(['success' => true, 'message' => 'Claim rejected and sent back to previous level']);
                }
                return back()->with('success', 'Claim rejected and sent back to previous level');
            } catch (\Exception $e) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
                }
                return back()->with('error', 'Error: ' . $e->getMessage());
            }
        }
        
        // Fallback to old claims system
        $claim = Claim::findOrFail($id);
        
        if (!$claim->canBeRejected()) {
            return response()->json(['success' => false, 'message' => 'This claim cannot be rejected.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            $claim->reject($validated['rejection_reason'], auth()->id());
            return response()->json(['success' => true, 'message' => 'Claim rejected successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error rejecting claim: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark the specified claim as paid.
     */
    public function markAsPaid(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        
        if (!$claim->canBePaid()) {
            return response()->json(['success' => false, 'message' => 'This claim cannot be marked as paid.']);
        }

        $validated = $request->validate([
            'payment_reference' => 'required|string|max:100',
            'payment_date' => 'required|date'
        ]);

        try {
            $claim->markAsPaid($validated['payment_reference'], $validated['payment_date'], auth()->id());
            return response()->json(['success' => true, 'message' => 'Claim marked as paid successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error marking claim as paid: ' . $e->getMessage()]);
        }
    }

    /**
     * Add a note to the claim.
     */
    public function addNote(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'is_internal' => 'boolean'
        ]);

        try {
            $note = $claim->addNote($validated['content'], auth()->id());
            $note->is_internal = $validated['is_internal'] ?? false;
            $note->save();
            
            return response()->json(['success' => true, 'message' => 'Note added successfully.', 'note' => $note]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error adding note: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified claim from storage.
     */
    public function destroy($id)
    {
        $claim = Claim::findOrFail($id);
        
        if (!$claim->canBeEdited() && !auth()->user()->hasRole(['admin', 'claims_manager'])) {
            return response()->json(['success' => false, 'message' => 'This claim cannot be deleted as it has already been processed.']);
        }

        try {
            // Delete associated files
            if ($claim->medical_report) {
                Storage::disk('public')->delete($claim->medical_report);
            }
            if ($claim->prescription) {
                Storage::disk('public')->delete($claim->prescription);
            }
            if ($claim->receipt) {
                Storage::disk('public')->delete($claim->receipt);
            }

            $claim->delete();
            
            return response()->json(['success' => true, 'message' => 'Claim deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting claim: ' . $e->getMessage()]);
        }
    }

    /**
     * Print the specified claim.
     */
    public function print($id)
    {
        $claim = Claim::with(['creator', 'updater', 'approver', 'rejecter', 'payer', 
                             'roReviewer', 'e5Reviewer'])
                    ->findOrFail($id);

        return view('claims.print', compact('claim'));
    }

    /**
     * Export claims to Excel.
     */
    public function export(Request $request)
    {
        $claims = \App\Models\FacilityClaim::with(['facility', 'diagnoses', 'submittedBy']);

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $claims->where('status', 'paid');
            } else {
                $claims->where('status', $request->status);
            }
        }
        
        if ($request->filled('facility_id')) {
            $claims->where('facility_id', $request->facility_id);
        }
        
        if ($request->filled('claim_type')) {
            $claims->where('claim_type', $request->claim_type);
        }
        
        if ($request->filled('date_from')) {
            $claims->whereDate('service_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $claims->whereDate('service_date', '<=', $request->date_to);
        }

        $filteredClaims = $claims->get();

        if ($request->status === 'approved' || $request->status === 'paid' || $request->status === 'es_approved') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('claims.approved-pdf', ['claims' => $filteredClaims]);
            // Set paper to A4
            $pdf->setPaper('A4', 'portrait');
            return $pdf->stream('approved-claims-slips.pdf');
        }

        return Excel::download(new ClaimsExport($filteredClaims), 'claims-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * RO Approval of the specified claim.
     */
    public function roApprove(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        
        if (!auth()->user()->hasRole(['admin', 'claims_manager', 'regional_officer'])) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to perform RO approval.']);
        }
        
        if ($claim->ro_review_date) {
            return response()->json(['success' => false, 'message' => 'This claim has already been reviewed by RO.']);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            $claim->ro_review_date = now();
            $claim->ro_status = 'approved';
            $claim->ro_reviewer_id = auth()->id();
            $claim->ro_notes = $validated['notes'] ?? null;
            $claim->updated_by = auth()->id();
            $claim->save();

            // Add history record
            $claim->addHistory('ro_approved', 'Claim approved by Regional Officer: ' . ($validated['notes'] ?? 'No notes'), auth()->id());

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Claim approved by Regional Officer successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error approving claim: ' . $e->getMessage()]);
        }
    }

    /**
     * RO Rejection of the specified claim.
     */
    public function roReject(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        
        if (!auth()->user()->hasRole(['admin', 'claims_manager', 'regional_officer'])) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to perform RO rejection.']);
        }
        
        if ($claim->ro_review_date) {
            return response()->json(['success' => false, 'message' => 'This claim has already been reviewed by RO.']);
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            $claim->ro_review_date = now();
            $claim->ro_status = 'rejected';
            $claim->ro_reviewer_id = auth()->id();
            $claim->ro_notes = $validated['notes'];
            $claim->status = 'rejected';
            $claim->rejection_reason = 'Rejected by Regional Officer: ' . $validated['notes'];
            $claim->updated_by = auth()->id();
            $claim->save();

            // Add history record
            $claim->addHistory('ro_rejected', 'Claim rejected by Regional Officer: ' . $validated['notes'], auth()->id());

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Claim rejected by Regional Officer successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error rejecting claim: ' . $e->getMessage()]);
        }
    }


    /**
     * Show bulk upload form.
     */
    public function bulkUpload()
    {
        return view('claims.bulk-upload');
    }

    /**
     * Process bulk upload of claims.
     */
    public function processBulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        try {
            $import = new ClaimsImport();
            Excel::import($import, $request->file('file'));
            
            $results = $import->getResults();
            
            return response()->json([
                'success' => true,
                'message' => "Bulk upload completed. {$results['success']} claims imported successfully, {$results['failed']} failed.",
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download claims template.
     */
    public function downloadTemplate()
    {
        $templatePath = storage_path('app/templates/claims_template.xlsx');
        
        if (!file_exists($templatePath)) {
            // Create template if it doesn't exist
            $this->createClaimsTemplate();
        }
        
        return response()->download($templatePath, 'claims_upload_template.xlsx');
    }

    /**
     * Download a specific claim as PDF.
     */
    public function download($id)
    {
        $claim = Claim::findOrFail($id);
        
        // For now, redirect to print view which can be saved as PDF
        // In a real implementation, you would generate a PDF using DomPDF or similar
        return redirect()->route('claims.print', $id);
    }

    /**
     * Create claims Excel template.
     */
    private function createClaimsTemplate()
    {
        $templatePath = storage_path('app/templates');
        if (!is_dir($templatePath)) {
            mkdir($templatePath, 0755, true);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = [
            'A1' => 'beneficiary_name',
            'B1' => 'boschma_id', 
            'C1' => 'nin',
            'D1' => 'phone_number',
            'E1' => 'claim_type',
            'F1' => 'healthcare_provider',
            'G1' => 'provider_type',
            'H1' => 'service_date',
            'I1' => 'claim_amount',
            'J1' => 'diagnosis',
            'K1' => 'treatment_description',
            'L1' => 'additional_notes'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Add sample data
        $sampleData = [
            'A2' => 'John Doe',
            'B2' => 'BOS001234',
            'C2' => '12345678901',
            'D2' => '08012345678',
            'E2' => 'medical',
            'F2' => 'General Hospital',
            'G2' => 'hospital',
            'H2' => '2024-12-15',
            'I2' => '15000.00',
            'J2' => 'Malaria',
            'K2' => 'Treatment and medication',
            'L2' => 'Patient admitted for 3 days'
        ];

        foreach ($sampleData as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Add validation notes
        $sheet->setCellValue('A14', 'IMPORTANT NOTES:');
        $sheet->setCellValue('A15', '- claim_type: medical, pharmacy, hospitalization, diagnostic, emergency');
        $sheet->setCellValue('A16', '- provider_type: hospital, clinic, pharmacy, laboratory, diagnostic_center');
        $sheet->setCellValue('A17', '- service_date: YYYY-MM-DD format');
        $sheet->setCellValue('A18', '- claim_amount: numeric value (e.g., 15000.00)');
        $sheet->setCellValue('A19', '- nin: 11 digits if provided');
        $sheet->setCellValue('A20', '- beneficiary_name, boschma_id, claim_type, healthcare_provider, provider_type, service_date, claim_amount are REQUIRED');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($templatePath . '/claims_template.xlsx');
    }

    /**
     * Display claims analytics dashboard.
     */
    public function analytics(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $statusFilter = $request->input('status', 'paid');

        // Base query for facility_claims
        $baseQuery = DB::table('facility_claims')->whereNull('deleted_at');
        
        if ($dateFrom) {
            $baseQuery->whereDate('service_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $baseQuery->whereDate('service_date', '<=', $dateTo);
        }
        if ($statusFilter) {
            $baseQuery->where('status', $statusFilter);
        }

        // Overall statistics (all statuses, ignoring status filter)
        $allClaimsQuery = DB::table('facility_claims')->whereNull('deleted_at');
        if ($dateFrom) $allClaimsQuery->whereDate('service_date', '>=', $dateFrom);
        if ($dateTo) $allClaimsQuery->whereDate('service_date', '<=', $dateTo);

        $overallStats = [
            'total_claims' => (clone $allClaimsQuery)->count(),
            'pending' => (clone $allClaimsQuery)->whereIn('status', ['draft', 'submitted'])->count(),
            'verified' => (clone $allClaimsQuery)->where('status', 'verified')->count(),
            'approved' => (clone $allClaimsQuery)->whereIn('status', ['approved', 'es_approved'])->count(),
            'paid' => (clone $allClaimsQuery)->where('status', 'paid')->count(),
            'rejected' => (clone $allClaimsQuery)->where('status', 'rejected')->count(),
            'total_amount' => (clone $allClaimsQuery)->sum('total_amount'),
            'paid_amount' => (clone $allClaimsQuery)->where('status', 'paid')->sum('total_amount'),
        ];

        // Paid claims per facility with amount breakdown
        $facilityBreakdown = (clone $baseQuery)
            ->join('facilities as f', 'facility_claims.facility_id', '=', 'f.id')
            ->select(
                'f.id as facility_id',
                'f.name as facility_name',
                DB::raw('COUNT(*) as claim_count'),
                DB::raw('SUM(consultation_amount) as admin_charges'),
                DB::raw('SUM(pharmacy_amount) as pharmacy'),
                DB::raw('SUM(laboratory_amount) as laboratory'),
                DB::raw('SUM(services_amount) as services'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('f.id', 'f.name')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Grand totals for the breakdown
        $grandTotals = [
            'claim_count' => $facilityBreakdown->sum('claim_count'),
            'admin_charges' => $facilityBreakdown->sum('admin_charges'),
            'pharmacy' => $facilityBreakdown->sum('pharmacy'),
            'laboratory' => $facilityBreakdown->sum('laboratory'),
            'services' => $facilityBreakdown->sum('services'),
            'total_amount' => $facilityBreakdown->sum('total_amount'),
        ];

        // Monthly trends from facility_claims
        $monthlyTrends = (clone $baseQuery)
            ->select(
                DB::raw('DATE_FORMAT(service_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as claim_count'),
                DB::raw('SUM(consultation_amount) as admin_charges'),
                DB::raw('SUM(pharmacy_amount) as pharmacy'),
                DB::raw('SUM(laboratory_amount) as laboratory'),
                DB::raw('SUM(services_amount) as services'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Claims by type
        $claimsByType = (clone $baseQuery)
            ->select(
                'claim_type',
                DB::raw('COUNT(*) as claim_count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('claim_type')
            ->orderBy('claim_count', 'desc')
            ->get();

        return view('claims.analytics', compact(
            'overallStats', 'facilityBreakdown', 'grandTotals',
            'monthlyTrends', 'claimsByType',
            'dateFrom', 'dateTo', 'statusFilter'
        ));
    }

    /**
     * Generate claims report.
     */
    public function generateReport(Request $request)
    {
        $reportType = $request->input('report_type', 'summary');
        $dateRange = $request->input('date_range', '30');
        $format = $request->input('format', 'excel');

        $startDate = now()->subDays($dateRange)->startOfDay();
        $endDate = now()->endOfDay();

        if ($reportType === 'detailed') {
            $claims = Claim::with(['creator', 'roReviewer', 'e5Reviewer'])
                           ->whereBetween('created_at', [$startDate, $endDate])
                           ->orderBy('created_at', 'desc')
                           ->get();
        } else {
            $claims = Claim::whereBetween('created_at', [$startDate, $endDate])
                           ->orderBy('created_at', 'desc')
                           ->get();
        }

        if ($format === 'excel') {
            return $this->exportReportToExcel($claims, $reportType, $startDate, $endDate);
        } elseif ($format === 'pdf') {
            return $this->exportReportToPDF($claims, $reportType, $startDate, $endDate);
        } else {
            return $this->exportReportToCSV($claims, $reportType, $startDate, $endDate);
        }
    }

    /**
     * Export report to Excel.
     */
    private function exportReportToExcel($claims, $reportType, $startDate, $endDate)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        if ($reportType === 'detailed') {
            $headers = [
                'A1' => 'Authorization Code',
                'B1' => 'Beneficiary Name',
                'C1' => 'BOSCHMA ID',
                'D1' => 'NIN',
                'E1' => 'Phone Number',
                'F1' => 'Claim Type',
                'G1' => 'Healthcare Provider',
                'H1' => 'Provider Type',
                'I1' => 'Service Date',
                'J1' => 'Claim Amount',
                'K1' => 'Status',
                'L1' => 'RO Status',
                'M1' => 'E5 Status',
                'N1' => 'Created By',
                'O1' => 'Created At'
            ];
        } else {
            $headers = [
                'A1' => 'Authorization Code',
                'B1' => 'Beneficiary Name',
                'C1' => 'BOSCHMA ID',
                'D1' => 'Claim Type',
                'E1' => 'Healthcare Provider',
                'F1' => 'Service Date',
                'G1' => 'Claim Amount',
                'H1' => 'Status'
            ];
        }

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Add data
        $row = 2;
        foreach ($claims as $claim) {
            if ($reportType === 'detailed') {
                $sheet->setCellValue('A' . $row, $claim->authorization_code);
                $sheet->setCellValue('B' . $row, $claim->beneficiary_name);
                $sheet->setCellValue('C' . $row, $claim->boschma_id);
                $sheet->setCellValue('D' . $row, $claim->nin);
                $sheet->setCellValue('E' . $row, $claim->phone_number);
                $sheet->setCellValue('F' . $row, $claim->claim_type);
                $sheet->setCellValue('G' . $row, $claim->healthcare_provider);
                $sheet->setCellValue('H' . $row, $claim->provider_type);
                $sheet->setCellValue('I' . $row, $claim->service_date->format('Y-m-d'));
                $sheet->setCellValue('J' . $row, $claim->claim_amount);
                $sheet->setCellValue('K' . $row, $claim->status);
                $sheet->setCellValue('L' . $row, $claim->ro_status ?: 'Pending');
                $sheet->setCellValue('M' . $row, $claim->e5_status ?: 'Pending');
                $sheet->setCellValue('N' . $row, $claim->creator ? $claim->creator->fullname : 'N/A');
                $sheet->setCellValue('O' . $row, $claim->created_at->format('Y-m-d H:i:s'));
            } else {
                $sheet->setCellValue('A' . $row, $claim->authorization_code);
                $sheet->setCellValue('B' . $row, $claim->beneficiary_name);
                $sheet->setCellValue('C' . $row, $claim->boschma_id);
                $sheet->setCellValue('D' . $row, $claim->claim_type);
                $sheet->setCellValue('E' . $row, $claim->healthcare_provider);
                $sheet->setCellValue('F' . $row, $claim->service_date->format('Y-m-d'));
                $sheet->setCellValue('G' . $row, $claim->claim_amount);
                $sheet->setCellValue('H' . $row, $claim->status);
            }
            $row++;
        }

        // Auto-size columns
        foreach (range('A', $reportType === 'detailed' ? 'O' : 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'claims_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.xlsx';
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }

    /**
     * Export report to CSV.
     */
    private function exportReportToCSV($claims, $reportType, $startDate, $endDate)
    {
        $filename = 'claims_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($claims, $reportType) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Add headers
            if ($reportType === 'detailed') {
                fputcsv($file, [
                    'Authorization Code', 'Beneficiary Name', 'BOSCHMA ID', 'NIN',
                    'Phone Number', 'Claim Type', 'Healthcare Provider', 'Provider Type',
                    'Service Date', 'Claim Amount', 'Status', 'RO Status', 'E5 Status',
                    'Created By', 'Created At'
                ]);
            } else {
                fputcsv($file, [
                    'Authorization Code', 'Beneficiary Name', 'BOSCHMA ID',
                    'Claim Type', 'Healthcare Provider', 'Service Date', 'Claim Amount', 'Status'
                ]);
            }
            
            // Add data
            foreach ($claims as $claim) {
                if ($reportType === 'detailed') {
                    fputcsv($file, [
                        $claim->authorization_code,
                        $claim->beneficiary_name,
                        $claim->boschma_id,
                        $claim->nin,
                        $claim->phone_number,
                        $claim->claim_type,
                        $claim->healthcare_provider,
                        $claim->provider_type,
                        $claim->service_date->format('Y-m-d'),
                        $claim->claim_amount,
                        $claim->status,
                        $claim->ro_status ?: 'Pending',
                        $claim->e5_status ?: 'Pending',
                        $claim->creator ? $claim->creator->fullname : 'N/A',
                        $claim->created_at->format('Y-m-d H:i:s')
                    ]);
                } else {
                    fputcsv($file, [
                        $claim->authorization_code,
                        $claim->beneficiary_name,
                        $claim->boschma_id,
                        $claim->claim_type,
                        $claim->healthcare_provider,
                        $claim->service_date->format('Y-m-d'),
                        $claim->claim_amount,
                        $claim->status
                    ]);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export report to PDF.
     */
    private function exportReportToPDF($claims, $reportType, $startDate, $endDate)
    {
        // For now, redirect to Excel export as PDF would require additional dependencies
        // In a real implementation, you would use a PDF library like DomPDF or TCPDF
        return $this->exportReportToExcel($claims, $reportType, $startDate, $endDate);
    }

    /**
     * Display claim audit trail.
     */
    public function auditTrail($id)
    {
        $claim = Claim::with(['creator', 'updater', 'roReviewer', 'e5Reviewer', 'approver', 'rejecter', 'paidBy'])
                     ->findOrFail($id);

        $histories = ClaimHistory::with('user')
                                 ->where('claim_id', $id)
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        $notes = ClaimNote::with('user')
                          ->where('claim_id', $id)
                          ->orderBy('created_at', 'desc')
                          ->get();

        return view('claims.audit-trail', compact('claim', 'histories', 'notes'));
    }

    /**
     * Add audit note to claim.
     */
    public function addAuditNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|max:1000'
        ]);

        $claim = Claim::findOrFail($id);

        $note = ClaimNote::create([
            'claim_id' => $claim->id,
            'user_id' => auth()->id(),
            'note' => $request->note,
            'type' => 'audit'
        ]);

        // Add history record
        $claim->addHistory('audit_note_added', 'Audit note added: ' . $request->note, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Audit note added successfully',
            'note' => [
                'id' => $note->id,
                'note' => $note->note,
                'created_at' => $note->created_at->format('M d, Y H:i'),
                'user' => auth()->user()->fullname
            ]
        ]);
    }

    /**
     * Get comprehensive audit report.
     */
    public function auditReport(Request $request)
    {
        $dateRange = $request->input('date_range', '30');
        $startDate = now()->subDays($dateRange)->startOfDay();
        $endDate = now()->endOfDay();

        // Get all claims with their complete audit trail
        $claims = Claim::with(['histories.user', 'notes.user', 'creator', 'roReviewer', 'e5Reviewer'])
                       ->whereBetween('created_at', [$startDate, $endDate])
                       ->orderBy('created_at', 'desc')
                       ->get();

        // Audit statistics
        $totalAudits = ClaimHistory::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalNotes = ClaimNote::whereBetween('created_at', [$startDate, $endDate])->count();

        // User activity
        $userActivity = ClaimHistory::select('user_id',
                                   DB::raw('count(*) as action_count'),
                                   DB::raw('MAX(created_at) as last_activity'))
                               ->with('user:id,fullname')
                               ->whereBetween('created_at', [$startDate, $endDate])
                               ->groupBy('user_id')
                               ->orderBy('action_count', 'desc')
                               ->limit(10)
                               ->get();

        // Action breakdown
        $actionBreakdown = ClaimHistory::select('action',
                                      DB::raw('count(*) as count'))
                                  ->whereBetween('created_at', [$startDate, $endDate])
                                  ->groupBy('action')
                                  ->orderBy('count', 'desc')
                                  ->get();

        return view('claims.audit-report', compact(
            'claims', 'totalAudits', 'totalNotes', 'userActivity', 
            'actionBreakdown', 'dateRange', 'startDate', 'endDate'
        ));
    }

    /**
     * Export audit trail to Excel.
     */
    public function exportAuditTrail(Request $request)
    {
        $dateRange = $request->input('date_range', '30');
        $startDate = now()->subDays($dateRange)->startOfDay();
        $endDate = now()->endOfDay();

        $histories = ClaimHistory::with(['claim', 'user'])
                                ->whereBetween('created_at', [$startDate, $endDate])
                                ->orderBy('created_at', 'desc')
                                ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'A1' => 'Date & Time',
            'B1' => 'Claim Authorization Code',
            'C1' => 'Action',
            'D1' => 'Description',
            'E1' => 'User',
            'F1' => 'IP Address'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Add data
        $row = 2;
        foreach ($histories as $history) {
            $sheet->setCellValue('A' . $row, $history->created_at->format('Y-m-d H:i:s'));
            $sheet->setCellValue('B' . $row, $history->claim ? $history->claim->authorization_code : 'N/A');
            $sheet->setCellValue('C' . $row, $history->action);
            $sheet->setCellValue('D' . $row, $history->description);
            $sheet->setCellValue('E' . $row, $history->user ? $history->user->fullname : 'N/A');
            $sheet->setCellValue('F' . $row, $history->ip_address ?: 'N/A');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'audit_trail_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.xlsx';
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }

    /**
     * Display notifications center.
     */
    public function notifications()
    {
        $user = auth()->user();
        
        // Get user notifications
        $notifications = $user->notifications()
                              ->orderBy('created_at', 'desc')
                              ->paginate(20);

        // Get unread count
        $unreadCount = $user->unreadNotifications()->count();

        return view('claims.notifications', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllNotificationsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete notification.
     */
    public function deleteNotification($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Get notification count for header.
     */
    public function getNotificationCount()
    {
        $count = auth()->user()->unreadNotifications()->count();
        
        return response()->json([
            'count' => $count
        ]);
    }

    /**
     * Send claim notification to users.
     */
    private function sendClaimNotification($claim, $action, $message, $users = null)
    {
        if ($users === null) {
            // Send to all users with claim.edit permission
            $users = \App\Models\Staff::whereHas('permissions', function($query) {
                $query->where('name', 'claim.edit');
            })->get();
        }

        foreach ($users as $user) {
            $user->notify(new \App\Notifications\ClaimNotification($claim, $action, $message));
        }
    }

    /**
     * Send approval notifications.
     */
    private function sendApprovalNotification($claim, $stage, $status)
    {
        $message = '';
        $action = '';
        
        if ($stage === 'ro') {
            if ($status === 'approved') {
                $action = 'ro_approved';
                $message = "Claim {$claim->authorization_code} has been approved by Regional Officer and is now pending E5 review";
            } else {
                $action = 'ro_rejected';
                $message = "Claim {$claim->authorization_code} has been rejected by Regional Officer";
            }
        } elseif ($stage === 'e5') {
            if ($status === 'approved') {
                $action = 'e5_approved';
                $message = "Claim {$claim->authorization_code} has been approved by E5 Authority";
            } else {
                $action = 'e5_rejected';
                $message = "Claim {$claim->authorization_code} has been rejected by E5 Authority";
            }
        }

        $this->sendClaimNotification($claim, $action, $message);
    }

    /**
     * Send bulk upload notification.
     */
    private function sendBulkUploadNotification($successCount, $failureCount)
    {
        $authUser = auth()->user();
        $message = "Bulk claim upload completed: {$successCount} successful, {$failureCount} failed";
        
        // Send to the user who uploaded
        $authUser->notify(new \App\Notifications\BulkUploadNotification($successCount, $failureCount));
        
        // Send to admin users
        $adminUsers = \App\Models\Staff::whereHas('permissions', function($query) {
            $query->where('name', 'claim.edit');
        })->where('id', '!=', $authUser->id)->get();

        foreach ($adminUsers as $admin) {
            $admin->notify(new \App\Notifications\BulkUploadNotification($successCount, $failureCount));
        }
    }

    /**
     * Display alerts dashboard.
     */
    public function alerts()
    {
        // Get claims that need attention
        $pendingClaims = Claim::where('status', 'pending')->count();
        $roPending = Claim::where('ro_status', '')->where('status', '!=', 'rejected')->count();
        $e5Pending = Claim::where('e5_status', '')->where('ro_status', 'approved')->count();
        
        // Get recent claims needing review
        $recentPending = Claim::where('status', 'pending')
                             ->orderBy('created_at', 'desc')
                             ->limit(10)
                             ->get();

        // Get overdue claims (older than 7 days and still pending)
        $overdueClaims = Claim::where('status', 'pending')
                             ->where('created_at', '<', now()->subDays(7))
                             ->orderBy('created_at', 'asc')
                             ->limit(10)
                             ->get();

        // Get high value claims (over 100,000)
        $highValueClaims = Claim::where('claim_amount', '>', 100000)
                               ->where('status', 'pending')
                               ->orderBy('claim_amount', 'desc')
                               ->limit(10)
                               ->get();

        return view('claims.alerts', compact(
            'pendingClaims', 'roPending', 'e5Pending',
            'recentPending', 'overdueClaims', 'highValueClaims'
        ));
    }

    // Medication Management Methods
    
    public function storeMedication(Request $request, $claimId)
    {
        $request->validate([
            'medication_name' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'frequency' => 'required|integer|min:1',
            'days' => 'required|integer|min:1',
        ]);

        $claim = Claim::findOrFail($claimId);
        
        $medication = $claim->medications()->create([
            'medication_name' => $request->medication_name,
            'cost' => $request->cost,
            'frequency' => $request->frequency,
            'days' => $request->days,
            'claimed_amount' => $request->cost * $request->frequency * $request->days,
        ]);

        // Recalculate claim total
        $claim->recalculateClaimAmount();

        return response()->json(['success' => true, 'medication' => $medication]);
    }

    public function getMedication($claimId, $medicationId)
    {
        $medication = ClaimMedication::where('claim_id', $claimId)
                                    ->where('id', $medicationId)
                                    ->firstOrFail();
        
        return response()->json($medication);
    }

    public function updateMedication(Request $request, $claimId, $medicationId)
    {
        $request->validate([
            'medication_name' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'frequency' => 'required|integer|min:1',
            'days' => 'required|integer|min:1',
        ]);

        $medication = ClaimMedication::where('claim_id', $claimId)
                                     ->where('id', $medicationId)
                                     ->firstOrFail();

        $medication->update([
            'medication_name' => $request->medication_name,
            'cost' => $request->cost,
            'frequency' => $request->frequency,
            'days' => $request->days,
            'claimed_amount' => $request->cost * $request->frequency * $request->days,
        ]);

        // Recalculate claim total
        $claim = Claim::findOrFail($claimId);
        $claim->recalculateClaimAmount();

        return response()->json(['success' => true, 'medication' => $medication]);
    }

    public function deleteMedication($claimId, $medicationId)
    {
        $medication = ClaimMedication::where('claim_id', $claimId)
                                     ->where('id', $medicationId)
                                     ->firstOrFail();
        
        $medication->delete();

        // Recalculate claim total
        $claim = Claim::findOrFail($claimId);
        $claim->recalculateClaimAmount();

        return response()->json(['success' => true]);
    }

    // Laboratory Test Management Methods
    
    public function storeLaboratory(Request $request, $claimId)
    {
        $request->validate([
            'test_name' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'frequency' => 'required|integer|min:1',
        ]);

        $claim = Claim::findOrFail($claimId);
        
        $test = $claim->laboratoryTests()->create([
            'test_name' => $request->test_name,
            'cost' => $request->cost,
            'frequency' => $request->frequency,
            'claimed_amount' => $request->cost * $request->frequency,
        ]);

        // Recalculate claim total
        $claim->recalculateClaimAmount();

        return response()->json(['success' => true, 'test' => $test]);
    }

    public function getLaboratory($claimId, $testId)
    {
        $test = ClaimLaboratoryTest::where('claim_id', $claimId)
                                   ->where('id', $testId)
                                   ->firstOrFail();
        
        return response()->json($test);
    }

    public function updateLaboratory(Request $request, $claimId, $testId)
    {
        $request->validate([
            'test_name' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'frequency' => 'required|integer|min:1',
        ]);

        $test = ClaimLaboratoryTest::where('claim_id', $claimId)
                                   ->where('id', $testId)
                                   ->firstOrFail();

        $test->update([
            'test_name' => $request->test_name,
            'cost' => $request->cost,
            'frequency' => $request->frequency,
            'claimed_amount' => $request->cost * $request->frequency,
        ]);

        // Recalculate claim total
        $claim = Claim::findOrFail($claimId);
        $claim->recalculateClaimAmount();

        return response()->json(['success' => true, 'test' => $test]);
    }

    public function deleteLaboratory($claimId, $testId)
    {
        $test = ClaimLaboratoryTest::where('claim_id', $claimId)
                                   ->where('id', $testId)
                                   ->firstOrFail();
        
        $test->delete();

        // Recalculate claim total
        $claim = Claim::findOrFail($claimId);
        $claim->recalculateClaimAmount();

        return response()->json(['success' => true]);
    }

    // Rendered Service Management Methods
    
    public function storeService(Request $request, $claimId)
    {
        $request->validate([
            'service_name' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'frequency' => 'required|integer|min:1',
        ]);

        $claim = Claim::findOrFail($claimId);
        
        $service = $claim->renderedServices()->create([
            'service_name' => $request->service_name,
            'cost' => $request->cost,
            'frequency' => $request->frequency,
            'claimed_amount' => $request->cost * $request->frequency,
        ]);

        // Recalculate claim total
        $claim->recalculateClaimAmount();

        return response()->json(['success' => true, 'service' => $service]);
    }

    public function getService($claimId, $serviceId)
    {
        $service = ClaimRenderedService::where('claim_id', $claimId)
                                       ->where('id', $serviceId)
                                       ->firstOrFail();
        
        return response()->json($service);
    }

    public function updateService(Request $request, $claimId, $serviceId)
    {
        $request->validate([
            'service_name' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'frequency' => 'required|integer|min:1',
        ]);

        $service = ClaimRenderedService::where('claim_id', $claimId)
                                       ->where('id', $serviceId)
                                       ->firstOrFail();

        $service->update([
            'service_name' => $request->service_name,
            'cost' => $request->cost,
            'frequency' => $request->frequency,
            'claimed_amount' => $request->cost * $request->frequency,
        ]);

        // Recalculate claim total
        $claim = Claim::findOrFail($claimId);
        $claim->recalculateClaimAmount();

        return response()->json(['success' => true, 'service' => $service]);
    }

    public function deleteService($claimId, $serviceId)
    {
        $service = ClaimRenderedService::where('claim_id', $claimId)
                                       ->where('id', $serviceId)
                                       ->firstOrFail();
        
        $service->delete();

        // Recalculate claim total
        $claim = Claim::findOrFail($claimId);
        $claim->recalculateClaimAmount();

        return response()->json(['success' => true]);
    }

    // Document Management Methods
    
    public function uploadDocuments(Request $request, $claimId)
    {
        $request->validate([
            'operation_sheets.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'prescription_sheets.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $claim = Claim::findOrFail($claimId);
        $uploadedDocuments = [];

        // Handle operation sheets
        if ($request->hasFile('operation_sheets')) {
            foreach ($request->file('operation_sheets') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('claim_documents/operation_sheets', $filename, 'public');
                
                $document = $claim->documents()->create([
                    'document_type' => 'operation_sheet',
                    'document_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
                
                $uploadedDocuments[] = $document;
            }
        }

        // Handle prescription sheets
        if ($request->hasFile('prescription_sheets')) {
            foreach ($request->file('prescription_sheets') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('claim_documents/prescription_sheets', $filename, 'public');
                
                $document = $claim->documents()->create([
                    'document_type' => 'prescription_sheet',
                    'document_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
                
                $uploadedDocuments[] = $document;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Documents uploaded successfully',
            'documents' => $uploadedDocuments
        ]);
    }

    public function deleteDocument($claimId, $documentId)
    {
        $document = ClaimDocument::where('claim_id', $claimId)
                                 ->where('id', $documentId)
                                 ->firstOrFail();
        
        $document->delete();

        return response()->json(['success' => true]);
    }

    // Master Data Fetching Methods
    
    public function getDrugs(Request $request)
    {
        $search = $request->get('q', '');
        
        $drugs = Drug::when($search, function($query, $search) {
            return $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('dosage_form', 'LIKE', "%{$search}%");
        })
        ->select('id', 'name', 'dosage_form', 'strength', 'unit', 'unit_price', 'description')
        ->limit(50)
        ->get()
        ->map(function($drug) {
            return [
                'id' => $drug->id,
                'text' => $drug->name . ' (' . $drug->dosage_form . ' ' . $drug->strength . ')',
                'name' => $drug->name,
                'dosage_form' => $drug->dosage_form,
                'strength' => $drug->strength,
                'unit' => $drug->unit,
                'unit_price' => $drug->unit_price,
                'description' => $drug->description
            ];
        });

        return response()->json(['results' => $drugs]);
    }

    public function getLaboratoryTests(Request $request)
    {
        $search = $request->get('q', '');
        
        $tests = LaboratoryTest::when($search, function($query, $search) {
            return $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('sample_type', 'LIKE', "%{$search}%");
        })
        ->select('id', 'name', 'sample_type', 'price', 'description')
        ->limit(50)
        ->get()
        ->map(function($test) {
            return [
                'id' => $test->id,
                'text' => $test->name . ' (' . $test->sample_type . ')',
                'name' => $test->name,
                'sample_type' => $test->sample_type,
                'price' => $test->price,
                'description' => $test->description
            ];
        });

        return response()->json(['results' => $tests]);
    }

    public function getServices(Request $request)
    {
        $search = $request->get('q', '');
        
        $services = Service::when($search, function($query, $search) {
            return $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('type', 'LIKE', "%{$search}%");
        })
        ->select('id', 'name', 'type', 'price', 'description')
        ->limit(50)
        ->get()
        ->map(function($service) {
            return [
                'id' => $service->id,
                'text' => $service->name . ' (' . $service->type . ')',
                'name' => $service->name,
                'type' => $service->type,
                'price' => $service->price,
                'description' => $service->description
            ];
        });

        return response()->json(['results' => $services]);
    }

    public function searchBeneficiaries(Request $request)
    {
        $search = $request->get('q', '');
        
        // Get all patients and search through their details
        $patients = Patient::when($search, function($query, $search) {
            return $query->search($search);
        })
        ->limit(100)
        ->get();

        $results = [];
        
        foreach ($patients as $patient) {
            $details = null;
            
            // Get details based on enrollee type
            switch ($patient->enrollee_type) {
                case 'beneficiary':
                    $details = Beneficiary::where('boschma_no', $patient->enrollee_number)->first();
                    break;
                case 'spouse':
                    $details = Spouse::where('boschma_no', $patient->enrollee_number)->first();
                    break;
                case 'child':
                    $details = Child::where('boschma_no', $patient->enrollee_number)->first();
                    break;
            }
            
            if ($details) {
                // Check if search matches name or NIN
                $fullname = $details->fullname ?? $details->name ?? '';
                $nin = $details->nin ?? '';
                
                if (empty($search) || 
                    stripos($fullname, $search) !== false || 
                    stripos($patient->enrollee_number, $search) !== false || 
                    stripos($nin, $search) !== false) {
                    
                    $results[] = [
                        'id' => $patient->id,
                        'file_number' => $patient->file_number,
                        'enrollee_number' => $patient->enrollee_number,
                        'enrollee_type' => ucfirst($patient->enrollee_type),
                        'fullname' => $fullname,
                        'boschma_no' => $patient->enrollee_number,
                        'nin' => $nin,
                        'gender' => $details->gender ?? '',
                        'date_of_birth' => $details->date_of_birth ?? $details->dob ?? '',
                        'phone_no' => $details->phone_no ?? $details->phone ?? '',
                        'email' => $details->email ?? '',
                    ];
                }
            }
        }

        return response()->json($results);
    }

    /**
     * Show RO review queue
     */
    public function roReview(Request $request)
    {
        // Check permission
        if (!auth()->user()->can('review-claims')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Claim::with(['medications', 'laboratoryTests', 'renderedServices', 'documents'])
            ->where('status', 'submitted')
            ->where(function($q) {
                $q->whereNull('ro_status')
                  ->orWhere('ro_status', '');
            });

        // Filter by facility if provided
        if ($request->has('facility')) {
            $query->where('healthcare_provider', $request->facility);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('service_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('service_date', '<=', $request->date_to);
        }

        $claims = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total_pending' => Claim::where('status', 'submitted')->whereNull('ro_status')->count(),
            'total_amount' => Claim::where('status', 'submitted')->whereNull('ro_status')->sum('claim_amount'),
        ];

        return view('claims.ro-review', compact('claims', 'stats'));
    }

    /**
     * Show E5 approval queue
     */
    public function e5Review(Request $request)
    {
        // Check permission
        if (!auth()->user()->can('approve-claims')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Claim::with(['medications', 'laboratoryTests', 'renderedServices', 'documents'])
            ->where('status', 'submitted')
            ->where('ro_status', 'approved')
            ->where(function($q) {
                $q->whereNull('e5_status')
                  ->orWhere('e5_status', '');
            });

        // Filter by facility if provided
        if ($request->has('facility')) {
            $query->where('healthcare_provider', $request->facility);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('service_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('service_date', '<=', $request->date_to);
        }

        $claims = $query->orderBy('ro_updated_at', 'desc')->paginate(20);

        $stats = [
            'total_pending' => Claim::where('ro_status', 'approved')->whereNull('e5_status')->count(),
            'total_amount' => Claim::where('ro_status', 'approved')->whereNull('e5_status')->sum('claim_amount'),
        ];

        return view('claims.e5-review', compact('claims', 'stats'));
    }

    /**
     * Process RO review
     */
    public function review(Request $request, $id)
    {
        // Check permission
        if (!auth()->user()->can('review-claims')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject,request_info',
            'notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $claim = Claim::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($validated['action'] === 'approve') {
                $claim->ro_status = 'approved';
                $claim->ro_updated_at = now();
                $claim->ro_updated_by = auth()->id();
                $historyAction = 'RO Approved';
            } elseif ($validated['action'] === 'reject') {
                $claim->ro_status = 'rejected';
                $claim->status = 'rejected';
                $claim->ro_updated_at = now();
                $claim->ro_updated_by = auth()->id();
                $claim->rejection_reason = $validated['rejection_reason'] ?? 'Rejected by Regional Office';
                $historyAction = 'RO Rejected';
            } else {
                $claim->ro_status = 'info_requested';
                $claim->ro_updated_at = now();
                $claim->ro_updated_by = auth()->id();
                $historyAction = 'Additional Information Requested';
            }

            $claim->save();

            // Create history record
            ClaimHistory::create([
                'claim_id' => $claim->id,
                'user_id' => auth()->id(),
                'action' => $historyAction,
                'notes' => $validated['notes'],
                'status_before' => $claim->status,
                'status_after' => $claim->status,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Claim review submitted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('RO Review Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the review.',
            ], 500);
        }
    }

    /**
     * Process E5 approval
     */
    public function e5Approve(Request $request, $id)
    {
        // Check permission
        if (!auth()->user()->can('approve-claims')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject,return_to_ro',
            'notes' => 'nullable|string|max:1000',
        ]);

        $claim = Claim::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($validated['action'] === 'approve') {
                $claim->e5_status = 'approved';
                $claim->status = 'approved';
                $claim->e5_updated_at = now();
                $claim->e5_updated_by = auth()->id();
                $claim->approved_by = auth()->id();
                $historyAction = 'E5 Approved';
            } elseif ($validated['action'] === 'reject') {
                $claim->e5_status = 'rejected';
                $claim->status = 'rejected';
                $claim->e5_updated_at = now();
                $claim->e5_updated_by = auth()->id();
                $claim->rejected_by = auth()->id();
                $historyAction = 'E5 Rejected';
            } else {
                $claim->e5_status = 'returned_to_ro';
                $claim->ro_status = '';
                $claim->e5_updated_at = now();
                $claim->e5_updated_by = auth()->id();
                $historyAction = 'Returned to Regional Office';
            }

            $claim->save();

            // Create history record
            ClaimHistory::create([
                'claim_id' => $claim->id,
                'user_id' => auth()->id(),
                'action' => $historyAction,
                'notes' => $validated['notes'],
                'status_before' => 'ro_approved',
                'status_after' => $claim->status,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Claim approval processed successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('E5 Approval Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the approval.',
            ], 500);
        }
    }

    /**
     * Bulk approve claims
     */
    public function bulkApprove(Request $request)
    {
        // Check permission
        if (!auth()->user()->can('claim.verify') && !auth()->user()->can('claim.approve')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'claim_ids' => 'required|array',
            'claim_ids.*' => 'exists:claims,id',
        ]);

        DB::beginTransaction();
        try {
            $approvedCount = 0;
            foreach ($validated['claim_ids'] as $claimId) {
                $claim = Claim::find($claimId);
                
                if ($claim && $claim->status === 'submitted' && empty($claim->ro_status)) {
                    $claim->ro_status = 'approved';
                    $claim->ro_updated_at = now();
                    $claim->ro_updated_by = auth()->id();
                    $claim->save();

                    ClaimHistory::create([
                        'claim_id' => $claim->id,
                        'user_id' => auth()->id(),
                        'action' => 'RO Bulk Approved',
                        'status_before' => $claim->status,
                        'status_after' => $claim->status,
                    ]);

                    $approvedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'approved_count' => $approvedCount,
                'message' => "$approvedCount claim(s) approved successfully.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk Approve Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing bulk approval.',
            ], 500);
        }
    }

    /**
     * Show facility claims
     */
    public function facilityShow(Request $request, $facilityId)
    {
        $facility = Facility::find($facilityId);
        if (!$facility) {
            abort(404, 'Facility not found');
        }

        $query = DB::table('facility_claims as fc')
            ->join('facilities as f', 'fc.facility_id', '=', 'f.id')
            ->where('fc.facility_id', $facility->id)
            ->whereNull('fc.deleted_at')
            ->select('fc.*', 'f.name as healthcare_provider');

        // Restrict visibility based on user workflow permissions
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('Super Admin') || $user->hasRole('admin'));

        if (!$isSuperAdmin && $user) {
            $allowedStatuses = [];
            
            if ($user->can('claim.verify')) {
                $allowedStatuses = array_merge($allowedStatuses, ['submitted', 'verified', 'approved', 'es_approved', 'paid', 'rejected']);
            }
            if ($user->can('claim.approve')) {
                $allowedStatuses = array_merge($allowedStatuses, ['verified', 'approved', 'es_approved', 'paid', 'rejected']);
            }
            if ($user->can('claim.es-approve')) {
                $allowedStatuses = array_merge($allowedStatuses, ['approved', 'es_approved', 'paid', 'rejected']);
            }
            if ($user->can('claim.finance-approve')) {
                $allowedStatuses = array_merge($allowedStatuses, ['es_approved', 'paid', 'rejected']);
            }
            
            $allowedStatuses = array_unique($allowedStatuses);
            
            if (!empty($allowedStatuses)) {
                $query->whereIn('fc.status', $allowedStatuses);
            }
        }

        // Search by patient name, claim number, or boschma_no
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fc.patient_name', 'LIKE', "%{$search}%")
                  ->orWhere('fc.claim_number', 'LIKE', "%{$search}%")
                  ->orWhere('fc.boschma_no', 'LIKE', "%{$search}%")
                  ->orWhere('fc.enrollee_number', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('fc.status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('fc.service_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('fc.service_date', '<=', $request->date_to);
        }

        $claims = $query->orderBy('fc.created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $stats = [
            'total_claims' => DB::table('facility_claims')->where('facility_id', $facility->id)->whereNull('deleted_at')->count(),
            'verifier_pending' => DB::table('facility_claims')
                                ->where('facility_id', $facility->id)
                                ->whereNull('deleted_at')
                                ->where(function($q) {
                                    $q->where('verifier_status', 'pending')
                                      ->orWhereNull('verifier_status');
                                })
                                ->whereNotIn('status', ['rejected', 'paid'])
                                ->count(),
            'es_pending' => DB::table('facility_claims')
                                ->where('facility_id', $facility->id)
                                ->whereNull('deleted_at')
                                ->where('verifier_status', 'approved')
                                ->where('approver_status', 'approved')
                                ->where(function($q) {
                                    $q->where('es_status', 'pending')
                                      ->orWhereNull('es_status');
                                })
                                ->whereNotIn('status', ['rejected'])
                                ->count(),
            'approved' => DB::table('facility_claims')->where('facility_id', $facility->id)->whereNull('deleted_at')->whereIn('status', ['approved', 'es_approved'])->count(),
            'total_amount' => DB::table('facility_claims')->where('facility_id', $facility->id)->whereNull('deleted_at')->sum('total_amount'),
            'approved_amount' => DB::table('facility_claims')
                                    ->where('facility_id', $facility->id)
                                    ->whereNull('deleted_at')
                                    ->where(function($q) {
                                        $q->whereIn('status', ['approved', 'es_approved', 'paid']);
                                    })
                                    ->sum('total_amount'),
        ];

        return view('claims.facility-show', compact('claims', 'facility', 'stats'));
    }

    /**
     * Show a single facility claim
     */
    public function showFacilityClaim($claimId)
    {
        $claim = DB::table('facility_claims as fc')
            ->join('facilities as f', 'fc.facility_id', '=', 'f.id')
            ->where('fc.id', $claimId)
            ->whereNull('fc.deleted_at')
            ->select('fc.*', 'f.name as facility_name')
            ->first();

        if (!$claim) {
            abort(404, 'Claim not found');
        }

        // Restrict details page visibility based on user workflow permissions
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('Super Admin') || $user->hasRole('admin'));

        if (!$isSuperAdmin && $user) {
            $allowedStatuses = [];
            
            if ($user->can('claim.verify')) {
                $allowedStatuses = array_merge($allowedStatuses, ['submitted', 'verified', 'approved', 'es_approved', 'paid', 'rejected']);
            }
            if ($user->can('claim.approve')) {
                $allowedStatuses = array_merge($allowedStatuses, ['verified', 'approved', 'es_approved', 'paid', 'rejected']);
            }
            if ($user->can('claim.es-approve')) {
                $allowedStatuses = array_merge($allowedStatuses, ['approved', 'es_approved', 'paid', 'rejected']);
            }
            if ($user->can('claim.finance-approve')) {
                $allowedStatuses = array_merge($allowedStatuses, ['es_approved', 'paid', 'rejected']);
            }
            
            $allowedStatuses = array_unique($allowedStatuses);
            
            if (!empty($allowedStatuses) && !in_array($claim->status, $allowedStatuses)) {
                abort(403, 'Unauthorized. This claim is not yet ready for your review.');
            }
        }

        // Initialize arrays and collections
        $medications = [];
        $services = [];
        $provisionalDiagnoses = [];
        $confirmedDiagnoses = [];
        $consultations = collect([]);
        $vitalSigns = collect([]);
        $actions = collect([]);

        // If claim has an encounter_id, fetch detailed data from related tables
        if (!empty($claim->encounter_id)) {
            try {
                // Load the encounter with all related data (removed serviceResults due to missing relationship)
                $encounter = \App\Models\Encounter::with([
                    'consultations.prescriptions.prescriptionItems.drug.dispensations',
                    'consultations.diagnoses.icdCode',
                    'serviceOrders.serviceOrderItems.serviceItem',
                    'vitalSigns',
                    'actions'
                ])->find($claim->encounter_id);

                if ($encounter) {
                    // Get clinical data from encounter with error handling
                    try {
                        $consultations = $encounter->consultations ?? collect([]);
                        $vitalSigns = $encounter->vitalSigns ?? collect([]);
                        $actions = $encounter->actions ?? collect([]);
                        
                        // Get prescriptions and pharmacy data safely
                        $prescriptions = $consultations->flatMap->prescriptions ?? collect([]);
                        
                        // Extract diagnoses by type
                        foreach ($consultations as $consultation) {
                            foreach ($consultation->diagnoses as $dx) {
                                $dxData = [
                                    'code' => $dx->icdCode->code ?? 'N/A',
                                    'description' => $dx->icdCode->description ?? ($dx->notes ?? 'N/A'),
                                    'notes' => $dx->notes,
                                ];
                                if (strtolower($dx->diagnosis_type ?? '') === 'confirmed') {
                                    $confirmedDiagnoses[] = $dxData;
                                } else {
                                    $provisionalDiagnoses[] = $dxData;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error loading encounter relationships: ' . $e->getMessage());
                        $consultations = collect([]);
                        $vitalSigns = collect([]);
                        $actions = collect([]);
                        $prescriptions = collect([]);
                    }
                    
                    // Get medications from facility_claim_medications table with drug details
                    $claimMedications = DB::table('facility_claim_medications')
                        ->leftJoin('prescription_items', 'facility_claim_medications.prescription_item_id', '=', 'prescription_items.id')
                        ->leftJoin('drugs', 'prescription_items.drug_id', '=', 'drugs.id')
                        ->where('facility_claim_medications.facility_claim_id', $claim->id)
                        ->select(
                            'facility_claim_medications.*',
                            'drugs.description as drug_description',
                            'drugs.dosage_form as drug_dosage_form',
                            'drugs.strength as drug_strength',
                            'drugs.unit as drug_unit'
                        )
                        ->get();
                    
                    foreach ($claimMedications as $item) {
                        // Build enhanced drug name with attributes
                        $drugName = $item->drug_name ?? 'N/A';
                        $attributes = [];
                        
                        if ($item->drug_strength) {
                            $attributes[] = $item->drug_strength;
                        }
                        if ($item->drug_unit) {
                            $attributes[] = $item->drug_unit;
                        }
                        if ($item->drug_dosage_form) {
                            $attributes[] = $item->drug_dosage_form;
                        }
                        
                        $enhancedName = $drugName;
                        if (!empty($attributes)) {
                            $enhancedName .= ' (' . implode(', ', $attributes) . ')';
                        }
                        
                        $medications[] = [
                            'id' => $item->id,
                            'name' => $enhancedName,
                            'base_name' => $drugName,
                            'description' => $item->drug_description ?? '',
                            'dosage_form' => $item->drug_dosage_form ?? '',
                            'strength' => $item->drug_strength ?? '',
                            'unit' => $item->drug_unit ?? '',
                            'dosage' => $item->dosage ?? 'N/A',
                            'quantity' => $item->quantity ?? 0,
                            'duration' => $item->days ?? 0,
                            'cost' => $item->total_price ?? 0,
                            'status' => 'approved' // Claim items are typically approved
                        ];
                    }
                    
                    // Get services from facility_claim_services table (for editing)
                    $claimServices = DB::table('facility_claim_services')
                        ->where('facility_claim_id', $claim->id)
                        ->get();
                    
                    foreach ($claimServices as $item) {
                        $services[] = [
                            'id' => $item->id,
                            'name' => $item->service_name ?? 'N/A',
                            'type' => $item->service_type ?? 'Service',
                            'description' => $item->service_description ?? 'N/A',
                            'cost' => $item->total_price ?? 0,
                            'status' => 'approved', // Claim items are typically approved
                            'results' => [], // Results would need to be loaded separately if needed
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error loading encounter data: ' . $e->getMessage());
            }
        }

        // Get submitter name from users table (facility admin)
        $submittedByName = null;
        if (!empty($claim->submitted_by)) {
            $submitter = DB::table('users')->where('id', $claim->submitted_by)->first();
            $submittedByName = $submitter ? $submitter->name : 'N/A';
        }
        
        // Get staff names for approval workflow
        $verifierName = null;
        $approverName = null;
        $esName = null;
        $financeName = null;
        
        if (!empty($claim->verifier_id)) {
            $verifier = DB::table('staff')->where('id', $claim->verifier_id)->first();
            $verifierName = $verifier ? $verifier->fullname : 'N/A';
        }
        
        if (!empty($claim->approver_id)) {
            $approver = DB::table('staff')->where('id', $claim->approver_id)->first();
            $approverName = $approver ? $approver->fullname : 'N/A';
        }
        
        if (!empty($claim->es_id)) {
            $es = DB::table('staff')->where('id', $claim->es_id)->first();
            $esName = $es ? $es->fullname : 'N/A';
        }
        
        if (!empty($claim->finance_id)) {
            $finance = DB::table('staff')->where('id', $claim->finance_id)->first();
            $financeName = $finance ? $finance->fullname : 'N/A';
        }

        // Fallback diagnosis from claim table when encounter is orphaned
        if (empty($provisionalDiagnoses) && empty($confirmedDiagnoses) && !empty($claim->diagnosis)) {
            $confirmedDiagnoses[] = [
                'code' => '',
                'description' => $claim->diagnosis,
                'notes' => null,
            ];
        }

        // Pass user permissions for role-based UI
        // Try staff guard first, then fall back to default guard
        $user = auth('staff')->user() ?: auth()->user();
        $userPermissions = [
            'isSuperAdmin' => $user && ($user->hasRole('Super Admin') || $user->hasRole('admin')),
            'canVerify' => $user && ($user->hasRole('Super Admin') || $user->hasRole('admin') || $user->can('claim.verify')),
            'canApprove' => $user && ($user->hasRole('Super Admin') || $user->hasRole('admin') || $user->can('claim.approve')),
            'canEsApprove' => $user && ($user->hasRole('Super Admin') || $user->hasRole('admin') || $user->can('claim.es-approve')),
            'canFinance' => $user && ($user->hasRole('Super Admin') || $user->hasRole('admin') || $user->can('claim.finance-approve')),
            'canEditItems' => $user && ($user->hasRole('Super Admin') || $user->hasRole('admin') || $user->can('claim.edit-items')),
        ];

        // Debug logging
        \Log::info('Claim ID: ' . $claim->id . ', Medications count: ' . count($medications));
        \Log::info('Medications data: ' . json_encode($medications));

        return view('claims.facility-claim-show', compact('claim', 'medications', 'services', 'provisionalDiagnoses', 'confirmedDiagnoses', 'consultations', 'vitalSigns', 'actions', 'submittedByName', 'verifierName', 'approverName', 'esName', 'financeName', 'userPermissions'));
    }

    /**
     * Process bulk payment for multiple claims.
     */
    public function bulkPayment(Request $request)
    {
        $claimIds = $request->input('claim_ids', []);
        
        if (empty($claimIds)) {
            return response()->json(['success' => false, 'message' => 'No claims selected'], 400);
        }
        
        $successCount = 0;
        $failCount = 0;
        $errors = [];
        
        foreach ($claimIds as $claimId) {
            try {
                $claim = DB::table('facility_claims')->where('id', $claimId)->first();
                
                if (!$claim) {
                    $errors[] = "Claim ID {$claimId}: Not found";
                    $failCount++;
                    continue;
                }
                
                // Check if ES has approved
                if (($claim->es_status ?? '') !== 'approved') {
                    $errors[] = "Claim #{$claim->claim_number}: Not approved by ES";
                    $failCount++;
                    continue;
                }
                
                // Check if already paid
                if (($claim->finance_status ?? 'pending') === 'paid') {
                    $errors[] = "Claim #{$claim->claim_number}: Already paid";
                    $failCount++;
                    continue;
                }
                
                // Mark as paid
                DB::table('facility_claims')->where('id', $claimId)->update([
                    'finance_status' => 'paid',
                    'finance_updated_at' => now(),
                    'finance_id' => auth()->user()->id ?? null,
                    'payment_date' => now(),
                    'paid_by' => auth()->user()->id ?? null,
                    'status' => 'paid'
                ]);
                
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Claim ID {$claimId}: " . $e->getMessage();
                $failCount++;
                \Log::error("Bulk payment error for claim {$claimId}: " . $e->getMessage());
            }
        }
        
        $message = "Bulk payment completed: {$successCount} successful";
        if ($failCount > 0) {
            $message .= ", {$failCount} failed";
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'errors' => $errors
        ]);
    }

    /**
     * Update a medication or service item on a facility claim.
     * Only Verifier and Super Admin can edit qty/price.
     */
    public function updateFacilityClaimItem(Request $request, $claimId)
    {
        // Try staff guard first, then fall back to default guard
        $user = auth('staff')->user() ?: auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('Super Admin') || $user->hasRole('admin'));

        if (!$isSuperAdmin && !$user->can('claim.edit-items')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized – only Verifiers and Super Admins can edit items'], 403);
        }

        $claim = DB::table('facility_claims')->where('id', $claimId)->first();
        if (!$claim) {
            return response()->json(['success' => false, 'message' => 'Claim not found'], 404);
        }

        $itemType = $request->input('item_type'); // 'medication' or 'service'
        $itemId = $request->input('item_index'); // This is now the database ID (UUID string)
        $newQty = $request->input('quantity');
        $newPrice = $request->input('price');

        try {
            if ($itemType === 'medication') {
                // Update medication in facility_claim_medications table
                $updateData = ['updated_at' => now()];
                if ($newQty !== null) $updateData['quantity'] = (int) $newQty;
                if ($newPrice !== null) {
                    $updateData['unit_price'] = (float) $newPrice;
                    $updateData['total_price'] = (float) $newPrice * (int) ($newQty ?? 1);
                }
                
                $updated = DB::table('facility_claim_medications')
                    ->where('id', $itemId)
                    ->where('facility_claim_id', $claimId)
                    ->update($updateData);
                    
                if (!$updated) {
                    return response()->json(['success' => false, 'message' => 'Medication not found'], 404);
                }
            } else {
                // Update service in facility_claim_services table
                $updateData = ['updated_at' => now()];
                if ($newPrice !== null) {
                    $updateData['unit_price'] = (float) $newPrice;
                    $updateData['total_price'] = (float) $newPrice; // Services typically have quantity of 1
                }
                
                $updated = DB::table('facility_claim_services')
                    ->where('id', $itemId)
                    ->where('facility_claim_id', $claimId)
                    ->update($updateData);
                    
                if (!$updated) {
                    return response()->json(['success' => false, 'message' => 'Service not found'], 404);
                }
            }

            // Recalculate claim totals from actual database tables
            $medTotal = DB::table('facility_claim_medications')
                ->where('facility_claim_id', $claimId)
                ->sum('total_price') ?? 0;
                
            $svcTotal = DB::table('facility_claim_services')
                ->where('facility_claim_id', $claimId)
                ->sum('total_price') ?? 0;
                
            DB::table('facility_claims')->where('id', $claimId)->update([
                'pharmacy_amount' => $medTotal,
                'services_amount' => $svcTotal,
                'total_amount' => $medTotal + $svcTotal,
            ]);

            return response()->json(['success' => true, 'message' => 'Item updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a medication or service item from a facility claim.
     * Only Verifier and Super Admin can delete items.
     */
    public function deleteFacilityClaimItem(Request $request, $claimId)
    {
        // Try staff guard first, then fall back to default guard
        $user = auth('staff')->user() ?: auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('Super Admin') || $user->hasRole('admin'));

        if (!$isSuperAdmin && !$user->can('claim.edit-items')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $claim = DB::table('facility_claims')->where('id', $claimId)->first();
        if (!$claim) {
            return response()->json(['success' => false, 'message' => 'Claim not found'], 404);
        }

        $itemType = $request->input('item_type');
        $itemId = $request->input('item_index'); // This is now the database ID (UUID string)

        try {
            if ($itemType === 'medication') {
                // Delete medication from facility_claim_medications table
                $deleted = DB::table('facility_claim_medications')
                    ->where('id', $itemId)
                    ->where('facility_claim_id', $claimId)
                    ->delete();
                    
                if (!$deleted) {
                    return response()->json(['success' => false, 'message' => 'Medication not found'], 404);
                }
            } else {
                // Delete service from facility_claim_services table
                $deleted = DB::table('facility_claim_services')
                    ->where('id', $itemId)
                    ->where('facility_claim_id', $claimId)
                    ->delete();
                    
                if (!$deleted) {
                    return response()->json(['success' => false, 'message' => 'Service not found'], 404);
                }
            }

            // Recalculate claim totals from actual database tables
            $medTotal = DB::table('facility_claim_medications')
                ->where('facility_claim_id', $claimId)
                ->sum('total_price') ?? 0;
                
            $svcTotal = DB::table('facility_claim_services')
                ->where('facility_claim_id', $claimId)
                ->sum('total_price') ?? 0;
                
            DB::table('facility_claims')->where('id', $claimId)->update([
                'pharmacy_amount' => $medTotal,
                'services_amount' => $svcTotal,
                'total_amount' => $medTotal + $svcTotal,
            ]);

            return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Batch approve facility claims for ES and Finance stages.
     * ES, Finance, Super Admin and Admin roles can use batch approval.
     */
    public function batchApproveFacilityClaims(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('Super Admin') || $user->hasRole('admin'));
        $approvalType = $request->input('approval_type'); // 'ro', 'es' or 'finance'
        $claimIds = $request->input('claim_ids', []);
        $notes = $request->input('notes');

        if (empty($claimIds)) {
            return response()->json(['success' => false, 'message' => 'No claims selected'], 400);
        }

        // Permission check
        if ($approvalType === 'verifier' && !$isSuperAdmin && !$user->can('claim.verify')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized for verification'], 403);
        }
        if ($approvalType === 'approver' && !$isSuperAdmin && !$user->can('claim.approve')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized for approval'], 403);
        }
        if ($approvalType === 'es' && !$isSuperAdmin && !$user->can('claim.es-approve')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized for ES approval'], 403);
        }
        if ($approvalType === 'finance' && !$isSuperAdmin && !$user->can('claim.finance-approve')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized for Finance approval'], 403);
        }

        $successCount = 0;
        $failCount = 0;
        $errors = [];
        $totalAmount = 0;

        foreach ($claimIds as $claimId) {
            try {
                $claim = DB::table('facility_claims')->where('id', $claimId)->first();
                if (!$claim) {
                    $errors[] = "Claim ID {$claimId}: Not found";
                    $failCount++;
                    continue;
                }

                $updateData = [];
                $staffId = $user->id ?? null;

                if ($approvalType === 'verifier') {
                    if (($claim->status ?? '') !== 'submitted' || !in_array($claim->verifier_status ?? 'pending', ['pending', null, ''])) {
                        $errors[] = "Claim #{$claim->claim_number}: Not pending verification";
                        $failCount++;
                        continue;
                    }
                    $updateData = [
                        'verifier_status' => 'approved',
                        'verifier_updated_at' => now(),
                        'verifier_id' => $staffId,
                        'status' => 'verified',
                    ];
                } elseif ($approvalType === 'approver') {
                    if (($claim->status ?? '') !== 'verified' || !in_array($claim->approver_status ?? 'pending', ['pending', null, ''])) {
                        $errors[] = "Claim #{$claim->claim_number}: Not pending approval";
                        $failCount++;
                        continue;
                    }
                    $updateData = [
                        'approver_status' => 'approved',
                        'approver_updated_at' => now(),
                        'approver_id' => $staffId,
                        'status' => 'approved',
                    ];
                } elseif ($approvalType === 'es') {
                    if (($claim->status ?? '') !== 'approved' || !in_array($claim->es_status ?? 'pending', ['pending', null, ''])) {
                        $errors[] = "Claim #{$claim->claim_number}: Not pending ES approval";
                        $failCount++;
                        continue;
                    }
                    $updateData = [
                        'es_status' => 'approved',
                        'es_notes' => $notes,
                        'es_updated_at' => now(),
                        'es_id' => $staffId,
                        'status' => 'es_approved',
                    ];
                } elseif ($approvalType === 'finance') {
                    if (($claim->status ?? '') !== 'es_approved' || !in_array($claim->finance_status ?? 'pending', ['pending', null, ''])) {
                        $errors[] = "Claim #{$claim->claim_number}: Not pending payment";
                        $failCount++;
                        continue;
                    }
                    $updateData = [
                        'finance_status' => 'paid',
                        'finance_notes' => $notes,
                        'finance_updated_at' => now(),
                        'finance_id' => $staffId,
                        'payment_date' => now(),
                        'paid_by' => $staffId,
                        'status' => 'paid',
                    ];
                }

                DB::table('facility_claims')->where('id', $claimId)->update($updateData);
                $totalAmount += $claim->total_amount ?? 0;
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Claim ID {$claimId}: " . $e->getMessage();
                $failCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$successCount} claims processed" . ($failCount > 0 ? ", {$failCount} failed" : ''),
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'total_amount' => $totalAmount,
            'errors' => $errors,
        ]);
    }

    /**
     * Download facility claim as PDF matching BOSCHMA format.
     */
    public function downloadFacilityClaimPdf($claimId)
    {
        $claim = DB::table('facility_claims')->where('id', $claimId)->first();
        if (!$claim) {
            abort(404, 'Claim not found');
        }

        // Reuse the same data-loading logic as showFacilityClaim
        $medications = [];
        $services = [];
        $provisionalDiagnoses = [];
        $confirmedDiagnoses = [];

        if (!empty($claim->encounter_id)) {
            try {
                $encounter = \App\Models\Encounter::with([
                    'consultations.prescriptions.prescriptionItems.drug.dispensations',
                    'consultations.diagnoses.icdCode',
                    'serviceOrders.serviceOrderItems.serviceItem',
                    'serviceOrders.serviceOrderItems.serviceResults',
                ])->find($claim->encounter_id);

                if ($encounter) {
                    $consultations = $encounter->consultations ?? collect([]);
                    $prescriptions = $consultations->flatMap->prescriptions ?? collect([]);

                    foreach ($consultations as $consultation) {
                        foreach ($consultation->diagnoses as $dx) {
                            $dxData = [
                                'code' => $dx->icdCode->code ?? 'N/A',
                                'description' => $dx->icdCode->description ?? ($dx->notes ?? 'N/A'),
                            ];
                            if (strtolower($dx->diagnosis_type ?? '') === 'confirmed') {
                                $confirmedDiagnoses[] = $dxData;
                            } else {
                                $provisionalDiagnoses[] = $dxData;
                            }
                        }
                    }

                    foreach ($prescriptions as $prescription) {
                        foreach ($prescription->prescriptionItems as $item) {
                            $dispensation = $item->dispensations->first();
                            $drug = $item->drug;
                            $status = $dispensation ? ($dispensation->status ?? 'pending') : ($item->dispensing_status ?? 'pending');
                            $cost = 0;
                            if ($status === 'dispensed') {
                                $cost = ($dispensation && $dispensation->cost_of_medication > 0)
                                    ? $dispensation->cost_of_medication
                                    : (($drug && $drug->unit_price > 0) ? $drug->unit_price * ($item->quantity ?? 1) : 0);
                            }
                            $medications[] = [
                                'name' => $drug->name ?? 'N/A',
                                'quantity' => $item->quantity ?? 0,
                                'cost' => $cost,
                            ];
                        }
                    }

                    foreach ($encounter->serviceOrders ?? collect([]) as $order) {
                        foreach ($order->serviceOrderItems as $orderItem) {
                            $si = $orderItem->serviceItem;
                            $status = $orderItem->status ?? 'pending';
                            $price = in_array($status, ['completed', 'approved', 'delivered']) ? ($si->price ?? 0) : 0;
                            $services[] = [
                                'name' => $si->name ?? 'N/A',
                                'cost' => $price,
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('PDF encounter error: ' . $e->getMessage());
            }
        }

        // Fallback diagnosis from claim table
        $diagnosisText = 'N/A';
        if (count($confirmedDiagnoses) > 0) {
            $diagnosisText = collect($confirmedDiagnoses)->pluck('description')->implode(', ');
        } elseif (count($provisionalDiagnoses) > 0) {
            $diagnosisText = collect($provisionalDiagnoses)->pluck('description')->implode(', ');
        } elseif (!empty($claim->diagnosis)) {
            $diagnosisText = $claim->diagnosis;
        }

        $logoPath = public_path('images/boschma_logo.png');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('claims.facility-claim-pdf', compact(
            'claim', 'medications', 'services', 'diagnosisText', 'logoPath'
        ));
        $pdf->setPaper('a4', 'portrait');

        $fileName = 'BHCPF-Claim-' . ($claim->claim_number ?? $claim->id) . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Download facility claim (legacy).
     */
    public function downloadFacilityClaim($id)
    {
        return $this->downloadFacilityClaimPdf($id);
    }
}

