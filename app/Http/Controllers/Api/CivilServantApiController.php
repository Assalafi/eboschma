<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CivilServant;
use App\Models\BeneficiaryLogin;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CivilServantApiController extends Controller
{
    /**
     * Verify civil servant by DP Number and NIN
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dp_no' => 'required|string',
            'nin' => 'required|string|max:11',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $civilServant = CivilServant::where('dp_no', $request->dp_no)
                                      ->where('bvn', $request->nin)
                                      ->first();

            if (!$civilServant) {
                return response()->json([
                    'success' => false,
                    'message' => 'No civil servant found with the provided DP Number and BVN'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Civil servant verified successfully',
                'data' => [
                    'id' => $civilServant->id,
                    'dp_no' => $civilServant->dp_no,
                    'fullname' => $civilServant->fullname,
                    'gender' => $civilServant->gender,
                    'mda' => $civilServant->mda,
                    'state' => $civilServant->state,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying civil servant'
            ], 500);
        }
    }

    /**
     * Create account for verified civil servant
     */
    public function createAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'civil_servant_id' => 'required|exists:civil_servants,id',
            'email' => 'required|email|unique:beneficiary_logins,email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors(),
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $civilServant = CivilServant::findOrFail($request->civil_servant_id);

            // Check if this civil servant already has an account
            $existingLogin = BeneficiaryLogin::where('civil_servant_id', $civilServant->id)->first();
            if ($existingLogin) {
                return response()->json([
                    'success' => false,
                    'message' => 'An account already exists for this civil servant'
                ], 409);
            }

            // Create beneficiary login account
            $beneficiaryLogin = BeneficiaryLogin::create([
                'name' => $civilServant->fullname,
                'email' => $request->email,
                'password' => $request->password,
                'civil_servant_id' => $civilServant->id,
                'program_id' => '1',
                'status' => 'Active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully',
                'data' => [
                    'user_id' => $beneficiaryLogin->id,
                    'name' => $beneficiaryLogin->name,
                    'email' => $beneficiaryLogin->email,
                    'dp_no' => $civilServant->dp_no,
                    'program_id' => $beneficiaryLogin->program_id,
                    'status' => $beneficiaryLogin->status,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating account ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login for civil servant users using DP Number and password
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dp_no' => 'required|string',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Find civil servant by DP number
            $civilServant = CivilServant::where('dp_no', $request->dp_no)->first();
            
            if (!$civilServant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid DP Number or password'
                ], 401);
            }

            // Find beneficiary login by civil_servant_id
            $beneficiaryLogin = BeneficiaryLogin::where('civil_servant_id', $civilServant->id)->first();

            if (!$beneficiaryLogin || !Hash::check($request->password, $beneficiaryLogin->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid DP Number or password'
                ], 401);
            }

            // Create personal access token
            $token = $beneficiaryLogin->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $beneficiaryLogin->id,
                        'name' => $beneficiaryLogin->name,
                        'email' => $beneficiaryLogin->email,
                        'program_id' => $beneficiaryLogin->program_id,
                        'status' => $beneficiaryLogin->status,
                    ],
                    'civil_servant' => [
                        'dp_no' => $civilServant->dp_no,
                        'fullname' => $civilServant->fullname,
                        'gender' => $civilServant->gender,
                        'mda' => $civilServant->mda,
                        'state' => $civilServant->state,
                        'dob' => $civilServant->dob,
                        'lga' => $civilServant->lga,
                    ],
                    'token' => $token,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user profile with complete civil servant data
     */
    public function profile(Request $request)
    {
        try {
            $beneficiaryLogin = $request->user();
            $civilServant = null;
            
            if ($beneficiaryLogin->civil_servant_id) {
                $civilServant = CivilServant::find($beneficiaryLogin->civil_servant_id);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $beneficiaryLogin->id,
                        'name' => $beneficiaryLogin->name,
                        'email' => $beneficiaryLogin->email,
                        'program_id' => $beneficiaryLogin->program_id,
                        'status' => $beneficiaryLogin->status,
                        'created_at' => $beneficiaryLogin->created_at,
                        'updated_at' => $beneficiaryLogin->updated_at,
                    ],
                    'civil_servant' => $civilServant ? [
                        'id' => $civilServant->id,
                        'dp_no' => $civilServant->dp_no,
                        'fullname' => $civilServant->fullname,
                        'gender' => $civilServant->gender,
                        'dob' => $civilServant->dob,
                        'nin' => $civilServant->nin,
                        'phone' => $civilServant->phone,
                        'email' => $civilServant->email,
                        'mda' => $civilServant->mda,
                        'department' => $civilServant->department,
                        'grade_level' => $civilServant->grade_level,
                        'step' => $civilServant->step,
                        'designation' => $civilServant->designation,
                        'state' => $civilServant->state,
                        'lga' => $civilServant->lga,
                        'address' => $civilServant->address,
                        'salary' => $civilServant->salary,
                        'bank_name' => $civilServant->bank_name,
                        'account_number' => $civilServant->account_number,
                        'employment_date' => $civilServant->employment_date,
                        'retirement_date' => $civilServant->retirement_date,
                        'status' => $civilServant->status,
                        'created_at' => $civilServant->created_at,
                        'updated_at' => $civilServant->updated_at,
                    ] : null,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard data for civil servant
     */
    public function dashboard(Request $request)
    {
        try {
            $beneficiaryLogin = $request->user();
            $civilServant = null;
            
            if ($beneficiaryLogin->civil_servant_id) {
                $civilServant = CivilServant::find($beneficiaryLogin->civil_servant_id);
            }

            // Calculate profile completion
            $profileFields = [
                'phone', 'email', 'address', 'bank_name', 'account_number'
            ];
            $completedFields = 0;
            $totalFields = count($profileFields);

            if ($civilServant) {
                foreach ($profileFields as $field) {
                    if (!empty($civilServant->$field)) {
                        $completedFields++;
                    }
                }
            }

            $profileCompletionPercentage = $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;

            // Calculate years of service
            $yearsOfService = 0;
            if ($civilServant && $civilServant->employment_date) {
                $employmentDate = \Carbon\Carbon::parse($civilServant->employment_date);
                $yearsOfService = $employmentDate->diffInYears(now());
            }

            // Calculate years to retirement
            $yearsToRetirement = 'N/A';
            if ($civilServant && $civilServant->retirement_date) {
                $retirementDate = \Carbon\Carbon::parse($civilServant->retirement_date);
                if ($retirementDate->isFuture()) {
                    $yearsToRetirement = now()->diffInYears($retirementDate);
                } else {
                    $yearsToRetirement = 'Retired';
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'name' => $beneficiaryLogin->name,
                        'email' => $beneficiaryLogin->email,
                        'program_id' => $beneficiaryLogin->program_id,
                        'status' => $beneficiaryLogin->status,
                        'last_login' => $beneficiaryLogin->updated_at,
                    ],
                    'civil_servant' => $civilServant ? [
                        'dp_no' => $civilServant->dp_no,
                        'fullname' => $civilServant->fullname,
                        'mda' => $civilServant->mda,
                        'department' => $civilServant->department,
                        'designation' => $civilServant->designation,
                        'grade_level' => $civilServant->grade_level,
                        'step' => $civilServant->step,
                        'status' => $civilServant->status,
                        'employment_date' => $civilServant->employment_date,
                        'retirement_date' => $civilServant->retirement_date,
                    ] : null,
                    'stats' => [
                        'profile_completion' => $profileCompletionPercentage,
                        'years_of_service' => $yearsOfService,
                        'years_to_retirement' => $yearsToRetirement,
                        'status' => $civilServant ? $civilServant->status : 'Unknown',
                        'last_updated' => $civilServant ? $civilServant->updated_at->format('M d, Y') : 'Never',
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 1: Verify identity for password reset using DP Number and BVN
     */
    public function verifyForReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dp_no' => 'required|string',
            'bvn' => 'required|string|size:11',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $civilServant = CivilServant::where('dp_no', $request->dp_no)
                                      ->where('bvn', $request->bvn)
                                      ->first();

            if (!$civilServant) {
                return response()->json([
                    'success' => false,
                    'message' => 'No civil servant found with the provided DP Number and BVN'
                ], 404);
            }

            $beneficiaryLogin = BeneficiaryLogin::where('civil_servant_id', $civilServant->id)->first();

            if (!$beneficiaryLogin) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found for this civil servant. Please create an account first.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Identity verified successfully',
                'data' => [
                    'dp_no' => $civilServant->dp_no,
                    'name' => $civilServant->fullname,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during verification'
            ], 500);
        }
    }

    /**
     * Step 2: Reset password after identity has been verified
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dp_no' => 'required|string',
            'bvn' => 'required|string|size:11',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $civilServant = CivilServant::where('dp_no', $request->dp_no)
                                      ->where('bvn', $request->bvn)
                                      ->first();

            if (!$civilServant) {
                return response()->json([
                    'success' => false,
                    'message' => 'No civil servant found with the provided DP Number and BVN'
                ], 404);
            }

            $beneficiaryLogin = BeneficiaryLogin::where('civil_servant_id', $civilServant->id)->first();

            if (!$beneficiaryLogin) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found for this civil servant. Please create an account first.'
                ], 404);
            }

            $beneficiaryLogin->password = Hash::make($request->new_password);
            $beneficiaryLogin->save();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. You can now sign in with your new password.',
                'data' => [
                    'dp_no' => $civilServant->dp_no,
                    'name' => $civilServant->fullname,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resetting password'
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during logout'
            ], 500);
        }
    }
}
