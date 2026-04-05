<?php

namespace App\Services;

use App\Models\GivingReportLog;
use App\Models\GivingStatementConfig;
use App\Models\GivingStatementPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Giving Report Service
 * 
 * Handles the business logic for generating giving statements.
 */
class GivingReportService
{
    private GivingReportPDFService $pdfService;
    private GivingReportEmailService $emailService;

    public function __construct(
        GivingReportPDFService $pdfService,
        GivingReportEmailService $emailService
    ) {
        $this->pdfService = $pdfService;
        $this->emailService = $emailService;
    }

    /**
     * Generate a preview of the giving statement.
     */
    public function generatePreview(
        int $tenantId,
        int $userId,
        string $dateFrom,
        string $dateTo,
        array $categories = [],
        string $groupBy = 'category'
    ): array {
        // Get member's giving data
        $query = DB::table('funds')
            ->where('funds.tenant_id', $tenantId)
            ->where('funds.user_id', $userId)
            ->whereBetween('funds.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->where('sources.ftype', 0) // Only collections
            ->join('sources', 'sources.id', '=', 'funds.source');

        if (!empty($categories)) {
            $query->whereIn('funds.source', $categories);
        }

        $transactions = $query->select(
                'funds.id',
                'funds.amount',
                'funds.description',
                'funds.created_at as date',
                'sources.name as category',
                'sources.id as source_id'
            )
            ->orderBy('funds.created_at')
            ->get();

        // Calculate totals
        $totalAmount = $transactions->sum('amount');
        $transactionCount = $transactions->count();

        // Group data
        $groupedData = $this->groupTransactions($transactions, $groupBy);

        return [
            'transactions' => $transactions,
            'grouped_data' => $groupedData,
            'total_amount' => $totalAmount,
            'transaction_count' => $transactionCount,
            'period_from' => $dateFrom,
            'period_to' => $dateTo,
            'group_by' => $groupBy,
        ];
    }

    /**
     * Generate PDF and create log entry.
     */
    public function generatePdf(
        $tenant,
        $user,
        string $dateFrom,
        string $dateTo,
        array $categories = [],
        string $passwordType = 'phone',
        ?string $passwordValue = null,
        ?int $generatedBy = null
    ): array {
        $config = GivingStatementConfig::forTenant($tenant->id);
        
        // Get data
        $reportData = $this->generatePreview(
            $tenant->id,
            $user->id,
            $dateFrom,
            $dateTo,
            $categories,
            'category'
        );

        // Determine password
        $password = $this->determinePassword($user, $passwordType, $passwordValue);
        
        // Generate PDF
        $pdfData = $this->pdfService->generate([
            'tenant' => $tenant,
            'user' => $user,
            'config' => $config,
            'report_data' => $reportData,
            'password' => $password,
            'password_hint' => $this->getPasswordHint($passwordType),
        ]);

        // Create log entry
        $log = GivingReportLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'generated_by' => $generatedBy ?? auth()->id(),
            'report_period_from' => $dateFrom,
            'report_period_to' => $dateTo,
            'period_type' => $this->determinePeriodType($dateFrom, $dateTo),
            'categories_included' => $categories,
            'total_amount' => $reportData['total_amount'],
            'transaction_count' => $reportData['transaction_count'],
            'delivery_method' => 'download',
            'password_protection_type' => $passwordType,
            'password_hint' => $this->getPasswordHint($passwordType),
            'file_path' => $pdfData['file_path'],
            'file_name' => $pdfData['file_name'],
            'file_format' => 'pdf',
            'file_size_bytes' => $pdfData['file_size'],
            'status' => 'generated',
            'generated_from_ip' => request()->ip(),
        ]);

        // Store password record
        $passwordRecord = GivingStatementPassword::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'report_log_id' => $log->id,
            'password_type' => $passwordType,
        ]);
        $passwordRecord->setPassword($password);
        $passwordRecord->save();

        return [
            'log_id' => $log->id,
            'file_path' => $pdfData['file_path'],
            'file_name' => $pdfData['file_name'],
        ];
    }

    /**
     * Generate and email a giving statement.
     */
    public function emailReport(
        $tenant,
        $user,
        string $dateFrom,
        string $dateTo,
        array $categories = [],
        string $passwordType = 'phone',
        ?string $passwordValue = null,
        ?int $generatedBy = null,
        ?string $customMessage = null
    ): array {
        // Generate PDF first
        $pdfResult = $this->generatePdf(
            $tenant,
            $user,
            $dateFrom,
            $dateTo,
            $categories,
            $passwordType,
            $passwordValue,
            $generatedBy
        );

        // Get the log
        $log = GivingReportLog::find($pdfResult['log_id']);
        
        // Send email
        $config = GivingStatementConfig::forTenant($tenant->id);
        $emailResult = $this->emailService->send([
            'tenant' => $tenant,
            'user' => $user,
            'config' => $config,
            'log' => $log,
            'file_path' => $pdfResult['file_path'],
            'custom_message' => $customMessage,
        ]);

        // Update log
        $log->update([
            'delivery_method' => 'email',
            'email_sent_to' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'sent_at' => now(),
            'status' => 'sent',
        ]);

        return [
            'log_id' => $log->id,
            'email_sent' => $emailResult['email_sent'] ?? true,
            'sms_sent' => $emailResult['sms_sent'] ?? false,
        ];
    }

    /**
     * Group transactions by specified criteria.
     */
    private function groupTransactions($transactions, string $groupBy): array
    {
        $grouped = [];

        switch ($groupBy) {
            case 'category':
                $grouped = $transactions->groupBy('category')->map(function ($items) {
                    return [
                        'name' => $items->first()->category,
                        'total' => $items->sum('amount'),
                        'count' => $items->count(),
                        'transactions' => $items,
                    ];
                })->values()->toArray();
                break;

            case 'month':
                $grouped = $transactions->groupBy(function ($item) {
                    return date('F Y', strtotime($item->date));
                })->map(function ($items, $month) {
                    return [
                        'name' => $month,
                        'total' => $items->sum('amount'),
                        'count' => $items->count(),
                        'transactions' => $items,
                    ];
                })->values()->toArray();
                break;

            case 'none':
            default:
                $grouped = [
                    [
                        'name' => 'All Transactions',
                        'total' => $transactions->sum('amount'),
                        'count' => $transactions->count(),
                        'transactions' => $transactions,
                    ]
                ];
        }

        return $grouped;
    }

    /**
     * Determine password based on type.
     */
    private function determinePassword($user, string $type, ?string $value): string
    {
        return match ($type) {
            'phone' => $this->extractLastDigits($user->phone, 4),
            // 'id_number' option removed - column doesn't exist in users table
            'custom_code' => $value ?? '0000',
            'sms_otp' => $this->generateOtp(),
            default => '',
        };
    }

    /**
     * Extract last N digits from a string.
     */
    private function extractLastDigits(?string $value, int $digits): string
    {
        if (!$value) {
            return str_repeat('0', $digits);
        }
        
        $cleaned = preg_replace('/\D/', '', $value);
        return substr($cleaned, -$digits) ?: str_repeat('0', $digits);
    }

    /**
     * Generate a random OTP.
     */
    private function generateOtp(int $length = 6): string
    {
        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Get password hint text.
     */
    private function getPasswordHint(string $type): string
    {
        return match ($type) {
            'phone' => 'Last 4 digits of your phone number',
            // 'id_number' => 'Last 4 digits of your National ID',
            'custom_code' => 'The code provided by your church',
            'sms_otp' => 'The OTP sent to your phone',
            default => 'No password required',
        };
    }

    /**
     * Determine period type based on date range.
     */
    private function determinePeriodType(string $from, string $to): string
    {
        $fromDate = \Carbon\Carbon::parse($from);
        $toDate = \Carbon\Carbon::parse($to);
        $diffInDays = $fromDate->diffInDays($toDate);

        if ($diffInDays <= 31) {
            return 'monthly';
        } elseif ($diffInDays <= 95) {
            return 'quarterly';
        } elseif ($diffInDays <= 366) {
            return 'annual';
        }
        return 'custom';
    }
}
