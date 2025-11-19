<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionUpload extends Model
{
    protected $fillable = [
        'filename',
        'stored_filename',
        'file_path',
        'month',
        'year',
        'status',
        'total_rows',
        'processed_rows',
        'success_count',
        'failed_count',
        'error_log',
        'started_at',
        'completed_at',
        'uploaded_by'
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getPeriodAttribute()
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        return $months[$this->month] . ' ' . $this->year;
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }
}
