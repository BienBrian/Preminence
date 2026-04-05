<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleOnboardingConfig;
use App\Models\ModuleOnboardingStep;
use Illuminate\Database\Seeder;

/**
 * Module Onboarding Defaults Seeder
 * 
 * Creates comprehensive onboarding configurations for all 30 modules.
 * Categorized by complexity and onboarding type.
 */
class ModuleOnboardingDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCoreModules();
        $this->seedPeopleModules();
        $this->seedFinanceModules();
        $this->seedCommunicationModules();
        $this->seedContentModules();
        $this->seedAdminModules();
        $this->seedPremiumModules();
    }

    /**
     * Core modules - instant activation, minimal onboarding
     */
    private function seedCoreModules(): void
    {
        // Core - instant, no onboarding needed
        $this->createConfig('core', 'instant', []);

        // Dashboard - guided welcome
        $this->createConfig('dashboard', 'guided', [
            'welcome_message' => 'Welcome to your Church Management Dashboard!',
            'completion_message' => 'Your dashboard is ready. Explore the modules to get started.',
            'tutorial_content' => [
                'steps' => [
                    [
                        'title' => 'Your Dashboard',
                        'content' => 'This is your central hub for managing your church. View key metrics, recent activities, and quick actions.',
                        'icon' => 'bi-speedometer2',
                    ],
                    [
                        'title' => 'Navigation',
                        'content' => 'Use the sidebar to access different modules. Each module helps you manage a specific aspect of your church.',
                        'icon' => 'bi-list',
                    ],
                    [
                        'title' => 'Profile Menu',
                        'content' => 'Access your profile, settings, and additional modules from the top-right menu.',
                        'icon' => 'bi-person',
                    ],
                ],
            ],
        ]);
    }

    /**
     * People & membership modules
     */
    private function seedPeopleModules(): void
    {
        // People - setup wizard for initial configuration
        $peopleConfig = $this->createConfig('people', 'setup_wizard', [
            'welcome_message' => 'Let\'s set up your member directory.',
            'completion_message' => 'Your member directory is configured! Start adding members or import existing data.',
            'estimated_setup_time_minutes' => 5,
            'setup_wizard_schema' => [
                ['name' => 'enable_family_tracking', 'type' => 'checkbox', 'label' => 'Enable family relationships', 'default' => true],
                ['name' => 'enable_children_profiles', 'type' => 'checkbox', 'label' => 'Enable children profiles', 'default' => true],
                ['name' => 'default_member_status', 'type' => 'select', 'label' => 'Default member status', 'options' => ['active' => 'Active', 'visitor' => 'Visitor', 'inactive' => 'Inactive']],
            ],
        ]);
        $this->createSteps($peopleConfig, [
            ['title' => 'Member Directory Setup', 'content' => 'Configure how you want to track members and their relationships.', 'content_type' => 'form'],
        ]);

        // Attendance - guided tutorial
        $this->createConfig('attendance', 'guided', [
            'welcome_message' => 'Track attendance for services and events.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Recording Attendance', 'content' => 'Mark attendance for services, events, or check-in children.', 'icon' => 'bi-clipboard-check'],
                    ['title' => 'Reports', 'content' => 'View attendance trends and generate reports.', 'icon' => 'bi-graph-up'],
                ],
            ],
        ]);
    }

    /**
     * Financial modules - complex setup wizards and KYC
     */
    private function seedFinanceModules(): void
    {
        // Finance - setup wizard for funds and currency
        $financeConfig = $this->createConfig('finance', 'setup_wizard', [
            'welcome_message' => 'Set up your church financial management.',
            'completion_message' => 'Your finance module is ready! Start recording contributions and managing funds.',
            'estimated_setup_time_minutes' => 10,
            'documentation_url' => '/docs/finance/getting-started',
            'setup_wizard_schema' => [
                ['name' => 'base_currency', 'type' => 'select', 'label' => 'Base Currency', 'options' => ['KES' => 'Kenyan Shilling (KES)', 'USD' => 'US Dollar (USD)', 'EUR' => 'Euro (EUR)', 'GBP' => 'British Pound (GBP)'], 'required' => true],
                ['name' => 'fiscal_year_start', 'type' => 'select', 'label' => 'Fiscal Year Starts', 'options' => ['01' => 'January', '04' => 'April', '07' => 'July', '10' => 'October'], 'required' => true],
                ['name' => 'default_funds', 'type' => 'checkbox', 'label' => 'Create default funds (Tithes, Offerings, Building Fund)', 'default' => true],
            ],
        ]);
        $this->createSteps($financeConfig, [
            ['title' => 'Currency & Fiscal Year', 'content' => 'Set your base currency and fiscal year start month.', 'content_type' => 'form', 'estimated_minutes' => 2],
            ['title' => 'Default Funds', 'content' => 'Create common fund categories or customize your own.', 'content_type' => 'form', 'estimated_minutes' => 3],
            ['title' => 'Ready to Go', 'content' => 'Your finance module is configured. You can now record contributions and track giving.', 'content_type' => 'completion'],
        ]);

        // M-Pesa - setup wizard for paybill
        $mpesaConfig = $this->createConfig('mpesa', 'setup_wizard', [
            'welcome_message' => 'Configure M-Pesa integration for automatic reconciliation.',
            'completion_message' => 'M-Pesa is configured! Contributions via your paybill will be automatically recorded.',
            'estimated_setup_time_minutes' => 15,
            'documentation_url' => '/docs/mpesa/setup',
            'setup_wizard_schema' => [
                ['name' => 'paybill_number', 'type' => 'text', 'label' => 'M-Pesa Paybill Number', 'required' => true],
                ['name' => 'account_number', 'type' => 'text', 'label' => 'Account Number (if applicable)', 'required' => false],
                ['name' => 'consumer_key', 'type' => 'text', 'label' => 'Daraja API Consumer Key', 'required' => true],
                ['name' => 'consumer_secret', 'type' => 'password', 'label' => 'Daraja API Consumer Secret', 'required' => true],
                ['name' => 'enable_sms_notifications', 'type' => 'checkbox', 'label' => 'Send SMS notifications for contributions', 'default' => true],
            ],
        ]);
        $this->createSteps($mpesaConfig, [
            ['title' => 'Paybill Information', 'content' => 'Enter your M-Pesa paybill number and account details.', 'content_type' => 'form', 'estimated_minutes' => 3],
            ['title' => 'API Credentials', 'content' => 'Connect to Safaricom Daraja API for automatic reconciliation.', 'content_type' => 'form', 'estimated_minutes' => 5],
            ['title' => 'Notifications', 'content' => 'Configure SMS notifications for donors and administrators.', 'content_type' => 'form', 'estimated_minutes' => 2],
            ['title' => 'Test & Activate', 'content' => 'Send a test transaction to verify the integration.', 'content_type' => 'confirmation', 'estimated_minutes' => 5],
        ]);

        // Donations - KYC required
        $this->createConfig('donations', 'kyc', [
            'welcome_message' => 'Accept online donations and run fundraising campaigns.',
            'completion_message' => 'Your donations module will be activated after document verification.',
            'requires_approval' => true,
            'estimated_setup_time_minutes' => 20,
            'approval_instructions' => 'Verify church registration documents are valid and bank details match registration.',
            'required_documents' => [
                'registration_certificate' => [
                    'label' => 'Church Registration Certificate',
                    'description' => 'Official government-issued registration document',
                    'accepted_types' => ['pdf', 'jpg', 'png'],
                    'required' => true,
                ],
                'bank_details' => [
                    'label' => 'Bank Account Verification',
                    'description' => 'Cancelled cheque or bank statement',
                    'accepted_types' => ['pdf', 'jpg', 'png'],
                    'required' => true,
                ],
            ],
            'kyc_form_schema' => [
                ['name' => 'church_reg_number', 'type' => 'text', 'label' => 'Church Registration Number', 'required' => true],
                ['name' => 'bank_name', 'type' => 'text', 'label' => 'Bank Name', 'required' => true],
                ['name' => 'bank_account_name', 'type' => 'text', 'label' => 'Bank Account Name', 'required' => true],
                ['name' => 'bank_account_number', 'type' => 'text', 'label' => 'Bank Account Number', 'required' => true],
            ],
        ]);

        // Budgets - setup wizard
        $budgetsConfig = $this->createConfig('budgets', 'setup_wizard', [
            'welcome_message' => 'Create and manage your church budget.',
            'completion_message' => 'Your budget framework is set up! Start creating your annual budget.',
            'estimated_setup_time_minutes' => 8,
            'setup_wizard_schema' => [
                ['name' => 'budget_period', 'type' => 'select', 'label' => 'Budget Period', 'options' => ['annual' => 'Annual', 'quarterly' => 'Quarterly'], 'default' => 'annual'],
                ['name' => 'default_categories', 'type' => 'checkbox', 'label' => 'Create default budget categories', 'default' => true],
            ],
        ]);
        $this->createSteps($budgetsConfig, [
            ['title' => 'Budget Setup', 'content' => 'Configure your budget period and categories.', 'content_type' => 'form'],
        ]);

        // Assets - guided
        $this->createConfig('assets', 'guided', [
            'welcome_message' => 'Track your church assets and depreciation.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Asset Categories', 'content' => 'Create categories for different types of assets (vehicles, equipment, furniture).', 'icon' => 'bi-box-seam'],
                    ['title' => 'Depreciation', 'content' => 'Set depreciation methods and rates for each category.', 'icon' => 'bi-graph-down'],
                    ['title' => 'Maintenance', 'content' => 'Schedule and track maintenance activities.', 'icon' => 'bi-tools'],
                ],
            ],
        ]);

        // M-Pesa Logs - guided
        $this->createConfig('mpesa_logs', 'guided', [
            'welcome_message' => 'Advanced M-PESA transaction reconciliation.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Transaction Matching', 'content' => 'Automatic and manual matching of M-Pesa transactions to contributions.', 'icon' => 'bi-link'],
                    ['title' => 'Reference Mapping', 'content' => 'Map M-Pesa references to funds and campaigns.', 'icon' => 'bi-diagram-2'],
                    ['title' => 'Reports', 'content' => 'Generate detailed reconciliation reports.', 'icon' => 'bi-file-earmark-text'],
                ],
            ],
        ]);

        // Shop - complex setup wizard
        $shopConfig = $this->createConfig('shop', 'setup_wizard', [
            'welcome_message' => 'Set up your church online store.',
            'completion_message' => 'Your shop is ready! Add products and start selling.',
            'estimated_setup_time_minutes' => 20,
            'setup_wizard_schema' => [
                ['name' => 'store_name', 'type' => 'text', 'label' => 'Store Name', 'required' => true],
                ['name' => 'currency', 'type' => 'select', 'label' => 'Currency', 'options' => ['KES' => 'KES', 'USD' => 'USD']],
                ['name' => 'enable_shipping', 'type' => 'checkbox', 'label' => 'Enable shipping', 'default' => true],
                ['name' => 'enable_digital_downloads', 'type' => 'checkbox', 'label' => 'Enable digital downloads', 'default' => false],
            ],
        ]);
        $this->createSteps($shopConfig, [
            ['title' => 'Store Basics', 'content' => 'Name your store and select currency.', 'content_type' => 'form'],
            ['title' => 'Payment Gateway', 'content' => 'Configure payment methods (M-Pesa, Cards, etc.).', 'content_type' => 'form'],
            ['title' => 'Shipping', 'content' => 'Set up shipping methods and rates.', 'content_type' => 'form'],
            ['title' => 'First Product', 'content' => 'Add your first product to the store.', 'content_type' => 'form'],
        ]);
    }

    /**
     * Communication modules
     */
    private function seedCommunicationModules(): void
    {
        // SMS - setup wizard
        $smsConfig = $this->createConfig('sms', 'setup_wizard', [
            'welcome_message' => 'Configure SMS messaging for your church.',
            'completion_message' => 'SMS is configured! Start sending messages to your members.',
            'estimated_setup_time_minutes' => 10,
            'setup_wizard_schema' => [
                ['name' => 'provider', 'type' => 'select', 'label' => 'SMS Provider', 'options' => ['africastalking' => 'Africa\'s Talking', 'twilio' => 'Twilio', 'other' => 'Other']],
                ['name' => 'sender_id', 'type' => 'text', 'label' => 'Sender ID', 'placeholder' => 'CHURCH'],
                ['name' => 'api_key', 'type' => 'password', 'label' => 'API Key'],
            ],
        ]);
        $this->createSteps($smsConfig, [
            ['title' => 'Provider Setup', 'content' => 'Choose your SMS provider and enter API credentials.', 'content_type' => 'form'],
            ['title' => 'Sender ID', 'content' => 'Configure your sender ID and test sending.', 'content_type' => 'form'],
        ]);

        // Email - setup wizard
        $emailConfig = $this->createConfig('email', 'setup_wizard', [
            'welcome_message' => 'Set up email campaigns for your church.',
            'completion_message' => 'Email campaigns are ready! Create your first newsletter.',
            'estimated_setup_time_minutes' => 8,
            'setup_wizard_schema' => [
                ['name' => 'smtp_host', 'type' => 'text', 'label' => 'SMTP Host'],
                ['name' => 'smtp_port', 'type' => 'number', 'label' => 'SMTP Port', 'default' => 587],
                ['name' => 'from_email', 'type' => 'email', 'label' => 'From Email Address'],
                ['name' => 'from_name', 'type' => 'text', 'label' => 'From Name'],
            ],
        ]);
        $this->createSteps($emailConfig, [
            ['title' => 'SMTP Configuration', 'content' => 'Enter your SMTP server details.', 'content_type' => 'form'],
            ['title' => 'Sender Details', 'content' => 'Set the from name and email address.', 'content_type' => 'form'],
        ]);
    }

    /**
     * Content modules
     */
    private function seedContentModules(): void
    {
        // Events - guided
        $this->createConfig('events', 'guided', [
            'welcome_message' => 'Manage church events and notices.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Creating Events', 'content' => 'Add events with dates, locations, and descriptions.', 'icon' => 'bi-calendar-plus'],
                    ['title' => 'Registration', 'content' => 'Enable registration and set capacity limits.', 'icon' => 'bi-ticket'],
                    ['title' => 'Notice Board', 'content' => 'Publish important announcements.', 'icon' => 'bi-megaphone'],
                ],
            ],
        ]);

        // Spiritual - guided
        $this->createConfig('spiritual', 'guided', [
            'welcome_message' => 'Manage spiritual content for your church.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Sermons', 'content' => 'Upload and organize sermons by series.', 'icon' => 'bi-mic'],
                    ['title' => 'Testimonials', 'content' => 'Collect and share member testimonies.', 'icon' => 'bi-chat-quote'],
                    ['title' => 'Prayer Requests', 'content' => 'Manage the prayer wall.', 'icon' => 'bi-praying-hands'],
                ],
            ],
        ]);

        // Sermons - guided
        $this->createConfig('sermons', 'guided', [
            'welcome_message' => 'Upload and organize your sermons.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Sermon Series', 'content' => 'Create series to organize related messages.', 'icon' => 'bi-collection'],
                    ['title' => 'Upload Formats', 'content' => 'Support for audio, video, and text sermons.', 'icon' => 'bi-upload'],
                    ['title' => 'Sharing', 'content' => 'Share sermons on your website and social media.', 'icon' => 'bi-share'],
                ],
            ],
        ]);

        // Articles - guided with network opt-in
        $this->createConfig('articles', 'guided', [
            'welcome_message' => 'Publish articles and blog posts.',
            'completion_message' => 'Your articles module is ready! Start writing and publishing.',
            'network_participation_enabled' => true,
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Writing Articles', 'content' => 'Create articles with rich text editor.', 'icon' => 'bi-pencil'],
                    ['title' => 'Network Participation', 'content' => 'Opt-in to share articles with other churches.', 'icon' => 'bi-globe'],
                    ['title' => 'Categories', 'content' => 'Organize articles by categories.', 'icon' => 'bi-folder'],
                ],
            ],
        ]);

        // Testimonials - guided
        $this->createConfig('testimonials', 'guided', [
            'welcome_message' => 'Collect and share testimonies.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Collection', 'content' => 'Members can submit testimonies for approval.', 'icon' => 'bi-inbox'],
                    ['title' => 'Moderation', 'content' => 'Review and approve testimonies before publishing.', 'icon' => 'bi-check-circle'],
                    ['title' => 'Display', 'content' => 'Showcase testimonies on your website.', 'icon' => 'bi-display'],
                ],
            ],
        ]);

        // Prayer Requests - guided
        $this->createConfig('prayer_requests', 'guided', [
            'welcome_message' => 'Manage your church prayer wall.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Prayer Wall', 'content' => 'Members submit prayer requests anonymously or identified.', 'icon' => 'bi-wall'],
                    ['title' => 'Prayer Chain', 'content' => 'Notify prayer teams of urgent requests.', 'icon' => 'bi-link'],
                    ['title' => 'Prayed For', 'content' => 'Track how many people prayed for each request.', 'icon' => 'bi-heart'],
                ],
            ],
        ]);

        // Website - setup wizard
        $websiteConfig = $this->createConfig('website', 'setup_wizard', [
            'welcome_message' => 'Set up your church website.',
            'completion_message' => 'Your website is configured! Customize the theme and publish.',
            'estimated_setup_time_minutes' => 15,
            'setup_wizard_schema' => [
                ['name' => 'site_title', 'type' => 'text', 'label' => 'Website Title', 'required' => true],
                ['name' => 'tagline', 'type' => 'text', 'label' => 'Tagline'],
                ['name' => 'theme', 'type' => 'select', 'label' => 'Theme', 'options' => ['default' => 'Default', 'modern' => 'Modern', 'classic' => 'Classic']],
                ['name' => 'custom_domain', 'type' => 'text', 'label' => 'Custom Domain (optional)', 'placeholder' => 'www.yourchurch.org'],
            ],
        ]);
        $this->createSteps($websiteConfig, [
            ['title' => 'Site Identity', 'content' => 'Set your church name and tagline.', 'content_type' => 'form'],
            ['title' => 'Theme Selection', 'content' => 'Choose a visual theme for your website.', 'content_type' => 'form'],
            ['title' => 'Domain', 'content' => 'Configure your custom domain or use the subdomain.', 'content_type' => 'form'],
        ]);

        // Media - guided
        $this->createConfig('media', 'guided', [
            'welcome_message' => 'Manage your media library.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'File Upload', 'content' => 'Upload images, documents, and media files.', 'icon' => 'bi-cloud-upload'],
                    ['title' => 'Organization', 'content' => 'Create folders to organize your files.', 'icon' => 'bi-folder-tree'],
                    ['title' => 'Usage', 'content' => 'Use media across different modules.', 'icon' => 'bi-link-45deg'],
                ],
            ],
        ]);
    }

    /**
     * Administration modules
     */
    private function seedAdminModules(): void
    {
        // Reports - guided
        $this->createConfig('reports', 'guided', [
            'welcome_message' => 'Generate reports for your church.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Report Types', 'content' => 'Financial, attendance, and member reports.', 'icon' => 'bi-file-earmark-text'],
                    ['title' => 'Date Ranges', 'content' => 'Filter reports by custom date ranges.', 'icon' => 'bi-calendar-range'],
                    ['title' => 'Export', 'content' => 'Export reports to PDF, Excel, or CSV.', 'icon' => 'bi-download'],
                ],
            ],
        ]);

        // Links - instant (simple feature)
        $this->createConfig('links', 'instant', []);

        // Duplication Checker - guided
        $this->createConfig('duplication_checker', 'guided', [
            'welcome_message' => 'Find and merge duplicate member records.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Scanning', 'content' => 'Run the duplicate detection algorithm.', 'icon' => 'bi-search'],
                    ['title' => 'Review', 'content' => 'Review suggested duplicates.', 'icon' => 'bi-eye'],
                    ['title' => 'Merge', 'content' => 'Merge duplicates while preserving data.', 'icon' => 'bi-intersect'],
                ],
            ],
        ]);

        // Children's Check-in - setup wizard
        $checkinConfig = $this->createConfig('children_checkin', 'setup_wizard', [
            'welcome_message' => 'Set up secure children\'s check-in.',
            'completion_message' => 'Check-in is configured! Set up your first location.',
            'estimated_setup_time_minutes' => 10,
            'setup_wizard_schema' => [
                ['name' => 'location_name', 'type' => 'text', 'label' => 'Location Name', 'placeholder' => 'Children\'s Ministry'],
                ['name' => 'age_groups', 'type' => 'checkbox', 'label' => 'Create default age groups', 'default' => true],
                ['name' => 'print_labels', 'type' => 'checkbox', 'label' => 'Print security labels', 'default' => true],
            ],
        ]);
        $this->createSteps($checkinConfig, [
            ['title' => 'Location Setup', 'content' => 'Add your check-in location.', 'content_type' => 'form'],
            ['title' => 'Age Groups', 'content' => 'Define age groups for your classes.', 'content_type' => 'form'],
            ['title' => 'Security', 'content' => 'Configure security labels and guardian notifications.', 'content_type' => 'form'],
        ]);

        // File Manager - guided
        $this->createConfig('file_manager', 'guided', [
            'welcome_message' => 'Manage your church files and documents.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Folders', 'content' => 'Create folders to organize files.', 'icon' => 'bi-folder-plus'],
                    ['title' => 'Permissions', 'content' => 'Control who can access different folders.', 'icon' => 'bi-shield-check'],
                    ['title' => 'Sharing', 'content' => 'Share files with members or teams.', 'icon' => 'bi-share'],
                ],
            ],
        ]);

        // Reports Advanced - guided
        $this->createConfig('reports_advanced', 'guided', [
            'welcome_message' => 'Create custom dashboards and reports.',
            'tutorial_content' => [
                'steps' => [
                    ['title' => 'Dashboards', 'content' => 'Build custom dashboards with widgets.', 'icon' => 'bi-grid'],
                    ['title' => 'Custom Reports', 'content' => 'Create reports with custom filters.', 'icon' => 'bi-funnel'],
                    ['title' => 'Scheduling', 'content' => 'Schedule automated report delivery.', 'icon' => 'bi-clock'],
                ],
            ],
        ]);
    }

    /**
     * Premium modules
     */
    private function seedPremiumModules(): void
    {
        // Discipleship - setup wizard
        $discipleshipConfig = $this->createConfig('discipleship', 'setup_wizard', [
            'welcome_message' => 'Set up discipleship tracking.',
            'completion_message' => 'Discipleship module is ready! Create your first track.',
            'estimated_setup_time_minutes' => 12,
            'setup_wizard_schema' => [
                ['name' => 'track_name', 'type' => 'text', 'label' => 'First Track Name', 'placeholder' => 'New Believers'],
                ['name' => 'enable_mentorship', 'type' => 'checkbox', 'label' => 'Enable mentorship matching', 'default' => true],
            ],
        ]);
        $this->createSteps($discipleshipConfig, [
            ['title' => 'Discipleship Tracks', 'content' => 'Create tracks for spiritual growth stages.', 'content_type' => 'form'],
            ['title' => 'Mentorship', 'content' => 'Configure mentor matching.', 'content_type' => 'form'],
        ]);

        // API Access - setup wizard
        $apiConfig = $this->createConfig('api_access', 'setup_wizard', [
            'welcome_message' => 'Set up API access for developers.',
            'completion_message' => 'API access is configured! Generate your first API key.',
            'estimated_setup_time_minutes' => 8,
            'setup_wizard_schema' => [
                ['name' => 'rate_limit', 'type' => 'select', 'label' => 'Rate Limit', 'options' => ['100' => '100/hour', '1000' => '1000/hour', '10000' => '10000/hour']],
                ['name' => 'enable_webhooks', 'type' => 'checkbox', 'label' => 'Enable webhooks', 'default' => false],
            ],
        ]);
        $this->createSteps($apiConfig, [
            ['title' => 'API Settings', 'content' => 'Configure rate limits and permissions.', 'content_type' => 'form'],
            ['title' => 'Webhooks', 'content' => 'Set up webhook endpoints for real-time events.', 'content_type' => 'form'],
        ]);

        // Integrations - setup wizard
        $integrationsConfig = $this->createConfig('integrations', 'setup_wizard', [
            'welcome_message' => 'Configure third-party integrations.',
            'completion_message' => 'Integration framework is ready! Add your first integration.',
            'setup_wizard_schema' => [
                ['name' => 'zapier_enabled', 'type' => 'checkbox', 'label' => 'Enable Zapier integration', 'default' => false],
            ],
        ]);
        $this->createSteps($integrationsConfig, [
            ['title' => 'Available Integrations', 'content' => 'Choose which integrations to enable.', 'content_type' => 'form'],
        ]);
    }

    /**
     * Helper: Create onboarding config
     */
    private function createConfig(string $moduleKey, string $type, array $data): ModuleOnboardingConfig
    {
        $defaults = [
            'module_key' => $moduleKey,
            'onboarding_type' => $type,
            'requires_approval' => false,
            'is_configured' => true,
            'configured_at' => now(),
            'preview_enabled' => true,
            'auto_redirect_to_module' => true,
            'contextual_help_enabled' => false,
        ];

        return ModuleOnboardingConfig::updateOrCreate(
            ['module_key' => $moduleKey],
            array_merge($defaults, $data)
        );
    }

    /**
     * Helper: Create onboarding steps
     */
    private function createSteps(ModuleOnboardingConfig $config, array $steps): void
    {
        foreach ($steps as $index => $stepData) {
            ModuleOnboardingStep::updateOrCreate(
                [
                    'module_onboarding_config_id' => $config->id,
                    'step_key' => $stepData['step_key'] ?? 'step_' . ($index + 1),
                ],
                array_merge([
                    'step_number' => $index + 1,
                    'is_active' => true,
                    'is_required' => true,
                    'is_skippable' => false,
                    'allow_back' => $index > 0,
                ], $stepData)
            );
        }
    }
}
