<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\UserTracking;
use App\Services\SubscriptionService;

class UserController extends Controller
{

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('users.index');
        // return view('users.index', ['users' => User::paginate(10)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $validated = $request->validate([
        //     'name' => 'required|string',
        //     'email' => 'required|string|unique:users',
        //     'password' => 'required|string',
        //     'is_active' => 'required|string',
        // ]);

        // User::create($validated);

        // return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $tracking = UserTracking::where('email', $user->email)->first();
        return view('users.edit', ['user' => $user, 'tracking' => $tracking]);
    }

    public function EditProfile()
    {
        return view('users.editprofile', ['user' => auth()->user()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email,' . $user->id,
            'is_active' => 'sometimes|boolean',
            'trial_started_at' => 'nullable|date',
            'trial_ends_at' => 'nullable|date|after_or_equal:trial_started_at',
            'plan_name' => 'nullable|string',
        ]);

        $this->subscriptionService->updateTrialInfo($user->id, $validated);
        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Profile updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();
        return redirect()->route('users.index')->with('success', 'Status updated successfully.');
    }
    /**
     * Update the specified resource in storage.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->route('home')->with('success', 'Profile updated successfully.');
    }

    public function UpdatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = auth()->user();

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('home')->with('success', 'Password updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function forceDelete(User $user)
    {
        $user->forceDelete();

        return redirect()->route('users.index')->with('success', 'User deleted permanently.');
    }
}
