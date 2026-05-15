<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BulkIdCardJob extends Model
{
    protected $fillable = [
        'job_id',
        'title',
        'status',
        'total_records',
        'processed_records',
        'failed_records',
        'progress_percentage',
        'generation_type',
        'generation_criteria',
        'file_path',
        'file_name',
        'file_size',
        'generated_at',
        'user_id',
        'started_at',
        'completed_at',
        'expires_at',
        'error_message',
        'failed_records_list',
    ];

    protected $casts = [
        'generation_criteria' => 'array',
        'failed_records_list' => 'array',
        'progress_percentage' => 'decimal:2',
        'generated_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-secondary">Pending</span>',
            'processing' => '<span class="badge bg-primary">Processing</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
            'cancelled' => '<span class="badge bg-warning">Cancelled</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getProgressWidthAttribute(): string
    {
        return $this->progress_percentage . '%';
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) return 'N/A';
        
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsDownloadableAttribute(): bool
    {
        return $this->status === 'completed' 
            && $this->file_path 
            && file_exists(storage_path('app/' . $this->file_path))
            && !$this->is_expired;
    }

    public function getGenerationTypeLabelAttribute(): string
    {
        return match($this->generation_type) {
            'all' => 'All Beneficiaries',
            'filtered' => 'Filtered',
            'facility' => 'By Facility',
            'workplace' => 'By Workplace',
            'custom_selection' => 'Custom Selection',
            'status' => 'By Status',
            'program' => 'By Program',
            default => 'Unknown',
        };
    }

    public function getCriteriaDescriptionAttribute(): string
    {
        if (!$this->generation_criteria) return 'N/A';
        
        $criteria = $this->generation_criteria;
        $description = [];
        
        if (isset($criteria['facility_name'])) {
            $description[] = 'Facility: ' . $criteria['facility_name'];
        }
        
        if (isset($criteria['status'])) {
            $description[] = 'Status: ' . ucfirst($criteria['status']);
        }
        
        if (isset($criteria['program_name'])) {
            $description[] = 'Program: ' . $criteria['program_name'];
        }
        
        if (isset($criteria['card_type'])) {
            $description[] = 'Card: ' . $criteria['card_type'];
        }
        
        if (isset($criteria['enrollment_date_from'])) {
            $description[] = 'From: ' . $criteria['enrollment_date_from'];
        }
        
        if (isset($criteria['enrollment_date_to'])) {
            $description[] = 'To: ' . $criteria['enrollment_date_to'];
        }
        
        if (isset($criteria['search'])) {
            $description[] = 'Search: ' . $criteria['search'];
        }
        
        if (isset($criteria['count'])) {
            $description[] = 'Count: ' . $criteria['count'] . ' beneficiaries';
        }
        
        return implode(', ', $description) ?: 'Custom criteria';
    }

    public function updateProgress(int $processed, int $total): void
    {
        $this->processed_records = $processed;
        $this->total_records = $total;
        $this->progress_percentage = $total > 0 ? ($processed / $total) * 100 : 0;
        $this->save();
    }

    public function markAsCompleted(string $filePath, string $fileName, int $fileSize): void
    {
        $this->status = 'completed';
        $this->processed_records = $this->total_records;
        $this->progress_percentage = 100;
        $this->file_path = $filePath;
        $this->file_name = $fileName;
        $this->file_size = $fileSize;
        $this->generated_at = now();
        $this->completed_at = now();
        $this->expires_at = now()->addDays(2); // File expires in 2 days
        $this->save();
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->status = 'failed';
        $this->error_message = $errorMessage;
        $this->completed_at = now();
        $this->save();
    }

    public function markAsCancelled(): void
    {
        $this->status = 'cancelled';
        $this->completed_at = now();
        $this->save();
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}
