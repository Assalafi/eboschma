<?php

namespace App\Jobs;

use App\Models\ContributionUpload;
use App\Models\Contribution;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProcessContributionUpload implements ShouldQueue
{
    use Queueable;

    protected $uploadId;

    /**
     * Create a new job instance.
     */
    public function __construct($uploadId)
    {
        $this->uploadId = $uploadId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $upload = ContributionUpload::find($this->uploadId);
        
        if (!$upload) {
            return;
        }

        try {
            // Check if file exists in storage
            if (!Storage::exists($upload->file_path)) {
                throw new \Exception("File not found in storage: {$upload->file_path}");
            }
            
            // Get full path to file
            $filePath = Storage::path($upload->file_path);
            
            $data = Excel::toArray([], $filePath)[0];
            array_shift($data); // Remove header

            $successCount = 0;
            $failedCount = 0;
            $processedRows = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($data as $index => $row) {
                // Skip empty rows
                if (empty($row[1]) || empty($row[2])) {
                    continue;
                }

                $dpNo = trim($row[1]);
                $amount = floatval($row[2]);
                
                if ($amount <= 0) {
                    $failedCount++;
                    $errors[] = "Row " . ($index + 2) . ": Invalid amount for DP No {$dpNo}";
                    $processedRows++;
                    
                    // Update progress
                    $upload->update(['processed_rows' => $processedRows]);
                    continue;
                }
                
                $contributed = $amount * 0.035;

                try {
                    Contribution::updateOrCreate(
                        [
                            'dp_no' => $dpNo,
                            'month' => $upload->month,
                            'year' => $upload->year,
                        ],
                        [
                            'amount' => $amount,
                            'contributed' => $contributed,
                            'status' => 1,
                        ]
                    );

                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }

                $processedRows++;
                
                // Update progress every 10 rows
                if ($processedRows % 10 == 0) {
                    $upload->update(['processed_rows' => $processedRows]);
                }
            }

            DB::commit();

            // Mark as completed
            $upload->update([
                'status' => 'completed',
                'processed_rows' => $processedRows,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'error_log' => !empty($errors) ? implode("\n", $errors) : null,
                'completed_at' => now(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            $upload->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
            ]);
        }
    }
}
