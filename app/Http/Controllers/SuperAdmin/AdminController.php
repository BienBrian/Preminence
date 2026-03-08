<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:superadmin');
    }

    /**
     * Display a listing of superadmins.
     */
    public function index()
    {
        $admins = SuperAdmin::orderBy('created_at', 'desc')->get();
        return view('superadmin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new superadmin.
     */
    public function create()
    {
        return view('superadmin.admins.create');
    }

    /**
     * Store a newly created superadmin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:super_admins,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $admin = SuperAdmin::create($validated);

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', "SuperAdmin '{$admin->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified superadmin.
     */
    public function edit($id)
    {
        $admin = SuperAdmin::findOrFail($id);
        return view('superadmin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified superadmin.
     */
    public function update(Request $request, $id)
    {
        $admin = SuperAdmin::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:super_admins,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $admin->update($validated);

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', "SuperAdmin '{$admin->name}' updated successfully.");
    }
}
