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

    /**
     * Show form to suspend a tenant with details.
     */
    public function showSuspendForm($id)
    {
        $tenant = Tenant::findOrFail($id);
        return view('superadmin.tenants.suspend', compact('tenant'));
    }

    /**
     * Suspend a tenant with details.
     */
    public function suspend(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        
        $validated = $request->validate([
            'suspension_type' => 'required|in:financial,terms_violation,admin_action,other',
            'suspension_reason' => 'required|string|max:1000',
            'suspension_amount_due' => 'nullable|numeric|min:0',
            'suspension_currency' => 'nullable|string|size:3',
            'suspension_ends_at' => 'nullable|date',
        ]);

        $updateData = [
            'status' => 'suspended',
            'suspension_type' => $validated['suspension_type'],
            'suspension_reason' => $validated['suspension_reason'],
            'suspended_by' => auth('superadmin')->id(),
        ];

        // Add financial details if applicable
        if ($validated['suspension_type'] === 'financial') {
            $updateData['suspension_amount_due'] = $validated['suspension_amount_due'] ?? 0;
            $updateData['suspension_currency'] = $validated['suspension_currency'] ?? 'KES';
        }

        // Add suspension end date if provided
        if (!empty($validated['suspension_ends_at'])) {
            $updateData['suspension_ends_at'] = $validated['suspension_ends_at'];
        }

        $tenant->update($updateData);

        // Log the suspension
        \App\Models\PlatformAuditLog::record(
            'tenant.suspended',
            [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'suspension_type' => $validated['suspension_type'],
                'suspension_reason' => $validated['suspension_reason'],
                'amount_due' => $updateData['suspension_amount_due'] ?? null,
            ],
            $tenant->id,
            auth('superadmin')->id()
        );

        return redirect()
            ->route('superadmin.tenants.show', $tenant->id)
            ->with('success', "Tenant '{$tenant->name}' has been suspended.");
    }

    /**
     * Quick action to activate a tenant.
     */
    public function activate($id)
    {
        try {
            $tenant = Tenant::findOrFail($id);
            
            // Store previous state for audit log
            $previousState = [
                'status' => $tenant->status,
                'suspension_type' => $tenant->suspension_type,
                'suspension_reason' => $tenant->suspension_reason,
            ];
            
            // Clear suspension details when activating
            // Keep suspension_details as history (contains JSON of previous suspension)
            $tenant->update([
                'status' => 'active',
                'suspension_type' => null,
                'suspension_reason' => null,
                'suspension_amount_due' => null,
                'suspension_currency' => null,
                'suspension_ends_at' => null,
                'suspended_by' => null,
            ]);

            // Log the activation
            \App\Models\PlatformAuditLog::record(
                'tenant.activated',
                [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'previous_state' => $previousState,
                    'activated_by' => auth('superadmin')->id(),
                ],
                $tenant->id,
                auth('superadmin')->id()
            );

            return redirect()
                ->route('superadmin.tenants.show', $tenant->id)
                ->with('success', "Tenant '{$tenant->name}' has been activated.");
                
        } catch (\Exception $e) {
            \Log::error('Failed to activate tenant', [
                'tenant_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()
                ->route('superadmin.tenants.show', $id)
                ->with('error', 'Failed to activate tenant: ' . $e->getMessage());
        }
    }

    /**
     * Impersonate as tenant admin (login to tenant dashboard).
     */
    public function impersonate($id)
    {
        $tenant = Tenant::findOrFail($id);
        
        // Get the first admin user of this tenant
        $adminUser = \App\Models\User::where('tenant_id', $tenant->id)
            ->role('admin')
            ->first();
        
        if (!$adminUser) {
            return redirect()
                ->route('superadmin.tenants.show', $tenant->id)
                ->with('error', "No admin user found for tenant '{$tenant->name}'.");
        }

        // Store superadmin ID for return
        session(['impersonate_return_id' => auth('superadmin')->id()]);
        
        // Login as tenant admin
        auth()->login($adminUser);
        
        return redirect()->route('home')
            ->with('success', "You are now impersonating as {$adminUser->firstname} {$adminUser->lastname} from {$tenant->name}.");
    }
}
