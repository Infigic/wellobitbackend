<?php

namespace App\Http\Controllers;

use App\DataTables\QuoteDataTable;
use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(QuoteDataTable $dataTable)
    {
        return $dataTable->render('quotes.index');
        return view('quotes.index', ['quotes' => Quote::orderBy('id', 'desc')->paginate(10)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('quotes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quote' => 'required|string|max:500',
            'type' => 'required|string',
            'status' => 'required|in:1,0',
        ]);

        Quote::create($validated);

        return redirect()->route('quotes.index')->with('success', 'Quote added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Quote $quote)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Quote $quote)
    {

        return view('quotes.edit', ['quote' => $quote]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'quote' => 'required|string|max:500',
            'type' => 'required|string',
        ]);

        $quote->update($validated);

        return redirect()->route('quotes.index')->with('success', 'Quote updated successfully.');
    }

    public function toggleStatus(Quote $quote)
    {
        $quote->is_active = !$quote->is_active;
        $quote->save();

        return redirect()->route('quotes.index')->with('success', 'Status updated successfully.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quote $quote)
    {
        Quote::destroy($quote->id);

        return redirect()->route('quotes.index')->with('success', 'Quote deleted successfully.');
    }
}
