<?php

namespace App\Imports;

use App\Models\ApprovedBudget;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;

class ApprovedBudgetImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
{
    $code = $this->cleanValue($row['code'] ?? $row['account_code'] ?? null);
    $session = session('session', '2025');
    $sector = session('sector', 'basic');
    $amount = $this->cleanValue($row['amount'] ?? 0);

    // Check if a record with this code and session already exists
    $existingBudget = ApprovedBudget::where('code', $code)
                                  ->where(['session' => $session, 'sector' => $sector])
                                  ->first();

    if ($existingBudget) {
        // Update the existing record's amount
        $existingBudget->update(['amount' => $amount]);
        return null; // Return null since we're updating, not creating
    }

    // Create new record if it doesn't exist
    return new ApprovedBudget([
        'code_id' => $sector . $code,
        'code' => $code,
        'amount' => $amount,
        'session' => $session,
        'sector' => $sector,
    ]);
}

    private function cleanValue($value)
    {
        if (is_string($value)) {
            return trim($value);
        }
        return $value;
    }

    public function rules(): array
    {
        return [
            '*.amount' => 'required|numeric|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.code.required' => 'The code field is required in all rows',
            '*.amount.required' => 'The amount field is required in all rows',
        ];
    }
}
