<?php

namespace App\Http\Controllers\Dashboard\Communication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Communication']);

    }
    public function index()
    {
        $emails = \DB::table("emails")->select("emails.id", "emails.subject", "emails.message", "emails.sent", \DB::Raw("count(email_recipients.email_id) as recipients"))
            ->leftJoin("email_recipients", "email_recipients.email_id", "=", "emails.id")
            ->groupBy("emails.id", "emails.message", "emails.subject", "emails.sent")->orderBy("emails.id", "DESC")->paginate(15);
        //$emails = \DB::table("emails")->join("users", "users.id", "emails.user_id")->leftJoin("profiles", "profiles.user_id", "=", "emails.user_id")->
        //select("emails.id", "emails.subject", "emails.message", "users.email", "users.firstname", "profiles.name as image", "users.lastname", "emails.sent")->orderBy("emails.id", "DESC")->paginate(15);
        return view("dashboard.communication.emails")->with("emails", $emails);
    }
    public function getEmails(Request $request)
    {
        $start = $request->limit == null ? "0" : intval($request->limit);
        if ($request->groups == 1) {
            if ($request->search == null) {
                return json_encode(\DB::table("people")->select("people.id", "people.name", "people.user_group", \DB::Raw("count(people.id) as members"))
                    ->leftJoin("people_members", "people_members.people_id", "=", "people.id")->groupBy('people.id')->groupBy('people.name')->groupBy('people.user_group')->
                    orderBy("people.name", "ASC")->skip($start)->take(10)->get());
            } else {
                return json_encode(\DB::table("people")->select("people.id", "people.name", "people.user_group", \DB::Raw("count(people.id) as members"))->where("name", "LIKE", "%" . $request->search . "%")
                    ->leftJoin("people_members", "people_members.people_id", "=", "people.id")->groupBy('people.id')->groupBy('people.name')->groupBy('people.user_group')->
                    orderBy("people.name", "ASC")->skip($start)->take(10)->get());
            }
        } else {
            if ($request->search == null) {
                return json_encode(\DB::table("users")->select("id", "firstname", "lastname", "email")->orderBy("firstname", "ASC")->skip($start)->take(10)->get());
            } else {
                return json_encode(\DB::table("users")->select("id", "firstname", "lastname", "email")->where("firstname", "LIKE", "%" . $request->search . "%")->orWhere("lastname", "LIKE", "%" . $request->search . "%")
                    ->orWhere("email", "LIKE", "%" . $request->search . "%")->orderBy("firstname", "ASC")->skip($start)->take(10)->get());
            }
        }
    }

    public function email(Request $request)
    {
        $email = \DB::table("emails")->where("emails.id", $request->id)->select("people.name", "emails.id", "emails.subject", "emails.message", "emails.sent", \DB::Raw("count(email_recipients.email_id) as recipients"))
            ->leftJoin("email_recipients", "email_recipients.email_id", "=", "emails.id")->leftJoin("people", "people.id", "emails.people_id")
            ->groupBy("people.name", "emails.id", "emails.message", "emails.subject", "emails.sent")->first();
        //return json_encode($email);
        $users = \DB::table("email_recipients")->where("email_recipients.email_id", "=", $request->id)->join("users", "users.id", "email_recipients.user_id")->leftJoin("profiles", "profiles.user_id", "=", "users.id")->
            select("users.firstname", "users.lastname", "users.email", "email_recipients.sent", "profiles.name as image")->paginate(15);

        return view("dashboard.communication.email-read", ["email" => $email, "users" => $users]);
    }
    public function removeemail(Request $request)
    {
        $message = \DB::table('emails')->where("id", $request->id)->first();
        if ($message == null) {
            return redirect()->back()->with("error", "Invalid Email ID");
        } else {
            if (\DB::table('emails')->where("id", $request->id)->delete()) {
                \DB::table('email_recipients')->where("email_id", $request->id)->delete();
                return redirect()->to('emails')->with("success", "Email removed successfully");
            } else {
                return redirect()->back()->with("error", "Unable to remove email");
            }
        }
    }

    public function html_email(Request $request)
    {
        $request->validate([
            "message" => "required",
            "subject" => "required",
        ]);
        set_time_limit(0);
        if ($request->choice == 0) {
            //send to individual
            if (empty($request['contacts'])) {
                return back()->with("error", "No recipients selected!");
            } else {
                $mid = \DB::table('emails')->insertGetId(["subject" => $request->subject, "people_id" => 0, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
                foreach ($request['contacts'] as $contact) {
                    $user = \DB::table("users")->where("id", $contact)->first();
                    if ($user != null) {
                        \DB::table("email_recipients")->insert(["user_id" => $user->id, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                        $this->sendemail($user->email, $request->subject, $user->firstname, $user->lastname, $request->message);
                    }
                }
                return back()->with("success", "Emails successfully sent!");
            }
        } else if ($request->choice == 1) {
            //send to groups
            foreach ($request['groups'] as $contact) {
                $mid = \DB::table('emails')->insertGetId(["subject" => $request->subject, "people_id" => $contact, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
                $leader = \DB::table("people")->where("people.id", $contact)->join("users", "users.id", "=", "people.leader")->first();
                if ($leader != null) {
                    \DB::table("email_recipients")->insert(["user_id" => $leader->leader, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                    $this->sendemail($leader->email, $request->subject, $leader->firstname, $leader->lastname, $request->message);
                }
                $members = \DB::table("people_members")->where("people_id", $contact)->where("people_members.status", 1)->join("users", "users.id", "=", "people_members.user_id")->get();
                foreach ($members as $member) {
                    \DB::table("email_recipients")->insert(["user_id" => $member->user_id, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                    $this->sendemail($member->email, $request->subject, $member->firstname, $member->lastname, $request->message);
                }
            }
        } else {
            //Send to all
            $users = \DB::table("users")->get();
            $mid = \DB::table('emails')->insertGetId(["subject" => $request->subject, "people_id" => 0, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
            foreach ($users as $user) {
                \DB::table("email_recipients")->insert(["user_id" => $user->id, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                $this->sendemail($user->email, $request->subject, $user->firstname, $user->lastname, $request->message);
            }
        }
        return redirect()->back()->with("success", "Emails sent successfully!");
    }
    public function sendemail($email, $subject, $firstname, $lastname, $mess)
    {
        $data = array('name' => $firstname . " " . $lastname, 'mes' => $mess);
        $site_settings = $this->site_settings;
        $church_name = $site_settings != null ? "" . $site_settings->name : "CHURCH APP";
        Mail::send('dashboard.communication.mail', $data, function ($message) use ($email, $subject, $firstname, $lastname, $church_name) {
            $message->to($email, $firstname . " " . $lastname)->subject
            ($subject);
            $message->from('info@happychurchruiru.org', $church_name);
        });
    }

}
