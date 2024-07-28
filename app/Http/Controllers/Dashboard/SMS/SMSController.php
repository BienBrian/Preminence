<?php

namespace App\Http\Controllers\Dashboard\SMS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;

class SMSController extends DashboardController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function sms(){
        $sms = \DB::table("sms")->select("sms.id", "sms.message", "sms.sent", \DB::Raw("count(sms_recipients.sms_id) as recipients"))->leftJoin("sms_recipients", "sms_recipients.sms_id", "=", "sms.id")
        ->groupBy("sms.id", "sms.message", "sms.sent")->orderBy("sms.id", "DESC")->paginate(15);

        return view("dashboard.communication.sms")->with("sms", $sms);
    }

    public function sendSms( Request $request )
    {
        $request->validate([
            "message"=>"required",
        ]);
        $sent = 0;
        $site_settings = $this->site_settings;
        $appname = $site_settings != null?"".$site_settings->name:"CHURCH APP";
        $message = $request->message;
        if($request->has('time')){
            //schedule sms
            $date = \Carbon\Carbon::parse($request->time);
            if($request->choice == 0){
                if(empty($request['contacts'])){
                    return back()->with("error", "No recipients selected!");
                }else{
                    foreach($request['contacts'] as $contact){
                        \DB::table('schedules')->insert(["message"=>$message, "type"=>0, "user_id"=>$contact, "group_id"=>0,
                            "schedule"=>$date, "status"=>0, "created_at"=>\Carbon\Carbon::now()]);
                    }
                }
            }else if($request->choice == 1){
                //send to groups
                if(empty($request['groups'])){
                    return back()->with("error", "No groups selected!");
                }else{
                    foreach($request['groups'] as $contact){
                        \DB::table('schedules')->insert(["message"=>$message, "type"=>0, "user_id"=>0, "group_id"=>$contact,
                            "schedule"=>$date, "status"=>0, "created_at"=>\Carbon\Carbon::now()]);
                    }
                }
            }else{
                //send ALL
                \DB::table('schedules')->insert(["message"=>$message, "type"=>0, "user_id"=>0, "group_id"=>0,
                "schedule"=>$date, "status"=>0, "created_at"=>\Carbon\Carbon::now()]);
            }
            return redirect()->to('dashboard/communication/schedule/sms')->with("success", "Your messages have been successfully scheduled!");
        }

        if($request->choice == 0){
            //send to individual
            if(empty($request['contacts'])){
                return back()->with("error", "No recipients selected!");
            }else{
                $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$request->message, "sent"=>\Carbon\Carbon::now()]);

                foreach($request['contacts'] as $contact){
                    $phone = \DB::table('contacts')->select("contacts.phone", "contacts.user_id")->join("users", "users.id", "=", "contacts.user_id")->where('contacts.id', '=', $contact)->where("users.status", 1)->first();
                    if($phone != null){
                        if($this->send("254".substr($phone->phone, 1), $message)){
                            $sent++;
                            \DB::table('sms_recipients')->insert(["recipients"=>$phone->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                        }
                    }

                    //$this->send("254".substr($phone->phone, 1), $message);

                }
            }
        }else{
            if($request->choice == 1){
                //send to groups
                if(empty($request['groups'])){
                    return back()->with("error", "No groups selected!");
                }else{
                    foreach($request['groups'] as $contact){
                        $members = \DB::table("people_members")->select("contacts.phone", "people_members.user_id")->where("people_id", $contact)->where("people_members.status", 1)->
                        where("users.status", 1)->join("contacts", "contacts.user_id", "=",
                        "people_members.user_id")->join("users", "users.id", "=", "contacts.user_id")->get();
                        $mid = \DB::table('sms')->insertGetId(["people_id"=>$contact, "message"=>$request->message, "sent"=>\Carbon\Carbon::now()]);
                        foreach($members as $member){
                            if($this->send("254".substr($member->phone, 1), $message)){
                                $sent++;
                                \DB::table('sms_recipients')->insert(["recipients"=>$member->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                            }
                        }
                    }
                }
            }else{
                //send ALL
                $contacts = \DB::table('contacts')->select("contacts.phone", "contacts.user_id")->join("users", "users.id", "=", "contacts.user_id")->where("users.status", 1)->get();
                $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$request->message, "sent"=>\Carbon\Carbon::now()]);
                foreach($contacts as $contact){
                    if($this->send("254".substr($contact->phone, 1), $message)){
                        $sent++;
                        \DB::table('sms_recipients')->insert(["recipients"=>$contact->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                    }
                }
                return back()->with("success", "Sent to <strong>".$sent."</strong> Members!");
            }
        }
        return back()->with("success", "Messages sent successfully!");
   }

   public function sendsinglesms( Request $request )
   {
        $site_settings = $this->site_settings;
        $appname = $site_settings != null?"\n".$site_settings->name:"\nCHURCH APP";
        $message = $request->message;

        $validator = Validator::make($request->all(), [
            'numbers' => 'required',
            'message' => 'required'
        ]);

        if($validator->passes()){
            $number = "254".substr($request->input( 'numbers' ), 1);
            //$message = $request->input( 'message' );

            $this->send($number, $message);

            $user = \DB::table("contacts")->where("phone", $request->numbers)->first();

            $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$request->message, "sent"=>\Carbon\Carbon::now()]);

            if($user != null){
                \DB::table('sms_recipients')->insert(["recipients"=>$user->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
            }
            return response()->json(['success'=>"Message sent to <strong>".$number."</strong>!"]);
            //return back()->with( 'success', "Message sent to <strong>".$number."</strong>!" );
        } else {
            return response()->json(["error"=>"Invalid info submitted! Check before sending again"]);
            //return back()->withErrors( $validator );
        }
  }

    public function removesms(Request $request)
    {
        $message = \DB::table('sms')->where("id", $request->id)->first();
        if($message == null){
            return redirect()->back()->with("error", "Invalid Message ID");
        }else{
            if(\DB::table('sms')->where("id", $request->id)->delete()){
                \DB::table('sms_recipients')->where("sms_id", $request->id)->delete();
                return redirect()->to('dashboard/communication/sms')->with("success", "Message removed successfully");
            }else{
                return redirect()->to('dashboard/communication/sms')->with("error", "Unable to remove message");
            }
        }
    }

    public function getPhone(Request $request){
        $phone = \DB::table("contacts")->select("phone")->where("user_id", $request->id)->first();
        return json_encode($phone);
    }

    public function phoneNumbers(Request $request){
        $start = $request->limit == null?"0":intval($request->limit);
        if($request->groups == 1){
            if($request->search == null){
                return json_encode(\DB::table("people")->select("people.id", "people.name", "people.user_group", \DB::Raw("count(people.id) as members"))
                ->leftJoin("people_members", "people_members.people_id", "=", "people.id")->groupBy('people.id')->groupBy('people.name')->groupBy('people.user_group')->
                orderBy("people.name", "ASC")->skip($start)->take(10)->get());
            }else{
                return json_encode(\DB::table("people")->select("people.id", "people.name", "people.user_group", \DB::Raw("count(people.id) as members"))->where("name", "LIKE", "%".$request->search."%")
                ->leftJoin("people_members", "people_members.people_id", "=", "people.id")->groupBy('people.id')->groupBy('people.name')->groupBy('people.user_group')->
                orderBy("people.name", "ASC")->skip($start)->take(10)->get());
            }
        }else{
            if($request->search == null){
                return json_encode(\DB::table("contacts")->select("contacts.id", "users.firstname", "users.lastname", "users.email", "contacts.phone")->join("users", "users.id", "=", "contacts.user_id")->orderBy("users.firstname", "ASC")->skip($start)->take(10)->get());
            }else{
                return json_encode(\DB::table("contacts")->select("contacts.id", "users.firstname", "users.lastname", "users.email", "contacts.phone")->join("users", "users.id", "=", "contacts.user_id")->
                where("firstname", "LIKE", "%".$request->search."%")->orWhere("lastname", "LIKE", "%".$request->search."%")->orWhere("contacts.phone", "LIKE", "%".$request->search."%")->orderBy("users.firstname", "ASC")->skip($start)->take(10)->get());
            }
        }
    }

    public function readsms(Request $request){
        $sms = \DB::table("sms")->where("sms.id", "=", $request->id)->select("sms.id", "message", "sent", "name")->leftJoin("people", "people.id", "=", "sms.people_id")->first();

        $members = \DB::table("sms_recipients")->where("sms_id", $request->id)->select("users.id", "users.firstname", "users.lastname", "users.phone", "profiles.name as image")->join("users", "users.id", "sms_recipients.recipients")
        ->join("contacts", "contacts.user_id", "sms_recipients.recipients")->leftJoin("profiles", "profiles.user_id", "=", "sms_recipients.recipients")->paginate(15);
        if($sms == null){
            return redirect()->to('sms')->with("Invalid SMS request");
        }

        return view("dashboard.communication.sms-read", ["sms"=>$sms, "members"=>$members, "recipients"=>\DB::table("sms_recipients")->where("sms_id", $request->id)->count()]);
    }

    public function pledges(Request $request){
        $request->validate([
            'amount'=>'required|numeric|min:50',
        ]);
        $send = false;
        if($request->has('notify')){
            $send = true;
        }
        if(!empty($request['contacts'])){
            foreach($request['contacts'] as $id){
                $user = \DB::table('contacts')->select("users.id", "users.firstname", "users.lastname", "contacts.phone")->where('contacts.id', $id)->join("users", "contacts.user_id", "=", "users.id")->first();
                if($user != null){
                    $message = "Dear ".strtoupper($user->firstname)." ".strtoupper($user->lastname).",\n".
                        "Thank You for pledging KSH ".number_format($request->amount). " in support of ".$request->activity_name;

                    $number = "254".substr($user->phone, 1);
                    $user->id." ".$user->firstname." ".$user->lastname." ".$user->phone;
                    if(\DB::table("pledges")->where("user_id", $user->id)->where("activity", $request->activity_id)->count() == 0){
                        if(\DB::table("pledges")->insert(["activity"=>$request->activity_id, "groups"=>0, "user_id"=>$user->id,
                            "paid"=>0, "amount"=>$request->amount, "status"=>0])){

                                if($send){
                                    $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$message, "sent"=>\Carbon\Carbon::now()]);
                                    \DB::table('sms_recipients')->insert(["recipients"=>$user->id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                                    $this->send($number, $message);
                                }
                        }
                    }//not to add user already committed to pledges
                }//user not null end
            }
            return back()->with("success", "Members Pledges Inserted Successfully");
        }else{
            return back()->with("error", "No members selected!");
        }
    }

    public function editPledge(Request $request){
        $request->validate([
            "amount"=>"required|min:50|numeric",
        ]);
        if($request->id > 0){
            $pledge = \DB::table("pledges")->select("pledges.user_id", "pledges.amount", "pledges.paid","activities.name")->where("pledges.id", $request->id)->join("activities",
            "activities.id", "=", "pledges.activity")->first();

            if($pledge == null){
                return back()->with("error", "Invalid request");
            }
            if($pledge->paid > $request->amount){
                return back()->with("error", "The amount is less than amount paid by user");
            }else{
                if(\DB::table("pledges")->where("id", $request->id)->update(["amount"=>$request->amount])){
                    if($request->has('notify')){
                        $user = \DB::table('contacts')->select("firstname", "contacts.phone")->where("user_id", $pledge->user_id)->leftJoin("users",
                        "users.id", "=", "contacts.user_id")->first();
                        if($user != null){
                            $message = "Dear ".strtoupper($user->firstname).",\nWe have adjusted your pledge towards ".strtoupper($pledge->name)." from ".
                            number_format($pledge->amount, 0)." to ".number_format($request->amount, 0);
                            $number = "254".substr($user->phone, 1);
                            $remove[] = "'";
                            $remove[] = '"';

                            $message = str_replace($remove, "", $message);

                            if($this->send($number, $message)){
                                $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$message, "sent"=>\Carbon\Carbon::now()]);
                                \DB::table('sms_recipients')->insert(["recipients"=>$pledge->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                            }
                        }
                    }
                    return back()->with("success", "Pledge Updated successfully");
                }else{
                    return back()->with("error", "Unable to update");
                }
            }
        }else{
            return back()->with("error", "Invalid pledge");
        }
    }

    public function pledgeReminder(Request $request){
        if($request->id > 0){
            $pledge = \DB::table('pledges')->select("contacts.phone", "pledges.user_id")->where("pledges.id", $request->id)->leftJoin("contacts", "contacts.user_id", "=", "pledges.user_id")
            ->leftJoin("users", "users.id", "=", "pledges.user_id")->first();

            $number = "254".substr($pledge->phone, 1);
            $message = $request->message;

            $remove[] = "'";
            $remove[] = '"';

            $message = str_replace($remove, "", $message);
            if($this->send($number, $message)){
                $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$message, "sent"=>\Carbon\Carbon::now()]);
                \DB::table('sms_recipients')->insert(["recipients"=>$pledge->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                return back()->with("success", "Reminder has been sent");
            }else{
                return back()->with("error", "Unable to send reminder");
            }
        }else{
            //send to all in this form
            $pledges = \DB::table("pledges")->select("users.id", "users.firstname", "users.lastname", "contacts.phone", "activities.name", "pledges.paid", "pledges.amount")
            ->where("activity", $request->activity_id)->leftJoin("activities", "activities.id", "=", "pledges.activity")->leftJoin("contacts", "contacts.user_id", "=", "pledges.user_id")
            ->leftJoin("users", "users.id", "=", "pledges.user_id")->get();
            $remove[] = "'";
            $remove[] = '"';

            $message = str_replace($remove, "", $request->message);

            foreach($pledges as $pledge){
                $balance = $pledge->amount - $pledge->paid;
                $m = str_replace("{{NAME}}", strtoupper($pledge->firstname), $message);
                $m = str_replace("{{ACTIVITY}}", strtoupper($pledge->name), $m);
                $m = str_replace("{{BALANCE}}", number_format($balance, 0), $m);
                $number = "254".substr($pledge->phone,1);
                if($this->send($number, $m)){
                    $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$m, "sent"=>\Carbon\Carbon::now()]);
                    \DB::table('sms_recipients')->insert(["recipients"=>$pledge->id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                }
            }
            return back()->with("success", "Message sent successfully");
        }
    }

    public function paypledge(Request $request){
        $pledge = \DB::table('pledges')->select("users.id", "users.firstname", "users.lastname", "pledges.paid", "activities.name",
        "pledges.amount", "contacts.phone")->where("pledges.id", $request->id)->leftJoin("users", "users.id", "=", "pledges.user_id")
        ->leftJoin("contacts", "contacts.user_id", "=", "users.id")->leftJoin("activities","pledges.activity","=", "activities.id")->first();

        //return json_encode($pledge->id);

        $paid = $pledge->paid + doubleval($request->payments);
        if(\DB::table("pledges")->where("id", $request->id)->update(["paid"=>$paid])){
            $number = "254".substr($pledge->phone, 1);
            $message = "Dear ".strtoupper($pledge->firstname).",\nThank you for honouring your pledge towards ".strtoupper($pledge->name).". KES ".number_format($request->payments,0)." has been recieved";
            if($paid >= $pledge->amount){
                $message .= ". Your pledge is FULLY settled. May GOD bless and increase you resources.";
            }else{
                $message .= ". Your balance now is KES ".number_format($pledge->amount - $paid).". May GOD bless and increase you resources.";
            }

            if($this->send($number, $message)){
                $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$message, "sent"=>\Carbon\Carbon::now()]);
                \DB::table('sms_recipients')->insert(["recipients"=>$pledge->id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
            }
            return back()->with("success", "Balance updated!");
        }else{
            return back()->with("error", "unable to update balance");
        }
    }

    public function addpledgegroupmembers(Request $request){
        if(!empty($request['contacts'])){
            $group = \DB::table("groups")->where("groups.id", $request->group_id)->first();

            foreach($request['contacts'] as $contact){
                if(\DB::table("pledges")->where("user_id", $contact)->where("groups", $request->group_id)->count() == 0){
                    \DB::table("pledges")->insert(["activity"=>$group->activity, "groups"=>$request->group_id, "user_id"=>$contact, "paid"=>0, "amount"=>0, "status"=>0]);
                }
            }

            //update amounts;
            $groups = \DB::table("groups")->select("groups.id", "groups.name", "groups.amount", \DB::Raw("sum(pledges.amount) as pledged"))->where("groups.id", $request->group_id)
            ->leftJoin("pledges", "pledges.groups", "=", "groups.id")->groupBy("groups.name", "groups.amount", "groups.id")->first();
            $members = \DB::table("pledges")->where("groups", $request->group_id)->count();

            $amount = round(($groups->amount/$members), 0);
            foreach(\DB::table("pledges")->where("groups", $request->group_id)->get() as $pledge){
                \DB::table("pledges")->where("id", $pledge->id)->update(['amount'=>$amount]);
            }
            return back()->with("success", "Members Updated successfully!");
        }else{
            return back()->with("error", "Choose participants for this group!");
        }

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
            CURLOPT_POSTFIELDS => 'apikey='.env('SMS_API_KEY').'&partnerID='.env('SMS_PARTNER_ID').'&message=' . urlencode($message) . '&shortcode='.env('SMS_SHORT_CODE').'&mobile='.$number,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        )
        );

        $curl_response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($curl_response, true);
        //\Log::info(json_encode($response).'NUMBER: '.$number);
        return $response;
    }
}
