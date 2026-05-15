<?php

namespace App\Imports;

use App\Models\Beneficiary;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Auth;

class BeneficiariesImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected $programId;
    protected $facilityId;
    protected $state;
    protected $lga;
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    public function __construct($programId, $facilityId, $state, $lga)
    {
        $this->programId = $programId;
        $this->facilityId = $facilityId;
        $this->state = $state;
        $this->lga = $lga;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Get boschma_number from the row
        $boschmaNo = $this->cleanStringField($row['boschma_number'] ?? null);
        
        // Skip if no boschma_number provided
        if (empty($boschmaNo)) {
            $this->skippedCount++;
            $this->errors[] = "Row skipped: Missing BOSCHMA number";
            return null;
        }

        // Check if boschma_no already exists
        if (Beneficiary::where('boschma_no', $boschmaNo)->exists()) {
            $this->skippedCount++;
            $this->errors[] = "Row skipped: BOSCHMA number '{$boschmaNo}' already exists";
            return null;
        }

        // Check if NIN already exists (if provided)
        $nin = $this->cleanStringField($row['nin'] ?? null);
        if (!empty($nin) && Beneficiary::where('nin', $nin)->exists()) {
            $this->skippedCount++;
            $this->errors[] = "Row skipped: NIN '{$nin}' already exists";
            return null;
        }

        $this->importedCount++;

        return new Beneficiary([
            'boschma_no' => $boschmaNo,
            'sequence_number' => null, // Leave sequence_number as null per user request
            'fullname' => $this->cleanStringField($row['name'] ?? null) ?? '',
            'date_of_birth' => $this->parseDate($row['dob'] ?? null),
            'gender' => $this->cleanGender($row['gender'] ?? null),
            'phone_no' => $this->cleanStringField($row['phone'] ?? null),
            'nin' => $nin,
            'marital_status' => $this->cleanStringField($row['marital_status'] ?? null),
            'ethnicity' => $this->cleanStringField($row['tribe'] ?? null),
            'religion' => $this->cleanStringField($row['religion'] ?? null),
            'category' => $this->cleanStringField($row['category'] ?? null),
            'program_id' => $this->programId,
            'facility_id' => $this->facilityId,
            'state' => $this->state,
            'lga' => $this->lga,
            'status' => 'active',
            'created_by' => Auth::guard('staff')->id(),
        ]);
    }

    /**
     * Validation rules for each row.
     */
    public function rules(): array
    {
        return [
            'boschma_number' => 'required',
            'name' => 'nullable',
            'dob' => 'nullable',
            'gender' => 'nullable',
            'phone' => 'nullable',
            'nin' => 'nullable',
            'marital_status' => 'nullable',
            'tribe' => 'nullable',
            'religion' => 'nullable',
            'category' => 'nullable',
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
        
        return trim(strval($value));
    }

    /**
     * Clean and normalize gender field.
     */
    private function cleanGender($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        $gender = strtolower(trim(strval($value)));
        
        if (in_array($gender, ['male', 'm'])) {
            return 'Male';
        }
        
        if (in_array($gender, ['female', 'f'])) {
            return 'Female';
        }
        
        return ucfirst($gender);
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
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($date - 2)->format('Y-m-d');
            }
            
            // Handle common date formats
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the count of imported records.
     */
    public function getImportedCount()
    {
        return $this->importedCount;
    }

    /**
     * Get the count of skipped records.
     */
    public function getSkippedCount()
    {
        return $this->skippedCount;
    }

    /**
     * Get import errors.
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
