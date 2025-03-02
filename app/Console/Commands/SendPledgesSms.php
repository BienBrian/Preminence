<?php

namespace App\Console\Commands;

use App\Http\Controllers\Dashboard\SMS\SMSController;
use App\Models\PledgeSms;
use App\Models\User;
use Illuminate\Console\Command;

class SendPledgesSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-pledges-sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pledgeSMS = PledgeSms::with('pledge')->where('status', false)->skip(0)->take(100)->get();
        foreach ($pledgeSMS as $sms) {
            $user = User::find($sms->pledge->user_id);
            if ($user != null) {
                $updatedSMS = str_replace("***NAME***", $user->firstname, $sms->message);
                $updatedSMSFinal = str_replace("***AMOUNT***", number_format($sms->pledge->amount,0,0), $updatedSMS);
                $smsController = new SMSController();
                $smsController->send($user->phone, $updatedSMSFinal);
                $sms->status = true;
                $sms->save();
            }
        }
    }
}
