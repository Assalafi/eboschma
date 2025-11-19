<?php

namespace App\Jobs;

use App\Models\BulkIdCardJob;
use App\Models\Beneficiary;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Bus;

class GenerateBulkIdCards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 1;
    
    protected $bulkJobId;
    private $bulkJob;

    public function __construct(int $bulkJobId)
    {
        $this->bulkJobId = $bulkJobId;
    }

    public function handle(): void
    {
        DB::beginTransaction();
        try {
            // Debug: Check if bulkJobId exists
            Log::info('Processing bulk job', ['bulk_job_id' => $this->bulkJobId]);
            
            // Reload the bulk job to avoid serialization issues
            $this->bulkJob = BulkIdCardJob::findOrFail($this->bulkJobId);
            
            // Debug: Check if bulk job was loaded
            if (!$this->bulkJob) {
                throw new \Exception('Failed to load bulk job with ID: ' . $this->bulkJobId);
            }
            
            Log::info('Bulk job loaded', ['job_id' => $this->bulkJob->job_id]);
            
            // Mark job as started
            $this->bulkJob->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);
            
            Log::info('Job marked as processing');
            DB::commit();

            // Get beneficiaries based on generation criteria
            $beneficiaries = $this->getBeneficiaries();
            $totalRecords = $beneficiaries->count();
            
            if ($totalRecords === 0) {
                DB::beginTransaction();
                $this->bulkJob->markAsFailed('No beneficiaries found matching the criteria');
                DB::commit();
                return;
            }

            // Update total records
            DB::beginTransaction();
            $this->bulkJob->update(['total_records' => $totalRecords]);
            DB::commit();

            // Generate HTML for all beneficiaries
            $allHtml = $this->generateHtmlContent($beneficiaries, $totalRecords);

            // Create PDF file
            $fileName = 'bulk-id-cards-' . $this->bulkJob->job_id . '.pdf';
            $filePath = 'bulk-id-cards/' . $fileName;
            
            // Ensure directory exists
            $fullDirectory = storage_path('app/bulk-id-cards');
            if (!file_exists($fullDirectory)) {
                mkdir($fullDirectory, 0755, true);
            }
            
            // Generate PDF using Browsershot
            $tempPath = storage_path('app/' . $filePath);
            
            \Spatie\Browsershot\Browsershot::html($allHtml)
                ->timeout(120)
                ->setOption('landscape', false)
                ->paperSize(210, 297, 'mm')
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->noSandbox()
                ->waitUntilNetworkIdle()
                ->save($tempPath);

            // Get file size
            $fileSize = filesize($tempPath);

            // Mark job as completed
            DB::beginTransaction();
            $this->bulkJob->markAsCompleted($filePath, $fileName, $fileSize);
            DB::commit();

            Log::info('Bulk ID card generation completed', [
                'job_id' => $this->bulkJob->job_id,
                'total_records' => $totalRecords,
                'file_size' => $fileSize,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk ID card generation failed', [
                'job_id' => $this->bulkJobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($this->bulkJob)) {
                DB::beginTransaction();
                $this->bulkJob->markAsFailed($e->getMessage());
                DB::commit();
            }
        }
    }

    protected function getBeneficiaries()
    {
        $query = Beneficiary::with(['facility', 'spouse', 'children']);
        
        $criteria = $this->bulkJob->generation_criteria ?? [];
        $generationType = $this->bulkJob->generation_type;

        switch ($generationType) {
            case 'all':
                // No filters - get all beneficiaries
                break;
                
            case 'facility':
                if (isset($criteria['facility_id'])) {
                    $query->where('facility_id', $criteria['facility_id']);
                }
                break;
                
            case 'status':
                if (isset($criteria['status'])) {
                    $query->where('status', $criteria['status']);
                }
                break;
                
            case 'workplace':
                if (isset($criteria['workplace'])) {
                    $query->where('workplace', 'LIKE', '%' . $criteria['workplace'] . '%');
                }
                break;
                
            case 'custom_selection':
                if (isset($criteria['beneficiary_ids']) && is_array($criteria['beneficiary_ids'])) {
                    $query->whereIn('id', $criteria['beneficiary_ids']);
                }
                break;
        }

        // Apply additional filters
        if (isset($criteria['search'])) {
            $search = $criteria['search'];
            $query->where(function($q) use ($search) {
                $q->where('fullname', 'LIKE', "%{$search}%")
                  ->orWhere('boschma_no', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    protected function generateHtmlContent($beneficiaries, int $totalRecords)
    {
        // Convert logo to base64 once
        $logoBase64 = $this->getLogoBase64();
        
        // Start with spacing CSS
        $allHtml = '<style>.card-spacing { margin-bottom: 2mm; }</style>';
        $processedCount = 0;
        
        foreach ($beneficiaries as $beneficiary) {
            try {
                // Debug: Check if beneficiary is object or array
                if (!is_object($beneficiary)) {
                    throw new \Exception('Beneficiary is not an object: ' . gettype($beneficiary));
                }
                
                // Convert beneficiary photo to base64
                $beneficiaryPhotoBase64 = $this->getPhotoBase64($beneficiary->photo);
                
                // Convert spouse photo to base64
                $spousePhotoBase64 = null;
                if ($beneficiary->spouse && $beneficiary->spouse->photo) {
                    $spousePhotoBase64 = $this->getPhotoBase64($beneficiary->spouse->photo);
                }
                
                // Convert children photos to base64
                $childrenPhotosBase64 = [];
                if ($beneficiary->children) {
                    foreach ($beneficiary->children as $child) {
                        $childrenPhotosBase64[$child->id] = $this->getPhotoBase64($child->photo);
                    }
                }
                
                // Generate QR code for this beneficiary
                $qrCodeBase64 = $this->generateQrCode($beneficiary);
                
                // Generate HTML for this beneficiary
                $html = view('admin.beneficiaries.id-card-pdf-dompdf', compact(
                    'beneficiary', 
                    'logoBase64', 
                    'beneficiaryPhotoBase64', 
                    'spousePhotoBase64', 
                    'childrenPhotosBase64',
                    'qrCodeBase64'
                ))->render();
                
                $allHtml .= '<div class="card-spacing">' . $html . '</div>';
                
                // Update progress
                $processedCount++;
                $this->bulkJob->updateProgress($processedCount, $totalRecords);
                
                // Prevent memory issues for very large datasets
                if ($processedCount % 50 === 0) {
                    gc_collect_cycles();
                }
                
            } catch (\Exception $e) {
                Log::warning('Failed to process beneficiary for bulk ID card', [
                    'beneficiary_id' => is_object($beneficiary) ? $beneficiary->id : (is_array($beneficiary) ? $beneficiary['id'] ?? 'unknown' : 'unknown'),
                    'beneficiary_type' => gettype($beneficiary),
                    'error' => $e->getMessage(),
                ]);
                
                // Add to failed records
                $failed = $this->bulkJob->failed_records_list ?? [];
                $failed[] = [
                    'beneficiary_id' => is_object($beneficiary) ? $beneficiary->id : (is_array($beneficiary) ? $beneficiary['id'] ?? 'unknown' : 'unknown'),
                    'boschma_no' => is_object($beneficiary) ? $beneficiary->boschma_no : (is_array($beneficiary) ? $beneficiary['boschma_no'] ?? 'unknown' : 'unknown'),
                    'error' => $e->getMessage(),
                ];
                $this->bulkJob->update(['failed_records_list' => $failed]);
                $this->bulkJob->increment('failed_records');
            }
        }
        
        return $allHtml;
    }

    protected function getLogoBase64(): ?string
    {
        $logoPath = public_path('assets/img/brand/logo.png');
        
        if (!file_exists($logoPath)) {
            return null;
        }
        
        $logoData = base64_encode(file_get_contents($logoPath));
        return 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . $logoData;
    }

    protected function getPhotoBase64(?string $photoPath): ?string
    {
        if (!$photoPath) {
            return null;
        }
        
        $fullPath = storage_path('app/public/' . $photoPath);
        
        if (!file_exists($fullPath)) {
            return null;
        }
        
        $photoData = base64_encode(file_get_contents($fullPath));
        return 'data:image/' . pathinfo($fullPath, PATHINFO_EXTENSION) . ';base64,' . $photoData;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk ID card job failed permanently', [
            'job_id' => $this->bulkJob->job_id,
            'exception' => $exception->getMessage(),
        ]);

        $this->bulkJob->markAsFailed($exception->getMessage());
    }

    /**
     * Generate QR code for beneficiary
     */
    protected function generateQrCode($beneficiary): ?string
    {
        try {
            // Prepare beneficiary data for QR code
            $beneficiaryData = [
                'boschma_no' => $beneficiary->boschma_no,
                'fullname' => $beneficiary->fullname,
                'dp_no' => $beneficiary->dp_no,
                'nin' => $beneficiary->nin,
                'facility' => $beneficiary->facility->name ?? 'N/A',
                'created_at' => $beneficiary->created_at->format('Y-m-d'),
                'expires_at' => $beneficiary->created_at->addYears(5)->format('Y-m-d')
            ];

            // Add spouse data if exists
            if ($beneficiary->spouse) {
                $beneficiaryData['spouse'] = [
                    'name' => $beneficiary->spouse->fullname,
                    'boschma_no' => $beneficiary->spouse->boschma_no,
                    'nin' => $beneficiary->spouse->nin,
                    'gender' => $beneficiary->spouse->gender,
                    'dob' => $beneficiary->spouse->date_of_birth,
                    'facility' => $beneficiary->spouse->facility->name ?? 'N/A'
                ];
            }

            // Add children data if exists
            if ($beneficiary->children && $beneficiary->children->count() > 0) {
                $beneficiaryData['children'] = [];
                foreach ($beneficiary->children as $child) {
                    $beneficiaryData['children'][] = [
                        'name' => $child->fullname,
                        'boschma_no' => $child->boschma_no,
                        'nin' => $child->nin,
                        'gender' => $child->gender,
                        'dob' => $child->date_of_birth,
                        'facility' => $child->facility->name ?? 'N/A'
                    ];
                }
            }

            return QrCodeService::generateBeneficiaryQrCode($beneficiaryData);

        } catch (\Exception $e) {
            Log::warning('Failed to generate QR code for beneficiary', [
                'beneficiary_id' => $beneficiary->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
