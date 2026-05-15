<?php

namespace App\Imports;

use App\Models\Claim;
use App\Models\Staff;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClaimsImport implements ToCollection, WithHeadingRow
{
    protected $importResults = [
        'success' => 0,
        'failed' => 0,
        'errors' => []
    ];

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $staff = Staff::first();
        if (!$staff) {
            throw new \Exception('No staff found to assign as creator');
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because of header row and 0-based index
            
            try {
                // Validate required fields
                $validator = Validator::make($row->toArray(), [
                    'beneficiary_name' => 'required|string|max:255',
                    'boschma_id' => 'required|string|max:50',
                    'claim_type' => 'required|in:medical,pharmacy,hospitalization,diagnostic,emergency',
                    'healthcare_provider' => 'required|string|max:255',
                    'provider_type' => 'required|in:hospital,clinic,pharmacy,laboratory,diagnostic_center',
                    'service_date' => 'required|date',
                    'claim_amount' => 'required|numeric|min:0',
                    'phone_number' => 'nullable|string|max:20',
                    'nin' => 'nullable|string|size:11',
                    'diagnosis' => 'nullable|string',
                    'treatment_description' => 'nullable|string',
                    'additional_notes' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    $this->importResults['errors'][] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    $this->importResults['failed']++;
                    continue;
                }

                // Generate unique authorization code
                $authorizationCode = 'BLK-' . strtoupper(Str::random(8)) . '-' . date('Ymd');

                // Create claim
                Claim::create([
                    'authorization_code' => $authorizationCode,
                    'beneficiary_name' => $row['beneficiary_name'],
                    'boschma_id' => $row['boschma_id'],
                    'nin' => $row['nin'] ?? null,
                    'phone_number' => $row['phone_number'] ?? null,
                    'claim_type' => $row['claim_type'],
                    'healthcare_provider' => $row['healthcare_provider'],
                    'provider_type' => $row['provider_type'],
                    'service_date' => Carbon::parse($row['service_date']),
                    'claim_amount' => $row['claim_amount'],
                    'diagnosis' => $row['diagnosis'] ?? null,
                    'treatment_description' => $row['treatment_description'] ?? null,
                    'additional_notes' => $row['additional_notes'] ?? null,
                    'created_by' => $staff->id,
                    'updated_by' => $staff->id,
                ]);

                $this->importResults['success']++;

            } catch (\Exception $e) {
                $this->importResults['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                $this->importResults['failed']++;
            }
        }
    }

    /**
     * Get import results
     */
    public function getResults()
    {
        return $this->importResults;
    }
}
