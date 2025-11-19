<?php

namespace App\Imports;

use App\Models\Facility;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FacilitiesImport implements ToCollection, WithHeadingRow
{
    protected $importedCount = 0;
    protected $errors = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        Log::info('Starting facilities import process');
        Log::info('Total rows to process: ' . $collection->count());
        
        $validLGAs = array_map('strtolower', Facility::getBornoLGAs());
        $validTypes = array_map('strtolower', Facility::getFacilityTypes());
        
        Log::info('Valid LGAs loaded: ' . count($validLGAs) . ' LGAs');
        Log::info('Valid facility types loaded: ' . count($validTypes) . ' types');
        Log::debug('Valid LGAs: ' . implode(', ', Facility::getBornoLGAs()));
        Log::debug('Valid facility types: ' . implode(', ', Facility::getFacilityTypes()));

        foreach ($collection as $index => $row) {
            $rowNumber = $index + 2; // +2 because of header row and 0-based index

            // Skip empty rows
            if (empty($row['name']) && empty($row['lga']) && empty($row['ward'])) {
                Log::debug("Row {$rowNumber}: Skipping empty row");
                continue;
            }

            Log::debug("Row {$rowNumber}: Processing facility data", [
                'original_name' => $row['name'] ?? '',
                'original_lga' => $row['lga'] ?? '',
                'original_ward' => $row['ward'] ?? '',
                'original_type' => $row['type_optional'] ?? $row['type'] ?? ''
            ]);

            // Clean and prepare data
            $data = [
                'name' => $this->cleanString($row['name'] ?? ''),
                'lga' => $this->cleanString($row['lga'] ?? ''),
                'ward' => $this->cleanString($row['ward'] ?? ''),
                'type' => $this->cleanString($row['type_optional'] ?? $row['type'] ?? ''),
            ];

            Log::debug("Row {$rowNumber}: Cleaned data", $data);

            // Validate required fields
            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'lga' => 'required|string',
                'ward' => 'required|string|max:255',
                'type' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $validationErrors = $validator->errors()->all();
                Log::warning("Row {$rowNumber}: Validation failed", [
                    'errors' => $validationErrors,
                    'data' => $data
                ]);
                $this->errors[] = "Row {$rowNumber}: " . implode(', ', $validationErrors);
                continue;
            }

            Log::debug("Row {$rowNumber}: Validation passed, checking LGA match");

            // Validate LGA matches exactly (case-insensitive)
            $lgaLower = strtolower($data['lga']);
            $matchedLGA = null;
            
            foreach (Facility::getBornoLGAs() as $validLGA) {
                if (strtolower($validLGA) === $lgaLower) {
                    $matchedLGA = $validLGA;
                    break;
                }
            }

            if (!$matchedLGA) {
                Log::warning("Row {$rowNumber}: Invalid LGA", [
                    'provided_lga' => $data['lga'],
                    'valid_lgas' => Facility::getBornoLGAs()
                ]);
                $this->errors[] = "Row {$rowNumber}: Invalid LGA '{$data['lga']}'. Must be one of: " . implode(', ', Facility::getBornoLGAs());
                continue;
            }

            Log::info("Row {$rowNumber}: LGA matched successfully", [
                'provided_lga' => $data['lga'],
                'matched_lga' => $matchedLGA
            ]);

            // Validate type if provided (case-insensitive)
            $matchedType = null;
            if (!empty($data['type'])) {
                Log::debug("Row {$rowNumber}: Validating facility type", [
                    'provided_type' => $data['type']
                ]);
                
                $typeLower = strtolower($data['type']);
                
                foreach (Facility::getFacilityTypes() as $validType) {
                    if (strtolower($validType) === $typeLower) {
                        $matchedType = $validType;
                        break;
                    }
                }

                if (!$matchedType) {
                    Log::warning("Row {$rowNumber}: Invalid facility type", [
                        'provided_type' => $data['type'],
                        'valid_types' => Facility::getFacilityTypes()
                    ]);
                    $this->errors[] = "Row {$rowNumber}: Invalid facility type '{$data['type']}'. Must be one of: " . implode(', ', Facility::getFacilityTypes());
                    continue;
                }

                Log::info("Row {$rowNumber}: Facility type matched successfully", [
                    'provided_type' => $data['type'],
                    'matched_type' => $matchedType
                ]);
            } else {
                Log::debug("Row {$rowNumber}: No facility type provided, using null");
            }

            // Check for duplicates
            Log::debug("Row {$rowNumber}: Checking for duplicate facility");
            $existing = Facility::where('name', $data['name'])
                               ->where('lga', $matchedLGA)
                               ->where('ward', $data['ward'])
                               ->first();

            if ($existing) {
                Log::warning("Row {$rowNumber}: Duplicate facility found", [
                    'facility_name' => $data['name'],
                    'lga' => $matchedLGA,
                    'ward' => $data['ward'],
                    'existing_id' => $existing->id
                ]);
                $this->errors[] = "Row {$rowNumber}: Facility '{$data['name']}' in {$matchedLGA}, {$data['ward']} already exists.";
                continue;
            }

            Log::info("Row {$rowNumber}: No duplicate found, proceeding to create facility");

            try {
                Log::info("Row {$rowNumber}: Creating new facility", [
                    'name' => $data['name'],
                    'lga' => $matchedLGA,
                    'ward' => $data['ward'],
                    'type' => $matchedType
                ]);

                // Create facility with matched values
                $facility = Facility::create([
                    'name' => $data['name'],
                    'lga' => $matchedLGA, // Use exact match from model
                    'ward' => $data['ward'],
                    'type' => $matchedType, // Use exact match from model or null
                ]);

                $this->importedCount++;
                
                Log::info("Row {$rowNumber}: Facility created successfully", [
                    'facility_id' => $facility->id,
                    'facility_name' => $facility->name,
                    'total_imported' => $this->importedCount
                ]);
                
            } catch (\Exception $e) {
                Log::error("Row {$rowNumber}: Error creating facility", [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'data' => $data,
                    'stack_trace' => $e->getTraceAsString()
                ]);
                $this->errors[] = "Row {$rowNumber}: Error creating facility - " . $e->getMessage();
            }
        }

        // Final summary logging
        Log::info('Facilities import process completed', [
            'total_rows_processed' => $collection->count(),
            'facilities_imported' => $this->importedCount,
            'errors_count' => count($this->errors),
            'errors' => $this->errors
        ]);

        if ($this->importedCount > 0) {
            Log::info("Import successful: {$this->importedCount} facilities imported");
        }

        if (!empty($this->errors)) {
            Log::warning("Import completed with " . count($this->errors) . " errors", [
                'errors' => $this->errors
            ]);
        }
    }

    private function cleanString($value)
    {
        return trim(str_replace(['"', "'"], '', $value));
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
