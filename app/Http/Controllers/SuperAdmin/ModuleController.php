<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleCategory;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ModuleController extends Controller
{
    private ModuleRepositoryInterface $moduleRepository;

    public function __construct(ModuleRepositoryInterface $moduleRepository)
    {
        $this->middleware('auth:superadmin');
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * Display module catalog.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'price_type', 'search', 'status']);
        $modules = $this->moduleRepository->search($filters, 20);
        $stats = $this->getModuleStats();
        $categories = ModuleCategory::active()->get();

        return view('superadmin.modules.index', compact('modules', 'stats', 'categories'));
    }

    /**
     * Show module creation form.
     */
    public function create()
    {
        $categories = ModuleCategory::active()->get();
        $allModules = Module::active()->pluck('name', 'key');

        return view('superadmin.modules.create', compact('categories', 'allModules'));
    }

    /**
     * Store new module.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        
        $validated['created_by'] = auth('superadmin')->id();
        $validated['slug'] = Str::slug($validated['key']);

        // Process arrays from form
        $validated['tags'] = $this->processArrayInput($request->input('tags', []));
        $validated['dependencies'] = $this->processArrayInput($request->input('dependencies', []));
        $validated['conflicts'] = $this->processArrayInput($request->input('conflicts', []));
        $validated['features'] = $this->processArrayInput($request->input('features', []));
        $validated['highlights'] = $this->processArrayInput($request->input('highlights', []));

        $module = Module::create($validated);

        // Invalidate cache
        $this->moduleRepository->invalidateModule($module->key);

        return redirect()
            ->route('superadmin.modules.edit', $module)
            ->with('success', "Module '{$module->name}' created successfully.");
    }

    /**
     * Show module edit form.
     */
    public function edit(Module $module)
    {
        $categories = ModuleCategory::active()->get();
        $allModules = Module::active()->where('key', '!=', $module->key)->pluck('name', 'key');
        $changelogs = $module->changelogs()->orderBy('released_at', 'desc')->get();
        $tenantStats = $this->moduleRepository->getStats($module->key);

        return view('superadmin.modules.edit', compact(
            'module', 'categories', 'allModules', 'changelogs', 'tenantStats'
        ));
    }

    /**
     * Update module.
     */
    public function update(Request $request, Module $module)
    {
        $validated = $request->validate($this->validationRules($module->id));
        
        $validated['updated_by'] = auth('superadmin')->id();

        // Process arrays from form
        $validated['tags'] = $this->processArrayInput($request->input('tags', []));
        $validated['dependencies'] = $this->processArrayInput($request->input('dependencies', []));
        $validated['conflicts'] = $this->processArrayInput($request->input('conflicts', []));
        $validated['features'] = $this->processArrayInput($request->input('features', []));
        $validated['highlights'] = $this->processArrayInput($request->input('highlights', []));
        $validated['default_limits'] = $request->input('default_limits', []);

        $module->update($validated);

        // Invalidate cache
        $this->moduleRepository->invalidateModule($module->key);

        return redirect()
            ->back()
            ->with('success', 'Module updated successfully.');
    }

    /**
     * Delete/Deactivate module.
     */
    public function destroy(Request $request, Module $module)
    {
        // Verify password confirmation for dangerous action
        if (!$this->verifyPasswordConfirmation($request)) {
            return redirect()
                ->back()
                ->with('error', 'Password verification failed. Module was not deleted.');
        }

        // Check if module has active installations
        $activeInstalls = $module->tenantSubscriptions()->active()->count();
        
        if ($activeInstalls > 0) {
            // Soft delete - just deactivate
            $module->update(['is_active' => false]);
            $message = "Module '{$module->name}' has been deactivated (has {$activeInstalls} active installations).";
        } else {
            // Hard delete if no installations
            $module->delete();
            $message = "Module '{$module->name}' has been deleted.";
        }

        // Invalidate cache
        $this->moduleRepository->invalidateModule($module->key);

        return redirect()
            ->route('superadmin.modules.index')
            ->with('success', $message);
    }

    /**
     * Verify password confirmation for dangerous actions.
     */
    private function verifyPasswordConfirmation(Request $request): bool
    {
        $password = $request->input('confirm_password');
        
        if (empty($password)) {
            return false;
        }

        $admin = auth('superadmin')->user();
        
        if (!$admin) {
            return false;
        }

        return Hash::check($password, $admin->password);
    }

    /**
     * Display module analytics.
     */
    public function analytics(Module $module)
    {
        $stats = $this->moduleRepository->getStats($module->key);
        $adoptionTrend = $this->getAdoptionTrend($module);
        $revenueStats = $this->getRevenueStats($module);
        $topTenants = $this->getTopTenants($module);

        return view('superadmin.modules.analytics', compact(
            'module', 'stats', 'adoptionTrend', 'revenueStats', 'topTenants'
        ));
    }

    /**
     * Toggle module active status.
     */
    public function toggleActive(Request $request, Module $module)
    {
        // Verify password confirmation for dangerous action
        if (!$this->verifyPasswordConfirmation($request)) {
            return redirect()
                ->back()
                ->with('error', 'Password verification failed. Module status was not changed.');
        }

        $module->update(['is_active' => !$module->is_active]);
        
        // Invalidate cache
        $this->moduleRepository->invalidateModule($module->key);

        $status = $module->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Module '{$module->name}' has been {$status}.");
    }

    /**
     * Get module statistics.
     */
    private function getModuleStats(): array
    {
        return [
            'total' => Module::count(),
            'active' => Module::active()->count(),
            'inactive' => Module::where('is_active', false)->count(),
            'free' => Module::free()->count(),
            'paid' => Module::paid()->count(),
            'total_installs' => \App\Models\TenantModuleSubscription::installed()->count(),
            'active_addons' => \App\Models\TenantModuleSubscription::whereIn('billing_type', ['addon_monthly', 'addon_yearly'])
                ->where('status', 'active')
                ->count(),
        ];
    }

    /**
     * Get adoption trend data.
     */
    private function getAdoptionTrend(Module $module): array
    {
        $trend = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = $module->tenantSubscriptions()
                ->where('installed_at', '<=', $month->endOfMonth())
                ->where('status', '!=', 'uninstalled')
                ->count();
            
            $trend[] = [
                'month' => $month->format('M Y'),
                'count' => $count,
            ];
        }
        
        return $trend;
    }

    /**
     * Get revenue statistics.
     */
    private function getRevenueStats(Module $module): array
    {
        $monthlyRevenue = $module->tenantSubscriptions()
            ->where('billing_type', 'addon_monthly')
            ->where('status', 'active')
            ->sum('price');

        $yearlyRevenue = $module->tenantSubscriptions()
            ->where('billing_type', 'addon_yearly')
            ->where('status', 'active')
            ->sum('price') / 12;

        return [
            'monthly_recurring' => round($monthlyRevenue, 2),
            'yearly_equivalent' => round($yearlyRevenue, 2),
            'total_mrr' => round($monthlyRevenue + $yearlyRevenue, 2),
        ];
    }

    /**
     * Get top tenants by usage.
     */
    private function getTopTenants(Module $module): array
    {
        return $module->tenantSubscriptions()
            ->with('tenant')
            ->where('status', 'active')
            ->orderBy('usage_count', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($sub) => [
                'tenant' => $sub->tenant->name,
                'usage' => $sub->usage_count,
                'price' => $sub->price,
            ])
            ->toArray();
    }

    /**
     * Process array input from form.
     */
    private function processArrayInput($input): array
    {
        if (is_string($input)) {
            return array_filter(array_map('trim', explode(',', $input)));
        }
        
        return array_filter($input);
    }

    /**
     * Validation rules.
     */
    private function validationRules(?int $moduleId = null): array
    {
        return [
            'key' => 'required|string|max:50|regex:/^[a-z0-9_]+$/|unique:modules,key,' . $moduleId,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:255',
            'category' => 'required|string|max:50|exists:module_categories,slug',
            'tags' => 'nullable|array',
            'version' => 'nullable|string|max:20',
            'min_platform_version' => 'nullable|string|max:20',
            'dependencies' => 'nullable|array',
            'conflicts' => 'nullable|array',
            'is_free' => 'boolean',
            'price_monthly' => 'nullable|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'setup_fee' => 'nullable|numeric|min:0',
            'billing_model' => 'required|in:flat,per_user,usage_based',
            'features' => 'nullable|array',
            'default_limits' => 'nullable|array',
            'icon' => 'nullable|string|max:255',
            'documentation_url' => 'nullable|url|max:500',
            'video_url' => 'nullable|url|max:500',
            'highlights' => 'nullable|array',
            'estimated_install_time_seconds' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'requires_approval' => 'boolean',
            'approval_level' => 'required|in:auto,admin,manual_review',
        ];
    }
}
