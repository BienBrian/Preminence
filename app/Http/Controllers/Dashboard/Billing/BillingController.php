<?php

namespace App\Http\Controllers\Dashboard\Billing;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\ModuleService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(private ModuleService $moduleService)
    {
        $this->middleware('auth');
    }

    /**
     * Show the billing dashboard with current plan and upgrade options.
     */
    public function index()
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Billing information not available.');
        }

        $currentPlan = $tenant->plan;
        $availablePlans = Plan::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        $enabledModules = $this->moduleService->enabledModules();
        
        // Get module details for display
        $moduleDetails = $this->getModuleDetails($enabledModules);

        return view('dashboard.billing.index', compact(
            'tenant',
            'currentPlan',
            'availablePlans',
            'enabledModules',
            'moduleDetails'
        ));
    }

    /**
     * Show the upgrade page with plan comparison.
     */
    public function upgrade()
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Billing information not available.');
        }

        $currentPlan = $tenant->plan;
        $plans = Plan::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        return view('dashboard.billing.upgrade', compact('tenant', 'currentPlan', 'plans'));
    }

    /**
     * Show the module locked message page.
     */
    public function moduleLocked(Request $request)
    {
        $module = $request->get('module', 'unknown');
        $label = $this->getModuleLabel($module);
        
        return view('dashboard.billing.module_locked', compact('module', 'label'));
    }

    /**
     * Get human-readable labels for modules.
     */
    private function getModuleLabel(string $module): string
    {
        return match ($module) {
            'people'       => 'People & Members',
            'attendance'   => 'Attendance',
            'finance'      => 'Finance & Giving',
            'mpesa'        => 'M-Pesa Integration',
            'sms'          => 'SMS Messaging',
            'email'        => 'Email Campaigns',
            'events'       => 'Events & Seminars',
            'spiritual'    => 'Spiritual Content',
            'website'      => 'Church Website',
            'shop'         => 'Shop & Products',
            'media'        => 'Media Library',
            'reports'      => 'Reports & Analytics',
            'discipleship' => 'Discipleship',
            'api_access'   => 'API Access',
            default        => ucfirst($module),
        };
    }

    /**
     * Get module details for display.
     */
    private function getModuleDetails(array $enabledModules): array
    {
        $allModules = [
            ['key' => 'people', 'name' => 'People & Members', 'icon' => 'bi-people', 'description' => 'Member directory and profiles'],
            ['key' => 'attendance', 'name' => 'Attendance', 'icon' => 'bi-clipboard-check', 'description' => 'Track service attendance'],
            ['key' => 'finance', 'name' => 'Finance & Giving', 'icon' => 'bi-cash-stack', 'description' => 'Tithes, offerings, and budgets'],
            ['key' => 'mpesa', 'name' => 'M-Pesa Integration', 'icon' => 'bi-phone', 'description' => 'Mobile money payments'],
            ['key' => 'sms', 'name' => 'SMS Messaging', 'icon' => 'bi-chat-dots', 'description' => 'Bulk SMS and notifications'],
            ['key' => 'email', 'name' => 'Email Campaigns', 'icon' => 'bi-envelope', 'description' => 'Email newsletters'],
            ['key' => 'events', 'name' => 'Events & Seminars', 'icon' => 'bi-calendar-event', 'description' => 'Event management'],
            ['key' => 'spiritual', 'name' => 'Spiritual Content', 'icon' => 'bi-book', 'description' => 'Sermons and testimonials'],
            ['key' => 'website', 'name' => 'Church Website', 'icon' => 'bi-globe', 'description' => 'Public website'],
            ['key' => 'shop', 'name' => 'Shop & Products', 'icon' => 'bi-shop', 'description' => 'Online store'],
            ['key' => 'media', 'name' => 'Media Library', 'icon' => 'bi-images', 'description' => 'File and media storage'],
            ['key' => 'reports', 'name' => 'Reports', 'icon' => 'bi-graph-up', 'description' => 'Analytics and reports'],
            ['key' => 'discipleship', 'name' => 'Discipleship', 'icon' => 'bi-heart', 'description' => 'Discipleship tracking'],
            ['key' => 'api_access', 'name' => 'API Access', 'icon' => 'bi-code', 'description' => 'Developer API'],
        ];

        foreach ($allModules as &$mod) {
            $mod['enabled'] = in_array($mod['key'], $enabledModules);
        }

        return $allModules;
    }
}
