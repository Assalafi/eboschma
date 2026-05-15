<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the BOSCHMA beneficiary enrollment dashboard with simplified data
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get overall enrollment statistics
        $stats = Beneficiary::getEnrollmentStats();
        $totalBeneficiaries = $stats['total_beneficiaries'];
        $totalSpouses = $stats['total_spouses'];
        $totalChildren = $stats['total_children'];
        $totalEnrollments = $stats['total_enrollments'];

        // Get self enrollment count
        $selfEnrollments = Beneficiary::getSelfEnrollmentCount();

        // Get monthly statistics
        $monthlyStats = Beneficiary::getMonthlyStats();
        $thisMonthEnrollments = $monthlyStats['this_month'];
        $lastMonthEnrollments = $monthlyStats['last_month'];

        // Get top 10 facilities by enrollment count
        $topFacilities = Beneficiary::getTopFacilities(10);

        // Get program-wise statistics
        $programStats = Beneficiary::getProgramStats();

        // Empty departments array to avoid memory exhaustion
        $departments = [];

        $page = 'admin.dashboard';
        return view('page', compact(
            'page', 
            'totalBeneficiaries', 
            'totalSpouses', 
            'totalChildren', 
            'totalEnrollments', 
            'selfEnrollments',
            'thisMonthEnrollments',
            'lastMonthEnrollments',
            'topFacilities',
            'programStats'
        ));
    }
}
