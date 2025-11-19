<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    protected $fillable = [
        'dp_no',
        'amount',
        'contributed',
        'month',
        'year',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'contributed' => 'decimal:2',
        'status' => 'boolean',
        'month' => 'integer',
        'year' => 'integer',
    ];

    /**
     * Get the month name
     */
    public function getMonthNameAttribute()
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        return $months[$this->month] ?? '';
    }

    /**
     * Get formatted period (Month Year)
     */
    public function getPeriodAttribute()
    {
        return $this->month_name . ' ' . $this->year;
    }
}
