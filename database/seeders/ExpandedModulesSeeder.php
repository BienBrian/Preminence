<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleOnboardingConfig;
use App\Models\ModuleActivationSettings;
use Illuminate\Database\Seeder;

/**
 * Expanded Modules Seeder
 * 
 * Seeds the new feature modules with onboarding configurations
 * and activation settings.
 */
class ExpandedModulesSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSpiritualModules();
        $this->seedFinancialModules();
        $this->seedAdminModules();
        $this->seedAdvancedModules();
    }

    /**
     * Spiritual Content Modules
     */
    private function seedSpiritualModules(): void
    {
        // Sermons Module
        $sermons = Module::firstOrCreate(
            ['key' => 'sermons'],
            [
                'name' => 'Sermon Management',
                'slug' => 'sermons',
                'description' => 'Upload, organize, and share sermons with your congregation. Supports audio, video, and text formats.',
                'short_description' => 'Upload and manage church sermons',
                'category' => 'spiritual',
                'tags' => ['content', 'media', 'spiritual'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-mic-fill',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'sermons'],
            [
                'onboarding_type' => 'guided',
                'requires_approval' => false,
                'tutorial_content' => [
                    'steps' => [
                        [
                            'title' => 'Welcome to Sermon Management',
                            'content' => 'Share your messages with your congregation and beyond. Upload audio, video, or written sermons.',
                            'icon' => 'mic',
                        ],
                        [
                            'title' => 'Upload Your First Sermon',
                            'content' => 'Learn how to upload and categorize sermons by series, speaker, and date.',
                            'icon' => 'upload',
                        ],
                        [
                            'title' => 'Share with Your Congregation',
                            'content' => 'Sermons can be accessed via your church website and mobile app.',
                            'icon' => 'share',
                        ],
                    ],
                ],
            ]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'sermons'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => false,
            ]
        );

        // Articles Module
        $articles = Module::firstOrCreate(
            ['key' => 'articles'],
            [
                'name' => 'Articles & Blog',
                'slug' => 'articles',
                'description' => 'Write and publish church articles, devotionals, and blog posts. Reach your congregation with written content.',
                'short_description' => 'Publish articles and blog posts',
                'category' => 'content',
                'tags' => ['content', 'blog', 'writing'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-journal-text',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'articles'],
            [
                'onboarding_type' => 'guided',
                'requires_approval' => false,
                'network_participation_enabled' => true,
                'tutorial_content' => [
                    'steps' => [
                        [
                            'title' => 'Welcome to Articles',
                            'content' => 'Share your thoughts, devotionals, and church news through written content.',
                            'icon' => 'journal',
                        ],
                        [
                            'title' => 'Network Participation',
                            'content' => 'Opt-in to share your articles with a wider network of churches and receive content from others.',
                            'show_network_option' => true,
                        ],
                        [
                            'title' => 'Setup Categories',
                            'content' => 'Organize your articles by categories like Devotionals, News, Testimonies, and more.',
                            'icon' => 'folder',
                        ],
                    ],
                ],
            ]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'articles'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => false,
            ]
        );

        // Testimonials Module
        Module::firstOrCreate(
            ['key' => 'testimonials'],
            [
                'name' => 'Testimonials',
                'slug' => 'testimonials',
                'description' => 'Collect and showcase member testimonies and life stories.',
                'short_description' => 'Member testimonies and stories',
                'category' => 'spiritual',
                'tags' => ['content', 'stories'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-chat-quote-fill',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'testimonials'],
            ['onboarding_type' => 'none', 'requires_approval' => false]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'testimonials'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => false,
            ]
        );

        // Prayer Requests Module
        Module::firstOrCreate(
            ['key' => 'prayer_requests'],
            [
                'name' => 'Prayer Requests',
                'slug' => 'prayer-requests',
                'description' => 'Prayer wall where members can submit prayer requests and pray for others.',
                'short_description' => 'Prayer wall and requests',
                'category' => 'spiritual',
                'tags' => ['prayer', 'community'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-praying-hands',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'prayer_requests'],
            ['onboarding_type' => 'none', 'requires_approval' => false]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'prayer_requests'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => false,
            ]
        );
    }

    /**
     * Financial Modules
     */
    private function seedFinancialModules(): void
    {
        // Donations Module (KYC Required)
        $donations = Module::firstOrCreate(
            ['key' => 'donations'],
            [
                'name' => 'Donations & Fundraising',
                'slug' => 'donations',
                'description' => 'Accept online donations and run fundraising campaigns. Includes donor management and tax receipts.',
                'short_description' => 'Online donations and campaigns',
                'category' => 'finance',
                'tags' => ['finance', 'donations', 'fundraising'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'setup_fee' => 0,
                'icon' => 'bi-heart-fill',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'donations'],
            [
                'onboarding_type' => 'kyc',
                'requires_approval' => true,
                'required_documents' => [
                    'registration_certificate' => [
                        'label' => 'Church Registration Certificate',
                        'description' => 'Official government-issued registration document',
                        'accepted_types' => ['pdf', 'jpg', 'png'],
                        'required' => true,
                    ],
                    'board_resolution' => [
                        'label' => 'Board Resolution Letter',
                        'description' => 'Signed letter from church leadership approving online fundraising',
                        'accepted_types' => ['pdf', 'jpg', 'png'],
                        'required' => true,
                        'template_url' => '/templates/board-resolution-sample.pdf',
                    ],
                    'bank_details' => [
                        'label' => 'Bank Account Verification',
                        'description' => 'Cancelled cheque or bank statement for receiving donations',
                        'accepted_types' => ['pdf', 'jpg', 'png'],
                        'required' => true,
                    ],
                    'tax_certificate' => [
                        'label' => 'Tax Exemption Certificate (if applicable)',
                        'description' => 'Charity/tax exemption certificate for issuing tax receipts',
                        'accepted_types' => ['pdf', 'jpg', 'png'],
                        'required' => false,
                    ],
                ],
                'kyc_form_schema' => [
                    [
                        'name' => 'church_reg_number',
                        'type' => 'text',
                        'required' => true,
                        'label' => 'Church Registration Number',
                        'placeholder' => 'e.g., NRBN/XXX/XXXXX',
                    ],
                    [
                        'name' => 'year_established',
                        'type' => 'number',
                        'required' => true,
                        'label' => 'Year Established',
                        'min' => 1800,
                        'max' => 2099,
                    ],
                    [
                        'name' => 'leadership_structure',
                        'type' => 'textarea',
                        'required' => true,
                        'label' => 'Leadership Structure',
                        'placeholder' => 'Describe your church leadership hierarchy',
                        'rows' => 4,
                    ],
                    [
                        'name' => 'bank_name',
                        'type' => 'text',
                        'required' => true,
                        'label' => 'Bank Name',
                    ],
                    [
                        'name' => 'bank_account_name',
                        'type' => 'text',
                        'required' => true,
                        'label' => 'Bank Account Name',
                    ],
                    [
                        'name' => 'bank_account_number',
                        'type' => 'text',
                        'required' => true,
                        'label' => 'Bank Account Number',
                    ],
                    [
                        'name' => 'avg_monthly_donations',
                        'type' => 'select',
                        'required' => true,
                        'label' => 'Expected Monthly Donations (KES)',
                        'options' => [
                            '0-50000' => 'Under 50,000',
                            '50000-100000' => '50,000 - 100,000',
                            '100000-500000' => '100,000 - 500,000',
                            '500000+' => 'Over 500,000',
                        ],
                    ],
                ],
                'approval_instructions' => 'Verify church registration documents are valid and bank details match registration. Check for any compliance issues.',
            ]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'donations'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => true,
                'allow_trial' => true,
                'trial_days' => 30,
                'auto_approve_for_plans' => ['enterprise'],
                'activation_messages' => [
                    'pending' => 'Your application is being reviewed. This typically takes 1-2 business days.',
                    'approved' => 'Your donations module is now active! You can start creating campaigns.',
                    'rejected' => 'Your application could not be approved. Please check the rejection reason and reapply.',
                ],
            ]
        );

        // Budgets Module
        Module::firstOrCreate(
            ['key' => 'budgets'],
            [
                'name' => 'Budget Management',
                'slug' => 'budgets',
                'description' => 'Create annual budgets, track spending against budget categories, and generate variance reports.',
                'short_description' => 'Annual budgeting and tracking',
                'category' => 'finance',
                'tags' => ['finance', 'budget', 'planning'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-pie-chart-fill',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'budgets'],
            [
                'onboarding_type' => 'guided',
                'requires_approval' => false,
                'tutorial_content' => [
                    'steps' => [
                        [
                            'title' => 'Budget Planning',
                            'content' => 'Create your first annual budget by department and category.',
                        ],
                        [
                            'title' => 'Track Spending',
                            'content' => 'Monitor actual spending against budget in real-time.',
                        ],
                        [
                            'title' => 'Variance Reports',
                            'content' => 'Generate reports showing budget vs actual analysis.',
                        ],
                    ],
                ],
            ]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'budgets'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => true,
                'trial_days' => 14,
            ]
        );

        // Assets Module
        Module::firstOrCreate(
            ['key' => 'assets'],
            [
                'name' => 'Asset Management',
                'slug' => 'assets',
                'description' => 'Track church assets, depreciation, maintenance schedules, and asset assignments.',
                'short_description' => 'Track church assets and depreciation',
                'category' => 'finance',
                'tags' => ['finance', 'assets', 'inventory'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-building',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'assets'],
            ['onboarding_type' => 'guided', 'requires_approval' => false]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'assets'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => true,
                'trial_days' => 14,
            ]
        );

        // M-PESA Logs Module
        Module::firstOrCreate(
            ['key' => 'mpesa_logs'],
            [
                'name' => 'M-PESA Reconciliation',
                'slug' => 'mpesa-logs',
                'description' => 'Advanced M-PESA transaction reconciliation with automatic matching and dispute resolution.',
                'short_description' => 'Advanced M-PESA reconciliation',
                'category' => 'finance',
                'tags' => ['finance', 'mpesa', 'reconciliation'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-phone',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'mpesa_logs'],
            ['onboarding_type' => 'guided', 'requires_approval' => false]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'mpesa_logs'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => true,
                'trial_days' => 14,
            ]
        );
    }

    /**
     * Administration Modules
     */
    private function seedAdminModules(): void
    {
        // Duplication Checker
        Module::firstOrCreate(
            ['key' => 'duplication_checker'],
            [
                'name' => 'Duplicate Finder',
                'slug' => 'duplicate-finder',
                'description' => 'Intelligently find and merge duplicate member records using fuzzy matching.',
                'short_description' => 'Find and merge duplicate members',
                'category' => 'admin',
                'tags' => ['admin', 'data-quality'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-funnel-fill',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'duplication_checker'],
            ['onboarding_type' => 'guided', 'requires_approval' => false]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'duplication_checker'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => true,
                'trial_days' => 14,
            ]
        );

        // Children's Check-in
        Module::firstOrCreate(
            ['key' => 'children_checkin'],
            [
                'name' => 'Children\'s Check-in',
                'slug' => 'children-checkin',
                'description' => 'Secure check-in/check-out system for children\'s ministry with parent notifications.',
                'short_description' => 'Child check-in system',
                'category' => 'admin',
                'tags' => ['admin', 'children', 'safety'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-shield-check',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'children_checkin'],
            ['onboarding_type' => 'guided', 'requires_approval' => false]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'children_checkin'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => true,
                'trial_days' => 14,
            ]
        );

        // File Manager
        Module::firstOrCreate(
            ['key' => 'file_manager'],
            [
                'name' => 'File Manager',
                'slug' => 'file-manager',
                'description' => 'Centralized document and media storage with folders, sharing, and permissions.',
                'short_description' => 'Document and media storage',
                'category' => 'admin',
                'tags' => ['admin', 'files', 'storage'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-folder-fill',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'file_manager'],
            ['onboarding_type' => 'none', 'requires_approval' => false]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'file_manager'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => false,
            ]
        );
    }

    /**
     * Advanced Modules
     */
    private function seedAdvancedModules(): void
    {
        // Advanced Reports
        Module::firstOrCreate(
            ['key' => 'reports_advanced'],
            [
                'name' => 'Advanced Analytics',
                'slug' => 'advanced-analytics',
                'description' => 'Custom reports, dashboards, and advanced analytics for church growth metrics.',
                'short_description' => 'Custom reports and dashboards',
                'category' => 'reports',
                'tags' => ['reports', 'analytics', 'insights'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-graph-up-arrow',
                'is_active' => true,
                'is_public' => true,
            ]
        );

        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'reports_advanced'],
            ['onboarding_type' => 'guided', 'requires_approval' => false]
        );

        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'reports_advanced'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => true,
                'trial_days' => 14,
                'minimum_plan_tier' => 'standard',
            ]
        );
    }
}
