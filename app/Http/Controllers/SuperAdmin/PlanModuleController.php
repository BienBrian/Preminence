<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Plan;
use App\Models\PlanModule;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Http\Request;

class PlanModuleController extends Controller
{
    private ModuleRepositoryInterface $moduleRepository;

    public function __construct(ModuleRepositoryInterface $moduleRepository)
    {
        $this->middleware('auth:superadmin');
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * Display plan-module matrix.
     */
    public function index()
    {
        $plans = Plan::with(['planModules.module'])->get();
        $modules = Module::orderBy('category')->orderBy('sort_order')->get();

        // Build matrix for display
        $matrix = $this->buildMatrix($plans, $modules);

        return view('superadmin.plan-modules.index', compact('plans', 'modules', 'matrix'));
    }

    /**
     * Show form to edit plan modules.
     */
    public function edit(Plan $plan)
    {
        $plan->load('planModules');
        $modules = Module::active()->orderBy('category')->orderBy('sort_order')->get();
        
        // Get existing assignments
        $assignments = $plan->planModules->keyBy('module_key');

        return view('superadmin.plan-modules.edit', compact('plan', 'modules', 'assignments'));
    }

    /**
     * Update plan module assignments.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'modules' => 'required|array',
            'modules.*.module_key' => 'required|exists:modules,key',
            'modules.*.is_included' => 'boolean',
            'modules.*.is_available' => 'boolean',
            'modules.*.price_monthly_override' => 'nullable|numeric|min:0',
            'modules.*.price_yearly_override' => 'nullable|numeric|min:0',
            'modules.*.trial_days' => 'nullable|integer|min:0',
        ]);

        foreach ($validated['modules'] as $moduleData) {
            PlanModule::updateOrCreate(
                [
                    'plan_id' => $plan->id,
                    'module_key' => $moduleData['module_key'],
                ],
                [
                    'is_included' => $moduleData['is_included'] ?? false,
                    'is_available' => $moduleData['is_available'] ?? false,
                    'price_monthly_override' => $moduleData['price_monthly_override'] ?? null,
                    'price_yearly_override' => $moduleData['price_yearly_override'] ?? null,
                    'trial_days' => $moduleData['trial_days'] ?? 0,
                ]
            );
        }

        // Invalidate cache
        $this->moduleRepository->invalidatePlan($plan->id);

        return redirect()
            ->route('superadmin.plan-modules.index')
            ->with('success', "Plan '{$plan->name}' module assignments updated.");
    }

    /**
     * Bulk update plan-module matrix.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.plan_id' => 'required|exists:plans,id',
            'assignments.*.module_key' => 'required|exists:modules,key',
            'assignments.*.is_included' => 'boolean',
            'assignments.*.is_available' => 'boolean',
        ]);

        foreach ($validated['assignments'] as $assignment) {
            PlanModule::updateOrCreate(
                [
                    'plan_id' => $assignment['plan_id'],
                    'module_key' => $assignment['module_key'],
                ],
                [
                    'is_included' => $assignment['is_included'] ?? false,
                    'is_available' => $assignment['is_available'] ?? false,
                ]
            );

            // Invalidate plan cache
            $this->moduleRepository->invalidatePlan($assignment['plan_id']);
        }

        return response()->json(['success' => true, 'message' => 'Matrix updated successfully.']);
    }

    /**
     * Quick toggle for plan module.
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'module_key' => 'required|exists:modules,key',
            'field' => 'required|in:is_included,is_available',
        ]);

        $planModule = PlanModule::firstOrCreate(
            [
                'plan_id' => $validated['plan_id'],
                'module_key' => $validated['module_key'],
            ]
        );

        $field = $validated['field'];
        $planModule->update([$field => !$planModule->$field]);

        // Invalidate cache
        $this->moduleRepository->invalidatePlan($validated['plan_id']);

        return response()->json([
            'success' => true,
            'field' => $field,
            'value' => $planModule->$field,
        ]);
    }

    /**
     * Copy plan modules from one plan to another.
     */
    public function copy(Request $request)
    {
        $validated = $request->validate([
            'source_plan_id' => 'required|exists:plans,id',
            'target_plan_id' => 'required|exists:plans,id|different:source_plan_id',
        ]);

        $sourcePlan = Plan::with('planModules')->find($validated['source_plan_id']);
        $targetPlan = Plan::find($validated['target_plan_id']);

        // Delete existing assignments for target
        PlanModule::where('plan_id', $targetPlan->id)->delete();

        // Copy from source
        foreach ($sourcePlan->planModules as $planModule) {
            PlanModule::create([
                'plan_id' => $targetPlan->id,
                'module_key' => $planModule->module_key,
                'is_included' => $planModule->is_included,
                'is_available' => $planModule->is_available,
                'price_monthly_override' => $planModule->price_monthly_override,
                'price_yearly_override' => $planModule->price_yearly_override,
                'setup_fee_override' => $planModule->setup_fee_override,
                'limits_override' => $planModule->limits_override,
                'trial_days' => $planModule->trial_days,
                'plan_highlights' => $planModule->plan_highlights,
            ]);
        }

        // Invalidate cache
        $this->moduleRepository->invalidatePlan($targetPlan->id);

        return redirect()
            ->back()
            ->with('success', "Module assignments copied from '{$sourcePlan->name}' to '{$targetPlan->name}'.");
    }

    /**
     * Preview plan pricing.
     */
    public function previewPricing(Plan $plan)
    {
        $plan->load('planModules.module');
        
        $includedModules = [];
        $availableModules = [];
        $totalValue = 0;

        foreach ($plan->planModules as $planModule) {
            $module = $planModule->module;
            if (!$module) continue;

            $price = $module->getPrice('monthly') ?? 0;

            if ($planModule->is_included) {
                $includedModules[] = [
                    'key' => $module->key,
                    'name' => $module->name,
                    'value' => $price,
                ];
                $totalValue += $price;
            } elseif ($planModule->is_available) {
                $availableModules[] = [
                    'key' => $module->key,
                    'name' => $module->name,
                    'price' => $planModule->getPrice('monthly') ?? $price,
                ];
            }
        }

        return response()->json([
            'plan' => $plan->name,
            'plan_price' => $plan->price,
            'included_value' => $totalValue,
            'savings' => $totalValue > $plan->price ? round($totalValue - $plan->price, 2) : 0,
            'included_modules' => $includedModules,
            'available_addons' => $availableModules,
        ]);
    }

    /**
     * Build plan-module matrix for display.
     */
    private function buildMatrix($plans, $modules): array
    {
        $matrix = [];

        foreach ($modules as $module) {
            $row = [
                'module' => $module,
                'plans' => [],
            ];

            foreach ($plans as $plan) {
                $planModule = $plan->planModules->firstWhere('module_key', $module->key);
                
                $row['plans'][$plan->id] = [
                    'is_included' => $planModule?->is_included ?? false,
                    'is_available' => $planModule?->is_available ?? false,
                    'price_override' => $planModule?->price_monthly_override,
                    'plan_module' => $planModule,
                ];
            }

            $matrix[] = $row;
        }

        return $matrix;
    }
}
