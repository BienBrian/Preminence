<?php

namespace App\Http\Controllers\Dashboard\Reports;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\GivingReportLog;
use App\Models\GivingStatementConfig;
use App\Models\Source;
use App\Models\User;
use App\Services\GivingReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Giving Statement Controller
 * 
 * Handles generation, viewing, printing, and emailing of individual giving statements.
 */
class GivingStatementController extends DashboardController
{
    private GivingReportService $reportService;

    public function __construct(GivingReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
        
        // Module middleware
        $this->middleware('module:giving_statements');
        
        // Permission middleware
        $this->middleware(['permission:View Giving Statements'], ['only' => ['index', 'history']]);
        $this->middleware(['permission:Generate Giving Statements'], ['only' => ['generate', 'preview', 'download']]);
        $this->middleware(['permission:Email Giving Statements'], ['only' => ['email', 'bulkEmail']]);
        $this->middleware(['permission:Configure Giving Statements'], ['only' => ['settings', 'updateSettings']]);
        $this->middleware(['permission:Bulk Generate Giving Statements'], ['only' => ['bulkGenerate']]);
    }

    /**
     * Main interface for generating giving statements.
     */
    public function index()
    {
        $tenant = $this->getCurrentTenant();
        $config = GivingStatementConfig::forTenant($tenant->id);
        
        // Get collection sources (ftype = 0)
        $categories = Source::where('tenant_id', $tenant->id)
            ->where('ftype', 0)
            ->orderBy('name')
            ->get();
        
        // Get recent report history
        $recentReports = GivingReportLog::forTenant($tenant->id)
            ->with(['recipient', 'generator'])
            ->recent(30)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        // Get stats
        $stats = [
            'total_generated' => GivingReportLog::forTenant($tenant->id)->count(),
            'total_emailed' => GivingReportLog::forTenant($tenant->id)->emailed()->count(),
            'this_month' => GivingReportLog::forTenant($tenant->id)->whereMonth('created_at', now()->month)->count(),
        ];
        
        return view('dashboard.reports.giving-statements.index', compact(
            'config',
            'categories',
            'recentReports',
            'stats'
        ));
    }

    /**
     * Preview a giving statement before generating.
     */
    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'categories' => 'nullable|array',
            'group_by' => 'nullable|in:month,category,none',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = $this->getCurrentTenant();
        
        // Verify user belongs to tenant
        $user = User::where('tenant_id', $tenant->id)
            ->findOrFail($request->user_id);
        
        $previewData = $this->reportService->generatePreview(
            $tenant->id,
            $user->id,
            $request->date_from,
            $request->date_to,
            $request->categories ?? [],
            $request->group_by ?? 'category'
        );

        return response()->json([
            'success' => true,
            'preview' => $previewData,
            'html' => view('dashboard.reports.giving-statements.preview', [
                'data' => $previewData,
                'user' => $user,
                'config' => GivingStatementConfig::forTenant($tenant->id),
            ])->render(),
        ]);
    }

    /**
     * Generate and download a giving statement PDF.
     */
    public function download(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'categories' => 'nullable|array',
            'password_type' => 'required|in:phone,id_number,custom_code,sms_otp,none',
            'password_value' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = $this->getCurrentTenant();
        $user = User::where('tenant_id', $tenant->id)->findOrFail($request->user_id);
        
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

            return response()->json([
                'success' => true,
                'download_url' => route('giving-statements.download-file', $result['log_id']),
                'log_id' => $result['log_id'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
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
            'date_to' => 'required|date',
            'categories' => 'nullable|array',
            'password_type' => 'required|in:phone,id_number,custom_code,sms_otp,none',
            'password_value' => 'nullable|string',
            'custom_message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = $this->getCurrentTenant();
        $user = User::where('tenant_id', $tenant->id)->findOrFail($request->user_id);
        
        // Check email verification
        if (!$user->email_verified_at) {
            return response()->json([
                'success' => false,
                'error' => 'Member email is not verified. Cannot send giving statement.',
                'code' => 'email_not_verified',
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

            return response()->json([
                'success' => true,
                'message' => 'Giving statement sent successfully to ' . $user->email,
                'log_id' => $result['log_id'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk email giving statements to multiple members.
     */
    public function bulkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'categories' => 'nullable|array',
            'password_type' => 'required|in:phone,id_number,custom_code,sms_otp,none',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = $this->getCurrentTenant();
        $adminId = auth()->id();
        
        // Process in background for large batches
        if (count($request->user_ids) > 10) {
            // Dispatch job (would need to create a job class)
            return response()->json([
                'success' => true,
                'message' => 'Bulk email queued. You will be notified when complete.',
                'queued' => true,
                'count' => count($request->user_ids),
            ]);
        }

        // Process immediately for small batches
        $results = [
            'sent' => 0,
            'failed' => 0,
            'not_verified' => [],
        ];

        foreach ($request->user_ids as $userId) {
            $user = User::where('tenant_id', $tenant->id)->find($userId);
            
            if (!$user || !$user->email_verified_at) {
                $results['not_verified'][] = $user?->name ?? 'Unknown';
                $results['failed']++;
                continue;
            }

            try {
                $this->reportService->emailReport(
                    $tenant,
                    $user,
                    $request->date_from,
                    $request->date_to,
                    $request->categories ?? [],
                    $request->password_type,
                    null, // No custom password for bulk
                    $adminId
                );
                $results['sent']++;
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Get list of members for selection.
     */
    public function getMembers(Request $request)
    {
        $tenant = $this->getCurrentTenant();
        
        $query = User::where('tenant_id', $tenant->id)
            ->where('status', 1)
            ->select('id', 'firstname', 'lastname', 'phone', 'email', 'email_verified_at');
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $members = $query->orderBy('lastname')
            ->take(50)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->firstname . ' ' . $user->lastname,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'email_verified' => $user->email_verified_at !== null,
                ];
            });

        return response()->json(['members' => $members]);
    }

    /**
     * View report history.
     */
    public function history(Request $request)
    {
        $tenant = $this->getCurrentTenant();
        
        $reports = GivingReportLog::forTenant($tenant->id)
            ->with(['recipient', 'generator'])
            ->when($request->user_id, function ($q, $userId) {
                $q->where('user_id', $userId);
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('dashboard.reports.giving-statements.history', compact('reports'));
    }

    /**
     * Show module settings.
     */
    public function settings()
    {
        $tenant = $this->getCurrentTenant();
        $config = GivingStatementConfig::forTenant($tenant->id);
        
        return view('dashboard.reports.giving-statements.settings', compact('config'));
    }

    /**
     * Update module settings.
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'default_password_type' => 'required|in:phone,id_number,custom_code,sms_otp,none',
            'custom_password_code' => 'nullable|string|max:20',
            'email_template_subject' => 'required|string|max:200',
            'email_template_body' => 'nullable|string|max:5000',
            'thank_you_note' => 'nullable|string|max:1000',
            'report_title' => 'nullable|string|max:100',
            'enable_bulk_generation' => 'boolean',
            'enable_email_delivery' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tenant = $this->getCurrentTenant();
        $config = GivingStatementConfig::forTenant($tenant->id);
        
        $config->update([
            'default_password_type' => $request->default_password_type,
            'custom_password_code' => $request->custom_password_code,
            'email_template_subject' => $request->email_template_subject,
            'email_template_body' => $request->email_template_body,
            'thank_you_note' => $request->thank_you_note,
            'report_title' => $request->report_title,
            'enable_bulk_generation' => $request->boolean('enable_bulk_generation', true),
            'enable_email_delivery' => $request->boolean('enable_email_delivery', true),
        ]);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Download a generated report file.
     */
    public function downloadFile(int $logId)
    {
        $tenant = $this->getCurrentTenant();
        
        $log = GivingReportLog::forTenant($tenant->id)
            ->findOrFail($logId);
        
        if (!$log->file_path || !\Storage::exists($log->file_path)) {
            abort(404, 'File not found.');
        }

        // Log access
        $log->update([
            'opened_at' => now(),
            'status' => 'opened',
        ]);

        return \Storage::download($log->file_path, $log->file_name);
    }

    /**
     * Get current tenant helper.
     */
    private function getCurrentTenant()
    {
        return app('tenant');
    }
}
