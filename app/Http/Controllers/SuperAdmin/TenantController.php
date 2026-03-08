<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:superadmin');
    }

    /**
     * Display a listing of tenants.
     */
    public function index()
    {
        $tenants = Tenant::with('plan')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('superadmin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        $plans = Plan::all();
        return view('superadmin.tenants.create', compact('plans'));
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'plan_id' => 'nullable|exists:plans,id',
            'status' => 'required|in:active,trial,suspended,pending',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8',
        ]);

        // Create tenant
        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'domain' => $validated['domain'] ?? null,
            'plan_id' => $validated['plan_id'] ?? null,
            'status' => $validated['status'],
            'trial_ends_at' => $validated['status'] === 'trial' ? now()->addDays(14) : null,
        ]);

        // Create admin user for the tenant
        \App\Models\User::create([
            'tenant_id' => $tenant->id,
            'firstname' => $validated['admin_name'],
            'surname' => '',
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'status' => 1,
        ]);

        return redirect()
            ->route('superadmin.tenants.index')
            ->with('success', "Tenant '{$tenant->name}' created successfully.");
    }

    /**
     * Display the specified tenant.
     */
    public function show($id)
    {
        $tenant = Tenant::with(['plan', 'subscription'])->findOrFail($id);
        
        $stats = [
            'users' => \App\Models\User::where('tenant_id', $tenant->id)->count(),
            'total_funds' => \App\Models\Funds::where('tenant_id', $tenant->id)->sum('amount'),
        ];

        return view('superadmin.tenants.show', compact('tenant', 'stats'));
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit($id)
    {
        $tenant = Tenant::findOrFail($id);
        $plans = Plan::all();
        
        return view('superadmin.tenants.edit', compact('tenant', 'plans'));
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug,' . $id,
            'domain' => 'nullable|string|max:255|unique:tenants,domain,' . $id,
            'plan_id' => 'nullable|exists:plans,id',
            'status' => 'required|in:active,trial,suspended,pending',
        ]);

        $tenant->update($validated);

        return redirect()
            ->route('superadmin.tenants.index')
            ->with('success', "Tenant '{$tenant->name}' updated successfully.");
    }
}
