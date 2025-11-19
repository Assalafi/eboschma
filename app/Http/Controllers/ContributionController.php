<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ContributionsImport;

class ContributionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contribution::query();

        // Filter by month
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by DP No
        if ($request->filled('dp_no')) {
            $query->where('dp_no', 'like', '%' . $request->dp_no . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contributions = $query->orderBy('year', 'desc')
                              ->orderBy('month', 'desc')
                              ->orderBy('dp_no', 'asc')
                              ->paginate(50);

        // Calculate summary statistics
        $summary = [
            'total_records' => Contribution::count(),
            'total_amount' => Contribution::sum('amount'),
            'total_contributed' => Contribution::sum('contributed'),
            'active_records' => Contribution::where('status', 1)->count(),
        ];

        // Get unique years for filter
        $years = Contribution::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        return view('admin.contributions.index', compact('contributions', 'summary', 'years'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dp_no' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        // Calculate 3.5% contribution
        $contributed = $request->amount * 0.035;

        // Use updateOrCreate to replace existing record if found
        $contribution = Contribution::updateOrCreate(
            [
                'dp_no' => $request->dp_no,
                'month' => $request->month,
                'year' => $request->year,
            ],
            [
                'amount' => $request->amount,
                'contributed' => $contributed,
                'status' => 1,
            ]
        );

        $message = $contribution->wasRecentlyCreated 
            ? 'Contribution record created successfully!' 
            : 'Contribution record updated successfully (existing record replaced)!';

        return redirect()->route('contributions.index')->with('success', $message);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contribution $contribution)
    {
        $request->validate([
            'dp_no' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'status' => 'required|boolean',
        ]);

        // Calculate 3.5% contribution
        $contributed = $request->amount * 0.035;

        try {
            $contribution->update([
                'dp_no' => $request->dp_no,
                'amount' => $request->amount,
                'contributed' => $contributed,
                'month' => $request->month,
                'year' => $request->year,
                'status' => $request->status,
            ]);

            return redirect()->route('contributions.index')
                           ->with('success', 'Contribution record updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error: Another record exists for this DP No and month/year combination.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contribution $contribution)
    {
        $contribution->delete();
        return redirect()->route('contributions.index')
                       ->with('success', 'Contribution record deleted successfully!');
    }

    /**
     * Download template file for contributions upload
     */
    public function downloadTemplate()
    {
        $filename = 'contributions_template.xlsx';
        $headers = ['SN', 'DP_NO', 'SALARY'];
        
        // Sample data
        $sampleData = [
            [1, 'DP001', 50000],
            [2, 'DP002', 75000],
            [3, 'DP003', 100000],
        ];

        // Create spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers with styling
        $sheet->fromArray($headers, null, 'A1');
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '01542B'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        
        // Add sample data
        $sheet->fromArray($sampleData, null, 'A2');
        
        // Apply borders to all data
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:C' . (count($sampleData) + 1))->applyFromArray($styleArray);

        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Upload and import contributions from CSV/Excel
     */
    public function import(Request $request)
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
            
            // Read file
            $data = Excel::toArray([], $file)[0];
            
            // Skip header row
            array_shift($data);
            
            $imported = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($data as $index => $row) {
                // Skip empty rows
                if (empty($row[1]) || empty($row[2])) {
                    continue;
                }

                $dp_no = trim($row[1]); // Column B (DP_NO)
                $amount = floatval($row[2]); // Column C (SALARY)
                
                // Skip if amount is 0 or negative
                if ($amount <= 0) {
                    $skipped++;
                    $errors[] = "Row " . ($index + 2) . ": Invalid amount for DP No {$dp_no}";
                    continue;
                }
                
                // Calculate 3.5% contribution
                $contributed = $amount * 0.035;

                try {
                    // Use updateOrCreate to replace existing records
                    $contribution = Contribution::updateOrCreate(
                        [
                            'dp_no' => $dp_no,
                            'month' => $month,
                            'year' => $year,
                        ],
                        [
                            'amount' => $amount,
                            'contributed' => $contributed,
                            'status' => 1,
                        ]
                    );

                    if ($contribution->wasRecentlyCreated) {
                        $imported++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Import completed! {$imported} new records, {$updated} updated, {$skipped} skipped.";
            
            if (!empty($errors)) {
                $errorMessage = implode('<br>', array_slice($errors, 0, 10)); // Show first 10 errors
                if (count($errors) > 10) {
                    $errorMessage .= '<br>... and ' . (count($errors) - 10) . ' more errors';
                }
                return redirect()->route('contributions.index')
                               ->with('warning', $message . '<br><br><strong>Errors:</strong><br>' . $errorMessage);
            }

            return redirect()->route('contributions.index')
                           ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
