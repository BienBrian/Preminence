<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RetryPendingSms extends Command
{
    protected $signature = 'app:retry-pending-sms';
    protected $description = 'Retry SMS messages that failed due to insufficient credits';

    public function handle(): void
    {
        $pending = DB::table('pending_sms')
            ->where('status', 'pending')
            ->where('attempts', '<', 5)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', Carbon::now());
            })
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        if ($pending->isEmpty()) {
            return;
        }

        $this->info("Retrying {$pending->count()} pending SMS(es)...");

        foreach ($pending as $smsRecord) {
            DB::table('pending_sms')
                ->where('id', $smsRecord->id)
                ->increment('attempts');

            $response = $this->sendSms($smsRecord->phone, $smsRecord->message);
            $success = false;

            if (is_array($response)) {
                $code = $response['response-code'] ?? $response['response_code'] ?? $response['code'] ?? null;
                $success = ($code === null || (int)$code === 200 || (int)$code === 0);
            } elseif ($response !== null && $response !== false) {
                $success = true;
            }

            if ($success) {
                // Log to sms table (and recipient if phone maps to a user)
                $mid = DB::table('sms')->insertGetId([
                    'people_id' => 0,
                    'message'   => $smsRecord->message,
                    'category'  => $smsRecord->category ?? 'mpesa',
                    'sent'      => Carbon::now(),
                ]);

                // Try to find user by phone and link as recipient
                $user = DB::table('users')->where('phone', function ($q) use ($smsRecord) {
                    // Check both full international format and local format
                    $q->select('phone')->from('users')
                      ->where('phone', $smsRecord->phone)
                      ->orWhere('phone', '0' . substr($smsRecord->phone, 3))
                      ->limit(1);
                })->orWhere('phone', $smsRecord->phone)
                  ->orWhere('phone', '0' . substr($smsRecord->phone, 3))
                  ->first();

                if ($user) {
                    DB::table('sms_recipients')->insert([
                        'recipients' => $user->id,
                        'sms_id'     => $mid,
                        'sent'       => Carbon::now(),
                    ]);
                }

                DB::table('pending_sms')->where('id', $smsRecord->id)->update([
                    'status'     => 'sent',
                    'updated_at' => Carbon::now(),
                ]);

                Log::info("Pending SMS {$smsRecord->id} sent successfully to {$smsRecord->phone}");
            } else {
                // If max attempts reached, mark as failed
                $currentAttempts = $smsRecord->attempts + 1;
                if ($currentAttempts >= 5) {
                    DB::table('pending_sms')->where('id', $smsRecord->id)->update([
                        'status'     => 'failed',
                        'updated_at' => Carbon::now(),
                    ]);
                    Log::warning("Pending SMS {$smsRecord->id} failed after {$currentAttempts} attempts to {$smsRecord->phone}");
                } else {
                    // Back off: schedule next retry in 5 minutes × attempts
                    DB::table('pending_sms')->where('id', $smsRecord->id)->update([
                        'scheduled_at' => Carbon::now()->addMinutes(5 * $currentAttempts),
                        'updated_at'   => Carbon::now(),
                    ]);
                }
            }
        }

        $this->info('Done.');
    }

    private function sendSms(string $phone, string $message): mixed
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => env('SMS_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_POSTFIELDS     => 'partnerID=' . env('SMS_PARTNER_ID') .
                                      '&message=' . urlencode($message) .
                                      '&shortcode=' . env('SMS_SHORT_CODE') .
                                      '&mobile=' . $phone,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . env('SMS_API_KEY'),
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}
