<?php

namespace App\Imports;

use App\Models\IcdCode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class IcdCodesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // return new IcdCode([
        //     'id' => (string) Str::uuid(),
        //     'code' => $row['code'],
        //     'description' => $row['description'],
        //     'category' => $this->convertCategoryToDescription($row['category']),
        // ]);
        return new IcdCode([
            'id' => (string) Str::uuid(),
            'code' => $row['code'],
            'description' => $row['description'],
            'category' => '',
        ]);
    }

    /**
     * Convert category code range to description for backward compatibility
     */
    private function convertCategoryToDescription($category)
    {
        $categories = IcdCode::getCategories();
        
        // If it's already a description, return as is
        if (in_array($category, array_values($categories))) {
            return $category;
        }
        
        // If it's a code range, convert to description
        if (isset($categories[$category])) {
            return $categories[$category];
        }
        
        // Fallback: return original value
        return $category;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        // return [
        //     'code' => 'required|string|max:10',
        //     'description' => 'required|string|max:500',
        //     'category' => 'required|string|in:' . implode(',', array_map(function($item) {
        //         return '"' . str_replace('"', '""', $item) . '"';
        //     }, array_merge(array_keys(IcdCode::getCategories()), array_values(IcdCode::getCategories())))),
        // ];
        return [
            'code' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string',
        ];
    }
}
