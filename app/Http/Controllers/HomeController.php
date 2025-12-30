<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $customer_count = \App\Models\User::where('role', 'customer')->count();
        $daily_quotes = \App\Models\Quote::count();
        $hrvs = \App\Models\Hrv::count();

        return view('home', compact('customer_count', 'daily_quotes', 'hrvs'));
    }
}
