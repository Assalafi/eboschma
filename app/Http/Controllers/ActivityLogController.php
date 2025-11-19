<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ActivityLog::query();
        
        // Apply filters if provided
        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }
        
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        
        if ($request->filled('user_email')) {
            $query->where('user_email', 'like', '%' . $request->input('user_email') . '%');
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        
        // Get all unique modules and actions for filtering
        $modules = ActivityLog::distinct()->pluck('module')->toArray();
        $actions = ActivityLog::distinct()->pluck('action')->toArray();
        
        // Get the logs with pagination
        $logs = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $page = 'activity-logs.index';
        return view('page', compact('page', 'logs', 'modules', 'actions'));
    }
    
    /**
     * Display the specified activity log.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $log = ActivityLog::findOrFail($id);
        $page = 'activity-logs.show';
        return view('page', compact('page', 'log'));
    }
    
    /**
     * Display the activity logs dashboard with analytics.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Get counts by module for cards
        $staffLogs = ActivityLog::where('module', 'staff')->orWhere('module', 'staff_role')->count();
        $roleLogs = ActivityLog::where('module', 'role')->count();
        $permissionLogs = ActivityLog::where('module', 'permission')->count();
        $totalLogs = ActivityLog::count();
        
        // Get recent logs for table
        $recentLogs = ActivityLog::orderBy('created_at', 'desc')->take(10)->get();
        
        // Prepare data for actions pie chart
        $actionCounts = ActivityLog::select('action', DB::raw('count(*) as total'))
                                ->groupBy('action')
                                ->orderBy('total', 'desc')
                                ->get();
        
        $actionLabels = $actionCounts->pluck('action')->map(function($item) {
            return ucfirst($item);
        })->toArray();
        
        $actionData = $actionCounts->pluck('total')->toArray();
        
        // Prepare data for timeline chart (last 7 days)
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dates->push(Carbon::now()->subDays($i)->format('Y-m-d'));
        }
        
        $timelineLabels = $dates->map(function($date) {
            return Carbon::parse($date)->format('M d');
        })->toArray();
        
        $timelineData = $dates->map(function($date) {
            return ActivityLog::whereDate('created_at', $date)->count();
        })->toArray();
        
        $page = 'activity-logs.dashboard';
        return view('page', compact(
            'page', 'staffLogs', 'roleLogs', 'permissionLogs', 'totalLogs',
            'recentLogs', 'actionLabels', 'actionData', 'timelineLabels', 'timelineData'
        ));
    }
}
