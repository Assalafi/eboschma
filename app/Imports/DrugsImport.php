<?php

namespace App\Imports;

use App\Models\Drug;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DrugsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $importedCount = 0;
    private $errors = [];
    private $facilityId;

    /**
     * Constructor
     */
    public function __construct($facilityId)
    {
        $this->facilityId = $facilityId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Debug: Log the row data
            Log::info('Processing row: ' . json_encode($row));

            // Skip empty rows - check all required fields
            $requiredFields = ['name', 'dosage_form', 'strength', 'unit', 'unit_price'];
            $hasData = false;
            
            foreach ($requiredFields as $field) {
                if (!empty($row[$field]) && $row[$field] !== '' && $row[$field] !== null) {
                    $hasData = true;
                    break;
                }
            }
            
            if (!$hasData) {
                Log::info('Skipping empty row');
                return null;
            }

            // Clean and validate data
            $name = trim($row['name'] ?? '');
            $dosageForm = trim($row['dosage_form'] ?? '');
            $strength = trim($row['strength'] ?? '');
            $unit = trim($row['unit'] ?? '');
            $unitPrice = floatval($row['unit_price'] ?? 0);
            $description = trim($row['description'] ?? '');

            // Validate required fields
            if (empty($name)) {
                $this->errors[] = "Name field is required and cannot be empty";
                return null;
            }
            if (empty($dosageForm)) {
                $this->errors[] = "Dosage Form field is required and cannot be empty";
                return null;
            }
            if (empty($strength)) {
                $this->errors[] = "Strength field is required and cannot be empty";
                return null;
            }
            if (empty($unit)) {
                $this->errors[] = "Unit field is required and cannot be empty";
                return null;
            }

            // Check for existing drug with same specifications (master list)
            $existingDrug = Drug::where('name', $name)
                ->where('dosage_form', $dosageForm)
                ->where('strength', $strength)
                ->where('unit', $unit)
                ->first();

            if ($existingDrug) {
                $this->errors[] = "Drug with these specifications already exists: " . $name;
                return null;
            }

            $drug = new Drug([
                'id' => (string) Str::uuid(),
                'name' => $name,
                'description' => $description ?: null,
                'dosage_form' => $dosageForm,
                'strength' => $strength,
                'unit' => $unit,
                'unit_price' => $unitPrice,
            ]);
            
            $drug->save();
            $this->importedCount++;
            
            Log::info('Successfully imported drug: ' . $name);
            
            return $drug;
        } catch (\Exception $e) {
            Log::error('Row processing error: ' . $e->getMessage());
            $this->errors[] = "Row error: " . $e->getMessage();
            return null;
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'dosage_form' => 'required|string',
            'strength' => 'required|string',
            'unit' => 'required|string',
            'unit_price' => 'required|numeric|min:0',
        ];
    }

    /**
     * Prepare data for validation
     */
    public function prepareForValidation($data, $index)
    {
        // Ensure strength is always treated as string to handle Excel auto-formatting
        if (isset($data['strength'])) {
            $data['strength'] = (string) $data['strength'];
        }
        
        return $data;
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'name.required' => 'The drug name field is required.',
            'name.string' => 'The drug name must be a string.',
            'name.max' => 'The drug name may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'dosage_form.required' => 'The dosage form field is required.',
            'dosage_form.string' => 'The dosage form must be a string.',
            'dosage_form.max' => 'The dosage form may not be greater than 100 characters.',
            'strength.required' => 'The strength field is required.',
            'strength.string' => 'The strength field must be a string (e.g., "500mg", "50 mg", "250MG").',
            'strength.max' => 'The strength may not be greater than 100 characters.',
            'unit.required' => 'The unit field is required.',
            'unit.string' => 'The unit must be a string.',
            'unit.max' => 'The unit may not be greater than 50 characters.',
            'unit_price.required' => 'The unit price field is required.',
            'unit_price.numeric' => 'The unit price must be a number.',
            'unit_price.min' => 'The unit price must be at least 0.',
        ];
    }

    /**
     * Get the count of imported records
     *
     * @return int
     */
    public function getImportedCount()
    {
        return $this->importedCount;
    }

    /**
     * Get the errors encountered during import
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
