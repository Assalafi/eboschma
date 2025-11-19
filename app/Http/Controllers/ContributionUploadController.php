<?php

namespace App\Http\Controllers;

use App\Models\ContributionUpload;
use App\Models\Contribution;
use App\Jobs\ProcessContributionUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ContributionUploadController extends Controller
{
    /**
     * Display list of all uploads
     */
    public function index()
    {
        $uploads = ContributionUpload::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.contributions.uploads.index', compact('uploads'));
    }

    /**
     * Show upload form
     */
    public function create()
    {
        return view('admin.contributions.uploads.create');
    }

    /**
     * Store uploaded file
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:10240',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        try {
            $file = $request->file('file');
            $month = $request->month;
            $year = $request->year;

            // Ensure contributions directory exists
            if (!Storage::exists('contributions')) {
                Storage::makeDirectory('contributions');
            }

            // Create filename as m_y format
            $storedFilename = $month . '_' . $year . '.' . $file->getClientOriginalExtension();
            
            // Store file in storage/app/contributions
            $filePath = $file->storeAs('contributions', $storedFilename);
            
            // Verify file was stored
            if (!Storage::exists($filePath)) {
                throw new \Exception("Failed to store file at: {$filePath}");
            }

            // Read file to count total rows
            $data = Excel::toArray([], $file)[0];
            array_shift($data); // Remove header
            $totalRows = count(array_filter($data, function($row) {
                return !empty($row[1]) && !empty($row[2]); // Has DP_NO and SALARY
            }));

            // Create upload record
            $upload = ContributionUpload::create([
                'filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'file_path' => $filePath,
                'month' => $month,
                'year' => $year,
                'status' => 'pending',
                'total_rows' => $totalRows,
                'uploaded_by' => Auth::id(),
            ]);

            return redirect()->route('contribution-uploads.index')
                           ->with('success', 'File uploaded successfully! Click "Start Processing" to begin import.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }

    /**
     * Start processing uploaded file (dispatch to background queue)
     */
    public function process($id)
    {
        $upload = ContributionUpload::findOrFail($id);

        if ($upload->status != 'pending') {
            return redirect()->back()
                           ->with('error', 'This upload has already been processed or is currently processing.');
        }

        // Mark as processing
        $upload->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        // Dispatch job to queue
        ProcessContributionUpload::dispatch($upload->id);

        return redirect()->route('contribution-uploads.index')
                       ->with('success', 'Processing started! The file will be processed in the background. You can close this page and check back later for results.');
    }

    /**
     * Get upload progress (for AJAX polling)
     */
    public function progress($id)
    {
        $upload = ContributionUpload::findOrFail($id);

        return response()->json([
            'status' => $upload->status,
            'total_rows' => $upload->total_rows,
            'processed_rows' => $upload->processed_rows,
            'progress_percentage' => $upload->progress_percentage,
            'success_count' => $upload->success_count,
            'failed_count' => $upload->failed_count,
        ]);
    }

    /**
     * Delete upload record and file
     */
    public function destroy($id)
    {
        $upload = ContributionUpload::findOrFail($id);

        // Delete file
        if (Storage::exists($upload->file_path)) {
            Storage::delete($upload->file_path);
        }

        $upload->delete();

        return redirect()->route('contribution-uploads.index')
                       ->with('success', 'Upload deleted successfully!');
    }

    /**
     * View error log
     */
    public function showErrors($id)
    {
        $upload = ContributionUpload::findOrFail($id);
        return view('admin.contributions.uploads.errors', compact('upload'));
    }
}
