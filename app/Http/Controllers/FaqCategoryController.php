<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\DataTables\FaqCategoryDataTable;

class FaqCategoryController extends Controller
{
    /**
     * Display a listing of the FAQ categories.
     */
    public function index(FaqCategoryDataTable $dataTable)
    {
        return $dataTable->render('faq_categories.index');
    }

    /**
     * Display the form to add FAQ category.
     */
    public function create()
    {
        return view('faq_categories.create');
    }

    /**
     * Store a newly created FAQ category.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return redirect()
                ->route('faq-categories.index')
                ->withErrors('You do not have permission to perform this action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:faq_categories,name',
        ]);

        try {
            FaqCategory::create($validated);

            return redirect()
                ->route('faq-categories.index')
                ->with('success', 'FAQ category created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create FAQ category.']);
        }
    }

    /**
     * Display the form to edit FAQ category.
     */
    public function edit($id)
    {
        $category = FaqCategory::findOrFail($id);
        return view('faq_categories.edit', compact('category'));
    }

    /**
     * Update the specified FAQ category.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return redirect()
                ->route('faq-categories.index')
                ->withErrors(['error' => 'You do not have permission to perform this action.']);
        }

        $faqCategory = FaqCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:faq_categories,name,' . $id,
        ]);

        try {
            $faqCategory->update($validated);

            return redirect()
                ->route('faq-categories.index')
                ->with('success', 'FAQ category updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update FAQ category.']);
        }
    }

    /**
     * Remove the specified FAQ category.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return redirect()
                ->route('faq-categories.index')
                ->withErrors(['error' => 'You do not have permission to perform this action.']);
        }

        $faqCategory = FaqCategory::findOrFail($id);

        try {
            $faqCategory->delete();

            return redirect()
                ->route('faq-categories.index')
                ->with('success', 'FAQ category deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('faq-categories.index')
                ->withErrors(['error' => 'Failed to delete FAQ category.']);
        }
    }
}
