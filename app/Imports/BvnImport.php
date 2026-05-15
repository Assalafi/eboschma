<?php

namespace App\Imports;

use App\Models\CivilServant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BvnImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    protected $updatedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Handle various column name variations
                $dpNo = null;
                $bvn = null;

                // Try different variations of dp_no column
                if (isset($row['dp_no'])) {
                    $dpNo = trim($row['dp_no']);
                } elseif (isset($row['dpno'])) {
                    $dpNo = trim($row['dpno']);
                } elseif (isset($row['dp_number'])) {
                    $dpNo = trim($row['dp_number']);
                } elseif (isset($row['dp'])) {
                    $dpNo = trim($row['dp']);
                }

                // Try different variations of bvn column
                if (isset($row['bvn'])) {
                    $bvn = trim($row['bvn']);
                }

                // Skip if both fields are empty (likely empty row)
                if (empty($dpNo) && empty($bvn)) {
                    continue;
                }

                // Validate required fields
                if (empty($dpNo)) {
                    $this->skippedCount++;
                    $this->errors[] = "Missing DP Number in row";
                    continue;
                }

                if (empty($bvn)) {
                    $this->skippedCount++;
                    $this->errors[] = "Missing BVN for DP '{$dpNo}'";
                    continue;
                }

                // Validate BVN length
                if (strlen($bvn) != 11) {
                    $this->skippedCount++;
                    $this->errors[] = "Invalid BVN length for DP '{$dpNo}' (must be 11 digits)";
                    continue;
                }

                // Find civil servant by dp_no
                $civilServant = CivilServant::where('dp_no', $dpNo)->first();

                if (!$civilServant) {
                    $this->skippedCount++;
                    $this->errors[] = "DP Number '{$dpNo}' not found in system";
                    continue;
                }

                // Update BVN
                $civilServant->bvn = $bvn;
                $civilServant->save();

                $this->updatedCount++;
                Log::info("BVN updated for DP: {$dpNo}");

            } catch (\Exception $e) {
                $this->skippedCount++;
                $dpNoForError = $dpNo ?? 'unknown';
                $this->errors[] = "Error updating DP '{$dpNoForError}': " . $e->getMessage();
                Log::error("BVN update error for DP {$dpNoForError}: " . $e->getMessage());
            }
        }
    }

    public function rules(): array
    {
        // Return empty rules since we handle validation manually in collection()
        // This allows the import to process all rows and skip invalid ones
        return [];
    }

    public function customValidationMessages()
    {
        return [];
    }

    public function onError(\Throwable $error)
    {
        $this->errors[] = $error->getMessage();
    }

    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
