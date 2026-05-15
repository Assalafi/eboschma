<?php

namespace App\Http\Controllers;

use App\Models\BeneficiaryCategory;
use Illuminate\Http\Request;

class BeneficiaryCategoryController extends Controller
{
    public function index()
    {
        $categories = BeneficiaryCategory::orderBy('name')->get();
        return view('admin.beneficiary-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.beneficiary-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:beneficiary_categories,name',
        ]);

        BeneficiaryCategory::create(['name' => $request->name]);

        return redirect()->route('beneficiary-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(BeneficiaryCategory $beneficiary_category)
    {
        return view('admin.beneficiary-categories.edit', ['category' => $beneficiary_category]);
    }

    public function update(Request $request, BeneficiaryCategory $beneficiary_category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:beneficiary_categories,name,' . $beneficiary_category->id,
        ]);

        $oldName = $beneficiary_category->name;
        $beneficiary_category->update(['name' => $request->name]);

        // Update all beneficiaries that had the old category name
        if ($oldName !== $request->name) {
            \App\Models\Beneficiary::where('category', $oldName)->update(['category' => $request->name]);
        }

        return redirect()->route('beneficiary-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(BeneficiaryCategory $beneficiary_category)
    {
        $count = \App\Models\Beneficiary::where('category', $beneficiary_category->name)->count();

        if ($count > 0) {
            return redirect()->route('beneficiary-categories.index')
                ->with('error', "Cannot delete: {$count} beneficiaries are using this category.");
        }

        $beneficiary_category->delete();

        return redirect()->route('beneficiary-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
