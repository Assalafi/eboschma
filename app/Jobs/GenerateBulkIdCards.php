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
use Illuminate\Contracts\Queue\ShouldBeUnique;

class GenerateBulkIdCards implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uniqueFor = 7200; // Lock for 2 hours

    public $timeout = 3600; // 1 hour timeout
    public $tries = 3; // Max 3 attempts to prevent infinite loops
    public $maxExceptions = 3; // Only fail after 3 actual exceptions
    public $backoff = [60, 120, 300]; // Wait 1min, 2min, 5min between retries
    public $failOnTimeout = true; // Fail immediately on timeout instead of retrying
    
    protected $bulkJobId;
    private $bulkJob;
    private $failedRecordsList = [];
    private $failedRecordsCount = 0;

    public function __construct(int $bulkJobId)
    {
        $this->bulkJobId = $bulkJobId;
    }

    public function uniqueId(): string
    {
        return 'bulk-id-card-' . $this->bulkJobId;
    }

    public function handle(): void
    {
        try {
            Log::info('Processing bulk job', ['bulk_job_id' => $this->bulkJobId]);
            
            // Reload the bulk job to avoid serialization issues
            $this->bulkJob = BulkIdCardJob::findOrFail($this->bulkJobId);
            
            if (!$this->bulkJob) {
                throw new \Exception('Failed to load bulk job with ID: ' . $this->bulkJobId);
            }
            
            Log::info('Bulk job loaded', ['job_id' => $this->bulkJob->job_id]);
            
            // Skip if already completed or cancelled (prevents duplicate runs)
            if (in_array($this->bulkJob->status, ['completed', 'cancelled', 'failed'])) {
                Log::info('Job already ' . $this->bulkJob->status . ', skipping', ['job_id' => $this->bulkJob->job_id]);
                return;
            }
            
            // Mark job as started (no wrapping transaction - single quick update)
            $this->bulkJob->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);
            
            Log::info('Job marked as processing');

            // Get beneficiaries count first (memory efficient)
            $totalRecords = $this->getBeneficiariesQuery()->count();
            
            if ($totalRecords === 0) {
                $this->bulkJob->markAsFailed('No beneficiaries found matching the criteria');
                return;
            }

            // Update total records (single quick update, no transaction needed)
            $this->bulkJob->update(['total_records' => $totalRecords]);

            // Ensure directory exists
            $fullDirectory = storage_path('app/bulk-id-cards');
            if (!file_exists($fullDirectory)) {
                mkdir($fullDirectory, 0755, true);
            }
            
            // Professional approach: Split large jobs into multiple PDFs
            $maxRecordsPerPdf = 50; // Safe limit for memory and Chrome stability
            $generateMultiplePdfs = $totalRecords > $maxRecordsPerPdf;
            
            if ($generateMultiplePdfs) {
                Log::info('Large job detected - splitting into multiple PDFs', [
                    'total_records' => $totalRecords,
                    'max_per_pdf' => $maxRecordsPerPdf
                ]);
                
                $pdfFiles = $this->generateMultiplePdfs($totalRecords, $maxRecordsPerPdf);
                
                // Store info about multiple files
                $filePath = 'bulk-id-cards/' . $this->bulkJob->job_id; // Directory
                $fileName = count($pdfFiles) . ' PDF files'; // Description
                $fileSize = array_sum(array_map('filesize', $pdfFiles));
                
                Log::info('Multiple PDFs generated successfully', [
                    'pdf_count' => count($pdfFiles),
                    'total_size' => $fileSize
                ]);
            } else {
                // Small job - single PDF (original approach)
                Log::info('Generating single PDF for ' . $totalRecords . ' records');
                $tempHtmlFile = $this->generateHtmlContentChunked($totalRecords);
                
                $fileName = 'bulk-id-cards-' . $this->bulkJob->job_id . '.pdf';
                $filePath = 'bulk-id-cards/' . $fileName;
                $tempPath = storage_path('app/' . $filePath);
                
                Log::info('Starting PDF generation', ['temp_file' => $tempHtmlFile]);
                
                if (!file_exists($tempHtmlFile)) {
                    throw new \Exception('Temp HTML file not found: ' . $tempHtmlFile);
                }
                
                $allHtml = file_get_contents($tempHtmlFile);
                Log::info('HTML loaded', ['size' => strlen($allHtml)]);
                
                $browsershot = \Spatie\Browsershot\Browsershot::html($allHtml)
                    ->timeout(300) // 5 minutes is enough for <100 records
                    ->setOption('landscape', false)
                    ->paperSize(210, 297, 'mm')
                    ->margins(10, 10, 10, 10)
                    ->showBackground()
                    ->noSandbox()
                    ->addChromiumArguments([
                        'disable-dev-shm-usage',
                        'disable-gpu',
                        'disable-extensions',
                        'disable-software-rasterizer',
                    ]);
                
                if (!$this->isLocalEnvironment()) {
                    $browsershot->setChromePath("/opt/chrome-linux64/chrome");
                }
                
                $browsershot->save($tempPath);
                
                // Clean up temp file
                @unlink($tempHtmlFile);
                unset($allHtml);
                gc_collect_cycles();
                
                $fileSize = filesize($tempPath);
            }

            // Mark job as completed (single quick update, reconnect for fresh connection)
            DB::reconnect();
            $this->bulkJob = BulkIdCardJob::findOrFail($this->bulkJobId);
            $this->bulkJob->markAsCompleted($filePath, $fileName, $fileSize);

            Log::info('Bulk ID card generation completed', [
                'job_id' => $this->bulkJob->job_id,
                'total_records' => $totalRecords,
                'file_size' => $fileSize,
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk ID card generation failed', [
                'job_id' => $this->bulkJobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Clean up any temp files left behind
            $this->cleanupTempFiles();

            if (isset($this->bulkJob)) {
                try {
                    DB::reconnect();
                    $this->bulkJob->markAsFailed($e->getMessage());
                } catch (\Exception $dbError) {
                    Log::error('Failed to mark job as failed', [
                        'job_id' => $this->bulkJobId,
                        'original_error' => $e->getMessage(),
                        'db_error' => $dbError->getMessage()
                    ]);
                }
            }
        }

        // Auto-cleanup: delete expired job files (older than 2 days)
        $this->cleanupExpiredJobs();
    }

    /**
     * Get the query builder for beneficiaries (does not execute query)
     */
    protected function getBeneficiariesQuery()
    {
        $query = Beneficiary::with(['facility', 'program', 'spouse', 'children']);
        
        $criteria = $this->bulkJob->generation_criteria ?? [];
        $generationType = $this->bulkJob->generation_type;

        switch ($generationType) {
            case 'all':
                // No filters - get all beneficiaries
                break;
            
            case 'filtered':
                // Apply all stored filter criteria
                if (isset($criteria['facility_id'])) {
                    $query->where('facility_id', $criteria['facility_id']);
                }
                if (isset($criteria['status'])) {
                    $query->where('status', $criteria['status']);
                }
                if (isset($criteria['workplace'])) {
                    $query->where('place_of_work', 'LIKE', '%' . $criteria['workplace'] . '%');
                }
                if (isset($criteria['program_id'])) {
                    $query->where('program_id', $criteria['program_id']);
                }
                if (isset($criteria['category'])) {
                    $query->where('category', $criteria['category']);
                }
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
                    $query->where('place_of_work', 'LIKE', '%' . $criteria['workplace'] . '%');
                }
                break;
                
            case 'program':
                if (isset($criteria['program_id'])) {
                    $query->where('program_id', $criteria['program_id']);
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
        
        // Filter by enrollment date range
        if (isset($criteria['enrollment_date_from'])) {
            $query->whereDate('created_at', '>=', $criteria['enrollment_date_from']);
        }
        
        if (isset($criteria['enrollment_date_to'])) {
            $query->whereDate('created_at', '<=', $criteria['enrollment_date_to']);
        }
        
        // Filter for beneficiaries with dependants
        if (isset($criteria['has_dependants']) && $criteria['has_dependants']) {
            $query->where(function($q) {
                $q->whereHas('spouse')
                  ->orWhereHas('children');
            });
        }

        return $query->orderBy('created_at', 'desc');
    }
    
    /**
     * Generate HTML content using chunked processing to prevent OOM
     * Writes to a temp file instead of keeping all HTML in memory
     */
    protected function generateHtmlContentChunked(int $totalRecords): string
    {
        // Create temp file for HTML output
        $tempFile = storage_path('app/temp-bulk-html-' . $this->bulkJobId . '.html');
        
        // Write initial HTML
        file_put_contents($tempFile, '<style>.card-spacing { margin-bottom: 1mm; }</style>');
        
        // Convert logo and signature to base64 once
        $logoBase64 = $this->getLogoBase64();
        $signBase64 = $this->getSignatureBase64();
        
        $processedCount = 0;
        $chunkSize = 25; // Process 25 records at a time to limit memory usage
        
        // Use chunk() to process in batches - much more memory efficient
        $this->getBeneficiariesQuery()->chunk($chunkSize, function ($beneficiaries) use ($tempFile, $logoBase64, $signBase64, $totalRecords, &$processedCount) {
            $chunkHtml = '';
            
            foreach ($beneficiaries as $beneficiary) {
                try {
                    if (!is_object($beneficiary)) {
                        throw new \Exception('Beneficiary is not an object: ' . gettype($beneficiary));
                    }
                    
                    // Convert beneficiary photo to base64
                    $beneficiaryPhotoBase64 = $this->getPhotoBase64($beneficiary->photo);
                    
                    // Generate QR code
                    $qrCodeBase64 = $this->generateQrCode($beneficiary);
                    
                    // Determine card format based on program
                    $isNoDependants = $beneficiary->program && !$beneficiary->program->has_dependant;
                    
                    if ($isNoDependants) {
                        // No-dependants card
                        $programLogoBase64 = ($beneficiary->program && $beneficiary->program->logo)
                            ? $this->getPhotoBase64($beneficiary->program->logo)
                            : null;
                        
                        $html = view('admin.beneficiaries.id-card-pdf-no-dependants', compact(
                            'beneficiary', 
                            'logoBase64', 
                            'signBase64',
                            'beneficiaryPhotoBase64', 
                            'qrCodeBase64',
                            'programLogoBase64'
                        ))->render();
                    } else {
                        // Dependants card (Formal Sector format)
                        $spousePhotoBase64 = null;
                        if ($beneficiary->spouse && $beneficiary->spouse->photo) {
                            $spousePhotoBase64 = $this->getPhotoBase64($beneficiary->spouse->photo);
                        }
                        
                        $childrenPhotosBase64 = [];
                        if ($beneficiary->children) {
                            foreach ($beneficiary->children as $child) {
                                $childrenPhotosBase64[$child->id] = $this->getPhotoBase64($child->photo);
                            }
                        }
                        
                        $html = view('admin.beneficiaries.id-card-pdf-dompdf', compact(
                            'beneficiary', 
                            'logoBase64', 
                            'beneficiaryPhotoBase64', 
                            'spousePhotoBase64', 
                            'childrenPhotosBase64',
                            'qrCodeBase64',
                            'signBase64'
                        ))->render();
                    }
                    
                    $chunkHtml .= '<div class="card-spacing">' . $html . '</div>';
                    $processedCount++;
                    
                } catch (\Exception $e) {
                    Log::warning('Failed to process beneficiary for bulk ID card', [
                        'beneficiary_id' => is_object($beneficiary) ? $beneficiary->id : 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                    
                    $this->failedRecordsList[] = [
                        'beneficiary_id' => is_object($beneficiary) ? $beneficiary->id : 'unknown',
                        'boschma_no' => is_object($beneficiary) ? $beneficiary->boschma_no : 'unknown',
                        'error' => $e->getMessage(),
                    ];
                    $this->failedRecordsCount++;
                }
            }
            
            // Append chunk HTML to file
            file_put_contents($tempFile, $chunkHtml, FILE_APPEND);
            
            // Update progress after each chunk
            try {
                DB::reconnect();
                $updateData = [
                    'processed_records' => $processedCount,
                    'progress_percentage' => ($processedCount / $totalRecords) * 100,
                ];
                
                if ($this->failedRecordsCount > 0) {
                    $updateData['failed_records'] = $this->failedRecordsCount;
                    $updateData['failed_records_list'] = $this->failedRecordsList;
                }
                
                $this->bulkJob->update($updateData);
            } catch (\Exception $dbError) {
                Log::warning('Failed to update progress', [
                    'processed' => $processedCount,
                    'error' => $dbError->getMessage()
                ]);
            }
            
            // Free memory after each chunk
            unset($chunkHtml);
            gc_collect_cycles();
            
            return true; // Continue processing
        });
        
        return $tempFile;
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

    protected function getSignatureBase64(): ?string
    {
        $signPath = public_path('assets/img/brand/sign.png');
        
        if (!file_exists($signPath)) {
            return null;
        }
        
        $signData = base64_encode(file_get_contents($signPath));
        return 'data:image/' . pathinfo($signPath, PATHINFO_EXTENSION) . ';base64,' . $signData;
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

    /**
     * Get Chrome path based on environment
     */
    protected function getChromePath(): ?string
    {
        // Check if we're on localhost/development
        if ($this->isLocalEnvironment()) {
            // Common Chrome paths for different operating systems
            $paths = [
                'darwin' => [ // macOS
                    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
                    '/Applications/Chromium.app/Contents/MacOS/Chromium',
                    '/usr/bin/google-chrome-stable',
                    '/usr/bin/google-chrome',
                ],
                'linux' => [ // Linux
                    '/usr/bin/google-chrome-stable',
                    '/usr/bin/google-chrome',
                    '/usr/bin/chromium-browser',
                    '/usr/bin/chromium',
                    '/snap/bin/chromium',
                ],
                'win' => [ // Windows
                    'C:\Program Files\Google\Chrome\Application\chrome.exe',
                    'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
                    'C:\Users\%USERNAME%\AppData\Local\Google\Chrome\Application\chrome.exe',
                ],
            ];
            
            $os = strtolower(PHP_OS);
            foreach ($paths as $system => $systemPaths) {
                if (strpos($os, $system) === 0) {
                    foreach ($systemPaths as $path) {
                        if (file_exists(str_replace('%USERNAME%', getenv('USERNAME') ?? '', $path))) {
                            return $path;
                        }
                    }
                }
            }
        } else {
            // Production server paths - prioritize your working path first
            $productionPaths = [
                '/opt/chrome-linux64/chrome',  // Your working live server path - PRIORITY #1
                '/usr/bin/google-chrome-stable',
                '/usr/bin/google-chrome',
                '/usr/bin/chromium-browser',
                '/usr/bin/chromium',
                '/usr/local/bin/chrome',
                '/usr/local/bin/chromium',
            ];
            
            foreach ($productionPaths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    return $path;
                }
            }
        }
        
        // IMPORTANT: Don't use PATH search on production - it finds snap versions first
        // Only use PATH search on localhost where snap versions work fine
        if ($this->isLocalEnvironment()) {
            $chromeInPath = shell_exec('which google-chrome 2>/dev/null || which chromium-browser 2>/dev/null || which chrome 2>/dev/null');
            if ($chromeInPath && trim($chromeInPath)) {
                return trim($chromeInPath);
            }
        }
        
        // Last resort: try to download and use Chrome headless shell for production
        if (!$this->isLocalEnvironment()) {
            $chromeShellPath = '/opt/chrome-headless-shell/chrome-headless-shell-linux';
            if (file_exists($chromeShellPath)) {
                return $chromeShellPath;
            }
            
            // Log warning about missing Chrome
            Log::warning('Chrome/Chromium not found. Please install Google Chrome or Chromium on the server.', [
                'server' => request()->getHost(),
                'paths_checked' => $productionPaths ?? [],
            ]);
        }
        
        return null; // Let Browsershot use its default
    }
    
    /**
     * Check if running in local/development environment
     */
    protected function isLocalEnvironment(): bool
    {
        // Check environment
        if (app()->environment(['local', 'development', 'testing'])) {
            return true;
        }
        
        // Check common localhost indicators
        $host = request()->getHost();
        $localhostIndicators = [
            'localhost',
            '127.0.0.1',
            '0.0.0.0',
            '10.0.0',
            '192.168.',
            '172.16.',
            '.local',
            '.test',
            '.dev',
            '.xampp',
            '.wamp',
            '.mamp',
        ];
        
        foreach ($localhostIndicators as $indicator) {
            if (strpos($host, $indicator) !== false) {
                return true;
            }
        }
        
        // Check server software (XAMPP, WAMP, etc.)
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
        if (stripos($serverSoftware, 'xampp') !== false || 
            stripos($serverSoftware, 'wamp') !== false ||
            stripos($serverSoftware, 'mamp') !== false) {
            return true;
        }
        
        // Check if running from common development directories
        $projectPath = base_path();
        $devPaths = [
            '/Applications/XAMPP/',
            '/Applications/MAMP/',
            '/Applications/WAMP/',
            '/var/www/html/',
            '/home/vagrant/',
            '/Users/',
        ];
        
        foreach ($devPaths as $path) {
            if (strpos($projectPath, $path) === 0) {
                return true;
            }
        }
        
        return false;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk ID card job failed permanently', [
            'bulk_job_id' => $this->bulkJobId,
            'exception' => $exception->getMessage(),
        ]);

        // Clean up any temp files left behind
        $this->cleanupTempFiles();

        // Load the bulk job from database since $this->bulkJob may be null
        try {
            $bulkJob = BulkIdCardJob::find($this->bulkJobId);
            if ($bulkJob) {
                $bulkJob->markAsFailed($exception->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Failed to mark bulk job as failed', [
                'bulk_job_id' => $this->bulkJobId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clean up temp files for this job
     */
    protected function cleanupTempFiles(): void
    {
        try {
            $storagePath = storage_path('app');
            
            // Delete temp HTML files
            foreach (glob($storagePath . '/temp-chunk-html-' . $this->bulkJobId . '-*.html') as $file) {
                @unlink($file);
            }
            foreach (glob($storagePath . '/temp-bulk-html-' . $this->bulkJobId . '.html') as $file) {
                @unlink($file);
            }
            // Delete temp batch PDFs
            foreach (glob($storagePath . '/temp-batch-' . $this->bulkJobId . '-*.pdf') as $file) {
                @unlink($file);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup temp files', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Auto-cleanup expired job files to prevent disk bloat
     */
    protected function cleanupExpiredJobs(): void
    {
        try {
            // Find completed jobs older than 2 days with files still on disk
            $expiredJobs = BulkIdCardJob::where('status', 'completed')
                ->where('completed_at', '<', now()->subDays(2))
                ->whereNotNull('file_path')
                ->get();

            foreach ($expiredJobs as $expiredJob) {
                $fullPath = storage_path('app/' . $expiredJob->file_path);
                
                if (is_dir($fullPath)) {
                    // Delete all PDFs in directory
                    foreach (glob($fullPath . '/*.pdf') as $file) {
                        @unlink($file);
                    }
                    @rmdir($fullPath);
                } elseif (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                
                // Clear file reference
                $expiredJob->update(['file_path' => null, 'file_name' => null, 'file_size' => null]);
                
                Log::info('Cleaned up expired job files', ['job_id' => $expiredJob->job_id]);
            }

            // Delete old temp files (any temp files older than 1 day)
            $storagePath = storage_path('app');
            foreach (glob($storagePath . '/temp-chunk-html-*.html') as $file) {
                if (filemtime($file) < time() - 86400) @unlink($file);
            }
            foreach (glob($storagePath . '/temp-bulk-html-*.html') as $file) {
                if (filemtime($file) < time() - 86400) @unlink($file);
            }
            foreach (glob($storagePath . '/temp-batch-*.pdf') as $file) {
                if (filemtime($file) < time() - 86400) @unlink($file);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup expired jobs', ['error' => $e->getMessage()]);
        }
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
                    'name' => $beneficiary->spouse->name,
                    'boschma_no' => $beneficiary->spouse->boschma_no,
                    'nin' => $beneficiary->spouse->nin,
                    'gender' => $beneficiary->spouse->gender,
                    'dob' => $beneficiary->spouse->dob,
                    'facility' => $beneficiary->spouse->facility->name ?? 'N/A'
                ];
            }

            // Add children data if exists
            if ($beneficiary->children && $beneficiary->children->count() > 0) {
                $beneficiaryData['children'] = [];
                foreach ($beneficiary->children as $child) {
                    $beneficiaryData['children'][] = [
                        'name' => $child->name,
                        'boschma_no' => $child->boschma_no,
                        'nin' => $child->nin,
                        'gender' => $child->gender,
                        'dob' => $child->dob,
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
    
    /**
     * Generate multiple PDFs for large datasets
     * Each PDF contains a safe number of records (max 100)
     */
    protected function generateMultiplePdfs(int $totalRecords, int $maxRecordsPerPdf): array
    {
        $pdfFiles = [];
        $totalChunks = ceil($totalRecords / $maxRecordsPerPdf);
        
        // Create subdirectory for this job's PDFs
        $jobDirectory = storage_path('app/bulk-id-cards/' . $this->bulkJob->job_id);
        if (!file_exists($jobDirectory)) {
            mkdir($jobDirectory, 0755, true);
        }
        
        $offset = 0;
        $chunkNumber = 1;
        
        while ($offset < $totalRecords) {
            $limit = min($maxRecordsPerPdf, $totalRecords - $offset);
            
            Log::info('Generating PDF chunk', [
                'chunk' => $chunkNumber,
                'total_chunks' => $totalChunks,
                'offset' => $offset,
                'limit' => $limit
            ]);
            
            // Get beneficiaries for this chunk
            $beneficiaries = $this->getBeneficiariesQuery()
                ->offset($offset)
                ->limit($limit)
                ->get();
            
            // Generate HTML for this chunk
            $tempHtmlFile = $this->generateHtmlForChunk($beneficiaries, $chunkNumber);
            
            // Generate PDF for this chunk
            $pdfFileName = sprintf(
                'bulk-id-cards-%s-part-%d-of-%d.pdf',
                $this->bulkJob->job_id,
                $chunkNumber,
                $totalChunks
            );
            $pdfPath = $jobDirectory . '/' . $pdfFileName;
            
            if (!file_exists($tempHtmlFile)) {
                throw new \Exception('Temp HTML file not found: ' . $tempHtmlFile);
            }
            
            $html = file_get_contents($tempHtmlFile);
            
            $browsershot = \Spatie\Browsershot\Browsershot::html($html)
                ->timeout(300) // 5 minutes per chunk
                ->setOption('landscape', false)
                ->paperSize(210, 297, 'mm')
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->noSandbox()
                ->addChromiumArguments([
                    'disable-dev-shm-usage',
                    'disable-gpu',
                    'disable-extensions',
                    'disable-software-rasterizer',
                ]);
            
            if (!$this->isLocalEnvironment()) {
                $browsershot->setChromePath("/opt/chrome-linux64/chrome");
            }
            
            $browsershot->save($pdfPath);
            $pdfFiles[] = $pdfPath;
            
            // Clean up
            @unlink($tempHtmlFile);
            unset($html);
            gc_collect_cycles();
            
            // Update progress
            $processedSoFar = min($offset + $limit, $totalRecords);
            $progress = ($processedSoFar / $totalRecords) * 100;
            
            DB::reconnect();
            $this->bulkJob->update([
                'processed_records' => $processedSoFar,
                'progress_percentage' => $progress,
            ]);
            
            Log::info('PDF chunk completed', [
                'chunk' => $chunkNumber,
                'progress' => round($progress, 1) . '%',
                'file' => $pdfFileName
            ]);
            
            $offset += $maxRecordsPerPdf;
            $chunkNumber++;
        }
        
        return $pdfFiles;
    }
    
    /**
     * Generate HTML content for a specific chunk of beneficiaries
     */
    protected function generateHtmlForChunk($beneficiaries, int $chunkNumber): string
    {
        $tempFile = storage_path('app/temp-chunk-html-' . $this->bulkJobId . '-' . $chunkNumber . '.html');
        
        // Write initial HTML
        file_put_contents($tempFile, '<style>.card-spacing { margin-bottom: 1mm; }</style>');
        
        // Convert logo and signature to base64 once
        $logoBase64 = $this->getLogoBase64();
        $signBase64 = $this->getSignatureBase64();
        
        foreach ($beneficiaries as $beneficiary) {
            try {
                // Convert beneficiary photo to base64
                $beneficiaryPhotoBase64 = $this->getPhotoBase64($beneficiary->photo);
                
                // Generate QR code
                $qrCodeBase64 = $this->generateQrCode($beneficiary);
                
                // Determine card format based on program
                $isNoDependants = $beneficiary->program && !$beneficiary->program->has_dependant;
                
                if ($isNoDependants) {
                    // No-dependants card
                    $programLogoBase64 = ($beneficiary->program && $beneficiary->program->logo)
                        ? $this->getPhotoBase64($beneficiary->program->logo)
                        : null;
                    
                    $html = view('admin.beneficiaries.id-card-pdf-no-dependants', compact(
                        'beneficiary',
                        'logoBase64',
                        'signBase64',
                        'beneficiaryPhotoBase64',
                        'qrCodeBase64',
                        'programLogoBase64'
                    ))->render();
                } else {
                    // Dependants card (Formal Sector format)
                    $spousePhotoBase64 = null;
                    if ($beneficiary->spouse && $beneficiary->spouse->photo) {
                        $spousePhotoBase64 = $this->getPhotoBase64($beneficiary->spouse->photo);
                    }
                    
                    $childrenPhotosBase64 = [];
                    if ($beneficiary->children) {
                        foreach ($beneficiary->children as $child) {
                            $childrenPhotosBase64[$child->id] = $this->getPhotoBase64($child->photo);
                        }
                    }
                    
                    $html = view('admin.beneficiaries.id-card-pdf-dompdf', compact(
                        'beneficiary',
                        'logoBase64',
                        'beneficiaryPhotoBase64',
                        'spousePhotoBase64',
                        'childrenPhotosBase64',
                        'qrCodeBase64',
                        'signBase64'
                    ))->render();
                }
                
                file_put_contents($tempFile, '<div class="card-spacing">' . $html . '</div>', FILE_APPEND);
                
            } catch (\Exception $e) {
                Log::warning('Failed to process beneficiary in chunk', [
                    'beneficiary_id' => $beneficiary->id,
                    'chunk' => $chunkNumber,
                    'error' => $e->getMessage(),
                ]);
                
                $this->failedRecordsList[] = [
                    'beneficiary_id' => $beneficiary->id,
                    'boschma_no' => $beneficiary->boschma_no,
                    'error' => $e->getMessage(),
                ];
                $this->failedRecordsCount++;
            }
        }
        
        return $tempFile;
    }
}
