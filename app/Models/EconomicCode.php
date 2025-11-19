<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\DepartmentFund;
use App\Models\DepartmentActivity;

class EconomicCode extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    protected $table = 'economic_codes';

    protected $fillable = [
        'code',
        'code_id',
        'description',
        'level',
        'sector',
        'user',
        'status'
    ];

    public function departmentActivities()
    {
        return $this->hasMany(DepartmentActivity::class, 'code_id', 'code_id');
    }

    public function approvedBudgets()
    {
        return $this
            ->hasMany(ApprovedBudget::class, 'code_id', 'code_id')
            ->where('sector', $this->sector);
    }

    public function getTotalCr($month = null, $year = null, $sector = null)
    {
        // Get amount from DepartmentActivity
        $query = DepartmentActivity::where('amount_type', 'Cr')
            ->where('code', 'like', $this->code . '%');

        if ($month && $year) {
            $query
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        }

        if ($sector) {
            $query->where('sector', $sector);
        } else {
            $query->where('sector', $this->sector);
        }

        $activityAmount = $query->sum('amount');
        
        // Get amount from DepartmentFund for the selected year and sector
        $fundSector = $sector ?: $this->sector;
        $fundQuery = DepartmentFund::where('session', $year)
            ->where('sector', $fundSector)
            ->whereHas('department', function($query) {
                $query->where('code', $this->code);
            });
            
        $fundAmount = $fundQuery->sum('amount');
        
        return $activityAmount + $fundAmount;
    }

    public function getTotalDr($month = null, $year = null, $sector = null)
    {
        $query = DepartmentActivity::where('amount_type', 'Dr')
            ->where('code', 'like', $this->code . '%');

        if ($month && $year) {
            $query
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        }

        if ($sector) {
            $query->where('sector', $sector);
        } else {
            $query->where('sector', $this->sector);
        }

        return $query->sum('amount');
    }

    /**
     * Get the approved budget amount for a specific session/year
     * considering both code and sector
     *
     * @param string $session
     * @return float
     */
    public function getApprovedBudget($session, $sector = null)
    {
        $budget = $this
            ->approvedBudgets()
            ->where(['session' => $session, 'sector' => $sector])
            ->first();

        return $budget ? $budget->amount : 0;
    }

    /**
     * Get the remaining budget balance for a sector
     *
     * @param string $session The budget session/year
     * @param int|null $month Optional month filter
     * @param int|null $year Optional year filter for transactions
     * @param string|null $sector Optional sector override
     * @return float
     */
    public function getRemainingBalance($session, $month = null, $year = null, $sector = null)
    {
        $approvedBudget = $this->getApprovedBudget($session);
        $totalCr = $this->getTotalCr($month, $year, $sector);
        $totalDr = $this->getTotalDr($month, $year, $sector);

        return $approvedBudget + $totalCr - $totalDr;
    }

    /**
     * Get the cumulative remaining balance up to specified month
     * for a specific sector
     *
     * @param string $session Budget session/year
     * @param int $month Target month
     * @param int|null $year Optional transaction year
     * @param string|null $sector Optional sector override
     * @return float
     */
    public function getCumulativeRemainingBalance($session, $month, $year = null, $sector = null)
    {
        $approvedBudget = $this->getApprovedBudget($session);
        $totalCr = 0;
        $totalDr = 0;
        $targetYear = $year ?? explode('-', $session)[0];  // Default to first year of session

        for ($m = 1; $m <= $month; $m++) {
            $totalCr += $this->getTotalCr($m, $targetYear, $sector);
            $totalDr += $this->getTotalDr($m, $targetYear, $sector);
        }

        return $approvedBudget + $totalCr - $totalDr;
    }

    /**
     * Get monthly net activity (Cr - Dr) for a sector
     *
     * @param int $month
     * @param int $year
     * @param string|null $sector
     * @return float
     */
    public function getMonthlyRemainingBalance($month, $year, $sector = null)
    {
        return $this->getTotalCr($month, $year, $sector) - $this->getTotalDr($month, $year, $sector);
    }
}
