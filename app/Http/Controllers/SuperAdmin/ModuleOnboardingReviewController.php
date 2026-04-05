<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ModuleOnboardingConfig;
use App\Models\Tenant;
use App\Models\TenantModuleOnboarding;
use App\Services\Modules\ModuleInstaller;
use App\DTOs\ModuleInstallRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Module Onboarding Review Controller
 * 
 * SuperAdmin interface for reviewing tenant module activation requests
 * that require KYC verification and approval.
 */
class ModuleOnboardingReviewController extends Controller
{
    private ModuleInstaller $moduleInstaller;

    public function __construct(ModuleInstaller $moduleInstaller)
    {
        $this->middleware('auth:superadmin');
        $this->moduleInstaller = $moduleInstaller;
    }

    /**
     * Display list of pending onboarding submissions.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $moduleFilter = $request->get('module');
        
        $query = TenantModuleOnboarding::with(['tenant', 'module', 'reviewer'])
            ->orderBy('submitted_at', 'desc');
        
        // Filter by status
        if ($status === 'pending') {
            $query->pending();
        } elseif (in_array($status, ['approved', 'rejected', 'needs_info'])) {
            $query->where('status', $status);
        }
        
        // Filter by module
        if ($moduleFilter) {
            $query->forModule($moduleFilter);
        }
        
        $submissions = $query->paginate(20);
        
        // Get stats
        $stats = [
            'pending' => TenantModuleOnboarding::pending()->count(),
            'approved' => TenantModuleOnboarding::where('status', 'approved')->count(),
            'rejected' => TenantModuleOnboarding::where('status', 'rejected')->count(),
            'needs_info' => TenantModuleOnboarding::where('status', 'needs_info')->count(),
        ];
        
        // Get modules with pending submissions for filter
        $modulesWithSubmissions = ModuleOnboardingConfig::whereHas('submissions')
            ->with('module')
            ->get()
            ->pluck('module.name', 'module.key');
        
        return view('superadmin.module-onboarding.index', compact(
            'submissions', 
            'stats', 
            'status',
            'moduleFilter',
            'modulesWithSubmissions'
        ));
    }

    /**
     * Show detailed review page for a submission.
     */
    public function show(int $id)
    {
        $submission = TenantModuleOnboarding::with(['tenant', 'module', 'reviewer'])
            ->findOrFail($id);
        
        $config = ModuleOnboardingConfig::where('module_key', $submission->module_key)
            ->firstOrFail();
        
        // Get document URLs for preview
        $documents = [];
        foreach ($submission->documents ?? [] as $key => $path) {
            $docConfig = $config->required_documents[$key] ?? null;
            $documents[$key] = [
                'label' => $docConfig['label'] ?? $key,
                'path' => $path,
                'url' => Storage::disk('private')->url($path),
                'extension' => pathinfo($path, PATHINFO_EXTENSION),
            ];
        }
        
        // Get tenant's current modules for context
        $tenantModules = $submission->tenant->modules()
            ->pluck('module')
            ->toArray();
        
        return view('superadmin.module-onboarding.show', compact(
            'submission',
            'config',
            'documents',
            'tenantModules'
        ));
    }

    /**
     * Preview a document.
     */
    public function previewDocument(int $id, string $documentKey)
    {
        $submission = TenantModuleOnboarding::findOrFail($id);
        $documents = $submission->documents ?? [];
        
        if (!isset($documents[$documentKey])) {
            abort(404, 'Document not found');
        }
        
        $path = $documents[$documentKey];
        
        if (!Storage::disk('private')->exists($path)) {
            abort(404, 'File not found');
        }
        
        // Return file for preview
        return Storage::disk('private')->response($path);
    }

    /**
     * Download a document.
     */
    public function downloadDocument(int $id, string $documentKey)
    {
        $submission = TenantModuleOnboarding::findOrFail($id);
        $documents = $submission->documents ?? [];
        
        if (!isset($documents[$documentKey])) {
            abort(404, 'Document not found');
        }
        
        $path = $documents[$documentKey];
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        $docConfig = ModuleOnboardingConfig::where('module_key', $submission->module_key)
            ->first()
            ?->required_documents[$documentKey] ?? null;
        
        $filename = ($docConfig['label'] ?? $documentKey) . '_' . $submission->tenant->slug . '.' . $extension;
        
        return Storage::disk('private')->download($path, $filename);
    }

    /**
     * Approve a submission and activate the module.
     */
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'trial_days' => 'nullable|integer|min:0|max:90',
        ]);
        
        $submission = TenantModuleOnboarding::with('tenant')
            ->findOrFail($id);
        
        if (!$submission->isPending()) {
            return redirect()
                ->back()
                ->with('error', 'This submission has already been processed.');
        }
        
        $adminId = auth('superadmin')->id();
        
        // Apve the submission
        $submission->approve($adminId, $request->input('notes'));
        
        // Activate the module for the tenant
        $tenant = $submission->tenant;
        $moduleKey = $submission->module_key;
        
        try {
            // Find an admin user in the tenant to assign as requester
            $adminUser = $tenant->users()->first();
            
            if (!$adminUser) {
                throw new \Exception('Tenant has no users');
            }
            
            // Get trial days from request or default
            $config = ModuleOnboardingConfig::where('module_key', $moduleKey)->first();
            $activationSettings = \App\Models\ModuleActivationSettings::forModule($moduleKey);
            $trialDays = $request->input('trial_days', $activationSettings->trial_days ?? 0);
            
            // Create install request
            $installRequest = new ModuleInstallRequest(
                moduleKey: $moduleKey,
                tenant: $tenant,
                requestedBy: $adminUser,
                billingCycle: 'monthly',
                autoApprove: true,
                trialDays: $trialDays
            );
            
            $result = $this->moduleInstaller->install($installRequest);
            
            if (!$result->success) {
                throw new \Exception($result->error);
            }
            
            // Log the approval
            \App\Models\PlatformAuditLog::record(
                'tenant.module.approved',
                [
                    'tenant_id' => $tenant->id,
                    'module_key' => $moduleKey,
                    'onboarding_id' => $submission->id,
                    'trial_days' => $trialDays,
                ],
                $tenant->id,
                $adminId
            );
            
            return redirect()
                ->route('superadmin.module-onboarding.index')
                ->with('success', "Module '{$moduleKey}' has been activated for {$tenant->name}.");
                
        } catch (\Exception $e) {
            // Revert approval if activation failed
            $submission->update([
                'status' => TenantModuleOnboarding::STATUS_SUBMITTED,
                'reviewed_at' => null,
                'reviewed_by' => null,
                'review_notes' => 'Activation failed: ' . $e->getMessage(),
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject a submission.
     */
    public function reject(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $submission = TenantModuleOnboarding::with('tenant')
            ->findOrFail($id);
        
        if (!$submission->isPending()) {
            return redirect()
                ->back()
                ->with('error', 'This submission has already been processed.');
        }
        
        $adminId = auth('superadmin')->id();
        
        $submission->reject(
            $adminId,
            $request->input('reason'),
            $request->input('notes')
        );
        
        // Log the rejection
        \App\Models\PlatformAuditLog::record(
            'tenant.module.rejected',
            [
                'tenant_id' => $submission->tenant_id,
                'module_key' => $submission->module_key,
                'onboarding_id' => $submission->id,
                'reason' => $request->input('reason'),
            ],
            $submission->tenant_id,
            $adminId
        );
        
        return redirect()
            ->route('superadmin.module-onboarding.index')
            ->with('success', 'Submission rejected. The tenant has been notified.');
    }

    /**
     * Request more information.
     */
    public function requestMoreInfo(Request $request, int $id)
    {
        $request->validate([
            'message' => 'required|string|min:10|max:1000',
        ]);
        
        $submission = TenantModuleOnboarding::with('tenant')
            ->findOrFail($id);
        
        if (!$submission->isPending()) {
            return redirect()
                ->back()
                ->with('error', 'This submission has already been processed.');
        }
        
        $adminId = auth('superadmin')->id();
        
        $submission->requestMoreInfo($adminId, $request->input('message'));
        
        return redirect()
            ->route('superadmin.module-onboarding.index')
            ->with('success', 'Request for more information sent to the tenant.');
    }

    /**
     * Bulk actions on submissions.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:tenant_module_onboarding,id',
            'reason' => 'required_if:action,reject|string|min:10',
        ]);
        
        $ids = $request->input('ids');
        $action = $request->input('action');
        $adminId = auth('superadmin')->id();
        
        $processed = 0;
        $failed = [];
        
        foreach ($ids as $id) {
            $submission = TenantModuleOnboarding::find($id);
            
            if (!$submission || !$submission->isPending()) {
                $failed[] = $id;
                continue;
            }
            
            if ($action === 'approve') {
                try {
                    $submission->approve($adminId, 'Bulk approved');
                    
                    // Activate module
                    $adminUser = $submission->tenant->users()->first();
                    if ($adminUser) {
                        $installRequest = new ModuleInstallRequest(
                            moduleKey: $submission->module_key,
                            tenant: $submission->tenant,
                            requestedBy: $adminUser,
                            billingCycle: 'monthly',
                            autoApprove: true,
                            trialDays: 0
                        );
                        $this->moduleInstaller->install($installRequest);
                    }
                    $processed++;
                } catch (\Exception $e) {
                    $failed[] = $id;
                }
            } else {
                $submission->reject($adminId, $request->input('reason'), 'Bulk rejected');
                $processed++;
            }
        }
        
        $message = "{$processed} submissions processed.";
        if (!empty($failed)) {
            $message .= " " . count($failed) . " failed.";
        }
        
        return redirect()
            ->route('superadmin.module-onboarding.index')
            ->with('success', $message);
    }

    /**
     * API: Get submission statistics.
     */
    public function stats()
    {
        $stats = [
            'pending' => TenantModuleOnboarding::pending()->count(),
            'by_module' => TenantModuleOnboarding::pending()
                ->selectRaw('module_key, COUNT(*) as count')
                ->groupBy('module_key')
                ->pluck('count', 'module_key'),
            'by_day' => TenantModuleOnboarding::where('status', 'approved')
                ->where('reviewed_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(reviewed_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date'),
        ];
        
        return response()->json($stats);
    }
}
