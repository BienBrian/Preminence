<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class HomeController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return view('dashboard.home');
    }
    public function home(){
        if(\Auth::user()->role > 0){
            $articles = \DB::table("articles")->where("user_id", \Auth::user()->id)->count();
            $prayers = \DB::table("prayers")->where("user_id", \Auth::user()->id)->count();
            $pledges = \DB::table("pledges")->where("user_id", \Auth::user()->id)->sum("amount") + \DB::table("participants")->where("user_id", \Auth::user()->id)->sum("amount");
            return view('admin.dashboard', ["prayers"=>$prayers, "articles"=>$articles, "pledges"=>$pledges]);
        }else{
            $events = \DB::table("registration")->join("events", "events.id", "registration.event_id")->where("registration.event_type", 0)->where("registration.user_id", \Auth::user()->id)->count();
            $seminars = \DB::table("registration")->join("seminars", "seminars.id", "registration.event_id")->where("registration.event_type", 1)->where("registration.user_id", \Auth::user()->id)->count();
            return view("guests.home")->with("seminars", $seminars)->with("events", $events);
        }
    }
    public function dashboard()
    {
        $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
        $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
        if($perm1 != null || $perm2 != null){
            if($perm1->dashboard >0 || $perm2->dashboard > 0){
                $collected = \DB::table('funds')->where('ftype', 0)->sum('amount');
                $spent = \DB::table('funds')->where('ftype', 1)->sum('amount');
                $donation = \DB::table('donations')->sum('amount');

                return view('user.permissions.dashboard')->with('collected', $collected)->with('spent', $spent)->with('donation', $donation);
            }else{
                return redirect()->back()->with("error", "Access denied");
            }
        }else{
            return redirect()->to("/home");
        }
    }

    public function orderofservice(){

        if(\Auth::user()->role == 1){
            $services = \DB::table('orderofservice')->paginate(15);
            return view('admin.orderofservice')->with("services", $services);
        }else{
            $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
            $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
            if($perm1 != null || $perm2 != null){
                if($perm1->dashboard >0 || $perm2->dashboard > 0){
                    $services = \DB::table('orderofservice')->paginate(15);
                    return view('user.permissions.orderofservice')->with("services", $services);
                }else{
                    return redirect()->back()->with("error", "Access denied");
                }
            }else{
                return redirect()->to("/home");
            }
        }
    }

    public function addservice(Request $request){
        request()->validate([
            'venue'=>'required|string|min:2',
            'days'=>'required|string',
            'time'=>'required|string',
            'description'=>'required|string',
        ]);
        if($request->id > 0){
            //update
            if(\DB::table('orderofservice')->where("id", $request->id)->update(["time"=>$request->time, "description"=>$request->description,
            "day"=>$request->days, "venue"=>$request->venue])){
                return redirect()->back()->with("success", "Service succesfully added!");
            }else{
                return redirect()->back()->with("error", "Unable to save!");
            }
        }else{
            //insert
            if(\DB::table('orderofservice')->insert(["time"=>$request->time, "description"=>$request->description,
            "day"=>$request->days, "venue"=>$request->venue])){
                return redirect()->back()->with("success", "Service succesfully added!");
            }else{
                return redirect()->back()->with("error", "Unable to save!");
            }
        }
    }

    public function removeservice(Request $request){
        if($request->id > 0){
            //update
            if(\DB::table('orderofservice')->where("id", $request->id)->delete()){
                return redirect()->back()->with("success", "Service succesfully removed!");
            }else{
                return redirect()->back()->with("error", "Unable to remove item");
            }
        }
        return redirect()->to('/orderofservice')->with('error', 'invalid request');
    }

    public function weeklyverse(){
        if(\Auth::user()->role == 1){
            $verse = \DB::table('weeklyverse')->first();
            return view('admin.weeklyverse')->with("verse", $verse);
        }else{

            $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
            $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
            if($perm1 != null || $perm2 != null){
                if($perm1->dashboard >0 || $perm2->dashboard > 0){
                    $verse = \DB::table('weeklyverse')->first();
                    return view('user.permissions.weeklyverse')->with("verse", $verse);
                }else{
                    return redirect()->back()->with("error", "Access denied");
                }
            }else{
                return redirect()->to("/home");
            }
        }
    }

    public function addverse(Request $request){
        request()->validate([
            'verse'=>'required|string',
            'description'=>'required|string',
            'version'=>'required|string',
        ]);

        if($request->id > 0){
            //update
            if(\DB::table('weeklyverse')->where("id", $request->id)->update(["verse"=>$request->verse, "description"=>$request->description,
            "version"=>$request->version])){
                return redirect()->back()->with("success", "Verse succesfully updated!");
            }else{
                return redirect()->back()->with("error", "Unable to save!");
            }
        }else{
            //insert
            if(\DB::table('weeklyverse')->insert(["verse"=>$request->verse, "description"=>$request->description,
            "version"=>$request->version])){
                return redirect()->back()->with("success", "Verse succesfully added!");
            }else{
                return redirect()->back()->with("error", "Unable to save!");
            }
        }
    }

    public function years(Request $request){
        $year = intval($request->years);
        $dashboard = \DB::table('funds')->where("sources.ftype", 0)->join("sources", "sources.id", "funds.source")->select(\DB::raw('sum(funds.amount) as totals'), \DB::raw('YEAR(funds.created_at) year, MONTH(funds.created_at) month'))->whereYear('funds.created_at', $year)->groupby(\DB::raw('YEAR(funds.created_at)'), \DB::raw('MONTH(funds.created_at)'))->get();
        return json_encode($dashboard);
    }

    public function expenditure(Request $request){
        $year = intval($request->years);
        $dashboard = \DB::table('funds')->where("sources.ftype", 1)->join("sources", "sources.id", "funds.source")->select(\DB::raw('sum(funds.amount) as totals'), \DB::raw('YEAR(funds.created_at) year, MONTH(funds.created_at) month'))->whereYear('funds.created_at', $year)->groupby(\DB::raw('YEAR(funds.created_at)'), \DB::raw('MONTH(funds.created_at)'))->get();
        return json_encode($dashboard);
    }

    public function myfunds(Request $request){
        $year = intval($request->years);
        $dashboard = \DB::table('funds')->where("source", $request->id)->select(\DB::raw('sum(amount) as totals'), \DB::raw('YEAR(created_at) year, MONTH(created_at) month'))->whereYear('created_at', $year)->groupby(\DB::raw('YEAR(created_at)'), \DB::raw('MONTH(created_at)'))->get();
        return json_encode($dashboard);
    }

    public function seminars(){
        if(\Auth::user()->role == 0){
            $events = \DB::table("registration")->join("events", "events.id", "registration.event_id")->where("registration.event_type", 0)->where("registration.user_id", \Auth::user()->id)->count();
            $seminars = \DB::table("registration")->select("registration.id", "seminars.title", "seminars.theme", "seminars.description", "registration.status", "seminars.start", "seminars.entry", "seminars.end")->join("seminars", "seminars.id", "registration.event_id")->where("registration.event_type", 1)->where("registration.user_id", \Auth::user()->id)->paginate(10);
            $sem = \DB::table("seminars")->select("id", "title")->where("start", ">=", \Carbon\Carbon::now()->setTimezone("Africa/Nairobi"))->get();
            return view("guests.seminars")->with("seminars", $seminars)->with("sem", $sem);
        }
    }

    public function addseminar(Request $request){
        $request->validate([
            "seminar"=>"required",
            'file.*' => 'file|mimes:png,jpeg,jpg,pdf,doc,docx,zip|max:2048',
        ]);
        $report = "";
        if(\DB::table("registration")->where("user_id", \Auth::user()->id)->where("event_type", 1)->where("event_id", $request->seminar)->count() == 0){
            if($request->report != null){
                $report = $request->report->getClientOriginalName();
                if(\DB::table("registration")->where('report', $report)->count() > 0){
                    $report = time().".".$request->report->getClientOriginalExtension();
                }
                request()->report->move(public_path('reports'), $report);
            }
            if(\DB::table("registration")->insert(["user_id"=>\Auth::user()->id, "event_id"=>$request->seminar, "event_type"=>
                1, "report"=>$report, "status"=>0, "rdate"=>\Carbon\Carbon::today()])){
                    return redirect()->back()->with("success", "Successfully saved seminar!");
            }else{
                return redirect()->back()->with("error", "Error booking seminar");
            }
        }else{
            return redirect()->back()->with("error", "You've already registered for this event!");
        }
    }

    public function removeseminar(Request $request){
        $seminar = \DB::table('registration')->where("id", $request->id)->first();
        if($seminar == null){
            return redirect()->back()->with("error", "invalid request!");
        }

        if($seminar->report != null){
            if(file_exists(public_path()."/reports/".$seminar->report)){
                unlink(public_path()."/reports/".$seminar->report);
            }
        }
        if(\DB::table("registration")->where("id", $request->id)->delete()){
            return redirect()->back()->with("success", "Seminar removed successfully!");
        }else{
            return redirect()->back()->with("error", "Unable to remove seminar!");
        }
    }

    public function events(){
        if(\Auth::user()->role == 0){
            $events = \DB::table("registration")->select("registration.id", "events.title", "events.theme", "events.description", "registration.status", "events.eventdate", "events.entry")->join("events", "events.id", "registration.event_id")->where("registration.event_type", 0)->where("registration.user_id", \Auth::user()->id)->paginate(10);
            $sem = \DB::table("events")->select("id", "title")->where("eventdate", ">=", \Carbon\Carbon::now()->setTimezone("Africa/Nairobi"))->get();
            return view("guests.events")->with("events", $events)->with("sem", $sem);
        }
    }

    public function addevent(Request $request){
        $request->validate([
            "event"=>"required",
            'file.*' => 'file|mimes:png,jpeg,jpg,pdf,doc,docx,zip|max:2048',
        ]);
        $report = "";
        if(\DB::table("registration")->where("user_id", \Auth::user()->id)->where("event_type", 0)->where("event_id", $request->event)->count() == 0){
            if($request->report != null){
                $report = $request->report->getClientOriginalName();
                if(\DB::table("registration")->where('report', $report)->count() > 0){
                    $report = time().".".$request->report->getClientOriginalExtension();
                }
                request()->report->move(public_path('reports'), $report);
            }
            if(\DB::table("registration")->insert(["user_id"=>\Auth::user()->id, "event_id"=>$request->event, "event_type"=>
                0, "report"=>$report, "status"=>0, "rdate"=>Carbon::today()])){
                    return redirect()->back()->with("success", "Successfully saved event!");
            }else{
                return redirect()->back()->with("error", "Error booking event");
            }
        }else{
            return redirect()->back()->with("error", "You've already registered for this event!");
        }
    }

    public function removeevent(Request $request){
        $event = \DB::table('registration')->where("id", $request->id)->first();
        if($event == null){
            return redirect()->back()->with("error", "invalid request!");
        }

        if($event->report != null){
            if(file_exists(public_path()."/reports/".$event->report)){
                unlink(public_path()."/reports/".$event->report);
            }
        }
        if(\DB::table("registration")->where("id", $request->id)->delete()){
            return redirect()->back()->with("success", "Event removed successfully!");
        }else{
            return redirect()->back()->with("error", "Unable to remove event!");
        }
    }


    public function newEmail(){
        return view("admin.email-new");
    }




    public function newSMS(){
        return view("admin.sms-new");
    }


    public function notifications(){
        $articles = \DB::table('articles')->where("status", 0)->count();
        $sermons = \DB::table('sermons')->where("created_at", "<=", Carbon::today()->addDay(1))->where("created_at", ">=", Carbon::today()->subDay(1))->count();
        $events = \DB::table('events')->where("eventdate", ">=", Carbon::today())->count();

        $notifications = array("articles"=>$articles, "sermons"=>$sermons, "events"=>$events);

        return json_encode($notifications);
    }
}
