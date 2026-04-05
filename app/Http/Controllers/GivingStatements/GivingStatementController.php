<?php

namespace App\Http\Controllers\GivingStatements;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\GivingReportLog;
use App\Models\GivingStatementConfig;
use App\Models\Source;
use App\Models\User;
use App\Services\GivingReportService;
use App\Services\TenantMailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GivingStatementController extends DashboardController
{
    private GivingReportService $reportService;

    public function __construct(GivingReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
        
        // Module-based permission middleware
        $this->middleware(['permission:View Giving Statements'], ['only' => ['index']]);
        $this->middleware(['permission:Generate Giving Statements'], ['only' => ['generate', 'preview', 'getContributors']]);
        $this->middleware(['permission:Download Giving Statements'], ['only' => ['download', 'downloadNew']]);
        $this->middleware(['permission:Email Giving Statements'], ['only' => ['email']]);
        $this->middleware(['permission:Bulk Email Giving Statements'], ['only' => ['bulkEmail']]);
        $this->middleware(['permission:Configure Giving Statements'], ['only' => ['settings', 'updateSettings', 'testEmail']]);
        $this->middleware(['permission:View All Reports'], ['only' => []]); // For future use
        $this->middleware(['permission:Delete Reports'], ['only' => []]); // For future use
    }

    /**
     * Display the giving statements main page.
     */
    public function index()
    {
        $tenant = auth()->user()->tenant;
        
        // Get recent report logs
        $recentReports = GivingReportLog::forTenant($tenant->id)
            ->with(['user:id,firstname,lastname,email,phone', 'generatedBy:id,firstname,lastname'])
            ->latest()
            ->limit(20)
            ->get();

        // Get statistics
        $stats = [
            'total_generated' => GivingReportLog::forTenant($tenant->id)->count(),
            'total_emailed' => GivingReportLog::forTenant($tenant->id)->whereNotNull('email_sent_to')->count(),
            'total_amount_reported' => GivingReportLog::forTenant($tenant->id)->sum('total_amount'),
            'this_month' => GivingReportLog::forTenant($tenant->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Get members for dropdown (all members, not just active)
        $members = User::where('tenant_id', $tenant->id)
            ->select('id', 'firstname', 'lastname', 'email', 'phone', 'status')
            ->orderBy('firstname')
            ->get();

        // Get giving categories
        $categories = Source::where('tenant_id', $tenant->id)
            ->where('ftype', 0) // Collections only
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('giving_statements.index', compact(
            'recentReports',
            'stats',
            'members',
            'categories'
        ));
    }

    /**
     * Display the generate form.
     */
    public function generate()
    {
        $tenant = auth()->user()->tenant;

        // Get all members (current and future)
        $members = User::where('tenant_id', $tenant->id)
            ->select('id', 'firstname', 'lastname', 'email', 'phone', 'status', 'email_verified_at')
            ->orderBy('firstname')
            ->get();

        // Get categories
        $categories = Source::where('tenant_id', $tenant->id)
            ->where('ftype', 0)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get config
        $config = GivingStatementConfig::forTenant($tenant->id);

        // Date presets
        $datePresets = $this->getDatePresets();

        return view('giving_statements.generate', compact(
            'members',
            'categories',
            'config',
            'datePresets'
        ));
    }

    /**
     * Get members who have contributions in selected categories for the given period.
     */
    public function getContributors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:sources,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = auth()->user()->tenant;

        $query = DB::table('funds')
            ->where('funds.tenant_id', $tenant->id)
            ->whereBetween('funds.created_at', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59'])
            ->where('sources.ftype', 0)
            ->join('sources', 'sources.id', '=', 'funds.source')
            ->join('users', 'users.id', '=', 'funds.user_id')
            ->select('users.id', 'users.firstname', 'users.lastname', 'users.email', 'users.phone', 'users.status', 'users.email_verified_at')
            ->distinct();

        if (!empty($request->categories)) {
            $query->whereIn('funds.source', $request->categories);
        }

        $contributors = $query->orderBy('users.firstname')->get();

        return response()->json([
            'success' => true,
            'contributors' => $contributors,
            'count' => $contributors->count(),
        ]);
    }

    /**
     * Generate a preview of the giving statement.
     */
    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:sources,id',
            'group_by' => 'in:category,month,none',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = auth()->user()->tenant;
        
        // Verify user belongs to tenant
        $user = User::where('id', $request->user_id)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        $preview = $this->reportService->generatePreview(
            $tenant->id,
            $user->id,
            $request->date_from,
            $request->date_to,
            $request->categories ?? [],
            $request->group_by ?? 'category'
        );

        return response()->json([
            'success' => true,
            'data' => $preview,
            'member' => [
                'id' => $user->id,
                'name' => trim("{$user->firstname} {$user->lastname}"),
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Download a generated giving statement from log.
     */
    public function download(int $logId)
    {
        $tenant = auth()->user()->tenant;

        $log = GivingReportLog::forTenant($tenant->id)
            ->where('id', $logId)
            ->firstOrFail();

        if (!Storage::exists($log->file_path)) {
            return redirect()->back()->with('error', 'File not found. Please regenerate the report.');
        }

        // Update opened timestamp
        if (!$log->opened_at) {
            $log->update(['opened_at' => now()]);
        }

        return Storage::download($log->file_path, $log->file_name);
    }

    /**
     * Generate and download a new giving statement.
     */
    public function downloadNew(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'categories' => 'nullable|array',
            'password_type' => 'required|in:phone,custom_code,none',
            'password_value' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tenant = auth()->user()->tenant;
        
        $user = User::where('id', $request->user_id)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Member not found');
        }

        try {
            $result = $this->reportService->generatePdf(
                $tenant,
                $user,
                $request->date_from,
                $request->date_to,
                $request->categories ?? [],
                $request->password_type,
                $request->password_value,
                auth()->id()
            );

            return Storage::download($result['file_path'], $result['file_name']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Email a giving statement to a member.
     */
    public function email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'categories' => 'nullable|array',
            'password_type' => 'required|in:phone,custom_code,none',
            'password_value' => 'nullable|string|max:20',
            'custom_message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = auth()->user()->tenant;
        
        $user = User::where('id', $request->user_id)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        // Check if email is verified
        if (!$user->email_verified_at) {
            return response()->json([
                'error' => 'Email not verified',
                'message' => "{$user->firstname} {$user->lastname} has not verified their email address ({$user->email}).",
                'member' => [
                    'id' => $user->id,
                    'name' => "{$user->firstname} {$user->lastname}",
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'has_phone' => !empty($user->phone),
                ],
                'can_send_sms' => !empty($user->phone),
                'send_verification_url' => url('dashboard/users/send-verification-request'),
            ], 422);
        }

        try {
            $result = $this->reportService->emailReport(
                $tenant,
                $user,
                $request->date_from,
                $request->date_to,
                $request->categories ?? [],
                $request->password_type,
                $request->password_value,
                auth()->id(),
                $request->custom_message
            );

            // Build success message
            $message = "Giving statement sent to {$user->firstname} {$user->lastname} at {$user->email}";
            if ($result['sms_sent'] ?? false) {
                $message .= " and SMS alert sent to their phone";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'log_id' => $result['log_id'],
                'email_sent' => $result['email_sent'] ?? true,
                'sms_sent' => $result['sms_sent'] ?? false,
                'recipient' => [
                    'name' => "{$user->firstname} {$user->lastname}",
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send email',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk email giving statements.
     */
    public function bulkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'categories' => 'nullable|array',
            'password_type' => 'required|in:phone,custom_code,none',
            'custom_message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = auth()->user()->tenant;
        $userIds = $request->user_ids;

        // Get users with verified emails
        $users = User::whereIn('id', $userIds)
            ->where('tenant_id', $tenant->id)
            ->whereNotNull('email_verified_at')
            ->get();

        $skippedUsers = User::whereIn('id', $userIds)
            ->where('tenant_id', $tenant->id)
            ->whereNull('email_verified_at')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => trim("{$u->firstname} {$u->lastname}")])
            ->values()
            ->toArray();

        $results = [
            'sent' => 0,
            'failed' => 0,
            'skipped' => count($skippedUsers),
            'log_ids' => [],
        ];

        foreach ($users as $user) {
            try {
                $result = $this->reportService->emailReport(
                    $tenant,
                    $user,
                    $request->date_from,
                    $request->date_to,
                    $request->categories ?? [],
                    $request->password_type,
                    null,
                    auth()->id(),
                    $request->custom_message
                );

                $results['sent']++;
                $results['log_ids'][] = $result['log_id'];
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sent {$results['sent']} giving statements. {$results['skipped']} skipped (unverified emails).",
            'results' => $results,
            'skipped_users' => $skippedUsers,
        ]);
    }

    /**
     * Display settings page.
     */
    public function settings()
    {
        $tenant = auth()->user()->tenant;
        $config = GivingStatementConfig::forTenant($tenant->id);

        $emailDrivers = TenantMailConfigService::getAvailableDrivers();
        $encryptionOptions = TenantMailConfigService::getEncryptionOptions();

        return view('giving_statements.settings', compact('config', 'emailDrivers', 'encryptionOptions'));
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // General settings
            'default_password_type' => 'required|in:phone,custom_code,sms_otp,none',
            'custom_password_code' => 'nullable|string|max:20',
            'email_template_subject' => 'nullable|string|max:255',
            'email_template_body' => 'nullable|string|max:5000',
            'include_thank_you_note' => 'boolean',
            'thank_you_note' => 'nullable|string|max:1000',
            'church_header_text' => 'nullable|string|max:255',
            'enable_email_delivery' => 'boolean',
            
            // Email driver settings
            'email_driver' => 'required|in:default,smtp,mailgun,sendgrid,postmark,ses,log',
            
            // SMTP settings (required if driver is smtp)
            'smtp_host' => 'nullable|required_if:email_driver,smtp|string|max:255',
            'smtp_port' => 'nullable|required_if:email_driver,smtp|integer|min:1|max:65535',
            'smtp_username' => 'nullable|required_if:email_driver,smtp|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl,none',
            
            // From address settings
            'email_from_address' => 'nullable|email|max:255',
            'email_from_name' => 'nullable|string|max:255',
            'email_reply_to' => 'nullable|email|max:255',
            
            // Service-specific settings
            'email_service_key' => 'nullable|string|max:500',
            'email_service_secret' => 'nullable|string|max:500',
            'email_service_domain' => 'nullable|string|max:255',
            'email_service_region' => 'nullable|string|max:50',
            
            // Test settings
            'test_email_address' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $tenant = auth()->user()->tenant;
        $config = GivingStatementConfig::forTenant($tenant->id);

        // Build update data
        $updateData = [
            // General settings
            'default_password_type' => $request->default_password_type,
            'custom_password_code' => $request->custom_password_code,
            'email_template_subject' => $request->email_template_subject,
            'email_template_body' => $request->email_template_body,
            'include_thank_you_note' => $request->boolean('include_thank_you_note'),
            'thank_you_note' => $request->thank_you_note,
            'church_header_text' => $request->church_header_text,
            'enable_bulk_generation' => true, // Always enabled
            'enable_email_delivery' => $request->boolean('enable_email_delivery'),
            
            // Email driver settings
            'email_driver' => $request->email_driver,
            'smtp_host' => $request->smtp_host,
            'smtp_port' => $request->smtp_port,
            'smtp_username' => $request->smtp_username,
            'smtp_encryption' => $request->smtp_encryption,
            'email_from_address' => $request->email_from_address,
            'email_from_name' => $request->email_from_name,
            'email_reply_to' => $request->email_reply_to,
            'email_service_key' => $request->email_service_key,
            'email_service_secret' => $request->email_service_secret,
            'email_service_domain' => $request->email_service_domain,
            'email_service_region' => $request->email_service_region,
            'test_email_address' => $request->test_email_address,
        ];

        // Only update password if provided (don't overwrite with null)
        if ($request->filled('smtp_password')) {
            $updateData['smtp_password'] = $request->smtp_password;
        }

        $config->update($updateData);

        // Test email configuration if requested
        if ($request->has('test_email') && $request->test_email_address) {
            $testResult = TenantMailConfigService::testConfiguration(
                $tenant->id,
                $request->test_email_address
            );

            if ($testResult['success']) {
                return redirect()->back()->with('success', 'Settings saved and test email sent successfully!');
            } else {
                return redirect()->back()
                    ->with('warning', 'Settings saved but test email failed: ' . $testResult['message'])
                    ->withInput();
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Test email configuration.
     */
    public function testEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_email_address' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid test email address',
            ], 422);
        }

        $tenant = auth()->user()->tenant;
        
        $result = TenantMailConfigService::testConfiguration(
            $tenant->id,
            $request->test_email_address
        );

        return response()->json($result);
    }

    /**
     * Get date presets for the date range selector.
     */
    private function getDatePresets(): array
    {
        return [
            'current_month' => [
                'label' => 'Current Month',
                'from' => now()->startOfMonth()->format('Y-m-d'),
                'to' => now()->endOfMonth()->format('Y-m-d'),
            ],
            'last_month' => [
                'label' => 'Last Month',
                'from' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
                'to' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
            'current_quarter' => [
                'label' => 'Current Quarter',
                'from' => now()->startOfQuarter()->format('Y-m-d'),
                'to' => now()->endOfQuarter()->format('Y-m-d'),
            ],
            'last_quarter' => [
                'label' => 'Last Quarter',
                'from' => now()->subQuarter()->startOfQuarter()->format('Y-m-d'),
                'to' => now()->subQuarter()->endOfQuarter()->format('Y-m-d'),
            ],
            'current_year' => [
                'label' => 'Current Year',
                'from' => now()->startOfYear()->format('Y-m-d'),
                'to' => now()->endOfYear()->format('Y-m-d'),
            ],
            'last_year' => [
                'label' => 'Last Year',
                'from' => now()->subYear()->startOfYear()->format('Y-m-d'),
                'to' => now()->subYear()->endOfYear()->format('Y-m-d'),
            ],
            'ytd' => [
                'label' => 'Year to Date',
                'from' => now()->startOfYear()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
            'custom' => [
                'label' => 'Custom Range',
                'from' => '',
                'to' => '',
            ],
        ];
    }
}
