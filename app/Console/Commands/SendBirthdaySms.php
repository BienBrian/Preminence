<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendBirthdaySms extends Command
{
    protected $signature = 'app:send-birthday-sms';
    protected $description = 'Send birthday SMS to members whose birthday is today';

    public function handle()
    {
        $settings = \DB::table('birthday_settings')->first();
        if (!$settings || !$settings->active) {
            $this->info('Birthday SMS is disabled.');
            return;
        }

        $site_settings = \DB::table('settings')->first();
        $churchName = $site_settings ? $site_settings->name : 'CHURCH APP';

        $today = Carbon::now()->setTimezone('Africa/Nairobi');
        $month = $today->format('m');
        $day = $today->format('d');

        // Query contacts with DOB matching today (handle multiple date formats)
        $contacts = \DB::table('contacts')
            ->join('users', 'users.id', '=', 'contacts.user_id')
            ->where('users.status', 1)
            ->whereNotNull('contacts.phone')
            ->where('contacts.phone', '!=', '')
            ->whereNotNull('contacts.dob')
            ->where('contacts.dob', '!=', '')
            ->select('contacts.user_id', 'contacts.phone', 'contacts.dob', 'users.firstname', 'users.lastname')
            ->get();

        $sent = 0;
        foreach ($contacts as $contact) {
            $dob = $this->parseDob($contact->dob);
            if (!$dob) continue;

            // Check if month and day match today
            if ($dob->format('m') != $month || $dob->format('d') != $day) continue;

            // Check if we already sent a birthday SMS today
            $alreadySent = \DB::table('sms')
                ->join('sms_recipients', 'sms_recipients.sms_id', '=', 'sms.id')
                ->where('sms_recipients.recipients', $contact->user_id)
                ->where('sms.message', 'LIKE', '%Happy Birthday%')
                ->whereDate('sms.sent', $today->toDateString())
                ->exists();

            if ($alreadySent) continue;

            // Prepare message
            $message = str_replace('{{NAME}}', $contact->firstname, $settings->message);
            $message = str_replace('{{CHURCH}}', $churchName, $message);

            // Send SMS
            $number = $contact->phone;
            if (substr($number, 0, 1) === '0') {
                $number = '254' . substr($number, 1);
            }

            if ($this->send($number, $message)) {
                $mid = \DB::table('sms')->insertGetId([
                    'people_id' => 0,
                    'message' => $message,
                    'category' => 'birthday',
                    'sent' => Carbon::now(),
                ]);
                \DB::table('sms_recipients')->insert([
                    'recipients' => $contact->user_id,
                    'sms_id' => $mid,
                    'sent' => Carbon::now(),
                ]);
                $sent++;
            }
        }

        $this->info("Sent birthday SMS to {$sent} members.");
    }

    private function parseDob($dob)
    {
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, trim($dob));
                if ($date) return $date;
            } catch (\Exception $e) {
                continue;
            }
        }
        // Try generic parse
        try {
            return Carbon::parse(trim($dob));
        } catch (\Exception $e) {
            return null;
        }
    }

    private function send($number, $message)
    {
        return app(\App\Services\IntegrationService::class)->sendSms($number, $message);
    }
}
