<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $schedules = \DB::table("schedules")->where("schedule", "<=", \Carbon\Carbon::now()->setTimezone('Africa/Nairobi'))->get();
        foreach($schedules as $schedule){
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
            \DB::table('schedules')->where("id", "=", $schedule->id)->delete();
        }
    }

    public function send($number, $message){
        $username = "happychurch"; //username for your bulk sms account
        $password = "Middle6224"; //password for your bulk sms account
        $apiKey = "5e427d38cb001"; //apikey for your bulk sms account
        $shortcode = "HappyChurch"; //"22136" for demo; //assigned sender ID
        $method = 'sendsms'; // method to invoke{sendsms - to send SMS | balance - to check credit balance}

        //$site_settings = $this->site_settings;
        //$appname = $site_settings != null?"".$site_settings->name:"CHURCH APP";

        $finalURL = "http://bulkapi.mobitechtechnologies.com/?username=" . urlencode($username) . "&password=" . urlencode($password) . "&apiKey=" . urlencode($apiKey) . "&message=" . urlencode($message) . "&senderID=".$shortcode."&msisdn=".$number."&method=".$method;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $finalURL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            return false;//return "cURL Error #:" . $err;
        } else {
            return true; //return $response;
        }
    }
}
