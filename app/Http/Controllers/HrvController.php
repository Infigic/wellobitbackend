<?php

namespace App\Http\Controllers;

use App\DataTables\HrvDataTable;
use App\Models\Hrv;
use App\Models\User;
use Illuminate\Http\Request;

class HrvController extends Controller
{
    public function index(HrvDataTable $dataTable)
    {
        return $dataTable->render('hrv.index');
        // $query = Hrv::with('user')->latest();

        // // Apply filters
        // if ($request->filled('user_id')) {
        //     $query->where('user_id', $request->user_id);
        // }

        // if ($request->filled('from_date')) {
        //     $query->whereDate('datetime', '>=', $request->from_date);
        // }

        // if ($request->filled('to_date')) {
        //     $query->whereDate('datetime', '<=', $request->to_date);
        // }
        // if ($request->filled('status')) {
        //     $query->where('status', $request->status);
        // }
        // $hrvs = $query->orderBy('datetime', 'desc')->paginate(10)->withQueryString(); // preserves filters in pagination links
        // $users = User::select('id', 'name')->where('role', 'customer')->orderBy('name')->get();

        // return view('hrv.index', compact('hrvs', 'users'));
    }
}
