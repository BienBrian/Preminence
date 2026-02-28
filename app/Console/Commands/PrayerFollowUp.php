<?php

namespace App\Console\Commands;

use App\Http\Controllers\Services\SendSMSController;
use App\Models\PrayerRequest;
use App\Models\PrayerRequestNote;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PrayerFollowUp extends Command
{
    protected $signature = 'app:prayer-follow-up';
    protected $description = 'Send follow-up SMS for prayer requests needing attention';

    public function handle()
    {
        $requests = PrayerRequest::needingFollowUp()->get();

        if ($requests->isEmpty()) {
            $this->info('No prayer requests need follow-up.');
            return 0;
        }

        $smsService = new SendSMSController();
        $settings = DB::table('settings')->first();
        $phoneCode = $settings->phone_code ?? '254';
        $orgName = $settings->name ?? 'Church';
        $sent = 0;

        foreach ($requests as $prayer) {
            $phone = $prayer->submitted_phone;

            // If submitted by a registered user, try their phone
            if (!$phone && $prayer->submitted_by) {
                $user = DB::table('users')->where('id', $prayer->submitted_by)->first();
                if ($user && $user->phone) {
                    $phone = $user->phone;
                }
            }

            if (!$phone) {
                // No phone to follow up - just update follow_up_at to avoid re-processing
                $prayer->update(['follow_up_at' => now()->addDays(14)]);
                continue;
            }

            // Normalize phone
            $number = $phone;
            if (substr($number, 0, 1) === '0') {
                $number = $phoneCode . substr($number, 1);
            }

            $name = $prayer->submitted_name;
            if (!$name && $prayer->submitter) {
                $name = $prayer->submitter->firstname;
            }
            $greeting = $name ? "Hi {$name}, " : "Hi, ";

            $message = $greeting . "we are still praying for your request \"{$prayer->title}\". Has God answered? Visit " . url('/prayer-wall') . " - {$orgName}";

            $smsService->sendSMS($number, $message);

            // Log the SMS
            $mid = DB::table('sms')->insertGetId([
                'people_id' => 0,
                'message'   => $message,
                'category'  => 'prayer',
                'sent'      => Carbon::now(),
            ]);

            if ($prayer->submitted_by) {
                DB::table('sms_recipients')->insert([
                    'recipients' => $prayer->submitted_by,
                    'sms_id'     => $mid,
                    'sent'       => Carbon::now(),
                ]);
            }

            // Create timeline note
            PrayerRequestNote::create([
                'prayer_request_id' => $prayer->id,
                'user_id'           => $prayer->submitted_by ?? 1,
                'note'              => 'Automated follow-up SMS sent.',
                'type'              => 'follow_up',
            ]);

            // Update follow_up_at and status
            $prayer->follow_up_at = now()->addDays(14);
            if ($prayer->status === 'new') {
                $prayer->status = 'follow_up';
            }
            $prayer->save();

            $sent++;
        }

        $this->info("Follow-up SMS sent for {$sent} prayer request(s).");
        return 0;
    }
}
