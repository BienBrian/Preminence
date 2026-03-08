<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Payment\SuspensionPaymentService;
use App\Services\Tenant\AutoReactivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SuspensionController extends Controller
{
    /**
     * Process payment from suspended tenant.
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:mpesa,card,airtel',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
            'phone_number' => 'nullable|string',
            'card_token' => 'nullable|string',
            'payment_reference' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment details.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        if (!$tenant->isSuspended()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not suspended.',
            ], 400);
        }

        // Process the payment
        $paymentData = [
            'phone_number' => $request->phone_number,
            'card_token' => $request->card_token,
            'payment_reference' => $request->payment_reference,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        $result = SuspensionPaymentService::processPayment(
            $tenant,
            $request->payment_method,
            (float) $request->amount,
            $request->currency,
            $paymentData
        );

        return response()->json($result);
    }

    /**
     * Submit contact form from suspended tenant.
     */
    public function submitContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please check your input and try again.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $tenant = $this->getTenant();
        
        if (!$tenant) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found.',
                ], 404);
            }

            return back()->with('error', 'An error occurred. Please try again.');
        }

        try {
            // Handle attachment if present
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('suspension-contacts', 'local');
            }

            // Prepare email data
            $emailData = [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'tenant_domain' => $tenant->domain,
                'suspension_type' => $tenant->suspension_type,
                'suspension_reason' => $tenant->suspension_reason,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'attachment_path' => $attachmentPath,
                'submitted_at' => now()->toIso8601String(),
                'ip_address' => $request->ip(),
            ];

            // Send email to super admins
            $this->sendContactEmail($emailData);

            // Log the contact submission
            Log::info('Suspension contact form submitted', [
                'tenant_id' => $tenant->id,
                'email' => $request->email,
                'subject' => $request->subject,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your message has been sent. Our team will review and respond shortly.',
                ]);
            }

            return back()->with('success', 'Your message has been sent. Our team will review and respond shortly.');

        } catch (\Exception $e) {
            Log::error('Failed to process suspension contact form', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send your message. Please try again or email us directly.',
                ], 500);
            }

            return back()->with('error', 'Failed to send your message. Please try again or email us directly.')->withInput();
        }
    }

    /**
     * Get available payment methods.
     */
    public function getPaymentMethods(Request $request)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        $methods = SuspensionPaymentService::getAvailableMethods($tenant);
        $totals = SuspensionPaymentService::calculateTotalDue($tenant);

        return response()->json([
            'success' => true,
            'methods' => $methods,
            'totals' => $totals,
            'amount_due' => $tenant->suspension_amount_due,
            'currency' => $tenant->suspension_currency ?? 'KES',
        ]);
    }

    /**
     * Get current tenant from context.
     */
    protected function getTenant(): ?Tenant
    {
        if (app()->bound('tenant')) {
            return app('tenant');
        }

        // Try to get tenant from domain
        $host = request()->getHost();
        return Tenant::where('domain', $host)
            ->orWhere('slug', $host)
            ->orWhere('domain', str_replace(['https://', 'http://'], '', $host))
            ->first();
    }

    /**
     * Send contact email to super admins.
     */
    protected function sendContactEmail(array $data): void
    {
        $superAdminEmails = config('mail.superadmin_emails', ['support@pisti.co.ke']);
        
        // Using a simple mail send - in production, use a Mailable class
        foreach ($superAdminEmails as $email) {
            try {
                Mail::send('emails.suspension_contact', $data, function ($message) use ($email, $data) {
                    $message->to($email)
                        ->subject("[SUSPENSION CONTACT] {$data['tenant_name']} - {$data['subject']}");
                    
                    if (!empty($data['attachment_path'])) {
                        $message->attach(storage_path('app/' . $data['attachment_path']));
                    }
                });
            } catch (\Exception $e) {
                Log::error('Failed to send suspension contact email', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
