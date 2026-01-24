<?php

namespace App\Http\Controllers;

use App\DataTables\FaqDataTable;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Services\FaqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaqController extends Controller
{
    protected FaqService $faqService;


    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(FaqDataTable $dataTable)
    {
        return $dataTable->render('faqs.index');
        return view('faqs.index', ['faqs' => Faq::orderBy('id', 'desc')->paginate(10)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = FaqCategory::all();
        return view('faqs.create', compact('categories'));
    }

    /**
     * Store a newly created FAQ
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string',
            'subtitle'    => 'required|string',
            'category_id' => 'required|exists:faq_categories,id',
            'image'       => 'nullable|image|max:5000',
        ]);

        try {
            $faq = $this->faqService->create($data);
            return redirect()->route('faqs.index')->with('success', 'FAQ created successfully');
        } catch (\Exception $e) {
            Log::error('FAQ creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create FAQ. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit(Faq $faq)
    {
        $categories = FaqCategory::all();
        return view('faqs.edit', compact('faq', 'categories'));
    }

    /**
     * Update the specified FAQ.
     */
    public function update(Request $request, Faq $faq)
    {
        $data = $request->validate([
            'title'       => 'required|string',
            'subtitle'    => 'required|string',
            'category_id' => 'required|exists:faq_categories,id',
            'image'       => 'nullable|image|max:5000',
            'remove_image' => 'nullable|string',
        ]);

        try {
            $updatedFaq = $this->faqService->update($faq->id, $data);

            return redirect()->route('faqs.edit', $faq->id)->with('success', 'FAQ updated successfully');
        } catch (\Exception $e) {
            Log::error('FAQ update failed: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Failed to update FAQ. Please try again.');
        }
    }

    /**
     * Remove the specified FAQ.
     */
    public function destroy(Faq $faq)
    {
        try {
            $this->faqService->delete($faq->id);

            return redirect()->route('faqs.index')->with('success', 'FAQ deleted successfully.');
        } catch (\Exception $e) {
            Log::error('FAQ deletion failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to delete FAQ. Please try again.');
        }
    }
}
