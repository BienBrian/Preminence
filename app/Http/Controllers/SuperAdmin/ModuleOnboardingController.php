<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleOnboardingConfig;
use App\Models\ModuleOnboardingStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Module Onboarding Controller
 * 
 * SuperAdmin interface for managing module onboarding configurations.
 * Supports instant, guided, setup_wizard, and kyc onboarding types.
 */
class ModuleOnboardingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:superadmin');
    }

    /**
     * Show the onboarding configuration for a module.
     */
    public function edit(Module $module)
    {
        $config = ModuleOnboardingConfig::with('steps')
            ->where('module_key', $module->key)
            ->first();

        if (!$config) {
            // Create default config if none exists
            $config = ModuleOnboardingConfig::create([
                'module_key' => $module->key,
                'onboarding_type' => 'guided',
                'is_configured' => false,
            ]);
        }

        // Get available templates
        $templates = $this->getOnboardingTemplates();

        // Get onboarding type counts for stats
        $typeStats = ModuleOnboardingConfig::selectRaw('onboarding_type, COUNT(*) as count')
            ->groupBy('onboarding_type')
            ->pluck('count', 'onboarding_type')
            ->toArray();

        return view('superadmin.modules.onboarding.edit', compact(
            'module',
            'config',
            'templates',
            'typeStats'
        ));
    }

    /**
     * Update the onboarding configuration.
     */
    public function update(Request $request, Module $module)
    {
        $config = ModuleOnboardingConfig::where('module_key', $module->key)->firstOrFail();

        $validated = $request->validate([
            'onboarding_type' => 'required|in:instant,guided,setup_wizard,kyc',
            'welcome_message' => 'nullable|string|max:500',
            'completion_message' => 'nullable|string|max:500',
            'estimated_setup_time_minutes' => 'nullable|integer|min:1|max:120',
            'requires_approval' => 'boolean',
            'preview_enabled' => 'boolean',
            'auto_redirect_to_module' => 'boolean',
            'contextual_help_enabled' => 'boolean',
            'video_url' => 'nullable|url|max:500',
            'documentation_url' => 'nullable|url|max:500',
            'approval_instructions' => 'nullable|string|max:2000',
        ]);

        // Set boolean defaults
        $validated['requires_approval'] = $request->boolean('requires_approval');
        $validated['preview_enabled'] = $request->boolean('preview_enabled', true);
        $validated['auto_redirect_to_module'] = $request->boolean('auto_redirect_to_module', true);
        $validated['contextual_help_enabled'] = $request->boolean('contextual_help_enabled');
        $validated['is_configured'] = true;
        $validated['configured_at'] = now();

        // Handle type-specific data
        switch ($validated['onboarding_type']) {
            case 'guided':
                $validated['tutorial_content'] = $this->processTutorialContent($request);
                break;
            case 'setup_wizard':
                $validated['setup_wizard_schema'] = $this->processWizardSchema($request);
                break;
            case 'kyc':
                $validated['required_documents'] = $this->processRequiredDocuments($request);
                $validated['kyc_form_schema'] = $this->processKycFormSchema($request);
                break;
        }

        $config->update($validated);

        return redirect()
            ->route('superadmin.modules.onboarding.edit', $module)
            ->with('success', 'Onboarding configuration updated successfully.');
    }

    /**
     * Store or update an onboarding step.
     */
    public function storeStep(Request $request, Module $module)
    {
        $config = ModuleOnboardingConfig::where('module_key', $module->key)->firstOrFail();

        $validated = $request->validate([
            'step_id' => 'nullable|integer|exists:module_onboarding_steps,id',
            'step_key' => 'required|string|max:50',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'content_type' => 'required|in:info,form,video,document_upload,confirmation,completion',
            'video_url' => 'nullable|url|max:500',
            'image_url' => 'nullable|url|max:500',
            'icon' => 'nullable|string|max:50',
            'form_schema' => 'nullable|json',
            'document_config' => 'nullable|json',
            'is_required' => 'boolean',
            'is_skippable' => 'boolean',
            'allow_back' => 'boolean',
            'estimated_minutes' => 'nullable|integer|min:1|max:60',
        ]);

        $validated['is_required'] = $request->boolean('is_required', true);
        $validated['is_skippable'] = $request->boolean('is_skippable');
        $validated['allow_back'] = $request->boolean('allow_back', true);

        // Decode JSON fields
        if (!empty($validated['form_schema'])) {
            $validated['form_schema'] = json_decode($validated['form_schema'], true);
        }
        if (!empty($validated['document_config'])) {
            $validated['document_config'] = json_decode($validated['document_config'], true);
        }

        if ($request->filled('step_id')) {
            // Update existing step
            $step = ModuleOnboardingStep::where('id', $request->step_id)
                ->where('module_onboarding_config_id', $config->id)
                ->firstOrFail();
            $step->update($validated);
            $message = 'Step updated successfully.';
        } else {
            // Create new step
            $maxStepNumber = $config->steps()->max('step_number') ?? 0;
            $validated['step_number'] = $maxStepNumber + 1;
            $validated['module_onboarding_config_id'] = $config->id;
            ModuleOnboardingStep::create($validated);
            $message = 'Step created successfully.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Delete an onboarding step.
     */
    public function destroyStep(Module $module, ModuleOnboardingStep $step)
    {
        $config = ModuleOnboardingConfig::where('module_key', $module->key)->firstOrFail();
        
        // Ensure step belongs to this config
        if ($step->module_onboarding_config_id !== $config->id) {
            abort(403, 'Step does not belong to this module.');
        }

        $deletedNumber = $step->step_number;
        $step->delete();

        // Reorder remaining steps
        $config->steps()
            ->where('step_number', '>', $deletedNumber)
            ->decrement('step_number');

        return response()->json([
            'success' => true,
            'message' => 'Step deleted successfully.',
        ]);
    }

    /**
     * Reorder steps.
     */
    public function reorderSteps(Request $request, Module $module)
    {
        $config = ModuleOnboardingConfig::where('module_key', $module->key)->firstOrFail();

        $validated = $request->validate([
            'steps' => 'required|array',
            'steps.*.id' => 'required|integer|exists:module_onboarding_steps,id',
            'steps.*.order' => 'required|integer|min:1',
        ]);

        foreach ($validated['steps'] as $stepData) {
            ModuleOnboardingStep::where('id', $stepData['id'])
                ->where('module_onboarding_config_id', $config->id)
                ->update(['step_number' => $stepData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Steps reordered successfully.',
        ]);
    }

    /**
     * Preview the onboarding flow.
     */
    public function preview(Module $module)
    {
        $config = ModuleOnboardingConfig::with('steps')
            ->where('module_key', $module->key)
            ->firstOrFail();

        return view('superadmin.modules.onboarding.preview', compact(
            'module',
            'config'
        ));
    }

    /**
     * Apply a template to the module.
     */
    public function applyTemplate(Request $request, Module $module)
    {
        $validated = $request->validate([
            'template_key' => 'required|string|in:simple_intro,financial_setup,communication_setup,content_workflow,full_kyc',
        ]);

        $config = ModuleOnboardingConfig::where('module_key', $module->key)->firstOrFail();

        // Get template data
        $template = $this->getTemplateData($validated['template_key']);

        // Apply template
        $config->update([
            'onboarding_type' => $template['onboarding_type'],
            'welcome_message' => $template['welcome_message'],
            'completion_message' => $template['completion_message'],
            'estimated_setup_time_minutes' => $template['estimated_setup_time_minutes'] ?? null,
            'tutorial_content' => $template['tutorial_content'] ?? null,
            'setup_wizard_schema' => $template['setup_wizard_schema'] ?? null,
            'required_documents' => $template['required_documents'] ?? null,
            'kyc_form_schema' => $template['kyc_form_schema'] ?? null,
            'template_key' => $validated['template_key'],
            'is_configured' => true,
            'configured_at' => now(),
        ]);

        // Delete existing steps
        $config->steps()->delete();

        // Create steps from template
        if (!empty($template['steps'])) {
            foreach ($template['steps'] as $index => $stepData) {
                $stepData['module_onboarding_config_id'] = $config->id;
                $stepData['step_number'] = $index + 1;
                ModuleOnboardingStep::create($stepData);
            }
        }

        return redirect()
            ->route('superadmin.modules.onboarding.edit', $module)
            ->with('success', 'Template applied successfully. Customize as needed.');
    }

    /**
     * Get available onboarding templates.
     */
    private function getOnboardingTemplates(): array
    {
        return [
            'simple_intro' => [
                'name' => 'Simple Feature Introduction',
                'description' => '3-step tutorial for simple feature modules',
                'onboarding_type' => 'guided',
                'icon' => 'bi-lightbulb',
            ],
            'financial_setup' => [
                'name' => 'Financial Module Setup',
                'description' => 'Multi-step wizard for finance-related modules',
                'onboarding_type' => 'setup_wizard',
                'icon' => 'bi-cash-stack',
            ],
            'communication_setup' => [
                'name' => 'Communication Configuration',
                'description' => 'API keys, templates, and testing setup',
                'onboarding_type' => 'setup_wizard',
                'icon' => 'bi-chat-dots',
            ],
            'content_workflow' => [
                'name' => 'Content Management Workflow',
                'description' => 'Categories, permissions, and publishing setup',
                'onboarding_type' => 'setup_wizard',
                'icon' => 'bi-file-text',
            ],
            'full_kyc' => [
                'name' => 'Full KYC Compliance',
                'description' => 'Document upload, verification, and approval workflow',
                'onboarding_type' => 'kyc',
                'icon' => 'bi-shield-check',
            ],
        ];
    }

    /**
     * Get template data by key.
     */
    private function getTemplateData(string $templateKey): array
    {
        $templates = [
            'simple_intro' => [
                'onboarding_type' => 'guided',
                'welcome_message' => 'Welcome to this feature!',
                'completion_message' => 'You\'re all set! Start using the feature.',
                'estimated_setup_time_minutes' => 2,
                'tutorial_content' => [
                    'steps' => [
                        ['title' => 'Welcome', 'content' => 'Learn about this feature and how it can help your church.', 'icon' => 'bi-hand-thumbs-up'],
                        ['title' => 'Key Features', 'content' => 'Discover the main capabilities and benefits.', 'icon' => 'bi-stars'],
                        ['title' => 'Get Started', 'content' => 'You\'re ready to start using this feature.', 'icon' => 'bi-rocket'],
                    ],
                ],
            ],
            'financial_setup' => [
                'onboarding_type' => 'setup_wizard',
                'welcome_message' => 'Let\'s set up your financial management.',
                'completion_message' => 'Your finance module is ready!',
                'estimated_setup_time_minutes' => 10,
                'steps' => [
                    ['title' => 'Currency & Settings', 'content' => 'Configure base currency and fiscal year.', 'content_type' => 'form'],
                    ['title' => 'Accounts', 'content' => 'Set up fund accounts and categories.', 'content_type' => 'form'],
                    ['title' => 'Permissions', 'content' => 'Configure who can manage finances.', 'content_type' => 'form'],
                ],
            ],
            'communication_setup' => [
                'onboarding_type' => 'setup_wizard',
                'welcome_message' => 'Configure your communication settings.',
                'completion_message' => 'Ready to send messages!',
                'estimated_setup_time_minutes' => 8,
                'steps' => [
                    ['title' => 'Provider Setup', 'content' => 'Enter API credentials.', 'content_type' => 'form'],
                    ['title' => 'Sender Details', 'content' => 'Configure sender ID and templates.', 'content_type' => 'form'],
                    ['title' => 'Test', 'content' => 'Send a test message.', 'content_type' => 'confirmation'],
                ],
            ],
            'content_workflow' => [
                'onboarding_type' => 'setup_wizard',
                'welcome_message' => 'Set up your content workflow.',
                'completion_message' => 'Content management is configured!',
                'estimated_setup_time_minutes' => 5,
                'steps' => [
                    ['title' => 'Categories', 'content' => 'Create content categories.', 'content_type' => 'form'],
                    ['title' => 'Authors', 'content' => 'Set up author permissions.', 'content_type' => 'form'],
                ],
            ],
            'full_kyc' => [
                'onboarding_type' => 'kyc',
                'welcome_message' => 'We need to verify your organization.',
                'completion_message' => 'Your application will be reviewed shortly.',
                'requires_approval' => true,
                'estimated_setup_time_minutes' => 20,
                'required_documents' => [
                    'registration' => [
                        'label' => 'Organization Registration',
                        'description' => 'Official registration certificate',
                        'accepted_types' => ['pdf', 'jpg', 'png'],
                        'required' => true,
                    ],
                ],
                'kyc_form_schema' => [
                    ['name' => 'org_name', 'type' => 'text', 'label' => 'Organization Name', 'required' => true],
                    ['name' => 'reg_number', 'type' => 'text', 'label' => 'Registration Number', 'required' => true],
                ],
            ],
        ];

        return $templates[$templateKey] ?? $templates['simple_intro'];
    }

    /**
     * Process tutorial content from request.
     */
    private function processTutorialContent(Request $request): ?array
    {
        if (!$request->has('tutorial_steps')) {
            return null;
        }

        $steps = [];
        foreach ($request->input('tutorial_steps', []) as $step) {
            if (!empty($step['title'])) {
                $steps[] = [
                    'title' => $step['title'],
                    'content' => $step['content'] ?? '',
                    'icon' => $step['icon'] ?? 'bi-circle',
                ];
            }
        }

        return ['steps' => $steps];
    }

    /**
     * Process wizard schema from request.
     */
    private function processWizardSchema(Request $request): ?array
    {
        if (!$request->has('wizard_fields')) {
            return null;
        }

        $fields = [];
        foreach ($request->input('wizard_fields', []) as $field) {
            if (!empty($field['name'])) {
                $fields[] = [
                    'name' => $field['name'],
                    'type' => $field['type'] ?? 'text',
                    'label' => $field['label'] ?? $field['name'],
                    'required' => $field['required'] ?? false,
                    'options' => $field['options'] ?? null,
                    'default' => $field['default'] ?? null,
                ];
            }
        }

        return $fields;
    }

    /**
     * Process required documents from request.
     */
    private function processRequiredDocuments(Request $request): ?array
    {
        if (!$request->has('required_documents')) {
            return null;
        }

        $documents = [];
        foreach ($request->input('required_documents', []) as $key => $doc) {
            if (!empty($doc['label'])) {
                $documents[$key] = [
                    'label' => $doc['label'],
                    'description' => $doc['description'] ?? '',
                    'accepted_types' => $doc['accepted_types'] ?? ['pdf', 'jpg', 'png'],
                    'required' => $doc['required'] ?? true,
                ];
            }
        }

        return $documents;
    }

    /**
     * Process KYC form schema from request.
     */
    private function processKycFormSchema(Request $request): ?array
    {
        if (!$request->has('kyc_form_fields')) {
            return null;
        }

        $fields = [];
        foreach ($request->input('kyc_form_fields', []) as $field) {
            if (!empty($field['name'])) {
                $fields[] = [
                    'name' => $field['name'],
                    'type' => $field['type'] ?? 'text',
                    'label' => $field['label'] ?? $field['name'],
                    'required' => $field['required'] ?? false,
                    'options' => $field['options'] ?? null,
                    'placeholder' => $field['placeholder'] ?? null,
                ];
            }
        }

        return $fields;
    }
}
