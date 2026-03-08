<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:superadmin');
    }

    /**
     * Display a listing of plans.
     */
    public function index()
    {
        $plans = Plan::orderBy('price', 'asc')->get();
        return view('superadmin.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        return view('superadmin.plans.create');
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'is_active' => 'boolean',
            'modules' => 'nullable|array',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['modules'] = $validated['modules'] ?? [];

        $plan = Plan::create($validated);

        return redirect()
            ->route('superadmin.plans.index')
            ->with('success', "Plan '{$plan->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit($id)
    {
        $plan = Plan::findOrFail($id);
        return view('superadmin.plans.edit', compact('plan'));
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,' . $id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'is_active' => 'boolean',
            'modules' => 'nullable|array',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['modules'] = $validated['modules'] ?? [];

        $plan->update($validated);

        return redirect()
            ->route('superadmin.plans.index')
            ->with('success', "Plan '{$plan->name}' updated successfully.");
    }
}
