<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AutomaticSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:automatic-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled SMS and Email messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $schedules = \DB::table("schedules")->where("schedule", "<=", \Carbon\Carbon::now()->setTimezone('Africa/Nairobi'))->get();
        foreach($schedules as $schedule){
            if ($schedule->type == 1) {
                // Process scheduled EMAIL
                $this->processEmail($schedule);
            } else {
                // Process scheduled SMS (type=0)
                $this->processSms($schedule);
            }
            \DB::table('schedules')->where("id", "=", $schedule->id)->delete();
        }
    }

    private function processSms($schedule)
    {
        $message = $schedule->message;
        if($schedule->user_id == 0 && $schedule->group_id==0){
            //Sending to ALL
            $contacts = \DB::table('contacts')->get();
            $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$schedule->message, "sent"=>\Carbon\Carbon::now()]);

            foreach($contacts as $contact){
                $this->send("254".substr($contact->phone, 1), $message);
                \DB::table('sms_recipients')->insert(["recipients"=>$contact->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
            }
        }elseif($schedule->user_id > 0){
            //Send to individual
            $phone = \DB::table('contacts')->where('id', '=', $schedule->user_id)->first();
            if($phone != null){
                $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$schedule->message, "sent"=>\Carbon\Carbon::now()]);
                $this->send("254".substr($phone->phone, 1), $message);
                \DB::table('sms_recipients')->insert(["recipients"=>$phone->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
            }
        }else{
            //send to group
            $groups = \DB::table("groups")->where("id", "=", $schedule->group_id)->get();
            foreach($groups as $group){
                $members = \DB::table("people_members")->select("contacts.phone", "people_members.user_id")->where("people_id", $group->id)->where("people_members.status", 1)->join("contacts", "contacts.user_id", "=", "people_members.user_id")->get();
                $mid = \DB::table('sms')->insertGetId(["people_id"=>$group->id, "message"=>$schedule->message, "sent"=>\Carbon\Carbon::now()]);
                foreach($members as $member){
                    $this->send("254".substr($member->phone, 1), $message);
                    \DB::table('sms_recipients')->insert(["recipients"=>$member->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                }
            }
        }
    }

    private function processEmail($schedule)
    {
        // Message is stored as "subject|||body"
        $parts = explode('|||', $schedule->message, 2);
        $subject = $parts[0] ?? 'No Subject';
        $body = $parts[1] ?? $schedule->message;

        $site_settings = \DB::table('settings')->first();
        $church_name = $site_settings ? $site_settings->name : 'CHURCH APP';

        if ($schedule->user_id == 0 && $schedule->group_id == 0) {
            // Send to ALL
            $users = \DB::table('users')->get();
            $mid = \DB::table('emails')->insertGetId(["subject" => $subject, "people_id" => 0, "message" => $body, "sent" => \Carbon\Carbon::now()]);
            foreach ($users as $user) {
                \DB::table('email_recipients')->insert(["user_id" => $user->id, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                $this->sendEmail($user->email, $subject, $user->firstname, $user->lastname, $body, $church_name);
            }
        } elseif ($schedule->user_id > 0) {
            // Send to individual
            $user = \DB::table('users')->where('id', $schedule->user_id)->first();
            if ($user) {
                $mid = \DB::table('emails')->insertGetId(["subject" => $subject, "people_id" => 0, "message" => $body, "sent" => \Carbon\Carbon::now()]);
                \DB::table('email_recipients')->insert(["user_id" => $user->id, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                $this->sendEmail($user->email, $subject, $user->firstname, $user->lastname, $body, $church_name);
            }
        } else {
            // Send to group
            $mid = \DB::table('emails')->insertGetId(["subject" => $subject, "people_id" => $schedule->group_id, "message" => $body, "sent" => \Carbon\Carbon::now()]);
            $leader = \DB::table("people")->where("people.id", $schedule->group_id)->join("users", "users.id", "=", "people.leader")->first();
            if ($leader) {
                \DB::table('email_recipients')->insert(["user_id" => $leader->leader, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                $this->sendEmail($leader->email, $subject, $leader->firstname, $leader->lastname, $body, $church_name);
            }
            $members = \DB::table("people_members")->where("people_id", $schedule->group_id)->where("people_members.status", 1)->join("users", "users.id", "=", "people_members.user_id")->get();
            foreach ($members as $member) {
                \DB::table('email_recipients')->insert(["user_id" => $member->user_id, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                $this->sendEmail($member->email, $subject, $member->firstname, $member->lastname, $body, $church_name);
            }
        }
    }

    private function sendEmail($email, $subject, $firstname, $lastname, $body, $church_name)
    {
        $data = ['name' => $firstname . ' ' . $lastname, 'mes' => $body];
        Mail::send('dashboard.communication.mail', $data, function ($message) use ($email, $subject, $firstname, $lastname, $church_name) {
            $message->to($email, $firstname . ' ' . $lastname)->subject($subject);
            $message->from('info@happychurchruiru.org', $church_name);
        });
    }

    public function send($number, $message){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('SMS_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => 'partnerID='.env('SMS_PARTNER_ID').'&message=' . urlencode($message) . '&shortcode='.env('SMS_SHORT_CODE').'&mobile='.$number,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . env('SMS_API_KEY'),
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {
            return true;
        }
    }
}
