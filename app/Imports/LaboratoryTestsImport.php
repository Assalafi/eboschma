<?php

namespace App\Imports;

use App\Models\LaboratoryTest;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class LaboratoryTestsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new LaboratoryTest([
            'id' => (string) Str::uuid(),
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'sample_type' => $row['sample_type'],
            'price' => $row['price'],
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sample_type' => 'required|string|in:' . implode(',', array_keys(LaboratoryTest::getSampleTypes())),
            'price' => 'required|numeric|min:0',
        ];
    }
}
