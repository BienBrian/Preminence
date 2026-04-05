<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Giving Report PDF Service
 * 
 * Handles PDF generation with password protection.
 */
class GivingReportPDFService
{
    /**
     * Generate a password-protected PDF giving statement.
     */
    public function generate(array $data): array
    {
        $tenant = $data['tenant'];
        $user = $data['user'];
        $config = $data['config'];
        $reportData = $data['report_data'];
        $password = $data['password'];

        // Build the view data
        $viewData = [
            'church_name' => $config->church_header_text ?? $tenant->church_name ?? $tenant->name ?? 'Church',
            'church_address' => $tenant->address ?? '',
            'church_phone' => $tenant->phone ?? '',
            'church_email' => $tenant->email ?? '',
            'member_name' => trim("{$user->firstname} {$user->lastname}"),
            'member_phone' => $user->phone ?? '',
            'member_email' => $user->email ?? '',
            'member_id' => $user->id_number ?? $user->national_id ?? '',
            'period_from' => $reportData['period_from'],
            'period_to' => $reportData['period_to'],
            'generated_date' => now()->format('F d, Y'),
            'total_amount' => $reportData['total_amount'],
            'transaction_count' => $reportData['transaction_count'],
            'grouped_data' => $reportData['grouped_data'],
            'include_thank_you' => $config->include_thank_you_note ?? false,
            'thank_you_note' => $config->thank_you_note ?? '',
            'password_hint' => $data['password_hint'] ?? '',
        ];

        // Generate PDF
        $pdf = PDF::loadView('giving_statements.pdf.statement', $viewData);

        // Apply password protection if password is set
        if (!empty($password)) {
            $pdf->getDomPDF()->getCanvas()->get_cpdf()->setEncryption($password);
        }

        // Generate filename
        $fileName = $this->generateFileName($user, $reportData['period_from'], $reportData['period_to']);
        
        // Storage path
        $tenantId = $tenant->id;
        $directory = "giving-statements/{$tenantId}";
        $filePath = "{$directory}/{$fileName}";

        // Ensure directory exists
        Storage::makeDirectory($directory);

        // Save to storage
        Storage::put($filePath, $pdf->output());

        return [
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => Storage::size($filePath),
        ];
    }

    /**
     * Generate filename for the report.
     */
    private function generateFileName($user, string $from, string $to): string
    {
        $name = str_replace(' ', '_', strtolower(trim("{$user->firstname}_{$user->lastname}")));
        $fromFormatted = date('Ymd', strtotime($from));
        $toFormatted = date('Ymd', strtotime($to));
        
        return "giving_statement_{$name}_{$fromFormatted}_{$toFormatted}.pdf";
    }
}
