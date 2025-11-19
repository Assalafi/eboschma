<?php

namespace App\Imports;

use App\Models\CivilServant;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class CivilServantsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Convert dp_no to string and preserve leading zeros
        $dpNo = $this->cleanStringField($row['dp_no'] ?? '');
        
        // Skip if empty or DP number already exists
        if (empty($dpNo) || CivilServant::where('dp_no', $dpNo)->exists()) {
            return null;
        }
        
        return new CivilServant([
            'dp_no' => $dpNo,
            'nin' => $this->cleanStringField($row['nin'] ?? null),
            'bvn' => $this->cleanStringField($row['bvn'] ?? null),
            'fullname' => $this->cleanStringField($row['fullname'] ?? null) ?? '',
            'dob' => $this->parseDate($row['dob']),
            'state' => $this->cleanStringField($row['state'] ?? null),
            'lga' => $this->cleanStringField($row['lga'] ?? null),
            'gender' => $this->cleanStringField($row['gender'] ?? null) ?? '',
            'mda' => $this->cleanStringField($row['mda'] ?? null) ?? '',
        ]);
    }

    /**
     * Validation rules for each row.
     * Only dp_no is required - all other fields are optional.
     * Note: dp_no accepts string or numeric to handle Excel's automatic type conversion
     */
    public function rules(): array
    {
        return [
            'dp_no' => 'required', // Accept both string and numeric
            'nin' => 'nullable',
            'bvn' => 'nullable', 
            'fullname' => 'nullable',
            'dob' => 'nullable',
            'state' => 'nullable',
            'lga' => 'nullable',
            'gender' => 'nullable|in:Male,Female,male,female',
            'mda' => 'nullable',
        ];
    }

    /**
     * Clean and convert field to string, handling various Excel data types.
     */
    private function cleanStringField($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        // Convert various data types to string
        return trim(strval($value));
    }

    /**
     * Parse date from various formats.
     */
    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            // Handle Excel date serial numbers
            if (is_numeric($date)) {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($date - 2);
            }
            
            // Handle common date formats
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
