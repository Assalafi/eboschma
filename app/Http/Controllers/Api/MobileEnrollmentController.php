<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\Program;
use App\Models\Facility;
use App\Models\Spouse;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MobileEnrollmentController extends Controller
{
    /**
     * Get programs list
     */
    public function getPrograms(Request $request)
    {
        Log::info('📋 Fetching Programs', [
            'staff_id' => $request->user()->id
        ]);

        $programs = Program::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'programs' => $programs
        ], 200);
    }

    /**
     * Get facilities list
     */
    public function getFacilities(Request $request)
    {
        Log::info('🏥 Fetching Facilities', [
            'staff_id' => $request->user()->id
        ]);

        $facilities = Facility::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'facilities' => $facilities
        ], 200);
    }

    /**
     * Get civil servants list for offline sync
     */
    public function getCivilServants(Request $request)
    {
        Log::info('👥 Fetching Civil Servants', [
            'staff_id' => $request->user()->id
        ]);

        $civilServants = DB::table('civil_servants')
            ->select('id', 'fullname', 'nin', 'dp_no', 'gender', 'lga', 'state', 'dob', 'mda', 'created_at', 'updated_at')
            ->orderBy('fullname')
            ->get();

        Log::info('✅ Civil Servants Retrieved', [
            'count' => $civilServants->count()
        ]);

        return response()->json([
            'success' => true,
            'civil_servants' => $civilServants
        ], 200);
    }

    /**
     * Get spouses list for offline sync (NIN verification)
     */
    public function getSpouses(Request $request)
    {
        Log::info('👰 Fetching Spouses', [
            'staff_id' => $request->user()->id
        ]);

        $spouses = DB::table('spouses')
            ->select('id', 'beneficiary_id', 'facility_id', 'boschma_no', 'name', 'gender', 'dob', 'nin', 'phone', 'email', 'photo', 'remarks', 'created_at', 'updated_at')
            ->whereNotNull('nin')
            ->where('nin', '!=', '')
            ->orderBy('name')
            ->get();

        Log::info('✅ Spouses Retrieved', [
            'count' => $spouses->count()
        ]);

        return response()->json([
            'success' => true,
            'spouses' => $spouses
        ], 200);
    }

    /**
     * Get children list for offline sync (NIN verification)
     */
    public function getChildren(Request $request)
    {
        Log::info('👶 Fetching Children', [
            'staff_id' => $request->user()->id
        ]);

        $children = DB::table('children')
            ->select('id', 'beneficiary_id', 'facility_id', 'boschma_no', 'name', 'gender', 'dob', 'nin', 'birth_certificate_no', 'birth_certificate_file', 'photo', 'remarks', 'created_at', 'updated_at')
            ->whereNotNull('nin')
            ->where('nin', '!=', '')
            ->orderBy('name')
            ->get();

        Log::info('✅ Children Retrieved', [
            'count' => $children->count()
        ]);

        return response()->json([
            'success' => true,
            'children' => $children
        ], 200);
    }

    /**
     * Get enumerators list for filtering
     */
    public function getEnumerators(Request $request)
    {
        Log::info('👤 Fetching Enumerators', [
            'staff_id' => $request->user()->id
        ]);

        // Get staff members as enumerators
        $enumerators = DB::table('staff')
            ->select(
                'id',
                'fullname as name',
                'email'
            )
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('fullname')
            ->get();

        Log::info('✅ Enumerators Retrieved', [
            'count' => $enumerators->count()
        ]);

        return response()->json([
            'success' => true,
            'enumerators' => $enumerators
        ], 200);
    }

    /**
     * Get beneficiaries list with pagination for mobile app
     */
    public function getBeneficiariesList(Request $request)
    {
        // Get filter and pagination parameters
        $filters = [
            'boschma_no' => $request->get('boschma_no'),
            'nin' => $request->get('nin'),
            'date' => $request->get('date'),
            'date_start' => $request->get('date_start'),
            'date_end' => $request->get('date_end'),
            'enumerator_id' => $request->get('enumerator_id'),
            'status' => $request->get('status'),
        ];
        
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);
        $offset = ($page - 1) * $perPage;

        Log::info('👨‍👩‍👧‍👦 Fetching Beneficiaries with Pagination and Filters', [
            'staff_id' => $request->user()->id,
            'page' => $page,
            'per_page' => $perPage,
            'filters' => array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            })
        ]);

        // Get beneficiaries with facility information for mobile app
        $query = DB::table('beneficiaries')
            ->select(
                'beneficiaries.id', 'beneficiaries.program_id', 'beneficiaries.facility_id', 'beneficiaries.alt_facility_id', 
                'beneficiaries.boschma_no', 'beneficiaries.sequence_number',
                'beneficiaries.fullname', 'beneficiaries.gender', 'beneficiaries.date_of_birth', 'beneficiaries.place_of_birth',
                'beneficiaries.lga', 'beneficiaries.state', 'beneficiaries.nationality', 
                'beneficiaries.marital_status', 'beneficiaries.ethnicity', 'beneficiaries.religion',
                'beneficiaries.contact_address', 'beneficiaries.phone_no', 'beneficiaries.email',
                'beneficiaries.occupation', 'beneficiaries.dp_no', 'beneficiaries.id_type', 'beneficiaries.id_no', 'beneficiaries.nin',
                'beneficiaries.place_of_work', 'beneficiaries.date_of_employment', 'beneficiaries.date_of_retirement', 'beneficiaries.category',
                'beneficiaries.photo', 'beneficiaries.has_spouse', 'beneficiaries.number_of_children', 'beneficiaries.status',
                'beneficiaries.created_at', 'beneficiaries.updated_at', 'beneficiaries.created_by', 'beneficiaries.submitted_by', 'beneficiaries.updated_by', 'beneficiaries.signature_date',
                'facilities.name as facility_name',
                'facilities.ward as facility_ward'
            )
            ->leftJoin('facilities', 'beneficiaries.facility_id', '=', 'facilities.id')
            ->whereNotNull('beneficiaries.nin')
            ->where('beneficiaries.nin', '!=', '');

        // Apply filters
        if (!empty($filters['boschma_no'])) {
            $query->where('beneficiaries.boschma_no', 'LIKE', '%' . $filters['boschma_no'] . '%');
        }
        
        if (!empty($filters['nin'])) {
            $query->where('beneficiaries.nin', 'LIKE', '%' . $filters['nin'] . '%');
        }
        
        if (!empty($filters['status'])) {
            $query->where('beneficiaries.status', $filters['status']);
        }
        
        if (!empty($filters['enumerator_id'])) {
            $query->where('beneficiaries.created_by', $filters['enumerator_id']);
        }
        
        // Date filtering
        if (!empty($filters['date'])) {
            $query->whereDate('beneficiaries.created_at', $filters['date']);
        } else {
            if (!empty($filters['date_start'])) {
                $query->whereDate('beneficiaries.created_at', '>=', $filters['date_start']);
            }
            if (!empty($filters['date_end'])) {
                $query->whereDate('beneficiaries.created_at', '<=', $filters['date_end']);
            }
        }

        $query->orderBy('beneficiaries.created_at', 'desc');

        // Get total count for pagination metadata
        $total = $query->count();

        // Get paginated results
        $beneficiaries = $query->offset($offset)->limit($perPage)->get();

        // Calculate pagination metadata
        $hasMorePages = ($offset + $perPage) < $total;
        $currentPage = $page;
        $totalPages = ceil($total / $perPage);

        Log::info('✅ Beneficiaries Retrieved with Pagination', [
            'page' => $currentPage,
            'per_page' => $perPage,
            'total' => $total,
            'returned' => $beneficiaries->count(),
            'has_more' => $hasMorePages
        ]);

        return response()->json([
            'success' => true,
            'data' => $beneficiaries,
            'pagination' => [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_more_pages' => $hasMorePages,
                'from' => $offset + 1,
                'to' => $offset + $beneficiaries->count()
            ]
        ], 200);
    }

    /**
     * Verify NIN against civil_servants table
     */
    public function verifyNin(Request $request)
    {
        Log::info('🔍 NIN Verification Request', [
            'staff_id' => $request->user()->id,
            'nin' => $request->nin
        ]);

        $validator = Validator::make($request->all(), [
            'nin' => 'required|string|size:11',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if NIN already exists in the system (beneficiaries, spouses, or children)
        $existingBeneficiary = Beneficiary::where('nin', $request->nin)->first();
        $existingSpouse = Spouse::where('nin', $request->nin)->first();
        $existingChild = Child::where('nin', $request->nin)->first();
        
        if ($existingBeneficiary) {
            Log::warning('⚠️ NIN Already Exists - Beneficiary', [
                'nin' => $request->nin,
                'beneficiary_id' => $existingBeneficiary->id,
                'boschma_no' => $existingBeneficiary->boschma_no
            ]);

            return response()->json([
                'success' => false,
                'message' => 'This NIN is already enrolled as a beneficiary (BOSCHMA ID: ' . $existingBeneficiary->boschma_no . ')',
                'data' => null
            ], 409);
        }
        
        if ($existingSpouse) {
            Log::warning('⚠️ NIN Already Exists - Spouse', [
                'nin' => $request->nin,
                'spouse_id' => $existingSpouse->id,
                'beneficiary_id' => $existingSpouse->beneficiary_id
            ]);

            $beneficiary = Beneficiary::find($existingSpouse->beneficiary_id);
            return response()->json([
                'success' => false,
                'message' => 'This NIN is already registered as a spouse (under BOSCHMA ID: ' . ($beneficiary->boschma_no ?? 'N/A') . ')',
                'data' => null
            ], 409);
        }
        
        if ($existingChild) {
            Log::warning('⚠️ NIN Already Exists - Child', [
                'nin' => $request->nin,
                'child_id' => $existingChild->id,
                'beneficiary_id' => $existingChild->beneficiary_id
            ]);

            $beneficiary = Beneficiary::find($existingChild->beneficiary_id);
            return response()->json([
                'success' => false,
                'message' => 'This NIN is already registered as a child (under BOSCHMA ID: ' . ($beneficiary->boschma_no ?? 'N/A') . ')',
                'data' => null
            ], 409);
        }

        // NIN is unique - return success
        Log::info('✅ NIN is Unique and Available', [
            'nin' => $request->nin
        ]);

        return response()->json([
            'success' => true,
            'message' => 'NIN is available for enrollment',
            'data' => [
                'nin' => $request->nin,
                'is_unique' => true
            ]
        ], 200);
    }

    /**
     * Verify DP Number
     */
    public function verifyDpNumber(Request $request)
    {
        Log::info('🔍 DP Number Verification Request', [
            'staff_id' => $request->user()->id,
            'dp_no' => $request->dp_no
        ]);

        $validator = Validator::make($request->all(), [
            'dp_no' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::warning('❌ DP Validation Failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if DP already exists in beneficiaries table (only beneficiaries have dp_no)
        $existingBeneficiary = Beneficiary::where('dp_no', $request->dp_no)->first();
        
        if ($existingBeneficiary) {
            // If enrollment is incomplete (no BOSCHMA ID), allow continuation
            if (empty($existingBeneficiary->boschma_no)) {
                Log::info('⚠️ DP Found - Incomplete Enrollment', [
                    'dp_no' => $request->dp_no,
                    'beneficiary_id' => $existingBeneficiary->id,
                    'status' => $existingBeneficiary->status
                ]);

                return response()->json([
                    'success' => true,
                    'in_progress' => true,
                    'message' => 'An enrollment with this DP Number is incomplete. You can continue editing.',
                    'data' => [
                        'dp_no' => $existingBeneficiary->dp_no,
                        'fullname' => $existingBeneficiary->fullname,
                        'beneficiary_id' => $existingBeneficiary->id,
                        'status' => $existingBeneficiary->status,
                        'is_unique' => false
                    ]
                ], 200);
            } else {
                // Enrollment is complete (has BOSCHMA ID)
                Log::warning('⚠️ DP Already Enrolled', [
                    'dp_no' => $request->dp_no,
                    'beneficiary_id' => $existingBeneficiary->id,
                    'boschma_no' => $existingBeneficiary->boschma_no
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'This DP Number is already enrolled (BOSCHMA ID: ' . $existingBeneficiary->boschma_no . ')',
                    'data' => null
                ], 409);
            }
        }

        // Check if DP exists in civil_servants table
        $civilServant = DB::table('civil_servants')
            ->where('dp_no', $request->dp_no)
            ->first();

        if (!$civilServant) {
            Log::warning('❌ DP Number Not Found in Civil Servants', [
                'dp_no' => $request->dp_no
            ]);

            return response()->json([
                'success' => false,
                'message' => 'DP Number not found in civil servants database',
                'data' => null
            ], 404);
        }

        // DP is valid and unique
        Log::info('✅ DP Number Verified Successfully', [
            'dp_no' => $request->dp_no,
            'civil_servant' => $civilServant->fullname ?? 'N/A'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'DP Number verified successfully',
            'data' => [
                'dp_no' => $civilServant->dp_no ?? null,
                'nin' => $civilServant->nin ?? null,
                'fullname' => $civilServant->fullname ?? null,
                'gender' => $civilServant->gender ?? null,
                'dob' => $civilServant->dob ?? null,
                'lga' => $civilServant->lga ?? null,
                'state' => $civilServant->state ?? null,
                'mda' => $civilServant->mda ?? null,
                'is_unique' => true
            ]
        ], 200);
    }

    /**
     * Get beneficiaries list (filtered by role)
     */
    public function getBeneficiaries(Request $request)
    {
        $staff = $request->user();
        $isSuperAdmin = strtolower($staff->role->name ?? '') === 'super admin';

        Log::info('📝 Fetching Beneficiaries', [
            'staff_id' => $staff->id,
            'is_super_admin' => $isSuperAdmin,
            'status_filter' => $request->status
        ]);

        // Base query with relationships
        $query = Beneficiary::with(['facility', 'program', 'creator', 'spouse', 'children']);

        // If not super admin, only show their own records
        if (!$isSuperAdmin) {
            $query->where('created_by', $staff->id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Get paginated results
        $beneficiaries = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $beneficiaries
        ], 200);
    }

    /**
     * Get single beneficiary details
     */
    public function getBeneficiary(Request $request, $id)
    {
        $staff = $request->user();
        $isSuperAdmin = strtolower($staff->role->name ?? '') === 'super admin';

        Log::info('👤 Fetching Beneficiary Details', [
            'staff_id' => $staff->id,
            'beneficiary_id' => $id
        ]);

        $beneficiary = Beneficiary::with(['facility', 'program', 'spouse.facility', 'children.facility', 'creator'])
            ->find($id);

        if (!$beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        }

        // Check access permissions - more lenient like the beneficiaries list
        // Allow access if beneficiary has NIN (same logic as beneficiaries-list)
        if (!$isSuperAdmin && $beneficiary->created_by !== $staff->id) {
            // Only allow access if beneficiary has NIN (matching beneficiaries-list logic)
            if (empty($beneficiary->nin)) {
                Log::warning('⚠️ Unauthorized Access Attempt - No NIN', [
                    'staff_id' => $staff->id,
                    'beneficiary_id' => $id,
                    'created_by' => $beneficiary->created_by,
                    'nin' => $beneficiary->nin
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied - beneficiary has no NIN'
                ], 403);
            }
            
            Log::info('✅ Access Granted - Has NIN', [
                'staff_id' => $staff->id,
                'beneficiary_id' => $id,
                'created_by' => $beneficiary->created_by,
                'nin' => $beneficiary->nin
            ]);
        }

        return response()->json([
            'success' => true,
            'beneficiary' => $beneficiary,
            'spouse' => $beneficiary->spouse,
            'children' => $beneficiary->children,
        ], 200);
    }

    /**
     * Create new beneficiary enrollment
     */
    public function createBeneficiary(Request $request)
    {
        $staff = $request->user();

        Log::info('➕ Creating New Beneficiary', [
            'staff_id' => $staff->id,
            'nin' => $request->nin
        ]);

        $validator = Validator::make($request->all(), [
            'program_id' => 'required|exists:programs,id',
            'facility_id' => 'required|exists:facilities,id',
            'nin' => 'required|string|size:11|unique:beneficiaries,nin',
            'fullname' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'phone_no' => 'required|string|max:20',
            'id_type' => 'required|string',
            'id_no' => 'required|string',
            'category' => 'required|string',
            'status' => 'nullable|in:In Progress,active,pending,rejected,inactive',
            // Spouse data
            'has_spouse' => 'nullable|boolean',
            'spouse_name' => 'nullable|required_if:has_spouse,true|string',
            'spouse_gender' => 'nullable|required_if:has_spouse,true|in:Male,Female',
            'spouse_dob' => 'nullable|required_if:has_spouse,true|date',
            'spouse_phone' => 'nullable|string',
            // Children data
            'has_children' => 'nullable|boolean',
            'children' => 'nullable|array',
            'children.*.name' => 'required|string',
            'children.*.gender' => 'required|in:Male,Female',
            'children.*.dob' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            Log::error('❌ Validation Failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Log::info('✅ Validation Passed');

        DB::beginTransaction();

        try {
            // Generate BOSCHMA ID
            $program = Program::find($request->program_id);
            $format = $program->format ?? 'BOH';
            $latestBeneficiary = Beneficiary::where('program_id', $request->program_id)
                ->where('boschma_no', 'LIKE', $format . '%')
                ->orderBy('boschma_no', 'desc')
                ->first();

            if ($latestBeneficiary) {
                $lastNumber = intval(substr($latestBeneficiary->boschma_no, strlen($format)));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            $boschmaNo = $format . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

            // Create beneficiary
            $beneficiary = Beneficiary::create([
                'program_id' => $request->program_id,
                'facility_id' => $request->facility_id,
                'boschma_no' => $boschmaNo,
                'nin' => $request->nin,
                'fullname' => $request->fullname,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_no' => $request->phone_no,
                'email' => $request->email,
                'contact_address' => $request->contact_address ?? $request->address,
                'lga' => $request->lga,
                'ward' => $request->ward,
                'state' => $request->state,
                'nationality' => $request->nationality,
                'place_of_birth' => $request->place_of_birth,
                'marital_status' => $request->marital_status,
                'ethnicity' => $request->ethnicity,
                'religion' => $request->religion,
                'occupation' => $request->occupation,
                'dp_no' => $request->dp_no,
                'id_type' => $request->id_type,
                'id_no' => $request->id_no,
                'date_of_first_appointment' => $request->date_of_first_appointment,
                'date_of_employment' => $request->date_of_employment,
                'date_of_retirement' => $request->date_of_retirement,
                'category' => $request->category,
                'status' => $request->status ?? 'In Progress',
                'created_by' => $staff->id,
                'updated_by' => $staff->id,
                'submitted_by' => $request->status !== 'In Progress' ? $staff->id : null,
            ]);

            // Create spouse if provided
            if ($request->has_spouse && $request->spouse_name) {
                Spouse::create([
                    'beneficiary_id' => $beneficiary->id,
                    'facility_id' => $request->facility_id,
                    'boschma_no' => $boschmaNo . 'A',
                    'nin' => $request->spouse_nin,
                    'name' => $request->spouse_name,
                    'gender' => $request->spouse_gender,
                    'dob' => $request->spouse_dob,
                    'phone' => $request->spouse_phone,
                    'email' => $request->spouse_email,
                ]);
            }

            // Create children if provided
            if ($request->has_children && $request->children) {
                $suffixes = ['B', 'C', 'D', 'E'];
                foreach ($request->children as $index => $childData) {
                    Child::create([
                        'beneficiary_id' => $beneficiary->id,
                        'facility_id' => $request->facility_id,
                        'boschma_no' => $boschmaNo . $suffixes[$index],
                        'nin' => $childData['nin'] ?? null,
                        'name' => $childData['name'],
                        'gender' => $childData['gender'],
                        'dob' => $childData['dob'] ?? null,
                        'birth_certificate_no' => $childData['birth_certificate_no'] ?? null,
                    ]);
                }
            }

            DB::commit();

            Log::info('✅ Beneficiary Created Successfully', [
                'staff_id' => $staff->id,
                'beneficiary_id' => $beneficiary->id,
                'boschma_no' => $boschmaNo
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Beneficiary created successfully',
                'data' => $beneficiary->load(['spouse', 'children'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Error Creating Beneficiary', [
                'staff_id' => $staff->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating beneficiary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing beneficiary
     */
    public function updateBeneficiary(Request $request, $id)
    {
        $staff = $request->user();
        $isSuperAdmin = strtolower($staff->role->name ?? '') === 'super admin';

        Log::info('✏️ Updating Beneficiary', [
            'staff_id' => $staff->id,
            'beneficiary_id' => $id
        ]);

        $beneficiary = Beneficiary::find($id);

        if (!$beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        }

        // Check access permissions
        if (!$isSuperAdmin && $beneficiary->created_by !== $staff->id) {
            Log::warning('⚠️ Unauthorized Update Attempt', [
                'staff_id' => $staff->id,
                'beneficiary_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'fullname' => 'sometimes|required|string|max:255',
            'phone_no' => 'sometimes|required|string|max:20',
            'status' => 'sometimes|in:In Progress,active,pending,rejected,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $beneficiary->update(array_merge(
                $request->only([
                    'fullname', 'phone_no', 'email', 'address', 'lga', 'ward',
                    'occupation', 'id_type', 'id_no', 'category', 'status'
                ]),
                ['updated_by' => $staff->id]
            ));

            DB::commit();

            Log::info('✅ Beneficiary Updated Successfully', [
                'staff_id' => $staff->id,
                'beneficiary_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Beneficiary updated successfully',
                'data' => $beneficiary->load(['spouse', 'children'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Error Updating Beneficiary', [
                'staff_id' => $staff->id,
                'beneficiary_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating beneficiary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete beneficiary
     */
    public function deleteBeneficiary(Request $request, $id)
    {
        $staff = $request->user();
        $isSuperAdmin = strtolower($staff->role->name ?? '') === 'super admin';

        Log::info('🗑️ Deleting Beneficiary', [
            'staff_id' => $staff->id,
            'beneficiary_id' => $id
        ]);

        $beneficiary = Beneficiary::find($id);

        if (!$beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        }

        // Check access permissions
        if (!$isSuperAdmin && $beneficiary->created_by !== $staff->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        try {
            $beneficiary->delete();

            Log::info('✅ Beneficiary Deleted Successfully', [
                'staff_id' => $staff->id,
                'beneficiary_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Beneficiary deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error Deleting Beneficiary', [
                'staff_id' => $staff->id,
                'beneficiary_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting beneficiary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(Request $request)
    {
        $staff = $request->user();
        $isSuperAdmin = strtolower($staff->role->name ?? '') === 'super admin';

        Log::info('📊 Fetching Dashboard Stats', [
            'staff_id' => $staff->id,
            'is_super_admin' => $isSuperAdmin
        ]);

        $query = Beneficiary::query();

        // If not super admin, only count their own records
        if (!$isSuperAdmin) {
            $query->where('created_by', $staff->id);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'in_progress' => (clone $query)->where('status', 'In Progress')->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }

    /**
     * Upload beneficiary enrollment from mobile with files
     * Status will be set to "pending" for approval
     */
    public function uploadBeneficiary(Request $request)
    {
        $staff = $request->user();

        Log::info('📤 Uploading Beneficiary from Mobile', [
            'staff_id' => $staff->id,
            'nin' => $request->nin,
            'created_at_received' => $request->created_at ?? 'NOT_SENT'
        ]);

        $validator = Validator::make($request->all(), [
            'program_id' => 'required|exists:programs,id',
            'facility_id' => 'required|exists:facilities,id',
            'alt_facility_id' => 'nullable|exists:facilities,id',
            'nin' => 'required|string|size:11',
            'dp_no' => 'required|string',
            'fullname' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'phone_no' => 'required|string|max:20',
            'created_at' => 'nullable|date', // Allow mobile app to set original creation date
            'email' => 'nullable|email',
            'contact_address' => 'nullable|string',
            'lga' => 'nullable|string',
            'state' => 'nullable|string',
            'nationality' => 'nullable|string',
            'place_of_birth' => 'nullable|string',
            'place_of_work' => 'nullable|string',
            'date_of_employment' => 'nullable|date',
            'date_of_retirement' => 'nullable|date',
            'occupation' => 'nullable|string',
            'category' => 'required|string',
            'id_type' => 'required|string',
            'id_no' => 'required|string',
            'marital_status' => 'nullable|string',
            'religion' => 'nullable|string',
            'beneficiary_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
            // Spouse
            'has_spouse' => 'nullable|boolean',
            'spouse_name' => 'nullable|string',
            'spouse_gender' => 'nullable|in:Male,Female',
            'spouse_dob' => 'nullable|date',
            'spouse_phone' => 'nullable|string',
            'spouse_email' => 'nullable|email',
            'spouse_nin' => 'nullable|string|size:11',
            'spouse_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
            'use_alt_facility_spouse' => 'nullable|boolean',
            // Children
            'has_children' => 'nullable|boolean',
            'children' => 'nullable|string', // JSON string
        ]);

        if ($validator->fails()) {
            Log::warning('❌ Upload Validation Failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify NIN uniqueness before processing
        $existingBeneficiary = Beneficiary::where('nin', $request->nin)->first();
        $existingSpouse = Spouse::where('nin', $request->nin)->first();
        $existingChild = Child::where('nin', $request->nin)->first();
        
        if ($existingBeneficiary) {
            Log::warning('⚠️ Upload Failed - NIN Already Exists as Beneficiary', [
                'nin' => $request->nin,
                'existing_boschma_no' => $existingBeneficiary->boschma_no
            ]);

            return response()->json([
                'success' => false,
                'error_code' => 'NIN_DUPLICATE_BENEFICIARY',
                'message' => 'This NIN is already enrolled as a beneficiary (BOSCHMA ID: ' . $existingBeneficiary->boschma_no . ')',
                'error_details' => [
                    'field' => 'nin',
                    'value' => $request->nin,
                    'existing_boschma_no' => $existingBeneficiary->boschma_no,
                ]
            ], 409);
        }
        
        if ($existingSpouse) {
            $beneficiary = Beneficiary::find($existingSpouse->beneficiary_id);
            Log::warning('⚠️ Upload Failed - NIN Already Exists as Spouse', [
                'nin' => $request->nin,
                'under_boschma_no' => $beneficiary->boschma_no ?? 'N/A'
            ]);

            return response()->json([
                'success' => false,
                'error_code' => 'NIN_DUPLICATE_SPOUSE',
                'message' => 'This NIN is already registered as a spouse (under BOSCHMA ID: ' . ($beneficiary->boschma_no ?? 'N/A') . ')',
                'error_details' => [
                    'field' => 'nin',
                    'value' => $request->nin,
                    'under_boschma_no' => $beneficiary->boschma_no ?? 'N/A',
                ]
            ], 409);
        }
        
        if ($existingChild) {
            $beneficiary = Beneficiary::find($existingChild->beneficiary_id);
            Log::warning('⚠️ Upload Failed - NIN Already Exists as Child', [
                'nin' => $request->nin,
                'under_boschma_no' => $beneficiary->boschma_no ?? 'N/A'
            ]);

            return response()->json([
                'success' => false,
                'error_code' => 'NIN_DUPLICATE_CHILD',
                'message' => 'This NIN is already registered as a child (under BOSCHMA ID: ' . ($beneficiary->boschma_no ?? 'N/A') . ')',
                'error_details' => [
                    'field' => 'nin',
                    'value' => $request->nin,
                    'under_boschma_no' => $beneficiary->boschma_no ?? 'N/A',
                ]
            ], 409);
        }

        // Verify spouse NIN if provided
        if ($request->has_spouse && $request->spouse_nin) {
            $existingSpouseNin = Beneficiary::where('nin', $request->spouse_nin)->first();
            if (!$existingSpouseNin) {
                $existingSpouseNin = Spouse::where('nin', $request->spouse_nin)->first();
            }
            if (!$existingSpouseNin) {
                $existingSpouseNin = Child::where('nin', $request->spouse_nin)->first();
            }
            
            if ($existingSpouseNin) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'SPOUSE_NIN_DUPLICATE',
                    'message' => 'Spouse NIN is already registered in the system',
                    'error_details' => [
                        'field' => 'spouse_nin',
                        'value' => $request->spouse_nin,
                    ]
                ], 409);
            }
        }

        DB::beginTransaction();

        try {
            // Generate BOSCHMA number
            $program = Program::findOrFail($request->program_id);
            $programFormat = $program->format ?? 'BOSCHMA';
            
            $latestSequence = Beneficiary::where('program_id', $request->program_id)
                ->whereNotNull('sequence_number')
                ->max('sequence_number');
            
            $sequenceNumber = $latestSequence ? $latestSequence + 1 : 1;
            $boschmaNo = $programFormat . str_pad($sequenceNumber, 6, '0', STR_PAD_LEFT);

            // Handle beneficiary photo
            $photoPath = null;
            if ($request->hasFile('beneficiary_photo')) {
                $photoPath = $request->file('beneficiary_photo')->store('beneficiary_photos', 'public');
                Log::info('✅ Beneficiary photo uploaded: ' . $photoPath);
            }

            // Calculate number of children
            $numberOfChildren = 0;
            if ($request->has_children && $request->children) {
                $childrenData = json_decode($request->children, true);
                if (is_array($childrenData)) {
                    $numberOfChildren = count(array_filter($childrenData, function($child) {
                        return !empty($child['name']);
                    }));
                }
            }

            // Create beneficiary with status="pending"
            $beneficiaryData = [
                'program_id' => $request->program_id,
                'facility_id' => $request->facility_id,
                'alt_facility_id' => $request->alt_facility_id,
                'boschma_no' => $boschmaNo,
                'sequence_number' => $sequenceNumber,
                'nin' => $request->nin,
                'dp_no' => $request->dp_no,
                'fullname' => $request->fullname,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone_no' => $request->phone_no,
                'email' => $request->email,
                'contact_address' => $request->contact_address,
                'lga' => $request->lga,
                'state' => $request->state,
                'nationality' => 'Nigerian',
                'place_of_birth' => $request->place_of_birth,
                'place_of_work' => $request->place_of_work,
                'date_of_employment' => $request->date_of_employment,
                'date_of_retirement' => $request->date_of_retirement,
                'occupation' => $request->occupation,
                'category' => $request->category,
                'id_type' => $request->id_type,
                'id_no' => $request->id_no,
                'marital_status' => $request->marital_status,
                'ethnicity' => $request->ethnicity,
                'religion' => $request->religion,
                'photo' => $photoPath,
                'status' => 'pending', // Always pending from mobile
                'has_spouse' => $request->has_spouse ? 1 : 0,
                'number_of_children' => $numberOfChildren,
                'created_by' => $staff->id,
                'updated_by' => $staff->id,
                'submitted_by' => $staff->id,
            ];
            
            // Use original creation date if provided by mobile app
            if ($request->has('created_at')) {
                // Temporarily disable timestamps to preserve original creation date
                $beneficiary = new Beneficiary();
                $beneficiary->timestamps = false; // Disable auto-timestamps
                
                // Set all fields including the original creation date
                foreach ($beneficiaryData as $key => $value) {
                    $beneficiary->$key = $value;
                }
                $beneficiary->created_at = $request->created_at;
                $beneficiary->updated_at = now(); // Set current time for updated_at
                $beneficiary->save();
                
                Log::info('📅 [API] Using original creation date from mobile app', [
                    'original_creation_date' => $request->created_at,
                    'beneficiary_name' => $request->fullname,
                    'final_created_at' => $beneficiary->created_at
                ]);
            } else {
                $beneficiary = Beneficiary::create($beneficiaryData);
                Log::info('📅 [API] Using current server timestamp (no original date provided)', [
                    'beneficiary_name' => $request->fullname,
                    'final_created_at' => $beneficiary->created_at
                ]);
            }
            
            Log::info('✅ [API] Beneficiary created successfully', [
                'beneficiary_id' => $beneficiary->id,
                'boschma_no' => $beneficiary->boschma_no,
                'final_created_at' => $beneficiary->created_at,
                'beneficiary_name' => $beneficiary->fullname
            ]);

            // Handle spouse
            if ($request->has_spouse && $request->spouse_name) {
                $spouseFacilityId = $request->use_alt_facility_spouse ? $request->alt_facility_id : $request->facility_id;
                $spousePhotoPath = null;
                
                if ($request->hasFile('spouse_photo')) {
                    $spousePhotoPath = $request->file('spouse_photo')->store('spouse_photos', 'public');
                    Log::info('✅ Spouse photo uploaded: ' . $spousePhotoPath);
                }

                Spouse::create([
                    'beneficiary_id' => $beneficiary->id,
                    'facility_id' => $spouseFacilityId,
                    'boschma_no' => $boschmaNo . 'A',
                    'nin' => $request->spouse_nin,
                    'name' => $request->spouse_name,
                    'gender' => $request->spouse_gender,
                    'dob' => $request->spouse_dob,
                    'phone' => $request->spouse_phone,
                    'email' => $request->spouse_email,
                    'photo' => $spousePhotoPath,
                ]);
            }

            // Handle children
            if ($request->has_children && $request->children) {
                $childrenData = json_decode($request->children, true);
                $suffixes = ['B', 'C', 'D', 'E'];
                
                Log::info('👶 Processing children', [
                    'count' => count($childrenData ?? []),
                    'data' => $childrenData
                ]);

                foreach ($childrenData as $index => $childData) {
                    if ($index > 3) break; // Max 4 children

                    $childFacilityId = isset($childData['use_alt_facility']) && $childData['use_alt_facility'] 
                        ? $request->alt_facility_id 
                        : $request->facility_id;

                    // Handle child photo
                    $childPhotoPath = null;
                    if ($request->hasFile("child_photo_{$index}")) {
                        $childPhotoPath = $request->file("child_photo_{$index}")->store('children_photos', 'public');
                        Log::info("✅ Child {$index} photo uploaded: " . $childPhotoPath);
                    } else {
                        Log::warning("⚠️ Child {$index} photo not found in request");
                    }

                    // Handle birth certificate
                    $birthCertPath = null;
                    if ($request->hasFile("child_birth_cert_{$index}")) {
                        $birthCertPath = $request->file("child_birth_cert_{$index}")->store('birth_certificates', 'public');
                        Log::info("✅ Child {$index} birth cert uploaded: " . $birthCertPath);
                    } else {
                        Log::warning("⚠️ Child {$index} birth cert not found in request");
                    }

                    $childRecord = Child::create([
                        'beneficiary_id' => $beneficiary->id,
                        'facility_id' => $childFacilityId,
                        'boschma_no' => $boschmaNo . $suffixes[$index],
                        'nin' => $childData['nin'] ?? null,
                        'name' => $childData['name'],
                        'gender' => $childData['gender'],
                        'dob' => $childData['dob'] ?? null,
                        'birth_certificate_no' => $childData['birth_certificate_no'] ?? null,
                        'birth_certificate_file' => $birthCertPath,
                        'photo' => $childPhotoPath,
                    ]);
                    
                    Log::info("✅ Child {$index} created", [
                        'id' => $childRecord->id,
                        'name' => $childRecord->name,
                        'dob' => $childRecord->dob,
                        'photo' => $childRecord->photo,
                        'birth_cert' => $childRecord->birth_certificate_file
                    ]);
                }
            }

            DB::commit();

            Log::info('✅ Beneficiary Uploaded Successfully', [
                'staff_id' => $staff->id,
                'beneficiary_id' => $beneficiary->id,
                'boschma_no' => $boschmaNo,
                'status' => 'pending'
            ]);

            // Load complete data for local database storage
            $beneficiary->refresh();
            $beneficiary->load(['spouse', 'children']);

            Log::info('📤 [API] Sending response back to mobile app', [
                'beneficiary_id' => $beneficiary->id,
                'created_at_in_response' => $beneficiary->created_at,
                'beneficiary_name' => $beneficiary->fullname
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Beneficiary uploaded successfully and awaiting approval',
                'data' => [
                    'beneficiary_id' => $beneficiary->id,
                    'boschma_no' => $boschmaNo,
                    'status' => 'pending',
                    'beneficiary' => $beneficiary,
                    'spouse' => $beneficiary->spouse,
                    'children' => $beneficiary->children,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Error Uploading Beneficiary', [
                'staff_id' => $staff->id,
                'nin' => $request->nin,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error_code' => 'SERVER_ERROR',
                'message' => 'Error uploading beneficiary: ' . $e->getMessage(),
                'error_details' => [
                    'timestamp' => now()->toIso8601String(),
                    'nin' => $request->nin,
                    'fullname' => $request->fullname,
                    'error_type' => get_class($e),
                    'error_message' => $e->getMessage(),
                ]
            ], 500);
        }
    }

    /**
     * Search beneficiaries, spouses, and children by BOSCHMA ID or NIN
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Query must be at least 3 characters',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $query = $request->input('query');
            $results = [];

            Log::info('🔍 Search Request', [
                'staff_id' => $request->user()->id,
                'query' => $query,
            ]);

            // Search Beneficiaries
            $beneficiaries = Beneficiary::where('boschma_no', 'LIKE', "%{$query}%")
                ->orWhere('nin', 'LIKE', "%{$query}%")
                ->orWhere('fullname', 'LIKE', "%{$query}%")
                ->with('facility')
                ->limit(50)
                ->get();

            foreach ($beneficiaries as $benef) {
                $results[] = [
                    'id' => $benef->id,
                    'boschma_no' => $benef->boschma_no,
                    'nin' => $benef->nin,
                    'name' => $benef->fullname,
                    'fullname' => $benef->fullname,
                    'gender' => $benef->gender,
                    'phone_no' => $benef->phone_no,
                    'email' => $benef->email,
                    'facility_id' => $benef->facility_id,
                    'facility_name' => optional($benef->facility)->name ?? 'N/A',
                    'status' => $benef->status,
                    'photo' => $benef->photo,
                    'record_level' => 'Principal',
                    'record_type' => 'beneficiary',
                    'parent_name' => null,
                ];
            }

            // Search Spouses
            $spouses = Spouse::where('boschma_no', 'LIKE', "%{$query}%")
                ->orWhere('nin', 'LIKE', "%{$query}%")
                ->orWhere('name', 'LIKE', "%{$query}%")
                ->with(['beneficiary', 'facility'])
                ->limit(50)
                ->get();

            foreach ($spouses as $spouse) {
                $results[] = [
                    'id' => $spouse->id,
                    'boschma_no' => $spouse->boschma_no,
                    'nin' => $spouse->nin,
                    'name' => $spouse->name,
                    'fullname' => $spouse->name,
                    'gender' => $spouse->gender,
                    'phone' => $spouse->phone,
                    'phone_no' => $spouse->phone,
                    'email' => $spouse->email,
                    'facility_id' => $spouse->facility_id,
                    'facility_name' => optional($spouse->facility)->name ?? 'N/A',
                    'status' => 'Active',
                    'photo' => $spouse->photo,
                    'record_level' => 'Spouse of',
                    'record_type' => 'spouse',
                    'parent_name' => optional($spouse->beneficiary)->fullname ?? 'Unknown',
                ];
            }

            // Search Children
            $children = Child::where('boschma_no', 'LIKE', "%{$query}%")
                ->orWhere('nin', 'LIKE', "%{$query}%")
                ->orWhere('name', 'LIKE', "%{$query}%")
                ->with(['beneficiary', 'facility'])
                ->limit(50)
                ->get();

            foreach ($children as $child) {
                $results[] = [
                    'id' => $child->id,
                    'boschma_no' => $child->boschma_no,
                    'nin' => $child->nin,
                    'name' => $child->name,
                    'fullname' => $child->name,
                    'gender' => $child->gender,
                    'phone' => 'N/A',
                    'phone_no' => 'N/A',
                    'email' => null,
                    'facility_id' => $child->facility_id,
                    'facility_name' => optional($child->facility)->name ?? 'N/A',
                    'status' => 'Active',
                    'photo' => $child->photo,
                    'record_level' => 'Child of',
                    'record_type' => 'child',
                    'parent_name' => optional($child->beneficiary)->fullname ?? 'Unknown',
                ];
            }

            Log::info('✅ Search Results', [
                'staff_id' => $request->user()->id,
                'query' => $query,
                'results_count' => count($results),
            ]);

            return response()->json([
                'success' => true,
                'query' => $query,
                'count' => count($results),
                'results' => $results,
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Search Error', [
                'staff_id' => $request->user()->id,
                'query' => $request->input('query'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get beneficiary with full data (spouse and children) for editing
     */
    public function getBeneficiaryFull(Request $request, $id)
    {
        $staff = $request->user();
        $isSuperAdmin = strtolower($staff->role->name ?? '') === 'super admin';

        Log::info('📝 Fetching Full Beneficiary Data for Edit', [
            'staff_id' => $staff->id,
            'beneficiary_id' => $id
        ]);

        $beneficiary = Beneficiary::with(['facility', 'altFacility', 'program', 'spouse', 'children'])
            ->find($id);

        if (!$beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        }

        // NOTE: Access control disabled - any user can edit any record
        // Original access control kept for reference (commented out):
        /*
        if (!$isSuperAdmin && $beneficiary->created_by !== $staff->id) {
            Log::warning('⚠️ Unauthorized Edit Access Attempt', [
                'staff_id' => $staff->id,
                'beneficiary_id' => $id,
                'created_by' => $beneficiary->created_by
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Access denied. You can only edit beneficiaries you created.'
            ], 403);
        }
        */

        Log::info('✅ Edit Access Granted (Open Access Mode)', [
            'staff_id' => $staff->id,
            'beneficiary_id' => $id,
            'created_by' => $beneficiary->created_by
        ]);

        // Prepare complete data with relationships
        $beneficiaryData = $beneficiary->toArray();
        
        // Add facility details
        if ($beneficiary->facility) {
            $beneficiaryData['facility'] = [
                'id' => $beneficiary->facility->id,
                'name' => $beneficiary->facility->name,
                'ward' => $beneficiary->facility->ward,
            ];
        }
        
        if ($beneficiary->altFacility) {
            $beneficiaryData['alt_facility'] = [
                'id' => $beneficiary->altFacility->id,
                'name' => $beneficiary->altFacility->name,
                'ward' => $beneficiary->altFacility->ward,
            ];
        }

        // Add spouse with facility details
        if ($beneficiary->spouse) {
            $spouseData = $beneficiary->spouse->toArray();
            $spouseData['use_alt_facility'] = $beneficiary->spouse->facility_id === $beneficiary->alt_facility_id;
            $beneficiaryData['spouse'] = $spouseData;
        }

        // Add children with facility details
        if ($beneficiary->children->isNotEmpty()) {
            $childrenData = [];
            foreach ($beneficiary->children as $index => $child) {
                $childData = $child->toArray();
                $childData['use_alt_facility'] = $child->facility_id === $beneficiary->alt_facility_id;
                $childrenData[] = $childData;
                
                // Log child data for verification
                Log::info("📝 Child {$index} data", [
                    'name' => $child->name,
                    'dob' => $child->dob ?? 'none',
                    'photo' => $child->photo ?? 'none',
                    'birth_certificate_file' => $child->birth_certificate_file ?? 'none'
                ]);
            }
            $beneficiaryData['children'] = $childrenData;
        }

        Log::info('✅ Full Beneficiary Data Retrieved', [
            'beneficiary_id' => $id,
            'boschma_no' => $beneficiary->boschma_no,
            'beneficiary_photo' => $beneficiary->photo ?? 'none',
            'has_spouse' => $beneficiary->spouse ? 'yes' : 'no',
            'spouse_photo' => $beneficiary->spouse->photo ?? 'none',
            'children_count' => $beneficiary->children->count()
        ]);

        return response()->json([
            'success' => true,
            'beneficiary' => $beneficiaryData,
        ], 200);
    }

    /**
     * Update beneficiary with CRUD operations on spouse and children
     */
    public function updateBeneficiaryFull(Request $request, $id)
    {
        $staff = $request->user();
        $isSuperAdmin = strtolower($staff->role->name ?? '') === 'super admin';

        Log::info('🔄 ============ BENEFICIARY UPDATE START ============', [
            'staff_id' => $staff->id,
            'staff_name' => $staff->name,
            'beneficiary_id' => $id,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Log request data (without files)
        Log::info('📥 Request Data Received', [
            'has_spouse' => $request->has_spouse,
            'has_children' => $request->has_children,
            'facility_id' => $request->facility_id,
            'alt_facility_id' => $request->alt_facility_id,
            'fullname' => $request->fullname,
            'gender' => $request->gender,
            'phone_no' => $request->phone_no,
            'marital_status' => $request->marital_status,
            'ethnicity' => $request->ethnicity,
            'religion' => $request->religion,
            'category' => $request->category,
        ]);

        // Log file uploads received
        $filesReceived = [];
        if ($request->hasFile('beneficiary_photo')) $filesReceived['beneficiary_photo'] = true;
        if ($request->hasFile('spouse_photo')) $filesReceived['spouse_photo'] = true;
        for ($i = 0; $i < 4; $i++) {
            if ($request->hasFile("child_photo_$i")) $filesReceived["child_photo_$i"] = true;
            if ($request->hasFile("child_birth_cert_$i")) $filesReceived["child_birth_cert_$i"] = true;
        }
        Log::info('📁 Files Received', count($filesReceived) > 0 ? $filesReceived : ['none']);

        // Find beneficiary
        $beneficiary = Beneficiary::find($id);

        if (!$beneficiary) {
            Log::error('❌ Beneficiary Not Found', ['beneficiary_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        }

        Log::info('📋 Existing Beneficiary Data', [
            'id' => $beneficiary->id,
            'boschma_no' => $beneficiary->boschma_no,
            'fullname' => $beneficiary->fullname,
            'created_by' => $beneficiary->created_by,
            'has_spouse' => $beneficiary->spouse ? 'yes' : 'no',
            'children_count' => $beneficiary->children->count()
        ]);

        // NOTE: Access control disabled - any user can update any record
        // Original access control kept for reference (commented out):
        /*
        if (!$isSuperAdmin && $beneficiary->created_by !== $staff->id) {
            Log::warning('⚠️ Unauthorized Update Attempt', [
                'staff_id' => $staff->id,
                'beneficiary_id' => $id,
                'created_by' => $beneficiary->created_by,
                'is_super_admin' => $isSuperAdmin
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Access denied. You can only update beneficiaries you created.'
            ], 403);
        }
        */

        Log::info('✅ Update Access Granted (Open Access Mode)', [
            'staff_id' => $staff->id,
            'beneficiary_id' => $id,
            'created_by' => $beneficiary->created_by
        ]);

        $validator = Validator::make($request->all(), [
            'program_id' => 'required|exists:programs,id',
            'facility_id' => 'required|exists:facilities,id',
            'alt_facility_id' => 'nullable|exists:facilities,id',
            'fullname' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'phone_no' => 'required|string|max:20',
            // Spouse
            'has_spouse' => 'nullable|boolean',
            'spouse_id' => 'nullable|integer', // For tracking UPDATE/DELETE
            // Children
            'has_children' => 'nullable|boolean',
            'children' => 'nullable|string', // JSON with child_id for tracking
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Log::info('✅ Validation Passed');

        DB::beginTransaction();

        try {
            $existingBoschmaNo = $beneficiary->boschma_no; // PRESERVE THIS!

            Log::info('📝 Preserving BOSCHMA ID', [
                'beneficiary_id' => $id,
                'boschma_no' => $existingBoschmaNo
            ]);

            // Calculate number of children
            $numberOfChildren = 0;
            if ($request->has_children && $request->children) {
                $childrenData = json_decode($request->children, true);
                if (is_array($childrenData)) {
                    $numberOfChildren = count(array_filter($childrenData, function($child) {
                        return !empty($child['name']);
                    }));
                }
            }

            // Update beneficiary - PRESERVE boschma_no and sequence_number
            $beneficiary->update([
                'program_id' => $request->program_id,
                'facility_id' => $request->facility_id,
                'alt_facility_id' => $request->alt_facility_id,
                'fullname' => $request->fullname,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'place_of_birth' => $request->place_of_birth,
                'phone_no' => $request->phone_no,
                'email' => $request->email,
                'contact_address' => $request->contact_address,
                'lga' => $request->lga,
                'ward' => $request->ward,
                'state' => $request->state,
                'nationality' => $request->nationality,
                'marital_status' => $request->marital_status,
                'ethnicity' => $request->ethnicity,
                'religion' => $request->religion,
                'occupation' => $request->occupation,
                'category' => $request->category,
                'dp_no' => $request->dp_no,
                'id_type' => $request->id_type,
                'id_no' => $request->id_no,
                'nin' => $request->nin,
                'place_of_work' => $request->place_of_work,
                'date_of_employment' => $request->date_of_employment,
                'date_of_retirement' => $request->date_of_retirement,
                'has_spouse' => $request->has_spouse ? 1 : 0,
                'number_of_children' => $numberOfChildren,
                'updated_by' => $staff->id,
                'status' => 'pending',
            ]);

            Log::info('✅ Beneficiary Core Data Updated', [
                'beneficiary_id' => $id,
                'boschma_no' => $existingBoschmaNo,
                'fullname' => $beneficiary->fullname,
                'facility_id' => $beneficiary->facility_id,
                'marital_status' => $beneficiary->marital_status,
                'ethnicity' => $beneficiary->ethnicity,
                'religion' => $beneficiary->religion,
                'category' => $beneficiary->category,
            ]);

            // Handle Spouse CRUD
            Log::info('👥 Processing Spouse Data', [
                'has_spouse' => $request->has_spouse ? 'yes' : 'no',
                'spouse_id_in_request' => $request->spouse_id
            ]);
            
            if (!$request->has_spouse) {
                // DELETE spouse if exists
                if ($beneficiary->spouse) {
                    Log::info('🗑️ Deleting spouse', [
                        'spouse_id' => $beneficiary->spouse->id,
                        'boschma_no' => $beneficiary->spouse->boschma_no
                    ]);
                    $beneficiary->spouse->delete();
                }
            } else {
                $spouseFacilityId = $request->spouse_use_alt_facility ? $request->alt_facility_id : $request->facility_id;
                
                $spouseData = [
                    'facility_id' => $spouseFacilityId,
                    'name' => $request->spouse_name,
                    'gender' => $request->spouse_gender,
                    'dob' => $request->spouse_dob,
                    'phone' => $request->spouse_phone,
                    'email' => $request->spouse_email,
                    'nin' => $request->spouse_nin,
                ];

                // Handle spouse photo upload
                if ($request->hasFile('spouse_photo')) {
                    $spousePhotoPath = $request->file('spouse_photo')->store('spouse_photos', 'public');
                    $spouseData['photo'] = $spousePhotoPath;
                    Log::info('📸 Spouse Photo Uploaded', ['path' => $spousePhotoPath]);
                }

                if ($request->spouse_id) {
                    // UPDATE existing spouse
                    $spouse = Spouse::find($request->spouse_id);
                    if ($spouse && $spouse->beneficiary_id === $beneficiary->id) {
                        $spouse->update($spouseData);
                        Log::info('✅ Spouse Updated', [
                            'spouse_id' => $spouse->id,
                            'name' => $spouse->name,
                            'photo_updated' => isset($spouseData['photo']) ? 'yes' : 'no'
                        ]);
                    }
                } else {
                    // CREATE new spouse
                    $spouseData['beneficiary_id'] = $beneficiary->id;
                    $spouseData['boschma_no'] = $existingBoschmaNo . 'A';
                    $newSpouse = Spouse::create($spouseData);
                    Log::info('✅ Spouse Created', [
                        'spouse_id' => $newSpouse->id,
                        'boschma_no' => $spouseData['boschma_no'],
                        'name' => $spouseData['name']
                    ]);
                }
            }

            // Handle Children CRUD
            Log::info('👶 Processing Children Data', [
                'has_children' => $request->has_children ? 'yes' : 'no',
                'children_json_length' => $request->children ? strlen($request->children) : 0
            ]);
            
            if (!$request->has_children) {
                // DELETE all children
                if ($beneficiary->children->isNotEmpty()) {
                    Log::info('🗑️ Deleting all children', [
                        'count' => $beneficiary->children->count()
                    ]);
                    $beneficiary->children()->delete();
                }
            } else if ($request->children) {
                $childrenData = json_decode($request->children, true);
                $suffixes = ['B', 'C', 'D', 'E'];
                
                // Get existing child IDs from request
                $requestChildIds = collect($childrenData)
                    ->pluck('child_id')
                    ->filter()
                    ->toArray();

                // DELETE children not in request
                $deletedCount = $beneficiary->children()
                    ->whereNotIn('id', $requestChildIds)
                    ->delete();
                    
                if ($deletedCount > 0) {
                    Log::info('🗑️ Deleted children not in update', [
                        'deleted_count' => $deletedCount
                    ]);
                }

                // CREATE or UPDATE each child
                foreach ($childrenData as $index => $childData) {
                    if ($index > 3) break; // Max 4 children
                    if (empty($childData['name'])) continue;

                    $childFacilityId = isset($childData['use_alt_facility']) && $childData['use_alt_facility']
                        ? $request->alt_facility_id
                        : $request->facility_id;

                    $childUpdateData = [
                        'facility_id' => $childFacilityId,
                        'name' => $childData['name'],
                        'gender' => $childData['gender'],
                        'dob' => $childData['dob'] ?? null,
                        'nin' => $childData['nin'] ?? null,
                        'birth_certificate_no' => $childData['birth_certificate_no'] ?? null,
                    ];
                    
                    // Handle child photo upload
                    if ($request->hasFile("child_photo_$index")) {
                        $childPhotoPath = $request->file("child_photo_$index")->store('children_photos', 'public');
                        $childUpdateData['photo'] = $childPhotoPath;
                        Log::info("📸 Child $index Photo Uploaded", ['path' => $childPhotoPath]);
                    }
                    
                    // Handle birth certificate upload
                    if ($request->hasFile("child_birth_cert_$index")) {
                        $birthCertPath = $request->file("child_birth_cert_$index")->store('birth_certificates', 'public');
                        $childUpdateData['birth_certificate_file'] = $birthCertPath;
                        Log::info("📄 Child $index Birth Cert Uploaded", ['path' => $birthCertPath]);
                    }

                    if (isset($childData['child_id']) && $childData['child_id']) {
                        // UPDATE existing child
                        $child = Child::find($childData['child_id']);
                        if ($child && $child->beneficiary_id === $beneficiary->id) {
                            $child->update($childUpdateData);
                            Log::info('✅ Child Updated', [
                                'child_id' => $child->id,
                                'name' => $child->name,
                                'photo_updated' => isset($childUpdateData['photo']) ? 'yes' : 'no',
                                'birth_cert_updated' => isset($childUpdateData['birth_certificate_file']) ? 'yes' : 'no'
                            ]);
                        }
                    } else {
                        // CREATE new child
                        $childUpdateData['beneficiary_id'] = $beneficiary->id;
                        $childUpdateData['boschma_no'] = $existingBoschmaNo . $suffixes[$index];
                        $newChild = Child::create($childUpdateData);
                        Log::info('✅ Child Created', [
                            'child_id' => $newChild->id,
                            'boschma_no' => $childUpdateData['boschma_no'],
                            'name' => $childUpdateData['name'],
                            'has_photo' => isset($childUpdateData['photo']) ? 'yes' : 'no',
                            'has_birth_cert' => isset($childUpdateData['birth_certificate_file']) ? 'yes' : 'no'
                        ]);
                    }
                }
            }

            DB::commit();

            // Reload complete data
            $beneficiary->refresh();
            $beneficiary->load(['spouse', 'children']);

            Log::info('✅ ============ BENEFICIARY UPDATE COMPLETE ============', [
                'beneficiary_id' => $id,
                'boschma_no' => $existingBoschmaNo,
                'fullname' => $beneficiary->fullname,
                'has_spouse' => $beneficiary->spouse ? 'yes' : 'no',
                'spouse_id' => $beneficiary->spouse ? $beneficiary->spouse->id : null,
                'children_count' => $beneficiary->children->count(),
                'children_ids' => $beneficiary->children->pluck('id')->toArray(),
                'timestamp' => now()->toDateTimeString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Beneficiary updated successfully',
                'beneficiary' => $beneficiary,
                'spouse' => $beneficiary->spouse,
                'children' => $beneficiary->children,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ ============ ERROR UPDATING BENEFICIARY ============', [
                'beneficiary_id' => $id,
                'staff_id' => $staff->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating beneficiary: ' . $e->getMessage(),
            ], 500);
        }
    }
}
