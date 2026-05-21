<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\Spouse;
use App\Models\Child;
use App\Models\BulkIdCardJob;
use App\Models\Facility;
use App\Models\Program;
use App\Models\Contribution;
use App\Models\ContributionType;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Announcement;
use App\Models\Enrollment;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Dependant;
use App\Models\AuditLog;
use App\Models\BeneficiaryDocument;
use App\Models\CivilServant;
use App\Services\QrCodeService;
use App\Jobs\GenerateBulkIdCards;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\Rule;
use Spatie\Browsershot\Browsershot;
use Dompdf\Dompdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BeneficiariesImport;
use App\Exports\BeneficiariesTemplateExport;
use Illuminate\Support\Facades\Log;

class BeneficiaryController extends Controller
{
    /**
     * Display a listing of beneficiaries.
     */
    public function index(Request $request)
    {
        $query = Beneficiary::with(['facility', 'spouse', 'children', 'creator', 'submitter', 'updater']);

        // Get status counts for summary
        $statusCounts = [
            'all' => Beneficiary::count(),
            'active' => Beneficiary::where('status', 'active')->count(),
            'inactive' => Beneficiary::where('status', 'inactive')->count(),
            'pending' => Beneficiary::where('status', 'pending')->count(),
            'rejected' => Beneficiary::where('status', 'rejected')->count(),
        ];

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('boschma_no', 'like', "%{$search}%")
                  ->orWhere('fullname', 'like', "%{$search}%")
                  ->orWhere('phone_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('export') && in_array($request->export, ['excel', 'pdf'])) {
            // Resolve facility name for report header
            $facilityName = 'ALL FACILITIES';
            if ($request->filled('facility_id')) {
                $facility = Facility::find($request->facility_id);
                if ($facility) {
                    $facilityName = strtoupper($facility->name);
                }
            }

            if ($request->export === 'pdf') {
                // Load a lean copy of the query for PDF (only needed columns)
                $pdfBeneficiaries = (clone $query)
                    ->select('id', 'fullname', 'gender', 'date_of_birth', 'marital_status', 'phone_no', 'nin', 'boschma_no')
                    ->orderBy('fullname')
                    ->get();

                // Group by age bracket
                $grouped = [
                    '0-5' => collect(), '6-17' => collect(), '18-35' => collect(),
                    '36-50' => collect(), '51-64' => collect(), '65+' => collect(),
                    'Unknown' => collect(),
                ];
                foreach ($pdfBeneficiaries as $b) {
                    $age = null;
                    if (!empty($b->date_of_birth)) {
                        try { $age = \Carbon\Carbon::parse($b->date_of_birth)->age; } catch (\Exception $e) {}
                    }
                    if ($age === null)      $grouped['Unknown']->push($b);
                    elseif ($age <= 5)      $grouped['0-5']->push($b);
                    elseif ($age <= 17)     $grouped['6-17']->push($b);
                    elseif ($age <= 35)     $grouped['18-35']->push($b);
                    elseif ($age <= 50)     $grouped['36-50']->push($b);
                    elseif ($age <= 64)     $grouped['51-64']->push($b);
                    else                    $grouped['65+']->push($b);
                }
                $groupedBeneficiaries = array_filter($grouped, fn($g) => $g->count() > 0);

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.prints.beneficiary-list', compact('groupedBeneficiaries', 'facilityName'))
                           ->setPaper('a4', 'landscape');

                return $pdf->stream('Beneficiary_List.pdf');
            }

            // Excel export (chunked, memory-efficient)
            return Excel::download(
                new \App\Exports\BeneficiariesExport($query, $facilityName),
                'Beneficiary_List_' . date('Ymd_His') . '.xlsx'
            );
        }

        $beneficiaries = $query->latest()->paginate(20)->withQueryString();
        $facilities = Facility::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();

        return view('admin.beneficiaries.index', [
            'beneficiaries' => $beneficiaries,
            'facilities' => $facilities,
            'programs' => $programs,
            'statusCounts' => $statusCounts,
        ]);
    }

    /**
     * Show verification page before creating a beneficiary.
     */
    public function verify()
    {
        $programs = Program::active()->get();
        
        return view('admin.beneficiaries.verify', compact('programs'));
    }

    /**
     * Verify NIN is not already in use.
     * Allow continuation for enrollments without BOSCHMA ID (incomplete).
     */
    public function verifyNin($nin)
    {
        // Check if NIN exists in beneficiaries
        $beneficiary = Beneficiary::where('nin', $nin)->first();
        if ($beneficiary) {
            // If no BOSCHMA number, enrollment is incomplete - allow continuation
            if (empty($beneficiary->boschma_no)) {
                return response()->json([
                    'available' => true,
                    'in_progress' => true,
                    'beneficiary_id' => $beneficiary->id,
                    'message' => 'This NIN belongs to an incomplete enrollment (Status: ' . ucfirst($beneficiary->status) . '). You can continue editing.',
                    'used_by' => $beneficiary->fullname,
                    'status' => $beneficiary->status
                ]);
            }
            
            // Has BOSCHMA number - enrollment is complete, don't allow
            return response()->json([
                'available' => false,
                'used_by' => $beneficiary->fullname,
                'record_type' => 'Beneficiary',
                'boschma_no' => $beneficiary->boschma_no,
                'status' => $beneficiary->status
            ]);
        }

        // Check if NIN exists in spouses
        $spouse = Spouse::where('nin', $nin)->first();
        if ($spouse) {
            return response()->json([
                'available' => false,
                'used_by' => $spouse->name,
                'record_type' => 'Spouse',
                'boschma_no' => $spouse->boschma_no
            ]);
        }

        // Check if NIN exists in children
        $child = Child::where('nin', $nin)->first();
        if ($child) {
            return response()->json([
                'available' => false,
                'used_by' => $child->name,
                'record_type' => 'Child',
                'boschma_no' => $child->boschma_no
            ]);
        }

        // NIN is available
        return response()->json([
            'available' => true,
            'in_progress' => false
        ]);
    }

    /**
     * Verify DP Number and fetch civil servant record.
     * Also check if beneficiary with this DP already exists.
     */
    public function verifyDp($dpNo)
    {
        // Check if a beneficiary with this DP number already exists
        $existingBeneficiary = Beneficiary::where('dp_no', $dpNo)->first();
        
        if ($existingBeneficiary) {
            // If no BOSCHMA number, enrollment is incomplete - allow continuation
            if (empty($existingBeneficiary->boschma_no)) {
                return response()->json([
                    'found' => true,
                    'in_progress' => true,
                    'beneficiary_id' => $existingBeneficiary->id,
                    'message' => 'An enrollment with this DP Number is incomplete (Status: ' . ucfirst($existingBeneficiary->status) . '). You can continue editing.',
                    'status' => $existingBeneficiary->status,
                    'civil_servant' => [
                        'id' => null,
                        'fullname' => $existingBeneficiary->fullname,
                        'dp_no' => $existingBeneficiary->dp_no,
                        'gender' => $existingBeneficiary->gender,
                        'phone_no' => $existingBeneficiary->phone_no,
                        'email' => $existingBeneficiary->email,
                        'lga' => $existingBeneficiary->lga,
                        'state' => $existingBeneficiary->state,
                        'date_of_birth' => $existingBeneficiary->date_of_birth,
                    ]
                ]);
            } else {
                // Has BOSCHMA number - enrollment is complete, don't allow
                return response()->json([
                    'found' => false,
                    'already_enrolled' => true,
                    'message' => 'This DP Number is already enrolled with BOSCHMA ID: ' . $existingBeneficiary->boschma_no . ' (Status: ' . ucfirst($existingBeneficiary->status) . ')',
                    'status' => $existingBeneficiary->status
                ]);
            }
        }
        
        // Check civil servant database
        $civilServant = CivilServant::where('dp_no', $dpNo)->first();

        if ($civilServant) {
            return response()->json([
                'found' => true,
                'in_progress' => false,
                'civil_servant' => [
                    'id' => $civilServant->id,
                    'fullname' => $civilServant->fullname,
                    'dp_no' => $civilServant->dp_no,
                    'gender' => $civilServant->gender,
                    'phone_no' => $civilServant->phone_no,
                    'email' => $civilServant->email,
                    'lga' => $civilServant->lga,
                    'state' => $civilServant->state,
                    'date_of_birth' => $civilServant->date_of_birth,
                ]
            ]);
        }

        return response()->json([
            'found' => false
        ]);
    }

    /**
     * Validate that all NINs are unique across all tables and within the form.
     * CRITICAL: Also validates that all NILs are verified (exist in civil_servants table).
     */
    private function validateNinUniqueness(Request $request, $excludeBeneficiaryId = null)
    {
        $nins = [];
        $errors = [];

        // Collect all NINs from the request
        if ($request->filled('nin')) {
            $nins['beneficiary'] = $request->nin;
        }

        if ($request->filled('spouse_nin')) {
            $nins['spouse'] = $request->spouse_nin;
        }

        if ($request->filled('child_nin_')) {
            foreach ($request->child_nin_ as $index => $childNin) {
                if (!empty($childNin)) {
                    $childName = $request->child_name_[$index] ?? "Child " . ($index + 1);
                    $nins["child ($childName)"] = $childNin;
                }
            }
        }

        // CRITICAL: Check that ONLY beneficiary NIN is verified (exists in civil_servants table)
        // NOTE: Spouse and children NILs are NOT checked against civil_servants
        // Only the primary beneficiary must be a civil servant; dependants (spouse/children) are not required to be
        foreach ($nins as $type => $nin) {
            if (strlen($nin) === 11) {
                // Only check civil_servants table for the main beneficiary
                if ($type === 'beneficiary') {
                    $civilServant = DB::table('civil_servants')->where('nin', $nin)->first();
                    if (!$civilServant) {
                        $errors[] = "Beneficiary NIN {$nin} must be verified before submission. Please verify through the NIN verification system.";
                    }
                }
                // Spouse and children NILs are not verified against civil_servants table
            }
        }

        // Check for duplicates within the form itself
        $ninValues = array_values($nins);
        $uniqueNins = array_unique($ninValues);
        
        if (count($ninValues) !== count($uniqueNins)) {
            $duplicates = array_diff_assoc($ninValues, $uniqueNins);
            return redirect()->back()
                ->withInput()
                ->withErrors(['nin' => 'Duplicate NINs found within the form. Each person must have a unique NIN.']);
        }

        // Check each NIN against the database
        foreach ($nins as $type => $nin) {
            // Check beneficiaries table (exclude current beneficiary if updating)
            $beneficiaryQuery = Beneficiary::where('nin', $nin);
            if ($excludeBeneficiaryId) {
                $beneficiaryQuery->where('id', '!=', $excludeBeneficiaryId);
            }
            $existsInBeneficiaries = $beneficiaryQuery->first();
            if ($existsInBeneficiaries) {
                $errors[] = "NIN {$nin} ({$type}) already exists in beneficiaries table (used by {$existsInBeneficiaries->fullname}).";
            }

            // Check spouses table (exclude spouse of current beneficiary if updating)
            $spouseQuery = Spouse::where('nin', $nin);
            if ($excludeBeneficiaryId) {
                $spouseQuery->where('boschma_no', '!=', Beneficiary::find($excludeBeneficiaryId)->boschma_no);
            }
            $existsInSpouses = $spouseQuery->first();
            if ($existsInSpouses) {
                $errors[] = "NIN {$nin} ({$type}) already exists in spouses table.";
            }

            // Check children table (exclude children of current beneficiary if updating)
            $childQuery = Child::where('nin', $nin);
            if ($excludeBeneficiaryId) {
                $childQuery->where('boschma_no', '!=', Beneficiary::find($excludeBeneficiaryId)->boschma_no);
            }
            $existsInChildren = $childQuery->first();
            if ($existsInChildren) {
                $errors[] = "NIN {$nin} ({$type}) already exists in children table.";
            }
        }

        // If any errors found, redirect back with errors
        if (!empty($errors)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nin' => implode(' ', $errors)]);
        }
    }

    /**
     * Show the form for creating a new beneficiary.
     */
    public function create(Request $request)
    {
        // Check if verification was completed
        if (!$request->has('verified')) {
            return redirect()->route('beneficiaries.verify')
                ->with('warning', 'Please complete verification first.');
        }

        // Get all facilities for selection
        $facilities = Facility::orderBy('name')->get();
        
        // Get all programs for dependant checking
        $programs = Program::all();
        $beneficiaryCategories = \App\Models\BeneficiaryCategory::orderBy('name')->get();

        return view('admin.beneficiaries.create', [
            'facilities' => $facilities,
            'programs' => $programs,
            'beneficiaryCategories' => $beneficiaryCategories,
        ]);
    }

    /**
     * Store a newly created beneficiary in storage.
     */
    public function store(Request $request)
    {
        // Validate NIN uniqueness across all tables and within the form
        $this->validateNinUniqueness($request);

        // Validate main beneficiary data
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'alt_facility_id' => 'nullable|exists:facilities,id',
            'program_id' => 'required|exists:programs,id',
            'fullname' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'nullable|string|max:255',
            'lga' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'contact_address' => 'nullable|string',
            'phone_no' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'occupation' => 'nullable|string|max:255',
            'dp_no' => 'nullable|string|max:255',
            'id_type' => 'nullable|string|max:255',
            'id_no' => 'nullable|string|max:255',
            'nin' => 'nullable|string|max:11',
            'place_of_work' => 'nullable|string|max:255',
            'date_of_employment' => 'nullable|date',
            'date_of_retirement' => 'nullable|date',
            'category' => 'nullable|string|max:255',
            'beneficiary_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Spouse data
            'has_spouse' => 'nullable|boolean',
            'spouse_name' => 'nullable|required_if:has_spouse,1|string|max:255',
            'spouse_gender' => 'nullable|required_if:has_spouse,1|in:Male,Female',
            'spouse_dob' => 'nullable|required_if:has_spouse,1|date',
            'spouse_phone' => 'nullable|string|max:20',
            'spouse_email' => 'nullable|email|max:255',
            'spouse_nin' => 'nullable|string|max:11',
            'spouse_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Children data (arrays for multiple children)
            'has_children' => 'nullable|boolean',
            'child_name_' => 'nullable|array',
            'child_name_.*' => 'nullable|string|max:255',
            'child_gender_' => 'nullable|array',
            'child_gender_.*' => 'required_with:child_name_.*|in:Male,Female',
            'child_date_of_birth_' => 'nullable|array',
            'child_date_of_birth_.*' => 'nullable|date',
            'child_birth_certificate_no_' => 'nullable|array',
            'child_birth_certificate_no_.*' => 'nullable|string|max:255',
            'child_nin_' => 'nullable|array',
            'child_nin_.*' => 'nullable|string|max:11',
            'child_birth_certificate_file_' => 'nullable|array',
            'child_birth_certificate_file_.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'child_photo_' => 'nullable|array',
            'child_photo_.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Check if program allows dependants
        $program = Program::findOrFail($request->program_id);
        
        if (!$program->has_dependant) {
            // If program doesn't allow dependants, ensure no spouse or children are submitted
            if ($request->has_spouse || $request->has_children) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['program_id' => 'The selected program does not allow dependants (spouse and children).']);
            }
        }

        // Begin transaction to ensure all related records are created
        DB::beginTransaction();

        try {
            // Check if this is a final submission of a progressively saved beneficiary
            $beneficiaryId = $request->input('beneficiary_id');
            $beneficiary = null;
            
            if ($beneficiaryId) {
                // Update existing beneficiary (from progressive saves)
                $beneficiary = Beneficiary::findOrFail($beneficiaryId);
            }
            
            // Generate BOSCHMA number atomically to prevent race conditions
            // Generate if:
            // 1. No existing BOSCHMA number AND
            // 2. Status is NOT "In Progress" (any other status means finalized)
            $boschmaNo = null;
            $shouldGenerateBoschma = (!$beneficiary || !$beneficiary->boschma_no) && 
                                     $request->status !== 'In Progress';
            
            $sequenceNumber = null;
            if ($shouldGenerateBoschma) {
                // Get program format/code
                $program = Program::findOrFail($request->program_id);
                $programFormat = $program->format ?? 'BOSCHMA'; // Default if format not set
                
                // Get the highest sequential number from the dedicated column
                $lastSequence = Beneficiary::lockForUpdate()->max('sequence_number') ?? 0;
                
                // Increment the sequence
                $sequenceNumber = $lastSequence + 1;
                
                // Pad to 6 digits
                $paddedNumber = str_pad($sequenceNumber, 6, '0', STR_PAD_LEFT);
                
                // Format: ProgramFormat + 6-digit number (e.g., BFS/000001, BFS/000002)
                $boschmaNo = $programFormat . $paddedNumber;
            } else if ($beneficiary) {
                $boschmaNo = $beneficiary->boschma_no; // Keep existing BOSCHMA number
                $sequenceNumber = $beneficiary->sequence_number; // Keep existing sequence
            }

            // Handle beneficiary photo upload
            $beneficiaryPhotoPath = null;
            if ($request->hasFile('beneficiary_photo')) {
                $beneficiaryPhotoPath = $request->file('beneficiary_photo')->store('beneficiary_photos', 'public');
            }

            // Prepare beneficiary data
            $beneficiaryData = [
                'facility_id' => $request->facility_id,
                'alt_facility_id' => $request->alt_facility_id,
                'program_id' => $request->program_id,
                'fullname' => $request->fullname,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'place_of_birth' => $request->place_of_birth,
                'lga' => $request->lga,
                'state' => $request->state,
                'nationality' => $request->nationality,
                'marital_status' => $request->marital_status,
                'ethnicity' => $request->ethnicity,
                'religion' => $request->religion,
                'contact_address' => $request->contact_address,
                'phone_no' => $request->phone_no,
                'email' => $request->email,
                'occupation' => $request->occupation,
                'dp_no' => $request->dp_no,
                'id_type' => $request->id_type,
                'id_no' => $request->id_no,
                'nin' => $request->nin,
                'place_of_work' => $request->place_of_work,
                'date_of_employment' => $request->date_of_employment,
                'date_of_retirement' => $request->date_of_retirement,
                'category' => $request->category,
                'status' => $request->status ?? 'active',
                'has_spouse' => $request->has_spouse ? 1 : 0,
                'number_of_children' => $request->has_children && is_array($request->child_name_) ? count(array_filter($request->child_name_)) : 0,
            ];
            
            // Track staff actions
            $currentStaffId = auth('staff')->id();
            if (!$beneficiary) {
                // New record - set created_by
                $beneficiaryData['created_by'] = $currentStaffId;
            }
            
            // Always update updated_by
            $beneficiaryData['updated_by'] = $currentStaffId;
            
            // If this is final submission (not "In Progress"), set submitted_by
            if ($request->status !== 'In Progress') {
                $beneficiaryData['submitted_by'] = $currentStaffId;
            }
            
            // Add photo if uploaded
            if ($beneficiaryPhotoPath) {
                $beneficiaryData['photo'] = $beneficiaryPhotoPath;
            }
            
            // Create or update beneficiary
            if ($beneficiary) {
                // If BOSCHMA number was just generated, include it in update
                if ($boschmaNo && !$beneficiary->boschma_no) {
                    $beneficiaryData['boschma_no'] = $boschmaNo;
                    $beneficiaryData['sequence_number'] = $sequenceNumber;
                }
                // Update existing beneficiary
                $beneficiary->update($beneficiaryData);
                // Refresh to ensure all data is current
                $beneficiary->refresh();
            } else {
                // Create new beneficiary - include boschma_no and sequence_number
                $beneficiaryData['boschma_no'] = $boschmaNo;
                $beneficiaryData['sequence_number'] = $sequenceNumber;
                $beneficiary = Beneficiary::create($beneficiaryData);
                // Refresh to ensure BOSCHMA number is loaded from database
                $beneficiary->refresh();
            }

            // Ensure we have a BOSCHMA number before proceeding with dependants
            if (!$beneficiary->boschma_no) {
                throw new \Exception('Failed to generate or retrieve BOSCHMA number for beneficiary ID: ' . $beneficiary->id);
            }

            // Handle spouse if exists
            if ($request->has_spouse) {
                $spousePhotoPath = null;
                if ($request->hasFile('spouse_photo')) {
                    $spousePhotoPath = $request->file('spouse_photo')->store('spouse_photos', 'public');
                }

                // Determine which facility to use for spouse
                $spouseFacilityId = $request->use_alt_facility_spouse ? $request->alt_facility_id : $request->facility_id;

                // Check if spouse already exists
                $existingSpouse = Spouse::where('beneficiary_id', $beneficiary->id)->first();

                $spouseData = [
                    'beneficiary_id' => $beneficiary->id,
                    'facility_id' => $spouseFacilityId,
                    'nin' => $request->spouse_nin,
                    'name' => $request->spouse_name,
                    'gender' => $request->spouse_gender,
                    'dob' => $request->spouse_dob,
                    'phone' => $request->spouse_phone,
                    'email' => $request->spouse_email,
                ];
                
                // Set boschma_no if spouse doesn't have one yet (new or from progressive save)
                if (!$existingSpouse || !$existingSpouse->boschma_no) {
                    $spouseData['boschma_no'] = $beneficiary->boschma_no . 'A';
                }
                
                if ($spousePhotoPath) {
                    $spouseData['photo'] = $spousePhotoPath;
                }
                
                // Update or create spouse
                if ($existingSpouse) {
                    $existingSpouse->update($spouseData);
                } else {
                    Spouse::create($spouseData);
                }
            }

            // Handle children if exist (up to 4)
            // Get existing children before deleting to preserve their files
            $existingChildren = Child::where('beneficiary_id', $beneficiary->id)
                ->orderBy('id')
                ->get()
                ->keyBy(function($child, $key) {
                    return $key; // Index by order (0, 1, 2, 3)
                });
            
            // Delete existing children before creating new ones to avoid duplicates
            Child::where('beneficiary_id', $beneficiary->id)->delete();
            
            if ($request->has_children && is_array($request->child_name_)) {
                $suffixes = ['B', 'C', 'D', 'E'];

                foreach ($request->child_name_ as $index => $childName) {
                    // Skip empty or invalid entries
                    if (empty($childName) || $index > 3) {  // Max 4 children (B,C,D,E)
                        continue;
                    }

                    $existingChild = $existingChildren->get($index);
                    
                    $childPhotoPath = null;
                    // Check for individual file names like child_photo_0, child_photo_1, etc.
                    $photoFieldName = "child_photo_{$index}";
                    if ($request->hasFile($photoFieldName)) {
                        $childPhotoPath = $request->file($photoFieldName)->store('children_photos', 'public');
                    } elseif ($existingChild && $existingChild->photo) {
                        // Preserve existing photo if no new one uploaded
                        $childPhotoPath = $existingChild->photo;
                    }

                    // Handle birth certificate file upload
                    $birthCertPath = null;
                    // Check for individual file names like child_birth_certificate_file_0, child_birth_certificate_file_1, etc.
                    $birthCertFieldName = "child_birth_certificate_file_{$index}";
                    if ($request->hasFile($birthCertFieldName)) {
                        // Store the file and get the full path (including directory)
                        $birthCertPath = $request->file($birthCertFieldName)->store('birth_certificates', 'public');
                    } elseif ($existingChild && $existingChild->birth_certificate_file) {
                        // Preserve existing birth certificate if no new one uploaded
                        $birthCertPath = $existingChild->birth_certificate_file;
                    }

                    // Determine which facility to use for this child
                    $useAltFacility = $request->input("use_alt_facility_child_{$index}", false);
                    $childFacilityId = $useAltFacility ? $request->alt_facility_id : $request->facility_id;

                    Child::create([
                        'beneficiary_id' => $beneficiary->id,
                        'facility_id' => $childFacilityId,
                        'boschma_no' => $beneficiary->boschma_no . $suffixes[$index],
                        'nin' => $request->child_nin_[$index] ?? null,
                        'name' => $childName,
                        'gender' => $request->child_gender_[$index] ?? 'Male', // Default to Male if not provided
                        'dob' => $request->child_date_of_birth_[$index] ?? null,
                        'birth_certificate_no' => $request->child_birth_certificate_no_[$index] ?? null,
                        'birth_certificate_file' => $birthCertPath,
                        'photo' => $childPhotoPath
                    ]);
                }
            }

            DB::commit();

            // Success message depends on whether BOSCHMA ID was generated
            if ($boschmaNo) {
                $message = 'Beneficiary enrollment finalized successfully! BOSCHMA ID: ' . $boschmaNo . ' (Status: ' . ucfirst($request->status ?? 'active') . ')';
            } else {
                $message = 'Beneficiary information saved successfully (Status: ' . ucfirst($request->status ?? 'In Progress') . ')';
            }

            return redirect()
                ->route('beneficiaries.show', $beneficiary)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error enrolling beneficiary: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified beneficiary.
     */
    public function show(Beneficiary $beneficiary)
    {
        // Get programs for convert modal
        $programs = Program::orderBy('name')->get();
        
        return view('admin.beneficiaries.show', [
            'beneficiary' => $beneficiary->load([
                'spouse', 
                'children', 
                'facility',
                'program',
                'creator',
                'submitter',
                'updater',
                'contributions' => function($query) {
                    $query->orderBy('year', 'desc')->orderBy('month', 'desc');
                }
            ]),
            'programs' => $programs,
        ]);
    }

    /**
     * Show the form for editing the specified beneficiary.
     */
    public function edit(Beneficiary $beneficiary)
    {
        // Get all facilities and programs for selection
        $facilities = Facility::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $beneficiaryCategories = \App\Models\BeneficiaryCategory::orderBy('name')->get();
        
        return view('admin.beneficiaries.edit', [
            'beneficiary' => $beneficiary->load(['spouse', 'children', 'facility', 'program']),
            'facilities' => $facilities,
            'programs' => $programs,
            'beneficiaryCategories' => $beneficiaryCategories,
        ]);
    }

    /**
     * Update the specified beneficiary in storage.
     */
    public function update(Request $request, Beneficiary $beneficiary)
    {
        // Validate NIN uniqueness (excluding current beneficiary's records)
        $this->validateNinUniqueness($request, $beneficiary->id);

        // Validate beneficiary data
        // NOTE: boschma_no is not validated here as it's immutable and should not be changed
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'alt_facility_id' => 'nullable|exists:facilities,id',
            'program_id' => 'required|exists:programs,id',
            'fullname' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',  // Maps to dob in DB
            'place_of_birth' => 'nullable|string|max:255',
            'lga' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'ethnicity' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'phone_no' => 'required|string|max:20',  // Maps to phone in DB
            'email' => 'nullable|email|max:255',
            'contact_address' => 'nullable|string|max:255',  // Maps to address in DB
            'id_type' => 'required|string|max:255',
            'id_no' => 'required|string|max:255',  // Maps to id_number in DB
            'nin' => 'nullable|string|max:11',
            'category' => 'required|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'dp_no' => 'nullable|string|max:255',
            'place_of_work' => 'nullable|string|max:255',
            'date_of_employment' => 'nullable|date',
            'date_of_retirement' => 'nullable|date',
            'signature_date' => 'nullable|date',
            'beneficiary_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',  // Maps to photo in DB
            'signature' => 'nullable|string',
            // Spouse data
            'has_spouse' => 'nullable|boolean',
            'spouse_name' => 'nullable|required_if:has_spouse,1|string|max:255',
            'spouse_gender' => 'nullable|required_if:has_spouse,1|in:Male,Female',
            'spouse_dob' => 'nullable|required_if:has_spouse,1|date',
            'spouse_phone' => 'nullable|string|max:20',
            'spouse_email' => 'nullable|email|max:255',
            'spouse_nin' => 'nullable|string|max:11',
            'spouse_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Children data
            'has_children' => 'nullable|boolean',
            'child_id' => 'nullable|array',
            'child_id.*' => 'nullable|numeric',
            'child_name' => 'nullable|array',
            'child_name.*' => 'nullable|string|max:255',
            'child_gender' => 'nullable|array',
            'child_gender.*' => 'nullable|in:Male,Female',
            'child_date_of_birth' => 'nullable|array',
            'child_date_of_birth.*' => 'nullable|date',
            'child_birth_certificate_no' => 'nullable|array',
            'child_birth_certificate_no.*' => 'nullable|string|max:255',
            'child_nin' => 'nullable|array',
            'child_nin.*' => 'nullable|string|max:11',
            'child_birth_certificate' => 'nullable|array',
            'child_birth_certificate.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'child_photo' => 'nullable|array',
            'child_photo.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Check if program allows dependants
        $program = Program::findOrFail($request->program_id);
        
        if (!$program->has_dependant) {
            // If program doesn't allow dependants, ensure no spouse or children are submitted
            if ($request->has_spouse || $request->has_children) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['program_id' => 'The selected program does not allow dependants (spouse and children).']);
            }
        }

        DB::beginTransaction();

        try {
            // Update beneficiary record
            // NOTE: Do NOT update boschma_no or sequence_number - these are immutable once assigned
            $beneficiaryData = [
                'facility_id' => $request->facility_id,
                'alt_facility_id' => $request->alt_facility_id,
                'program_id' => $request->program_id,
                'fullname' => $request->fullname,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'place_of_birth' => $request->place_of_birth,
                'lga' => $request->lga,
                'state' => $request->state,
                'nationality' => $request->nationality,
                'marital_status' => $request->marital_status,
                'ethnicity' => $request->ethnicity,
                'religion' => $request->religion,
                'contact_address' => $request->contact_address,
                'phone_no' => $request->phone_no,
                'email' => $request->email,
                'occupation' => $request->occupation,
                'dp_no' => $request->dp_no,
                'id_type' => $request->id_type,
                'id_no' => $request->id_no,
                'nin' => $request->nin,
                'place_of_work' => $request->place_of_work,
                'date_of_employment' => $request->date_of_employment,
                'date_of_retirement' => $request->date_of_retirement,
                'category' => $request->category,
                'signature_date' => $request->signature_date,
                'has_spouse' => $request->has_spouse ? 1 : 0,
                'number_of_children' => $request->has_children && is_array($request->child_name) ? count(array_filter($request->child_name)) : 0,
            ];
            
            // Track who updated this record
            $beneficiaryData['updated_by'] = auth('staff')->id();

            // Handle beneficiary photo upload if provided
            if ($request->hasFile('beneficiary_photo')) {
                // Delete old photo if exists
                if ($beneficiary->photo) {
                    Storage::disk('public')->delete($beneficiary->photo);
                }

                // Store new photo
                $photoPath = $request->file('beneficiary_photo')->store('beneficiary_photos', 'public');
                $beneficiaryData['photo'] = $photoPath;
            }

            // Handle signature if provided
            if ($request->filled('signature') && $request->signature != $beneficiary->signature) {
                $beneficiaryData['signature'] = $request->signature;
            }

            // Update beneficiary
            $beneficiary->update($beneficiaryData);

            // Handle spouse data
            if ($request->has_spouse) {
                // Determine which facility to use for spouse
                $spouseFacilityId = $request->use_alt_facility_spouse ? $request->alt_facility_id : $request->facility_id;
                
                $spouseData = [
                    'beneficiary_id' => $beneficiary->id,
                    'facility_id' => $spouseFacilityId,
                    'boschma_no' => $beneficiary->boschma_no . 'A',
                    'nin' => $request->spouse_nin,
                    'name' => $request->spouse_name,
                    'gender' => $request->spouse_gender,
                    'dob' => $request->spouse_dob,
                    'phone' => $request->spouse_phone,
                    'email' => $request->spouse_email,
                ];

                // Handle spouse photo upload
                if ($request->hasFile('spouse_photo')) {
                    // Check if spouse exists and has a photo
                    if ($beneficiary->spouse && $beneficiary->spouse->photo) {
                        Storage::disk('public')->delete($beneficiary->spouse->photo);
                    }

                    // Store new photo
                    $spousePhotoPath = $request->file('spouse_photo')->store('spouse_photos', 'public');
                    $spouseData['photo'] = $spousePhotoPath;
                }

                // Update or create spouse
                if ($beneficiary->spouse) {
                    $beneficiary->spouse->update($spouseData);
                } else {
                    Spouse::create($spouseData);
                }
            } elseif ($beneficiary->spouse) {
                // Delete spouse if checkbox unchecked
                if ($beneficiary->spouse->photo) {
                    Storage::disk('public')->delete('spouses/' . $beneficiary->spouse->photo);
                }
                $beneficiary->spouse->delete();
            }

            // Handle children data
            if ($request->has_children) {
                $suffixes = ['B', 'C', 'D', 'E'];
                $existingChildIds = [];

                foreach ($request->child_name ?? [] as $index => $childName) {
                    // Skip if child name is empty
                    if (empty($childName)) {
                        continue;
                    }

                    // Determine which facility to use for this child
                    $useAltFacility = $request->input("use_alt_facility_child_{$index}", false);
                    $childFacilityId = $useAltFacility ? $request->alt_facility_id : $request->facility_id;
                    
                    // Prepare child data (WITHOUT boschma_no initially - will be added for new children only)
                    $childData = [
                        'beneficiary_id' => $beneficiary->id,
                        'facility_id' => $childFacilityId,
                        'nin' => $request->child_nin[$index] ?? null,
                        'name' => $childName,
                        'gender' => $request->child_gender[$index] ?? null,
                        'dob' => $request->child_date_of_birth[$index] ?? null,
                        'birth_certificate_no' => $request->child_birth_certificate_no[$index] ?? null,
                    ];

                    // Handle child photo upload
                    if ($request->hasFile('child_photo') && isset($request->file('child_photo')[$index])) {
                        $childId = $request->child_id[$index] ?? null;
                        $child = $childId ? Child::find($childId) : null;

                        // Delete old photo if exists
                        if ($child && $child->photo) {
                            Storage::disk('public')->delete($child->photo);
                        }

                        // Store new photo
                        $childPhotoPath = $request->file('child_photo')[$index]->store('children_photos', 'public');
                        $childData['photo'] = $childPhotoPath;
                    }

                    // Handle birth certificate file upload
                    if ($request->hasFile('child_birth_certificate_file') && isset($request->file('child_birth_certificate_file')[$index])) {
                        $childId = $request->child_id[$index] ?? null;
                        $child = $childId ? Child::find($childId) : null;

                        // Delete old birth certificate file if exists
                        if ($child && $child->birth_certificate_file) {
                            Storage::disk('public')->delete($child->birth_certificate_file);
                        }

                        // Store new birth certificate file
                        $birthCertPath = $request->file('child_birth_certificate_file')[$index]->store('birth_certificates', 'public');
                        $childData['birth_certificate_file'] = $birthCertPath;
                    }

                    // Update or create child
                    if (isset($request->child_id[$index]) && $request->child_id[$index]) {
                        // Update existing child (do NOT update boschma_no)
                        $childId = $request->child_id[$index];
                        Child::where('id', $childId)->update($childData);
                        $existingChildIds[] = $childId;
                    } else {
                        // Create new child - set boschma_no ONLY on creation
                        $childData['boschma_no'] = $beneficiary->boschma_no . $suffixes[$index];
                        $child = Child::create($childData);
                        $existingChildIds[] = $child->id;
                    }
                }

                // Delete children that were removed from the form
                $removedChildren = Child::where('beneficiary_id', $beneficiary->id)
                    ->whereNotIn('id', $existingChildIds)
                    ->get();

                foreach ($removedChildren as $child) {
                    if ($child->photo) {
                        Storage::disk('public')->delete('children/' . $child->photo);
                    }
                    $child->delete();
                }
            } else {
                // Delete all children if checkbox unchecked
                foreach ($beneficiary->children as $child) {
                    if ($child->photo) {
                        Storage::disk('public')->delete('children/' . $child->photo);
                    }
                    $child->delete();
                }
            }

            DB::commit();
            return redirect()
                ->route('beneficiaries.show', $beneficiary)
                ->with('success', 'Beneficiary information updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating beneficiary: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified beneficiary from storage.
     */
    public function destroy(Beneficiary $beneficiary)
    {
        // Optional: Also delete associated files
        if ($beneficiary->photo) {
            Storage::disk('public')->delete($beneficiary->photo);
        }

        $beneficiary->delete();

        return redirect()
            ->route('beneficiaries.index')
            ->with('success', 'Beneficiary deleted successfully');
    }

    /**
     * Download a PDF of the beneficiary record
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf($id)
    {
        $beneficiary = Beneficiary::with(['spouse', 'children'])->findOrFail($id);

        $pdf = \PDF::loadView('admin.beneficiaries.pdf', compact('beneficiary'));

        // Sanitize the boschma_no to remove invalid filename characters
        $sanitizedBoschmaNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $beneficiary->boschma_no);
        
        return $pdf->download('beneficiary-' . $sanitizedBoschmaNo . '.pdf');
    }


    /**
     * Handle bulk actions for beneficiaries
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'beneficiary_ids' => 'required|array',
            'beneficiary_ids.*' => 'exists:beneficiaries,id',
            'action' => 'required|in:delete,active,inactive,pending,rejected'
        ]);

        $beneficiaryIds = $request->beneficiary_ids;
        $action = $request->action;

        try {
            if ($action === 'delete') {
                // Delete selected beneficiaries
                $beneficiaries = Beneficiary::whereIn('id', $beneficiaryIds)->get();
                foreach ($beneficiaries as $beneficiary) {
                    if ($beneficiary->photo) {
                        Storage::disk('public')->delete($beneficiary->photo);
                    }
                    $beneficiary->delete();
                }
                return redirect()
                    ->route('beneficiaries.index')
                    ->with('success', count($beneficiaryIds) . ' beneficiary(ies) deleted successfully');
            } else {
                // Update status for selected beneficiaries
                Beneficiary::whereIn('id', $beneficiaryIds)->update(['status' => $action]);
                return redirect()
                    ->route('beneficiaries.index')
                    ->with('success', count($beneficiaryIds) . ' beneficiary(ies) updated to ' . $action . ' status');
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('beneficiaries.index')
                ->with('error', 'Error performing bulk action: ' . $e->getMessage());
        }
    }

    /**
     * Update beneficiary status
     */
    public function updateStatus(Request $request, Beneficiary $beneficiary)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,pending,rejected'
        ]);

        try {
            $beneficiary->status = $request->status;
            $beneficiary->save();

            return redirect()
                ->route('beneficiaries.show', $beneficiary->id)
                ->with('success', 'Beneficiary status updated to ' . ucfirst($request->status) . ' successfully');
        } catch (\Exception $e) {
            return redirect()
                ->route('beneficiaries.show', $beneficiary->id)
                ->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }

    /**
     * Load existing beneficiary data for continuation
     */
    public function loadData(Beneficiary $beneficiary)
    {
        try {
            // Load related data
            $beneficiary->load(['spouse', 'children']);
            
            return response()->json([
                'success' => true,
                'beneficiary' => $beneficiary,
                'spouse' => $beneficiary->spouse,
                'children' => $beneficiary->children
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading beneficiary data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save individual sections of beneficiary form (Beneficiary Info, Spouse, Children)
     * Used for progressive saving before final submission
     */
    public function saveSection(Request $request)
    {
        $section = $request->input('section');
        $beneficiaryId = $request->input('beneficiary_id');

        DB::beginTransaction();

        try {
            if ($section === 'beneficiary_info') {
                // NOTE: NIN verification is NOT required for section saves (progressive save)
                // Users can save their work in progress even without NIN verification
                // NIN verification is only enforced during final submission (store method)
                // This allows users to work on the form in multiple sessions
                
                // Save or update main beneficiary information
                $data = [
                    'facility_id' => $request->facility_id,
                    'alt_facility_id' => $request->alt_facility_id,
                    'program_id' => $request->program_id,
                    'fullname' => $request->fullname,
                    'gender' => $request->gender,
                    'date_of_birth' => $request->date_of_birth,
                    'place_of_birth' => $request->place_of_birth,
                    'lga' => $request->lga,
                    'state' => $request->state,
                    'nationality' => $request->nationality,
                    'marital_status' => $request->marital_status,
                    'ethnicity' => $request->ethnicity,
                    'religion' => $request->religion,
                    'contact_address' => $request->contact_address,
                    'phone_no' => $request->phone_no,
                    'email' => $request->email,
                    'occupation' => $request->occupation,
                    'dp_no' => $request->dp_no,
                    'id_type' => $request->id_type,
                    'id_no' => $request->id_no,
                    'nin' => $request->nin,
                    'place_of_work' => $request->place_of_work,
                    'date_of_employment' => $request->date_of_employment,
                    'date_of_retirement' => $request->date_of_retirement,
                    'category' => $request->category,
                    'status' => 'In Progress',
                    'has_spouse' => $request->has_spouse ? 1 : 0,
                ];

                if ($beneficiaryId) {
                    // Update existing beneficiary
                    $beneficiary = Beneficiary::findOrFail($beneficiaryId);
                    
                    // Only update photo if new file uploaded, otherwise preserve existing
                    if ($request->hasFile('beneficiary_photo')) {
                        $data['photo'] = $request->file('beneficiary_photo')->store('beneficiary_photos', 'public');
                    }
                    
                    // Only update signature if new file uploaded, otherwise preserve existing
                    if ($request->hasFile('beneficiary_signature')) {
                        $data['signature'] = $request->file('beneficiary_signature')->store('beneficiary_signatures', 'public');
                    }
                    
                    $beneficiary->update($data);
                } else {
                    // Create new beneficiary (without BOSCHMA number yet)
                    $data['boschma_no'] = null; // Will be generated on final submission
                    
                    // Handle photo upload for new beneficiary
                    if ($request->hasFile('beneficiary_photo')) {
                        $data['photo'] = $request->file('beneficiary_photo')->store('beneficiary_photos', 'public');
                    }
                    
                    // Handle signature upload for new beneficiary
                    if ($request->hasFile('beneficiary_signature')) {
                        $data['signature'] = $request->file('beneficiary_signature')->store('beneficiary_signatures', 'public');
                    }
                    
                    $beneficiary = Beneficiary::create($data);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'beneficiary_id' => $beneficiary->id,
                    'message' => 'Beneficiary information saved successfully'
                ]);
            }

            if ($section === 'spouse_info') {
                if (!$beneficiaryId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please save beneficiary information first'
                    ], 400);
                }

                $beneficiary = Beneficiary::findOrFail($beneficiaryId);

                // Update beneficiary has_spouse flag
                $beneficiary->update(['has_spouse' => $request->has_spouse ? 1 : 0]);

                if ($request->has_spouse) {
                    // NOTE: Spouse NILs don't require civil_servants verification
                    // Spouses may not be civil servants, they're dependants of the beneficiary
                    // We only check for NIN uniqueness, not civil_servants table membership

                    $spouseData = [
                        'beneficiary_id' => $beneficiary->id,
                        'facility_id' => $request->use_alt_facility_spouse ? $request->alt_facility_id : $request->facility_id,
                        'boschma_no' => null, // Will be set on final submission
                        'nin' => $request->spouse_nin,
                        'name' => $request->spouse_name,
                        'gender' => $request->spouse_gender,
                        'dob' => $request->spouse_dob,
                        'phone' => $request->spouse_phone,
                        'email' => $request->spouse_email,
                    ];

                    // Handle spouse photo upload - only update if new file uploaded
                    if ($request->hasFile('spouse_photo')) {
                        $spouseData['photo'] = $request->file('spouse_photo')->store('spouse_photos', 'public');
                    }

                    // Get existing spouse if any
                    $existingSpouse = Spouse::where('beneficiary_id', $beneficiary->id)->first();
                    
                    if ($existingSpouse) {
                        // Update existing - don't include photo in update if no new file
                        $existingSpouse->update($spouseData);
                    } else {
                        // Create new spouse
                        Spouse::create($spouseData);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Spouse information saved successfully'
                ]);
            }

            if ($section === 'children_info') {
                if (!$beneficiaryId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please save beneficiary information first'
                    ], 400);
                }

                $beneficiary = Beneficiary::findOrFail($beneficiaryId);

                // Get existing children to preserve their files if not replaced
                $existingChildren = Child::where('beneficiary_id', $beneficiary->id)
                    ->orderBy('id')
                    ->get()
                    ->keyBy(function($child, $key) {
                        return $key; // Index 0, 1, 2, 3
                    });

                // Delete all existing children - we'll recreate them with preserved files
                Child::where('beneficiary_id', $beneficiary->id)->delete();

                // Update beneficiary has_children flag and count
                $childCount = 0;

                if ($request->has_children && is_array($request->child_name_)) {
                    // NOTE: Children NILs are optional and don't need verification
                    // Children are typically not civil servants, so we don't check civil_servants table
                    // We only validate uniqueness later in the process

                    $suffixes = ['B', 'C', 'D', 'E'];

                    foreach ($request->child_name_ as $index => $childName) {
                        if (empty($childName) || $index > 3) {
                            continue;
                        }

                        // Get existing child at this index to preserve files
                        $existingChild = $existingChildren->get($index);

                        // Handle child photo - use new upload or preserve existing
                        $childPhotoPath = null;
                        $photoFieldName = "child_photo_{$index}";
                        if ($request->hasFile($photoFieldName)) {
                            $childPhotoPath = $request->file($photoFieldName)->store('children_photos', 'public');
                        } elseif ($existingChild && $existingChild->photo) {
                            // Preserve existing photo
                            $childPhotoPath = $existingChild->photo;
                        }

                        // Handle birth certificate - use new upload or preserve existing
                        $birthCertPath = null;
                        $birthCertFieldName = "child_birth_certificate_file_{$index}";
                        if ($request->hasFile($birthCertFieldName)) {
                            $birthCertPath = $request->file($birthCertFieldName)->store('birth_certificates', 'public');
                        } elseif ($existingChild && $existingChild->birth_certificate_file) {
                            // Preserve existing birth certificate
                            $birthCertPath = $existingChild->birth_certificate_file;
                        }

                        $useAltFacility = $request->input("use_alt_facility_child_{$index}", false);
                        $childFacilityId = $useAltFacility ? $request->alt_facility_id : $request->facility_id;

                        Child::create([
                            'beneficiary_id' => $beneficiary->id,
                            'facility_id' => $childFacilityId,
                            'boschma_no' => null, // Will be set on final submission
                            'nin' => $request->child_nin_[$index] ?? null,
                            'name' => $childName,
                            'gender' => $request->child_gender_[$index] ?? 'Male',
                            'dob' => $request->child_date_of_birth_[$index] ?? null,
                            'birth_certificate_no' => $request->child_birth_certificate_no_[$index] ?? null,
                            'birth_certificate_file' => $birthCertPath,
                            'photo' => $childPhotoPath
                        ]);

                        $childCount++;
                    }
                }

                $beneficiary->update([
                    'number_of_children' => $childCount
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Children information saved successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid section'
            ], 400);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error saving: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate ID Card preview (HTML view)
     */
    public function generateIdCard(Beneficiary $beneficiary)
    {
        // Load beneficiary with relationships
        $beneficiary->load(['facility', 'program', 'spouse', 'children']);
        
        // Check if the program has dependants - use appropriate card format
        if ($beneficiary->program && !$beneficiary->program->has_dependant) {
            return view('admin.beneficiaries.id-card-no-dependants-preview', compact('beneficiary'));
        }
        
        return view('admin.beneficiaries.id-card-new', compact('beneficiary'));
    }

    /**
     * Download ID Card as PDF (Stream to browser) using Browsershot (Chrome) for perfect rendering
     */
    public function downloadIdCard(Beneficiary $beneficiary)
{
    // Load program to determine card format
    $beneficiary->load(['facility', 'program', 'spouse', 'children']);
    
    // Convert all images to base64
    $logoBase64 = $this->getAssetBase64(public_path('assets/img/brand/logo.png'));
    $signBase64 = $this->getAssetBase64(public_path('assets/img/brand/sign.png'));
    
    // Convert beneficiary photo to base64
    $beneficiaryPhotoBase64 = null;
    if ($beneficiary->photo) {
        $photoPath = storage_path('app/public/' . $beneficiary->photo);
        if (file_exists($photoPath)) {
            $photoData = base64_encode(file_get_contents($photoPath));
            $beneficiaryPhotoBase64 = 'data:image/' . pathinfo($photoPath, PATHINFO_EXTENSION) . ';base64,' . $photoData;
        }
    }
    
    // Generate QR code
    $beneficiaryData = [
        'boschma_no' => $beneficiary->boschma_no,
        'fullname' => $beneficiary->fullname,
        'dp_no' => $beneficiary->dp_no,
        'nin' => $beneficiary->nin,
        'facility' => $beneficiary->facility->name ?? 'N/A',
        'created_at' => $beneficiary->created_at->format('Y-m-d'),
        'expires_at' => $beneficiary->created_at->addYears(5)->format('Y-m-d')
    ];
    
    // Check if program has dependants - use appropriate card template
    $isNoDependants = $beneficiary->program && !$beneficiary->program->has_dependant;
    
    if ($isNoDependants) {
        // No-dependants card - simpler, no spouse/children
        $qrCodeBase64 = QrCodeService::generateBeneficiaryQrCode($beneficiaryData);
        
        // Convert program logo to base64 if available
        $programLogoBase64 = null;
        if ($beneficiary->program && $beneficiary->program->logo) {
            $programLogoBase64 = $this->getAssetBase64(storage_path('app/public/' . $beneficiary->program->logo));
        }
        
        $html = view('admin.beneficiaries.id-card-pdf-no-dependants', compact(
            'beneficiary', 'logoBase64', 'signBase64', 'beneficiaryPhotoBase64', 'qrCodeBase64', 'programLogoBase64'
        ))->render();
    } else {
        // Dependants card (Formal Sector format) - with spouse/children
        $spousePhotoBase64 = null;
        if ($beneficiary->spouse && $beneficiary->spouse->photo) {
            $spousePhotoPath = storage_path('app/public/' . $beneficiary->spouse->photo);
            if (file_exists($spousePhotoPath)) {
                $spousePhotoData = base64_encode(file_get_contents($spousePhotoPath));
                $spousePhotoBase64 = 'data:image/' . pathinfo($spousePhotoPath, PATHINFO_EXTENSION) . ';base64,' . $spousePhotoData;
            }
        }
        
        $childrenPhotosBase64 = [];
        if ($beneficiary->children) {
            foreach ($beneficiary->children as $child) {
                $childPhotoBase64 = null;
                if ($child->photo) {
                    $childPhotoPath = storage_path('app/public/' . $child->photo);
                    if (file_exists($childPhotoPath)) {
                        $childPhotoData = base64_encode(file_get_contents($childPhotoPath));
                        $childPhotoBase64 = 'data:image/' . pathinfo($childPhotoPath, PATHINFO_EXTENSION) . ';base64,' . $childPhotoData;
                    }
                }
                $childrenPhotosBase64[$child->id] = $childPhotoBase64;
            }
        }
        
        // Add spouse data to QR if exists
        if ($beneficiary->spouse) {
            $beneficiaryData['spouse'] = [
                'name' => $beneficiary->spouse->name,
                'boschma_no' => $beneficiary->spouse->boschma_no,
                'nin' => $beneficiary->spouse->nin,
                'gender' => $beneficiary->spouse->gender,
                'dob' => $beneficiary->spouse->dob,
                'facility' => $beneficiary->spouse->facility->name ?? 'N/A'
            ];
        }
        
        // Add children data to QR if exists
        if ($beneficiary->children && $beneficiary->children->count() > 0) {
            $beneficiaryData['children'] = [];
            foreach ($beneficiary->children as $child) {
                $beneficiaryData['children'][] = [
                    'name' => $child->name,
                    'boschma_no' => $child->boschma_no,
                    'nin' => $child->nin,
                    'gender' => $child->gender,
                    'dob' => $child->dob,
                    'facility' => $child->facility->name ?? 'N/A'
                ];
            }
        }
        
        $qrCodeBase64 = QrCodeService::generateBeneficiaryQrCode($beneficiaryData);
        
        $html = view('admin.beneficiaries.id-card-pdf-dompdf', compact(
            'beneficiary', 'logoBase64', 'signBase64', 'beneficiaryPhotoBase64', 
            'spousePhotoBase64', 'childrenPhotosBase64', 'qrCodeBase64'
        ))->render();
    }
    
    $safeBoschmaNo = str_replace(['/', '\\'], '-', $beneficiary->boschma_no);
    $filename = 'id-card-' . $safeBoschmaNo . '.pdf';
    $tempPath = storage_path('app/temp/' . $filename);
    
    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }
    
    // Simplified Browsershot without network dependencies
    $browsershot = Browsershot::html($html)
        ->timeout(60)
        ->setOption('landscape', false)
        ->paperSize(210, 297, 'mm')
        ->margins(10, 10, 10, 10)
        ->showBackground()
        ->noSandbox();
    
    // Only set Chrome path on live server, not localhost
    if (!$this->isLocalEnvironment()) {
        $browsershot->setChromePath("/opt/chrome-linux64/chrome");
    }
    
    $browsershot->save($tempPath);
    
    return response()->file($tempPath, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $filename . '"'
    ])->deleteFileAfterSend(true);
}


/**
 * Generate bulk ID cards
 */
public function generateBulkIdCards(Request $request)
{
    try {
        $beneficiaryIds = $request->input('beneficiary_ids', []);
        
        if (empty($beneficiaryIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No beneficiaries selected'
            ], 400);
        }
        
        // Limit to reasonable batch size
        if (count($beneficiaryIds) > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot generate more than 100 ID cards at once'
            ], 400);
        }
        
        $beneficiaries = Beneficiary::with(['facility', 'program', 'spouse', 'children'])
            ->whereIn('id', $beneficiaryIds)
            ->get();
        
        if ($beneficiaries->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid beneficiaries found'
            ], 404);
        }
        
        // Convert all images to base64 once
        $logoBase64 = $this->getAssetBase64(public_path('assets/img/brand/logo.png'));
        $signBase64 = $this->getAssetBase64(public_path('assets/img/brand/sign.png'));
        
        // Generate HTML for all beneficiaries with spacing between cards
        $allHtml = '<style>.card-spacing { margin-bottom: 1mm; }</style>';
        foreach ($beneficiaries as $beneficiary) {
            // Convert beneficiary photo to base64
            $beneficiaryPhotoBase64 = null;
            if ($beneficiary->photo) {
                $photoPath = storage_path('app/public/' . $beneficiary->photo);
                if (file_exists($photoPath)) {
                    $photoData = base64_encode(file_get_contents($photoPath));
                    $beneficiaryPhotoBase64 = 'data:image/' . pathinfo($photoPath, PATHINFO_EXTENSION) . ';base64,' . $photoData;
                }
            }
            
            // Determine card format based on program
            $isNoDependants = $beneficiary->program && !$beneficiary->program->has_dependant;
            
            if ($isNoDependants) {
                // No-dependants card
                $qrCodeBase64 = QrCodeService::generateBeneficiaryQrCode([
                    'boschma_no' => $beneficiary->boschma_no,
                    'fullname' => $beneficiary->fullname,
                    'facility' => $beneficiary->facility->name ?? 'N/A',
                ]);
                
                // Convert program logo to base64 if available
                $programLogoBase64 = null;
                if ($beneficiary->program && $beneficiary->program->logo) {
                    $programLogoPath = storage_path('app/public/' . $beneficiary->program->logo);
                    if (file_exists($programLogoPath)) {
                        $programLogoData = base64_encode(file_get_contents($programLogoPath));
                        $programLogoBase64 = 'data:image/' . pathinfo($programLogoPath, PATHINFO_EXTENSION) . ';base64,' . $programLogoData;
                    }
                }
                
                $html = view('admin.beneficiaries.id-card-pdf-no-dependants', compact(
                    'beneficiary', 
                    'logoBase64',
                    'signBase64',
                    'beneficiaryPhotoBase64', 
                    'qrCodeBase64',
                    'programLogoBase64'
                ))->render();
            } else {
                // Dependants card (Formal Sector format)
                $spousePhotoBase64 = null;
                if ($beneficiary->spouse && $beneficiary->spouse->photo) {
                    $spousePhotoPath = storage_path('app/public/' . $beneficiary->spouse->photo);
                    if (file_exists($spousePhotoPath)) {
                        $spousePhotoData = base64_encode(file_get_contents($spousePhotoPath));
                        $spousePhotoBase64 = 'data:image/' . pathinfo($spousePhotoPath, PATHINFO_EXTENSION) . ';base64,' . $spousePhotoData;
                    }
                }
                
                $childrenPhotosBase64 = [];
                if ($beneficiary->children) {
                    foreach ($beneficiary->children as $child) {
                        $childPhotoBase64 = null;
                        if ($child->photo) {
                            $childPhotoPath = storage_path('app/public/' . $child->photo);
                            if (file_exists($childPhotoPath)) {
                                $childPhotoData = base64_encode(file_get_contents($childPhotoPath));
                                $childPhotoBase64 = 'data:image/' . pathinfo($childPhotoPath, PATHINFO_EXTENSION) . ';base64,' . $childPhotoData;
                            }
                        }
                        $childrenPhotosBase64[$child->id] = $childPhotoBase64;
                    }
                }
                
                $html = view('admin.beneficiaries.id-card-pdf-dompdf', compact(
                    'beneficiary', 
                    'logoBase64',
                    'signBase64',
                    'beneficiaryPhotoBase64', 
                    'spousePhotoBase64', 
                    'childrenPhotosBase64'
                ))->render();
            }
            
            // Add spacing after each card
            $allHtml .= '<div class="card-spacing">' . $html . '</div>';
        }
        
        // Create temporary file
        $filename = 'bulk-id-cards-' . date('Y-m-d-H-i-s') . '.pdf';
        $tempPath = storage_path('app/temp/' . $filename);
        
        // Create temp directory if it doesn't exist
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        // Generate PDF using Browsershot
        $browsershot = Browsershot::html($allHtml)
            ->timeout(120) // Longer timeout for bulk generation
            ->setOption('landscape', false)
            ->paperSize(210, 297, 'mm')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->noSandbox();
        
        // Only set Chrome path on live server, not localhost
        if (!$this->isLocalEnvironment()) {
            $browsershot->setChromePath("/opt/chrome-linux64/chrome");
        }
        
        $browsershot->save($tempPath);
        
        // Return the file directly for download
        return response()->json([
            'success' => true,
            'message' => 'Successfully generated ID cards',
            'count' => $beneficiaries->count(),
            'filename' => $filename,
            'download_url' => route('beneficiaries.bulk-id-cards.download', ['filename' => $filename])
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Bulk ID card generation error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while generating ID cards: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Download bulk generated ID cards
 */
public function downloadBulkIdCards($filename)
{
    try {
        // Validate filename format
        if (!preg_match('/^bulk-id-cards-\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}\.pdf$/', $filename)) {
            abort(404, 'File not found');
        }
        
        $filePath = storage_path('app/temp/' . $filename);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found or expired');
        }
        
        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
        
    } catch (\Exception $e) {
        \Log::error('Bulk ID card download error: ' . $e->getMessage());
        abort(500, 'Download failed');
    }
}

/**
 * Show enhanced bulk ID cards generation page with background processing
 */
public function bulkIdCards(Request $request)
{
    $query = Beneficiary::with(['facility', 'program', 'spouse', 'children']);
    
    // Apply filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    if ($request->filled('facility_id')) {
        $query->where('facility_id', $request->facility_id);
    }
    
    if ($request->filled('program_id')) {
        $query->where('program_id', $request->program_id);
    }
    
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('fullname', 'LIKE', "%{$search}%")
              ->orWhere('boschma_no', 'LIKE', "%{$search}%");
        });
    }
    
    if ($request->filled('workplace')) {
        $query->where('place_of_work', $request->workplace);
    }
    
    if ($request->filled('category')) {
        $query->where('category', $request->category);
    }
    
    // Filter by enrollment date range
    if ($request->filled('enrollment_date_from')) {
        $query->whereDate('created_at', '>=', $request->enrollment_date_from);
    }
    
    if ($request->filled('enrollment_date_to')) {
        $query->whereDate('created_at', '<=', $request->enrollment_date_to);
    }
    
    // Filter for beneficiaries with dependants (spouse or children)
    if ($request->filled('has_dependants') && $request->has_dependants) {
        $query->where(function($q) {
            $q->whereHas('spouse')
              ->orWhereHas('children');
        });
    }
    
    $beneficiaries = $query->orderBy('created_at', 'desc')->paginate(500);
    $facilities = DB::table('facilities')->orderBy('name')->get();
    $programs = \App\Models\Program::orderBy('name')->get();
    
    // Get unique workplaces for dropdown
    $workplaces = DB::table('beneficiaries')
        ->where('place_of_work', '!=', '')
        ->whereNotNull('place_of_work')
        ->distinct()
        ->pluck('place_of_work')
        ->sort()
        ->values();
    
    // Get unique categories for dropdown
    $categories = DB::table('beneficiaries')
        ->where('category', '!=', '')
        ->whereNotNull('category')
        ->distinct()
        ->pluck('category')
        ->sort()
        ->values();
    
    // Get user's job history
    $jobHistory = BulkIdCardJob::forUser(Auth::id())
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    // Get active jobs
    $activeJobs = BulkIdCardJob::forUser(Auth::id())
        ->active()
        ->get();
    
    return view('admin.beneficiaries.bulk-id-cards', compact(
        'beneficiaries', 
        'facilities', 
        'programs',
        'workplaces',
        'categories',
        'jobHistory', 
        'activeJobs'
    ));
}

/**
 * Start background bulk ID card generation
 */
public function startBulkIdCardGeneration(Request $request)
{
    try {
        $request->validate([
            'generation_type' => 'required|in:all,filtered,facility,workplace,custom_selection,status,program',
            'title' => 'required|string|max:255',
        ]);
        
        $generationType = $request->input('generation_type');
        $title = $request->input('title');
        $criteria = [];
        
        // Build criteria based on generation type
        switch ($generationType) {
            case 'all':
                $criteria = ['type' => 'all'];
                break;
                
            case 'filtered':
                $criteria = ['type' => 'filtered'];
                if ($request->filled('facility_id')) {
                    $criteria['facility_id'] = $request->facility_id;
                }
                if ($request->filled('filter_status')) {
                    $criteria['status'] = $request->filter_status;
                }
                if ($request->filled('workplace')) {
                    $criteria['workplace'] = $request->workplace;
                }
                if ($request->filled('program_id')) {
                    $criteria['program_id'] = $request->program_id;
                }
                if ($request->filled('category')) {
                    $criteria['category'] = $request->category;
                }
                break;
                
            case 'facility':
                $request->validate(['facility_id' => 'required|exists:facilities,id']);
                $facility = DB::table('facilities')->find($request->facility_id);
                $criteria = [
                    'facility_id' => $request->facility_id,
                    'facility_name' => $facility->name,
                ];
                break;
                
            case 'status':
                $request->validate(['status' => 'required|in:active,pending,inactive']);
                $criteria = ['status' => $request->status];
                break;
                
            case 'workplace':
                $request->validate(['workplace' => 'required|string']);
                $criteria = ['workplace' => $request->workplace];
                break;
                
            case 'program':
                $request->validate(['program_id' => 'required|exists:programs,id']);
                $program = \App\Models\Program::find($request->program_id);
                $criteria = [
                    'program_id' => $request->program_id,
                    'program_name' => $program->name,
                    'card_type' => $program->has_dependant ? 'With Dependants' : 'No Dependants',
                ];
                break;
                
            case 'custom_selection':
                $request->validate(['beneficiary_ids' => 'required|array']);
                $request->validate(['beneficiary_ids.*' => 'exists:beneficiaries,id']);
                $criteria = [
                    'beneficiary_ids' => $request->beneficiary_ids,
                    'count' => count($request->beneficiary_ids),
                ];
                break;
        }
        
        // Add additional filters
        if ($request->filled('search')) {
            $criteria['search'] = $request->search;
        }
        
        // Add enrollment date range filters
        if ($request->filled('enrollment_date_from')) {
            $criteria['enrollment_date_from'] = $request->enrollment_date_from;
        }
        
        if ($request->filled('enrollment_date_to')) {
            $criteria['enrollment_date_to'] = $request->enrollment_date_to;
        }
        
        // Add dependants filter
        if ($request->filled('has_dependants') && $request->has_dependants) {
            $criteria['has_dependants'] = true;
        }
        
        // Create bulk job record
        $bulkJob = BulkIdCardJob::create([
            'job_id' => 'BULK-' . strtoupper(Str::random(8)),
            'title' => $title,
            'generation_type' => $generationType,
            'generation_criteria' => $criteria,
            'user_id' => Auth::id(),
        ]);
        
        // Dispatch background job with only the ID to avoid serialization issues
        GenerateBulkIdCards::dispatch($bulkJob->id);
        
        return response()->json([
            'success' => true,
            'message' => 'ID card generation started successfully',
            'job_id' => $bulkJob->job_id,
            'bulk_job_id' => $bulkJob->id,
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Failed to start bulk ID card generation: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to start generation: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Get job status and progress
 */
public function getBulkIdCardJobStatus($jobId)
{
    $job = BulkIdCardJob::where('job_id', $jobId)
        ->where('user_id', Auth::id())
        ->first();
    
    if (!$job) {
        return response()->json([
            'success' => false,
            'message' => 'Job not found',
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'job' => [
            'id' => $job->id,
            'job_id' => $job->job_id,
            'title' => $job->title,
            'status' => $job->status,
            'status_badge' => $job->status_badge,
            'total_records' => $job->total_records,
            'processed_records' => $job->processed_records,
            'failed_records' => $job->failed_records,
            'progress_percentage' => $job->progress_percentage,
            'generation_type' => $job->generation_type,
            'criteria_description' => $job->criteria_description,
            'file_name' => $job->file_name,
            'file_size' => $job->formatted_file_size,
            'is_downloadable' => $job->is_downloadable,
            'created_at' => $job->created_at->format('Y-m-d H:i:s'),
            'started_at' => $job->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $job->completed_at?->format('Y-m-d H:i:s'),
            'error_message' => $job->error_message,
        ]
    ]);
}

/**
 * Download generated bulk ID cards file (handles both single PDF and multiple PDFs as ZIP)
 */
public function downloadBulkIdCardFile($jobId)
{
    $job = BulkIdCardJob::where('job_id', $jobId)
        ->where('user_id', Auth::id())
        ->firstOrFail();
    
    if (!$job->is_downloadable) {
        abort(404, 'File not available for download');
    }
    
    $filePath = storage_path('app/' . $job->file_path);
    
    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }
    
    // Check if it's a directory (multiple PDFs) or a single file
    if (is_dir($filePath)) {
        // Multiple PDFs - create ZIP file for download
        $zipFileName = 'bulk-id-cards-' . $job->job_id . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        // Only create ZIP if it doesn't already exist
        if (!file_exists($zipPath)) {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $pdfFiles = glob($filePath . '/*.pdf');
                foreach ($pdfFiles as $pdfFile) {
                    $zip->addFile($pdfFile, basename($pdfFile));
                }
                $zip->close();
            } else {
                abort(500, 'Failed to create ZIP file');
            }
        }
        
        // Use Nginx X-Accel-Redirect to serve file directly (frees PHP worker instantly)
        return response('', 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
            'X-Accel-Redirect' => '/protected-files/temp/' . $zipFileName,
        ]);
    } else {
        // Single PDF file
        $fileName = $job->file_name ?? basename($filePath);
        
        // Use Nginx X-Accel-Redirect to serve file directly (frees PHP worker instantly)
        return response('', 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'X-Accel-Redirect' => '/protected-files/' . $job->file_path,
        ]);
    }
}

/**
 * Cancel bulk ID card generation job
 */
public function cancelBulkIdCardJob($jobId)
{
    $job = BulkIdCardJob::where('job_id', $jobId)
        ->where('user_id', Auth::id())
        ->whereIn('status', ['pending', 'processing'])
        ->first();
    
    if (!$job) {
        return response()->json([
            'success' => false,
            'message' => 'Job not found or cannot be cancelled',
        ], 404);
    }
    
    $job->markAsCancelled();
    
    return response()->json([
        'success' => true,
        'message' => 'Job cancelled successfully',
    ]);
}

/**
 * Delete a bulk ID card job and its associated files
 */
public function deleteBulkIdCardJob($jobId)
{
    $job = BulkIdCardJob::where('job_id', $jobId)
        ->where('user_id', Auth::id())
        ->first();
    
    if (!$job) {
        return response()->json([
            'success' => false,
            'message' => 'Job not found',
        ], 404);
    }
    
    // Delete associated files
    if ($job->file_path) {
        $fullPath = storage_path('app/' . $job->file_path);
        
        if (is_dir($fullPath)) {
            // Delete all PDFs in directory
            foreach (glob($fullPath . '/*.pdf') as $file) {
                @unlink($file);
            }
            @rmdir($fullPath);
        } elseif (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }
    
    // Delete the DB record
    $job->delete();
    
    return response()->json([
        'success' => true,
        'message' => 'Job and associated files deleted successfully',
    ]);
}

/**
 * Get all jobs for current user
 */
public function getBulkIdCardJobs(Request $request)
{
    $query = BulkIdCardJob::forUser(Auth::id());
    
    // Filter by status if provided
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    $jobs = $query->orderBy('created_at', 'desc')
        ->paginate(20);
    
    return response()->json([
        'success' => true,
        'jobs' => $jobs->items(),
        'pagination' => [
            'current_page' => $jobs->currentPage(),
            'last_page' => $jobs->lastPage(),
            'per_page' => $jobs->perPage(),
            'total' => $jobs->total(),
        ],
    ]);
    }
    
    /**
     * Get Chrome path based on environment
     */
    protected function getChromePath(): ?string
    {
        // Check if we're on localhost/development
        if ($this->isLocalEnvironment()) {
            // Common Chrome paths for different operating systems
            $paths = [
                'darwin' => [ // macOS
                    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
                    '/Applications/Chromium.app/Contents/MacOS/Chromium',
                    '/usr/bin/google-chrome-stable',
                    '/usr/bin/google-chrome',
                ],
                'linux' => [ // Linux
                    '/usr/bin/google-chrome-stable',
                    '/usr/bin/google-chrome',
                    '/usr/bin/chromium-browser',
                    '/usr/bin/chromium',
                    '/snap/bin/chromium',
                ],
                'win' => [ // Windows
                    'C:\Program Files\Google\Chrome\Application\chrome.exe',
                    'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
                    'C:\Users\%USERNAME%\AppData\Local\Google\Chrome\Application\chrome.exe',
                ],
            ];
            
            $os = strtolower(PHP_OS);
            foreach ($paths as $system => $systemPaths) {
                if (strpos($os, $system) === 0) {
                    foreach ($systemPaths as $path) {
                        if (file_exists(str_replace('%USERNAME%', getenv('USERNAME') ?? '', $path))) {
                            return $path;
                        }
                    }
                }
            }
        } else {
            // Production server paths - prioritize your working path first
            $productionPaths = [
                '/opt/chrome-linux64/chrome',  // Your working live server path - PRIORITY #1
                '/usr/bin/google-chrome-stable',
                '/usr/bin/google-chrome',
                '/usr/bin/chromium-browser',
                '/usr/bin/chromium',
                '/usr/local/bin/chrome',
                '/usr/local/bin/chromium',
            ];
            
            // Log detailed checking for production
            \Log::info('Chrome path checking on production', [
                'environment' => app()->environment(),
                'host' => request()->getHost(),
                'paths_to_check' => $productionPaths,
            ]);
            
            foreach ($productionPaths as $path) {
                $exists = file_exists($path);
                $executable = $exists && is_executable($path);
                
                \Log::info("Checking path: $path", [
                    'exists' => $exists,
                    'executable' => $executable,
                ]);
                
                if ($executable) {
                    \Log::info("Found working Chrome: $path");
                    return $path;
                }
            }
        }
        
        // IMPORTANT: Don't use PATH search on production - it finds snap versions first
        // Only use PATH search on localhost where snap versions work fine
        if ($this->isLocalEnvironment()) {
            $chromeInPath = shell_exec('which google-chrome 2>/dev/null || which chromium-browser 2>/dev/null || which chrome 2>/dev/null');
            if ($chromeInPath && trim($chromeInPath)) {
                return trim($chromeInPath);
            }
        }
        
        return null; // Let Browsershot use its default
    }
    
    /**
     * Convert a file asset to base64 data URI
     */
    protected function getAssetBase64(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }
        
        $data = base64_encode(file_get_contents($path));
        return 'data:image/' . pathinfo($path, PATHINFO_EXTENSION) . ';base64,' . $data;
    }

    /**
     * Check if running in local/development environment
     */
    protected function isLocalEnvironment(): bool
    {
        // Check environment
        if (app()->environment(['local', 'development', 'testing'])) {
            return true;
        }
        
        // Check common localhost indicators
        $host = request()->getHost();
        $localhostIndicators = [
            'localhost',
            '127.0.0.1',
            '0.0.0.0',
            '10.0.0',
            '192.168.',
            '172.16.',
            '.local',
            '.test',
            '.dev',
            '.xampp',
            '.wamp',
            '.mamp',
        ];
        
        foreach ($localhostIndicators as $indicator) {
            if (strpos($host, $indicator) !== false) {
                return true;
            }
        }
        
        // Check server software (XAMPP, WAMP, etc.)
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
        if (stripos($serverSoftware, 'xampp') !== false || 
            stripos($serverSoftware, 'wamp') !== false ||
            stripos($serverSoftware, 'mamp') !== false) {
            return true;
        }
        
        // Check if running from common development directories
        $projectPath = base_path();
        $devPaths = [
            '/Applications/XAMPP/',
            '/Applications/MAMP/',
            '/Applications/WAMP/',
            '/var/www/html/',
            '/home/vagrant/',
            '/Users/',
        ];
        
        foreach ($devPaths as $path) {
            if (strpos($projectPath, $path) === 0) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Show the beneficiary upload form.
     */
    public function uploadForm()
    {
        $programs = Program::active()->get();
        $states = ['Borno']; // For now, only Borno state
        $beneficiaryCategories = \App\Models\BeneficiaryCategory::orderBy('name')->get();
        
        return view('admin.beneficiaries.upload', compact('programs', 'states', 'beneficiaryCategories'));
    }

    /**
     * Handle Excel file upload and import beneficiaries.
     */
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'state' => 'required|string',
            'lga' => 'required|string',
            'facility_id' => 'required|exists:facilities,id',
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new BeneficiariesImport(
                $request->program_id,
                $request->facility_id,
                $request->state,
                $request->lga
            );
            
            Excel::import($import, $request->file('excel_file'));
            
            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();
            $errors = $import->getErrors();

            $message = "Import completed: {$importedCount} beneficiaries imported";
            
            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} skipped";
            }

            return redirect()->route('beneficiaries.upload.form')
                ->with('success', $message)
                ->with('import_results', [
                    'imported' => $importedCount,
                    'skipped' => $skippedCount,
                    'total' => $importedCount + $skippedCount,
                    'errors' => array_slice($errors, 0, 20), // Limit errors shown
                ]);
                
        } catch (\Exception $e) {
            Log::error('Beneficiary import failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to import Excel file: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Download beneficiary import template.
     */
    public function downloadTemplate()
    {
        return Excel::download(new BeneficiariesTemplateExport, 'beneficiaries_import_template.xlsx');
    }

    /**
     * Convert beneficiary to a different program.
     * Updates BOSCHMA numbers for beneficiary, spouse, and children.
     */
    public function convertProgram(Request $request, Beneficiary $beneficiary)
    {
        $request->validate([
            'new_program_id' => 'required|exists:programs,id|different:current_program_id',
        ], [
            'new_program_id.different' => 'Please select a different program to convert to.',
        ]);

        // Don't allow conversion if no BOSCHMA number exists
        if (!$beneficiary->boschma_no) {
            return redirect()->back()->with('error', 'Cannot convert: Beneficiary does not have a valid BOSCHMA number.');
        }

        // Get the new program
        $newProgram = Program::findOrFail($request->new_program_id);
        $newProgramFormat = $newProgram->format ?? 'BOSCHMA';

        // Check if user wants to generate a new BOSCHMA number
        $generateNew = $request->has('generate_new_boschma_no');
        
        if ($generateNew) {
            // Get the last sequence number and use next one
            $lastSequence = Beneficiary::lockForUpdate()->max('sequence_number') ?? 0;
            $sequenceNumber = $lastSequence + 1;
        } else {
            // Keep the same sequence number; if missing, try to extract from boschma_no
            $sequenceNumber = $beneficiary->sequence_number;
            if (!$sequenceNumber) {
                // Extract trailing digits from current boschma_no
                if (preg_match('/(\d+)$/', $beneficiary->boschma_no, $m)) {
                    $sequenceNumber = (int) $m[1];
                }
            }
            if (!$sequenceNumber) {
                return redirect()->back()->with('error', 'Cannot convert: Beneficiary does not have a valid sequence number. Please check "Generate New Boschma No" to assign a new one.');
            }
        }
        
        $paddedNumber = str_pad($sequenceNumber, 6, '0', STR_PAD_LEFT);
        
        // Generate new BOSCHMA number with new program format
        $newBoschmaNo = $newProgramFormat . $paddedNumber;

        // Store old values for logging
        $oldBoschmaNo = $beneficiary->boschma_no;
        $oldProgramId = $beneficiary->program_id;
        $oldSequenceNumber = $beneficiary->sequence_number;

        try {
            DB::beginTransaction();

            // Update beneficiary
            $updateData = [
                'program_id' => $request->new_program_id,
                'boschma_no' => $newBoschmaNo,
                'updated_by' => auth('staff')->id(),
            ];
            
            // Update sequence number if generating new BOSCHMA number
            if ($generateNew) {
                $updateData['sequence_number'] = $sequenceNumber;
            }
            
            $beneficiary->update($updateData);

            // Update spouse if exists
            if ($beneficiary->spouse) {
                $beneficiary->spouse->update([
                    'boschma_no' => $newBoschmaNo . 'A',
                ]);
            }

            // Update children if any
            $suffixes = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
            foreach ($beneficiary->children as $index => $child) {
                if (isset($suffixes[$index])) {
                    $child->update([
                        'boschma_no' => $newBoschmaNo . $suffixes[$index],
                    ]);
                }
            }

            DB::commit();

            // Log the conversion
            Log::info("Beneficiary program converted", [
                'beneficiary_id' => $beneficiary->id,
                'old_program_id' => $oldProgramId,
                'new_program_id' => $request->new_program_id,
                'old_boschma_no' => $oldBoschmaNo,
                'new_boschma_no' => $newBoschmaNo,
                'old_sequence' => $oldSequenceNumber,
                'new_sequence' => $sequenceNumber,
                'generate_new' => $generateNew,
                'converted_by' => auth('staff')->id(),
            ]);

            return redirect()->route('beneficiaries.show', $beneficiary->id)
                ->with('success', "Program converted successfully. BOSCHMA ID changed from {$oldBoschmaNo} to {$newBoschmaNo}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to convert beneficiary program: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to convert program: ' . $e->getMessage());
        }
    }
}
