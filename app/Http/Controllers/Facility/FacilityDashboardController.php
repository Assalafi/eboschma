<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Models\Encounter;
use App\Models\ServiceReferral;
use App\Models\FacilityClaim;
use App\Models\Patient;
use App\Models\Beneficiary;
use App\Models\Spouse;
use App\Models\Child;
use Carbon\Carbon;

class FacilityDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Show the facility staff dashboard.
     */
    public function index(): View
    {
        $user = Auth::guard('web')->user();
        $facility = $user->facility;
        
        if (!$facility) {
            return view('facility.dashboard', [
                'user' => $user,
                'facility' => null,
                'stats' => $this->getEmptyStats()
            ]);
        }

        $facilityId = $facility->id;
        
        Log::info('Facility staff dashboard accessed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'facility_id' => $facilityId,
            'facility_name' => $facility->name,
        ]);

        
        // Get real statistics for the dashboard
        $stats = [
            // Total encounters at this facility
            'total_encounters' => Encounter::where('facility_id', $facilityId)->count(),
            
            // Encounters today
            'encounters_today' => Encounter::where('facility_id', $facilityId)
                ->whereDate('created_at', Carbon::today())
                ->count(),
            
            // Total referrals (incoming + outgoing)
            'total_referrals' => ServiceReferral::where(function($query) use ($facilityId) {
                $query->where('from_facility_id', $facilityId)
                      ->orWhere('to_facility_id', $facilityId);
            })->count(),
            
            // Pending referrals
            'pending_referrals' => ServiceReferral::where(function($query) use ($facilityId) {
                $query->where('from_facility_id', $facilityId)
                      ->orWhere('to_facility_id', $facilityId);
            })->where('status', ServiceReferral::STATUS_PENDING)->count(),
            
            // Total claims
            'total_claims' => FacilityClaim::where('facility_id', $facilityId)->count(),
            
            // Pending claims
            'pending_claims' => FacilityClaim::where('facility_id', $facilityId)
                ->where('status', 'pending')
                ->count(),
            
            // Unique patients served (count from enrollee tables that exist in patients table)
            'unique_patients' => Beneficiary::where('facility_id', $facilityId)
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('patients')
                        ->whereRaw('patients.enrollee_number = beneficiaries.boschma_no');
                })->count() +
                Spouse::where('facility_id', $facilityId)
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('patients')
                        ->whereRaw('patients.enrollee_number = spouses.boschma_no');
                })->count() +
                Child::where('facility_id', $facilityId)
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('patients')
                        ->whereRaw('patients.enrollee_number = children.boschma_no');
                })->count(),
            
            // Total enrolled beneficiaries (all enrollees registered to this facility)
            'total_beneficiaries' => Beneficiary::where('facility_id', $facilityId)
                ->where('status', '!=', 'draft')->count(),
            'total_spouses' => Spouse::where('facility_id', $facilityId)->count(),
            'total_children' => Child::where('facility_id', $facilityId)->count(),
            
            // Active today (encounters + referrals today)
            'active_today' => Encounter::where('facility_id', $facilityId)
                ->whereDate('created_at', Carbon::today())
                ->count() +
                ServiceReferral::where(function($query) use ($facilityId) {
                    $query->where('from_facility_id', $facilityId)
                          ->orWhere('to_facility_id', $facilityId);
                })->whereDate('created_at', Carbon::today())
                ->count(),
            
            // Recent activities
            'recent_activities' => $this->getRecentActivities($facilityId),
        ];

        return view('facility.dashboard', compact('user', 'facility', 'stats'));
    }

    /**
     * Get recent activities for the facility
     */
    private function getRecentActivities($facilityId)
    {
        $activities = [];

        // Recent encounters
        $encounters = Encounter::where('facility_id', $facilityId)
            ->with('patient')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($encounters as $encounter) {
            $patient = $encounter->patient;
            $enrolleeDetails = $patient ? $patient->enrolleeDetails : null;
            $patientName = $enrolleeDetails ? $enrolleeDetails->fullname : 'Unknown Patient';
            
            $activities[] = [
                'type' => 'encounter',
                'icon' => 'fe-user',
                'color' => 'primary',
                'title' => 'New Encounter',
                'description' => "Patient: {$patientName}",
                'time' => $encounter->created_at,
                'url' => null
            ];
        }

        // Recent referrals
        $referrals = ServiceReferral::where(function($query) use ($facilityId) {
            $query->where('from_facility_id', $facilityId)
                  ->orWhere('to_facility_id', $facilityId);
        })
        ->with(['encounter.patient', 'fromFacility', 'toFacility'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        foreach ($referrals as $referral) {
            $isOutgoing = $referral->from_facility_id == $facilityId;
            $otherFacility = $isOutgoing ? $referral->toFacility : $referral->fromFacility;
            
            $activities[] = [
                'type' => 'referral',
                'icon' => $isOutgoing ? 'fe-arrow-up-right' : 'fe-arrow-down-left',
                'color' => $isOutgoing ? 'warning' : 'info',
                'title' => $isOutgoing ? 'Outgoing Referral' : 'Incoming Referral',
                'description' => ($isOutgoing ? 'To: ' : 'From: ') . $otherFacility->name,
                'time' => $referral->created_at,
                'url' => route('facility.referrals.show', $referral->id)
            ];
        }

        // Sort by time and limit to 10
        usort($activities, function($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get empty stats when no facility
     */
    private function getEmptyStats()
    {
        return [
            'total_encounters' => 0,
            'encounters_today' => 0,
            'total_referrals' => 0,
            'pending_referrals' => 0,
            'total_claims' => 0,
            'pending_claims' => 0,
            'unique_patients' => 0,
            'active_today' => 0,
            'recent_activities' => [],
        ];
    }
}
