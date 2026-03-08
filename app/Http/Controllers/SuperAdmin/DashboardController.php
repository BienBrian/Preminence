<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\SuperAdmin;
use App\Models\Tenant;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:superadmin');
    }

    /**
     * Show the superadmin dashboard.
     */
    public function index()
    {
        $stats = [
            'tenants' => [
                'total' => Tenant::count(),
                'active' => Tenant::where('status', 'active')->count(),
                'trial' => Tenant::where('status', 'trial')->count(),
                'suspended' => Tenant::where('status', 'suspended')->count(),
            ],
            'plans' => Plan::count(),
            'superadmins' => SuperAdmin::where('is_active', true)->count(),
        ];

        $recentTenants = Tenant::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('superadmin.dashboard', compact('stats', 'recentTenants'));
    }
}
