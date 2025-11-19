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
        // Count beneficiaries - simplified to avoid memory issues
        $totalBeneficiaries = Beneficiary::count();

        // Count new registrations (beneficiaries registered in the last 30 days)
        $newRegistrations = Beneficiary::where('created_at', '>=', now()->subDays(30))->count();
        //dd($newRegistrations);

        // Empty departments array to avoid memory exhaustion
        $departments = [];

        $page = 'admin.dashboard';
        return view('page', compact('page', 'totalBeneficiaries', 'newRegistrations'));
    }
}
