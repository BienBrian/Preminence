<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Module;
use App\Models\ModuleActivationSettings;
use App\Models\ModuleOnboardingConfig;
use App\Models\TenantModuleOnboarding;
use App\Models\TenantModuleSubscription;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use App\Services\Modules\ModuleInstaller;
use App\DTOs\ModuleInstallRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Tenant Marketplace Controller
 * 
 * Handles tenant self-service module activation through the profile dropdown.
 * Supports instant activation, guided onboarding, and KYC workflows.
 */
class TenantMarketplaceController extends DashboardController
{
    private ModuleRepositoryInterface $moduleRepository;
    private ModuleInstaller $moduleInstaller;

    public function __construct(
        ModuleRepositoryInterface $moduleRepository,
        ModuleInstaller $moduleInstaller
    ) {
        parent::__construct();
        $this->moduleRepository = $moduleRepository;
        $this->moduleInstaller = $moduleInstaller;
    }

    /**
     * Get available modules for the tenant marketplace.
     * Called via AJAX from profile dropdown.
     */
    public function availableModules()
    {
        $tenant = $this->getCurrentTenant();
        
        // Get all active modules
        $allModules = Module::active()->public()->get();
        
        // Get current tenant modules
        $installedModules = $tenant->modules()->pluck('module')->toArray();
        
        // Get pending onboarding submissions
        $pendingOnboarding = TenantModuleOnboarding::forTenant($tenant->id)
            ->whereIn('status', ['draft', 'submitted', 'under_review'])
            ->pluck('status', 'module_key')
            ->toArray();
        
        $availableModules = [];
        
        foreach ($allModules as $module) {
            // Skip already installed modules
            if (in_array($module->key, $installedModules)) {
                continue;
            }
            
            // Get activation settings
            $settings = ModuleActivationSettings::forModule($module->key);
            
            // Check if tenant can self-activate
            if (!$settings->canSelfActivate()) {
                continue;
            }
            
            // Check plan requirements
            $tenantPlan = $tenant->plan?->slug;
            if (!$settings->meetsPlanRequirement($tenantPlan)) {
                $module->activation_blocked = true;
                $module->block_reason = 'Requires ' . $settings->minimum_plan_tier . ' plan or higher';
            }
            
            // Check onboarding status
            if (isset($pendingOnboarding[$module->key])) {
                $module->onboarding_status = $pendingOnboarding[$module->key];
            }
            
            // Get onboarding type
            $onboardingConfig = ModuleOnboardingConfig::where('module_key', $module->key)->first();
            $module->onboarding_type = $onboardingConfig?->onboarding_type ?? 'none';
            $module->requires_approval = $onboardingConfig?->requires_approval ?? false;
            
            // Get pricing
            $module->price_info = [
                'monthly' => $module->price_monthly,
                'yearly' => $module->price_yearly,
                'is_free' => $module->is_free,
            ];
            
            $availableModules[] = $module;
        }
        
        return response()->json([
            'modules' => $availableModules,
            'tenant_plan' => $tenant->plan?->name ?? 'None',
        ]);
    }

    /**
     * Start module activation process.
     */
    public function startActivation(string $moduleKey)
    {
        $tenant = $this->getCurrentTenant();
        
        // Check if module exists and is available
        $module = Module::where('key', $moduleKey)->active()->public()->first();
        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }
        
        // Check if already installed
        if ($tenant->hasModule($moduleKey)) {
            return response()->json(['error' => 'Module is already active'], 400);
        }
        
        // Check activation settings
        $settings = ModuleActivationSettings::forModule($moduleKey);
        if (!$settings->canSelfActivate()) {
            return response()->json(['error' => 'This module requires SuperAdmin approval'], 403);
        }
        
        // Check plan requirements
        if (!$settings->meetsPlanRequirement($tenant->plan?->slug)) {
            return response()->json(['error' => 'Upgrade required to activate this module'], 403);
        }
        
        // Get onboarding config
        $onboardingConfig = ModuleOnboardingConfig::where('module_key', $moduleKey)->first();
        
        // If no onboarding required, activate immediately
        if (!$onboardingConfig || $onboardingConfig->isInstant()) {
            return $this->activateModule($moduleKey, $tenant);
        }
        
        // Check for existing onboarding submission
        $existing = TenantModuleOnboarding::forTenant($tenant->id)
            ->forModule($moduleKey)
            ->first();
        
        if ($existing && $existing->isPending()) {
            return response()->json([
                'status' => 'pending',
                'message' => 'Your application is being reviewed.',
                'onboarding_id' => $existing->id,
            ]);
        }
        
        if ($existing && $existing->isRejected()) {
            // Allow resubmission
            $existing->delete();
        }
        
        // Create onboarding record
        $onboarding = TenantModuleOnboarding::create([
            'tenant_id' => $tenant->id,
            'module_key' => $moduleKey,
            'status' => TenantModuleOnboarding::STATUS_DRAFT,
        ]);
        
        return response()->json([
            'status' => 'onboarding_required',
            'onboarding_type' => $onboardingConfig->onboarding_type,
            'onboarding_id' => $onboarding->id,
            'config' => [
                'required_documents' => $onboardingConfig->required_documents,
                'kyc_form_schema' => $onboardingConfig->kyc_form_schema,
                'tutorial_steps' => $onboardingConfig->getTutorialSteps(),
                'network_participation' => $onboardingConfig->network_participation_enabled,
            ],
        ]);
    }

    /**
     * Get onboarding form data.
     */
    public function getOnboardingForm(int $onboardingId)
    {
        $tenant = $this->getCurrentTenant();
        
        $onboarding = TenantModuleOnboarding::forTenant($tenant->id)
            ->findOrFail($onboardingId);
        
        $config = ModuleOnboardingConfig::where('module_key', $onboarding->module_key)->first();
        
        return response()->json([
            'onboarding' => $onboarding,
            'config' => [
                'type' => $config->onboarding_type,
                'documents' => $config->required_documents,
                'form_schema' => $config->kyc_form_schema,
                'tutorial' => $config->tutorial_content,
                'network_enabled' => $config->network_participation_enabled,
            ],
        ]);
    }

    /**
     * Render onboarding HTML for modal display.
     */
    public function renderOnboarding(Request $request, int $onboardingId)
    {
        $tenant = $this->getCurrentTenant();
        $type = $request->get('type', 'guided');
        
        $onboarding = TenantModuleOnboarding::forTenant($tenant->id)
            ->findOrFail($onboardingId);
        
        $module = Module::where('key', $onboarding->module_key)->firstOrFail();
        $config = ModuleOnboardingConfig::where('module_key', $onboarding->module_key)->first();
        
        if (!$config) {
            return response()->json(['html' => '<div class="alert alert-danger">Onboarding not configured</div>']);
        }
        
        $html = '';
        
        switch ($type) {
            case 'setup_wizard':
                $html = view('components.onboarding.setup-wizard', [
                    'config' => $config,
                    'module' => $module,
                    'onboardingId' => $onboardingId,
                ])->render();
                break;
                
            case 'guided':
                $html = view('components.onboarding.guided-tutorial', [
                    'config' => $config,
                    'module' => $module,
                    'onboardingId' => $onboardingId,
                ])->render();
                break;
                
            case 'kyc':
                // Use existing KYC view
                $html = view('dashboard.marketplace.onboarding.kyc', [
                    'config' => $config,
                    'module' => $module,
                    'onboarding' => $onboarding,
                ])->render();
                break;
                
            default:
                $html = '<div class="alert alert-info">No onboarding required for this module.</div>';
        }
        
        return response()->json(['html' => $html]);
    }

    /**
     * Save onboarding form progress (draft).
     */
    public function saveOnboardingProgress(Request $request, int $onboardingId)
    {
        $tenant = $this->getCurrentTenant();
        
        $onboarding = TenantModuleOnboarding::forTenant($tenant->id)
            ->findOrFail($onboardingId);
        
        if (!$onboarding->isDraft()) {
            return response()->json(['error' => 'Cannot edit submitted application'], 400);
        }
        
        $onboarding->update([
            'form_data' => $request->input('form_data'),
            'network_participation_opt_in' => $request->input('network_opt_in', false),
        ]);
        
        return response()->json(['success' => 'Progress saved']);
    }

    /**
     * Upload onboarding document.
     */
    public function uploadDocument(Request $request, int $onboardingId)
    {
        $tenant = $this->getCurrentTenant();
        
        $onboarding = TenantModuleOnboarding::forTenant($tenant->id)
            ->findOrFail($onboardingId);
        
        if (!$onboarding->isDraft()) {
            return response()->json(['error' => 'Cannot edit submitted application'], 400);
        }
        
        $request->validate([
            'document_key' => 'required|string',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);
        
        $documentKey = $request->input('document_key');
        $file = $request->file('document');
        
        // Store file
        $path = $file->storeAs(
            "onboarding/{$tenant->id}/{$onboarding->module_key}",
            "{$documentKey}_" . time() . "." . $file->getClientOriginalExtension(),
            'private'
        );
        
        // Update onboarding record
        $onboarding->setDocument($documentKey, $path);
        
        return response()->json([
            'success' => 'Document uploaded',
            'document_key' => $documentKey,
            'path' => $path,
        ]);
    }

    /**
     * Submit onboarding application.
     */
    public function submitOnboarding(Request $request, int $onboardingId)
    {
        $tenant = $this->getCurrentTenant();
        
        $onboarding = TenantModuleOnboarding::forTenant($tenant->id)
            ->findOrFail($onboardingId);
        
        if (!$onboarding->isDraft()) {
            return response()->json(['error' => 'Application already submitted'], 400);
        }
        
        // Get config to validate
        $config = ModuleOnboardingConfig::where('module_key', $onboarding->module_key)->first();
        
        // Validate required documents for KYC
        if ($config && $config->isKyc()) {
            $requiredDocs = $config->getDocumentsList();
            $uploadedDocs = $onboarding->documents ?? [];
            
            foreach ($requiredDocs as $key => $docConfig) {
                if (($docConfig['required'] ?? true) && !isset($uploadedDocs[$key])) {
                    return response()->json([
                        'error' => "Required document missing: {$docConfig['label']}",
                    ], 422);
                }
            }
        }
        
        // Submit application
        $onboarding->submit();
        
        // Check if auto-approval is possible
        $settings = ModuleActivationSettings::forModule($onboarding->module_key);
        $tenantPlan = $tenant->plan?->slug;
        
        if (!$config?->requires_approval && !$settings->requiresApproval()) {
            // Auto-approve and activate
            $onboarding->approve(0, 'Auto-approved - no approval required');
            $this->activateModule($onboarding->module_key, $tenant);
            
            return response()->json([
                'status' => 'activated',
                'message' => 'Module activated successfully!',
            ]);
        }
        
        // Auto-approve for certain plans
        if ($settings->isAutoApprovedForPlan($tenantPlan)) {
            $onboarding->approve(0, "Auto-approved for {$tenantPlan} plan");
            $this->activateModule($onboarding->module_key, $tenant);
            
            return response()->json([
                'status' => 'activated',
                'message' => 'Module activated successfully!',
            ]);
        }
        
        // Pending review
        return response()->json([
            'status' => 'pending',
            'message' => $settings->getMessage('pending', 'Your application has been submitted for review.'),
        ]);
    }

    /**
     * Check onboarding status.
     */
    public function checkOnboardingStatus(int $onboardingId)
    {
        $tenant = $this->getCurrentTenant();
        
        $onboarding = TenantModuleOnboarding::forTenant($tenant->id)
            ->findOrFail($onboardingId);
        
        return response()->json([
            'status' => $onboarding->status,
            'submitted_at' => $onboarding->submitted_at,
            'reviewed_at' => $onboarding->reviewed_at,
            'rejection_reason' => $onboarding->rejection_reason,
            'review_notes' => $onboarding->review_notes,
        ]);
    }

    /**
     * Activate module immediately (no onboarding).
     */
    private function activateModule(string $moduleKey, $tenant)
    {
        $module = $this->moduleRepository->findByKey($moduleKey);
        
        // Check if trial is allowed
        $settings = ModuleActivationSettings::forModule($moduleKey);
        $trialDays = $settings->allowsTrial() ? $settings->trial_days : 0;
        
        // Create install request
        $installRequest = new ModuleInstallRequest(
            moduleKey: $moduleKey,
            tenant: $tenant,
            requestedBy: Auth::user(),
            billingCycle: 'monthly',
            autoApprove: true,
            trialDays: $trialDays
        );
        
        // Install module
        $result = $this->moduleInstaller->install($installRequest);
        
        if (!$result->success) {
            return response()->json([
                'error' => 'Activation failed: ' . $result->error,
            ], 500);
        }
        
        return response()->json([
            'status' => 'activated',
            'message' => 'Module activated successfully!' . ($trialDays > 0 ? " Your {$trialDays}-day trial has started." : ''),
            'subscription' => [
                'status' => $result->subscription->status,
                'trial_ends_at' => $result->subscription->trial_ends_at,
            ],
        ]);
    }

    /**
     * Get current tenant helper.
     */
    private function getCurrentTenant()
    {
        return app('tenant');
    }
}
