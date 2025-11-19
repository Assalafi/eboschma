<?php

namespace App\Models;

use App\Models\DepartmentFund;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory;

    public $incrementing = false;  // Disable auto-incrementing
    protected $keyType = 'string';  // Set primary key type to string

    protected static function boot()
    {
        parent::boot();

        // Automatically generate a UUID when creating a new model
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // fields that can be mass assigned
    protected $fillable = ['code', 'name', 'enrollee_amount', 'enrollee_rate', 'image', 'user', 'level', 'status'];

    // has many relationship with DepartmentActivity
    public function departmentActivities()
    {
        return $this->hasMany(DepartmentActivity::class);
    }

    /**
     * Get the department funds for the current session and sector
     */
    public function currentFund()
    {
        return $this
            ->hasOne(DepartmentFund::class)
            ->where('session', session('session', '2025'))
            ->where('sector', session('sector', 'basic'));
    }

    /**
     * Get all department funds
     */
    public function funds()
    {
        return $this->hasMany(DepartmentFund::class);
    }

    /**
     * Get total credit for a department for a specific month and year
     *
     * @param int $month
     * @param int|null $year
     * @return float
     */
    public function getMonthlyCredit($month = null, $year = null)
    {
        // Use filter_month from session and session('session') for year
        $month = $month ?? session('filter_month', date('m'));
        $year = $year ?? session('session', '2025');

        return $this
            ->departmentActivities()
            ->where(['amount_type' => 'Cr', 'session' => session('session', '2025'), 'sector' => session('sector', 'basic')])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');
    }

    /**
     * Get total debit for a department for a specific month and year
     *
     * @param int $month
     * @param int|null $year
     * @return float
     */
    public function getMonthlyDebit($month = null, $year = null)
    {
        // Use filter_month from session and session('session') for year
        $month = $month ?? session('filter_month', date('m'));
        $year = $year ?? session('session', '2025');

        return $this
            ->departmentActivities()
            ->where(['amount_type' => 'Dr', 'session' => session('session', '2025'), 'sector' => session('sector', 'basic')])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');
    }

    /**
     * Get the monthly allocation (annual budget divided by 12)
     *
     * @return float
     */
    public function getMonthlyAllocation()
    {
        if ($this->currentFund) {
            return $this->currentFund->amount / 12;
        }
        return 0;
    }

    /**
     * Calculate carry-over budget from previous months
     * If a department doesn't spend its monthly allocation, the remainder carries over
     *
     * @param int $month Month to calculate carry-over for (1-12)
     * @param int|null $year Year to calculate carry-over for
     * @return float Total carry-over amount from previous months
     */
    public function getCarryOverBudget($month = null, $year = null)
    {
        // Use filter_month from session and session('session') for year
        $month = $month ?? session('filter_month', date('m'));
        $year = $year ?? session('session', '2025');
        $currentMonth = (int) $month;

        // No carry-over for January
        if ($currentMonth <= 1) {
            return 0;
        }

        $monthlyAllocation = $this->getMonthlyAllocation();
        $carryOver = 0;

        // Calculate carry-over from each previous month
        for ($i = 1; $i < $currentMonth; $i++) {
            $monthFormatted = str_pad($i, 2, '0', STR_PAD_LEFT);
            $credits = $this->getMonthlyCredit($monthFormatted, $year);
            $debits = $this->getMonthlyDebit($monthFormatted, $year);

            // Monthly allocation plus any carried over amount from previous month
            $availableForMonth = $monthlyAllocation + $carryOver;

            // Actual spending this month (credits minus debits)
            $spentThisMonth = $debits - $credits;

            // If spent less than allocation, carry over the difference
            $unusedBudget = $availableForMonth - $spentThisMonth;
            if ($unusedBudget > 0) {
                $carryOver = $unusedBudget;
            } else {
                $carryOver = 0;  // If overspent, no carry-over
            }
        }

        return $carryOver;
    }

    /**
     * Calculate monthly balance based on sequence of months
     * Includes carry-over from previous months
     *
     * @param int $month
     * @param int|null $year
     * @return float
     */
    public function getMonthlyBalance($month = null, $year = null)
    {
        // Use filter_month from session and session('session') for year
        $month = $month ?? session('filter_month', date('m'));
        $year = $year ?? session('session', '2025');
        $currentMonth = (int) $month;

        // Base monthly allocation
        $monthlyAllocation = $this->getMonthlyAllocation();

        // Get carry-over from previous months
        $carryOver = $this->getCarryOverBudget($month, $year);

        // Credits and debits for this month
        $credits = $this->getMonthlyCredit($month, $year);
        $debits = $this->getMonthlyDebit($month, $year);

        // Total available for this month = monthly allocation + carry-over + credits - debits
        return $monthlyAllocation + $carryOver + $credits - $debits;
    }
}
