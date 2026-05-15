<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\Spouse;
use App\Models\Child;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class BeneficiaryApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Beneficiary::with(['facility', 'spouse', 'children']);
            
            // Add filtering options
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('category')) {
                $query->where('category', $request->category);
            }
            
            if ($request->has('facility_id')) {
                $query->where('facility_id', $request->facility_id);
            }
            
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('boschma_no', 'like', "%{$searchTerm}%")
                      ->orWhere('fullname', 'like', "%{$searchTerm}%")
                      ->orWhere('phone', 'like', "%{$searchTerm}%");
                });
            }
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $beneficiaries = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $beneficiaries,
                'message' => 'Beneficiaries retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving beneficiaries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        Log::info("Received request data:", $request->all());
        
        // Validate request data
        $validator = Validator::make($request->all(), [
            // Beneficiary data
            'facility_id' => 'required|exists:facilities,id',
            'boschma_no' => 'required|string|unique:beneficiaries,boschma_no',
            'fullname' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'nullable|string|max:255',
            'lga' => 'nullable|string|max:255',
            'state' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'ethnicity' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'id_type' => 'required|string|max:255',
            'id_number' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'dp_no' => 'nullable|string|max:255',
            'place_of_work' => 'nullable|string|max:255',
            'date_of_employment' => 'nullable|date',
            'date_of_retirement' => 'nullable|date',
            'status' => 'nullable|in:active,inactive',
            'beneficiary_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Spouse data
            'has_spouse' => 'nullable|boolean',
            'spouse_name' => 'nullable|required_if:has_spouse,true|string|max:255',
            'spouse_gender' => 'nullable|required_if:has_spouse,true|in:Male,Female',
            'spouse_dob' => 'nullable|required_if:has_spouse,true|date',
            'spouse_phone' => 'nullable|string|max:20',
            'spouse_email' => 'nullable|email|max:255',
            'spouse_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Children data
            'has_children' => 'nullable|boolean',
            'child_name' => 'nullable|array',
            'child_name.*' => 'required_with:child_name|string|max:255',
            'child_gender' => 'nullable|array',
            'child_gender.*' => 'required_with:child_name.*|in:Male,Female',
            'child_dob' => 'nullable|array',
            'child_dob.*' => 'nullable|date',
            'child_birth_certificate_no' => 'nullable|array',
            'child_birth_certificate_no.*' => 'nullable|string|max:255',
        ]);

        Log::info("Validation errors:", $validator->errors()->toArray());
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            // Handle beneficiary photo upload
            $beneficiaryPhotoPath = null;
            if ($request->hasFile('beneficiary_photo')) {
                Log::info("Beneficiary photo file found: " . $request->file('beneficiary_photo')->getClientOriginalName());
                $beneficiaryPhotoPath = $request->file('beneficiary_photo')->store('beneficiary_photos', 'public');
            }
            
            // Create main beneficiary record
            $beneficiary = Beneficiary::create([
                'facility_id' => $request->facility_id,
                'boschma_no' => $request->boschma_no,
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
                'contact_address' => $request->address,
                'phone_no' => $request->phone,
                'email' => $request->email,
                'occupation' => $request->occupation,
                'dp_no' => $request->dp_no,
                'id_type' => $request->id_type,
                'id_no' => $request->id_number,
                'nin' => $request->nin,
                'place_of_work' => $request->place_of_work,
                'date_of_employment' => $request->date_of_employment,
                'date_of_retirement' => $request->date_of_retirement,
                'category' => $request->category,
                'status' => $request->status ?? 'active',
                'has_spouse' => $request->has_spouse ? 1 : 0,
                'number_of_children' => $request->has_children && is_array($request->child_name) ? count(array_filter($request->child_name)) : 0,
                'photo' => $beneficiaryPhotoPath
            ]);
            
            // Handle spouse data
            if ($request->has_spouse && $request->spouse_name) {
                $spousePhotoPath = null;
                if ($request->hasFile('spouse_photo')) {
                    $spousePhotoPath = $request->file('spouse_photo')->store('spouse_photos', 'public');
                }
                
                Spouse::create([
                    'beneficiary_id' => $beneficiary->id,
                    'boschma_no' => $request->boschma_no . 'A',
                    'nin' => $request->spouse_nin,
                    'name' => $request->spouse_name,
                    'gender' => $request->spouse_gender,
                    'dob' => $request->spouse_dob,
                    'phone' => $request->spouse_phone,
                    'email' => $request->spouse_email,
                    'photo' => $spousePhotoPath
                ]);
            }
            
            // Handle children data
            if ($request->has_children && $request->child_name) {
                $suffixes = ['B', 'C', 'D', 'E'];
                
                foreach ($request->child_name as $index => $childName) {
                    if (!empty($childName)) {
                        // Handle child photo upload
                        $childPhotoPath = null;
                        if ($request->hasFile("child_photo_{$index}")) {
                            $childPhotoPath = $request->file("child_photo_{$index}")->store('children_photos', 'public');
                        }
                        
                        // Handle birth certificate upload
                        $birthCertPath = null;
                        if ($request->hasFile("child_birth_certificate_file_{$index}")) {
                            $birthCertPath = $request->file("child_birth_certificate_file_{$index}")->store('birth_certificates', 'public');
                        }
                        
                        Child::create([
                            'beneficiary_id' => $beneficiary->id,
                            'boschma_no' => $request->boschma_no . $suffixes[$index],
                            'nin' => $request->child_nin[$index] ?? null,
                            'name' => $childName,
                            'gender' => $request->child_gender[$index] ?? 'Male',
                            'dob' => $request->child_dob[$index] ?? null,
                            'birth_certificate_no' => $request->child_birth_certificate_no[$index] ?? null,
                            'photo' => $childPhotoPath,
                            'birth_certificate_file' => $birthCertPath,
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            // Return beneficiary with relationships
            $beneficiary->load(['facility', 'spouse', 'children']);
            
            return response()->json([
                'success' => true,
                'data' => $beneficiary,
                'message' => 'Beneficiary created successfully'
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating beneficiary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $beneficiary = Beneficiary::with(['facility', 'spouse', 'children'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $beneficiary,
                'message' => 'Beneficiary retrieved successfully'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving beneficiary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Debug: Log the received request data
        Log::info("UPDATE Request received for beneficiary ID: $id");
        Log::info("Request method: " . $request->method());
        Log::info("Request content type: " . $request->header('Content-Type'));
        Log::info("All request data:", $request->all());
        Log::info("Request has files: " . (count($request->allFiles()) > 0 ? 'Yes' : 'No'));
        
        try {
            $beneficiary = Beneficiary::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        }
        
        // Validate request data
        $validator = Validator::make($request->all(), [
            // Beneficiary data
            'facility_id' => 'required|exists:facilities,id',
            'boschma_no' => 'required|string|unique:beneficiaries,boschma_no,' . $beneficiary->id,
            'fullname' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'nullable|string|max:255',
            'lga' => 'nullable|string|max:255',
            'state' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'ethnicity' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'id_type' => 'required|string|max:255',
            'id_number' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'dp_no' => 'nullable|string|max:255',
            'place_of_work' => 'nullable|string|max:255',
            'date_of_employment' => 'nullable|date',
            'date_of_retirement' => 'nullable|date',
            'status' => 'required|in:active,inactive',
            'beneficiary_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Spouse data
            'has_spouse' => 'nullable|boolean',
            'spouse_name' => 'nullable|required_if:has_spouse,true|string|max:255',
            'spouse_gender' => 'nullable|required_if:has_spouse,true|in:Male,Female',
            'spouse_dob' => 'nullable|required_if:has_spouse,true|date',
            'spouse_phone' => 'nullable|string|max:20',
            'spouse_email' => 'nullable|email|max:255',
            'spouse_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Children data
            'has_children' => 'nullable|boolean',
            'child_id' => 'nullable|array',
            'child_id.*' => 'nullable|numeric',
            'child_name' => 'nullable|array',
            'child_name.*' => 'nullable|string|max:255',
            'child_gender' => 'nullable|array',
            'child_gender.*' => 'nullable|in:Male,Female',
            'child_dob' => 'nullable|array',
            'child_dob.*' => 'nullable|date',
            'child_birth_certificate_no' => 'nullable|array',
            'child_birth_certificate_no.*' => 'nullable|string|max:255',
        ]);

        Log::info("Validation errors:", $validator->errors()->toArray());
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Update beneficiary record
            $beneficiaryData = [
                'facility_id' => $request->facility_id,
                'boschma_no' => $request->boschma_no,
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
                'contact_address' => $request->address,
                'phone_no' => $request->phone,
                'email' => $request->email,
                'occupation' => $request->occupation,
                'dp_no' => $request->dp_no,
                'id_type' => $request->id_type,
                'id_no' => $request->id_number,
                'nin' => $request->nin,
                'place_of_work' => $request->place_of_work,
                'date_of_employment' => $request->date_of_employment,
                'date_of_retirement' => $request->date_of_retirement,
                'category' => $request->category,
                'status' => $request->status,
                'has_spouse' => $request->has_spouse ? 1 : 0,
                'number_of_children' => $request->has_children && is_array($request->child_name) ? count(array_filter($request->child_name)) : 0,
            ];

            // Handle beneficiary photo upload if provided
            if ($request->hasFile('beneficiary_photo')) {
                Log::info("Beneficiary photo file found: " . $request->file('beneficiary_photo')->getClientOriginalName());
                // Delete old photo if exists
                if ($beneficiary->photo) {
                    Storage::disk('public')->delete($beneficiary->photo);
                }
                
                // Store new photo
                $photoPath = $request->file('beneficiary_photo')->store('beneficiary_photos', 'public');
                $beneficiaryData['photo'] = $photoPath;
            }

            // Update beneficiary
            $beneficiary->update($beneficiaryData);

            // Handle spouse data
            if ($request->has_spouse && $request->spouse_name) {
                $spouseData = [
                    'beneficiary_id' => $beneficiary->id,
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
                    Storage::disk('public')->delete($beneficiary->spouse->photo);
                }
                $beneficiary->spouse->delete();
            }

            // Handle children updates
            if ($request->has_children && $request->has('child_name')) {
                // Log all files in the request
                Log::info("All files in request: " . json_encode(array_keys($request->allFiles())));
                
                // Define suffix patterns
                $suffixes = ['A', 'B', 'C', 'D'];
                $existingChildIds = [];

                foreach ($request->child_name as $index => $childName) {
                    // Skip if child name is empty
                    if (empty($childName)) {
                        continue;
                    }

                    // Handle child photo upload
                    $childPhotoPath = null;
                    Log::info("Checking for child photo at index $index");
                    Log::info("Has file child_photo[$index]: " . ($request->hasFile("child_photo[$index]") ? 'YES' : 'NO'));
                    
                    // Try both bracket and array notation
                    if ($request->hasFile("child_photo[$index]")) {
                        Log::info("Processing child photo at index $index");
                        try {
                            $file = $request->file("child_photo[$index]");
                            Log::info("File object retrieved: " . $file->getClientOriginalName());
                            Log::info("File size: " . $file->getSize());
                            $childPhotoPath = $file->store('children_photos', 'public');
                            Log::info("Child photo stored successfully at: $childPhotoPath");
                            
                            // Verify the file exists
                            $fullPath = storage_path("app/public/$childPhotoPath");
                            Log::info("Full storage path: $fullPath");
                            Log::info("File exists on disk: " . (file_exists($fullPath) ? 'YES' : 'NO'));
                        } catch (\Exception $e) {
                            Log::error("Error storing child photo: " . $e->getMessage());
                        }
                    } elseif ($request->hasFile("child_photo.$index")) {
                        Log::info("Processing child photo (dot notation) at index $index");
                        try {
                            $childPhotoPath = $request->file("child_photo.$index")->store('children_photos', 'public');
                            Log::info("Child photo stored at: $childPhotoPath");
                        } catch (\Exception $e) {
                            Log::error("Error storing child photo (dot notation): " . $e->getMessage());
                        }
                    } else {
                        Log::info("No child photo file found for index $index");
                    }

                    // Handle birth certificate file upload
                    $birthCertPath = null;
                    Log::info("Checking for birth certificate at index $index");
                    Log::info("Has file birth_certificate[$index]: " . ($request->hasFile("birth_certificate[$index]") ? 'YES' : 'NO'));
                    
                    if ($request->hasFile("birth_certificate[$index]")) {
                        Log::info("Processing birth certificate at index $index");
                        try {
                            $file = $request->file("birth_certificate[$index]");
                            Log::info("Birth cert file retrieved: " . $file->getClientOriginalName());
                            Log::info("Birth cert file size: " . $file->getSize());
                            $birthCertPath = $file->store('birth_certificates', 'public');
                            Log::info("Birth certificate stored successfully at: $birthCertPath");
                            
                            // Verify the file exists
                            $fullPath = storage_path("app/public/$birthCertPath");
                            Log::info("Birth cert full path: $fullPath");
                            Log::info("Birth cert file exists on disk: " . (file_exists($fullPath) ? 'YES' : 'NO'));
                        } catch (\Exception $e) {
                            Log::error("Error storing birth certificate: " . $e->getMessage());
                        }
                    } elseif ($request->hasFile("birth_certificate.$index")) {
                        Log::info("Processing birth certificate (dot notation) at index $index");
                        try {
                            $birthCertPath = $request->file("birth_certificate.$index")->store('birth_certificates', 'public');
                            Log::info("Birth certificate stored at: $birthCertPath");
                        } catch (\Exception $e) {
                            Log::error("Error storing birth certificate (dot notation): " . $e->getMessage());
                        }
                    } else {
                        Log::info("No birth certificate file found for index $index");
                    }

                    // Prepare child data
                    $childData = [
                        'beneficiary_id' => $beneficiary->id,
                        'boschma_no' => $beneficiary->boschma_no . $suffixes[$index],
                        'nin' => $request->child_nin[$index] ?? null,
                        'name' => $childName,
                        'gender' => $request->child_gender[$index] ?? 'Male',
                        'dob' => $request->child_dob[$index] ?? null,
                        'birth_certificate_no' => $request->child_birth_certificate_no[$index] ?? null,
                    ];

                    Log::info("Child data prepared: " . json_encode($childData));
                    Log::info("Child photo path to add: " . ($childPhotoPath ?? 'NULL'));
                    Log::info("Birth cert path to add: " . ($birthCertPath ?? 'NULL'));

                    // Add photo and birth certificate paths ONLY if uploaded
                    if ($childPhotoPath) {
                        $childData['photo'] = $childPhotoPath;
                        Log::info("Added photo to child data: $childPhotoPath");
                    }
                    if ($birthCertPath) {
                        $childData['birth_certificate_file'] = $birthCertPath;
                        Log::info("Added birth cert to child data: $birthCertPath");
                    }

                    // Update existing child or create new one
                    if (isset($request->child_id[$index]) && $request->child_id[$index]) {
                        // Update by ID if provided
                        $childId = $request->child_id[$index];
                        Child::where('id', $childId)->update($childData);
                        $existingChildIds[] = $childId;
                        Log::info("Updated child with ID: $childId");
                    } else {
                        // Check if child with same boschma_no exists (handles missing IDs)
                        $existingChild = Child::where('boschma_no', $childData['boschma_no'])->first();
                        
                        if ($existingChild) {
                            Log::info("Found existing child by boschma_no: {$childData['boschma_no']}, ID: {$existingChild->id}");
                            
                            // Delete old photo if new one is uploaded
                            if ($childPhotoPath && $existingChild->photo && $existingChild->photo !== $childPhotoPath) {
                                Log::info("Deleting old photo: {$existingChild->photo}");
                                Storage::disk('public')->delete($existingChild->photo);
                            }
                            
                            // Delete old birth certificate if new one is uploaded
                            if ($birthCertPath && $existingChild->birth_certificate_file && $existingChild->birth_certificate_file !== $birthCertPath) {
                                Log::info("Deleting old birth certificate: {$existingChild->birth_certificate_file}");
                                Storage::disk('public')->delete($existingChild->birth_certificate_file);
                            }
                            
                            // Update existing child with new data (includes new photo/cert paths if uploaded)
                            $existingChild->update($childData);
                            $existingChildIds[] = $existingChild->id;
                            Log::info("Updated existing child: {$existingChild->name}, Photo: " . ($childData['photo'] ?? 'no change') . ", Cert: " . ($childData['birth_certificate_file'] ?? 'no change'));
                        } else {
                            // Create new child only if no existing one found
                            $child = Child::create($childData);
                            $existingChildIds[] = $child->id;
                            Log::info("Created new child: {$child->name}");
                        }
                    }
                }

                // Delete children that were removed from the form
                $removedChildren = Child::where('beneficiary_id', $beneficiary->id)
                    ->whereNotIn('id', $existingChildIds)
                    ->get();

                foreach ($removedChildren as $child) {
                    if ($child->photo) {
                        Storage::disk('public')->delete($child->photo);
                    }
                    $child->delete();
                }
            } else {
                // Delete all children if checkbox unchecked
                foreach ($beneficiary->children as $child) {
                    if ($child->photo) {
                        Storage::disk('public')->delete($child->photo);
                    }
                    $child->delete();
                }
            }

            DB::commit();
            
            // Return updated beneficiary with relationships
            $beneficiary->load(['facility', 'spouse', 'children']);
            
            return response()->json([
                'success' => true,
                'data' => $beneficiary,
                'message' => 'Beneficiary updated successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating beneficiary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $beneficiary = Beneficiary::with(['spouse', 'children'])->findOrFail($id);
            
            DB::beginTransaction();
            
            // Delete beneficiary photo if exists
            if ($beneficiary->photo) {
                Storage::disk('public')->delete($beneficiary->photo);
            }
            
            // Delete spouse and spouse photo if exists
            if ($beneficiary->spouse) {
                if ($beneficiary->spouse->photo) {
                    Storage::disk('public')->delete($beneficiary->spouse->photo);
                }
                $beneficiary->spouse->delete();
            }
            
            // Delete children and their photos/documents if exist
            foreach ($beneficiary->children as $child) {
                if ($child->photo) {
                    Storage::disk('public')->delete($child->photo);
                }
                if ($child->birth_certificate_file) {
                    Storage::disk('public')->delete($child->birth_certificate_file);
                }
                $child->delete();
            }
            
            // Delete the beneficiary
            $beneficiary->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Beneficiary deleted successfully'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting beneficiary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get beneficiary's children
     */
    public function children(string $id): JsonResponse
    {
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $children = $beneficiary->children;
            
            return response()->json([
                'success' => true,
                'data' => $children,
                'message' => 'Children retrieved successfully'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving children: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get beneficiary's spouse
     */
    public function spouse(string $id): JsonResponse
    {
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $spouse = $beneficiary->spouse;
            
            return response()->json([
                'success' => true,
                'data' => $spouse,
                'message' => 'Spouse retrieved successfully'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving spouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload beneficiary photo
     */
    public function uploadPhoto(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        Log::info("Validation errors:", $validator->errors()->toArray());
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $beneficiary = Beneficiary::findOrFail($id);
            
            // Delete old photo if exists
            if ($beneficiary->photo) {
                Storage::disk('public')->delete($beneficiary->photo);
            }
            
            // Store new photo
            $photoPath = $request->file('photo')->store('beneficiary_photos', 'public');
            $beneficiary->update(['photo' => $photoPath]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'photo_url' => url('storage/' . $photoPath),
                    'photo_path' => $photoPath
                ],
                'message' => 'Photo uploaded successfully'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading photo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload spouse photo
     */
    public function uploadSpousePhoto(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        Log::info("Validation errors:", $validator->errors()->toArray());
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $beneficiary = Beneficiary::findOrFail($id);
            
            if (!$beneficiary->spouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Spouse not found for this beneficiary'
                ], 404);
            }
            
            // Delete old photo if exists
            if ($beneficiary->spouse->photo) {
                Storage::disk('public')->delete($beneficiary->spouse->photo);
            }
            
            // Store new photo
            $photoPath = $request->file('photo')->store('spouse_photos', 'public');
            $beneficiary->spouse->update(['photo' => $photoPath]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'photo_url' => url('storage/' . $photoPath),
                    'photo_path' => $photoPath
                ],
                'message' => 'Spouse photo uploaded successfully'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading spouse photo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search beneficiary by DP No
     */
    public function searchByDpNo(string $dpNo): JsonResponse
    {
        try {
            $beneficiary = Beneficiary::with(['facility', 'spouse', 'children'])
                ->where('dp_no', $dpNo)
                ->first();
            
            if ($beneficiary) {
                return response()->json([
                    'success' => true,
                    'data' => $beneficiary,
                    'message' => 'Beneficiary found successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'No beneficiary found with this DP No'
                ], 404);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching beneficiary by DP No: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search beneficiaries
     */
    public function search(string $query): JsonResponse
    {
        try {
            $beneficiaries = Beneficiary::with(['facility', 'spouse', 'children'])
                ->where(function($q) use ($query) {
                    $q->where('boschma_no', 'like', "%{$query}%")
                      ->orWhere('fullname', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->paginate(15);
            
            return response()->json([
                'success' => true,
                'data' => $beneficiaries,
                'message' => 'Search results retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching beneficiaries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Filter beneficiaries by category
     */
    public function filterByCategory(string $category): JsonResponse
    {
        try {
            $beneficiaries = Beneficiary::with(['facility', 'spouse', 'children'])
                ->where('category', $category)
                ->paginate(15);
            
            return response()->json([
                'success' => true,
                'data' => $beneficiaries,
                'message' => 'Beneficiaries filtered by category successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering beneficiaries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Filter beneficiaries by status
     */
    public function filterByStatus(string $status): JsonResponse
    {
        try {
            $beneficiaries = Beneficiary::with(['facility', 'spouse', 'children'])
                ->where('status', $status)
                ->paginate(15);
            
            return response()->json([
                'success' => true,
                'data' => $beneficiaries,
                'message' => 'Beneficiaries filtered by status successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering beneficiaries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Filter beneficiaries by facility
     */
    public function filterByFacility(string $facilityId): JsonResponse
    {
        try {
            $beneficiaries = Beneficiary::with(['facility', 'spouse', 'children'])
                ->where('facility_id', $facilityId)
                ->paginate(15);
            
            return response()->json([
                'success' => true,
                'data' => $beneficiaries,
                'message' => 'Beneficiaries filtered by facility successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering beneficiaries by facility: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all facilities for selection
     */
    public function facilities(): JsonResponse
    {
        try {
            $facilities = Facility::orderBy('name')->get();
            
            return response()->json([
                'success' => true,
                'data' => $facilities,
                'message' => 'Facilities retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving facilities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify NIN uniqueness across beneficiaries, spouses, and children tables
     * Used by beneficiary mobile app for NIN verification
     */
    public function verifyNinUniqueness(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nin' => 'required|string|size:11',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'exists' => false,
                    'message' => 'Invalid NIN format - must be 11 digits',
                    'errors' => $validator->errors()
                ], 422);
            }

            $nin = $request->nin;

            // Check if NIN exists in beneficiaries table
            $existingBeneficiary = Beneficiary::where('nin', $nin)->first();
            if ($existingBeneficiary) {
                return response()->json([
                    'success' => false,
                    'exists' => true,
                    'exists_in' => 'beneficiaries',
                    'message' => 'This NIN is already enrolled as a beneficiary (BOSCHMA ID: ' . $existingBeneficiary->boschma_no . ')'
                ], 409);
            }

            // Check if NIN exists in spouses table
            $existingSpouse = Spouse::where('nin', $nin)->first();
            if ($existingSpouse) {
                $beneficiary = Beneficiary::find($existingSpouse->beneficiary_id);
                return response()->json([
                    'success' => false,
                    'exists' => true,
                    'exists_in' => 'spouses',
                    'message' => 'This NIN is already registered as a spouse (under BOSCHMA ID: ' . ($beneficiary->boschma_no ?? 'N/A') . ')'
                ], 409);
            }

            // Check if NIN exists in children table
            $existingChild = Child::where('nin', $nin)->first();
            if ($existingChild) {
                $beneficiary = Beneficiary::find($existingChild->beneficiary_id);
                return response()->json([
                    'success' => false,
                    'exists' => true,
                    'exists_in' => 'children',
                    'message' => 'This NIN is already registered as a child (under BOSCHMA ID: ' . ($beneficiary->boschma_no ?? 'N/A') . ')'
                ], 409);
            }

            // NIN is unique
            return response()->json([
                'success' => true,
                'exists' => false,
                'message' => 'NIN is available and unique'
            ], 200);

        } catch (\Exception $e) {
            Log::error('NIN uniqueness check error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Error checking NIN uniqueness: ' . $e->getMessage()
            ], 500);
        }
    }
}
