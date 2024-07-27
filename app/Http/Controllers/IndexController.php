<?php

namespace App\Http\Controllers;
use App\Models\Gallery;
use App\Models\HomePage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $site_settings = \DB::table("settings")->first();
        \View::share('site_settings', $site_settings);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index()
    {
        $message = \DB::table("pastorsmessage")->first();
        $homepage = HomePage::first();
        $communities = \DB::table("people")->where("user_group", 1)->orderBy("id", "desc")->offset(0)->limit(3)->get();
        $articles = \DB::table("articles")->orderBy("id", "desc")->where("status", 1)->offset(0)->limit(3)->get();
        $sermons = \DB::table("sermons")->orderBy("id", "desc")->offset(0)->limit(3)->get();
        $services = \DB::table("orderofservice")->orderBy("id", "desc")->get();

        return view("index")->with("homepage", $homepage)->with("message", $message)->with("services", $services)
        ->with("communities", $communities)->with("articles", $articles)->with("sermons", $sermons);
    }
    public function people()
    {
        $pastors = \DB::table("pastors")->select("users.firstname", "users.lastname", "profiles.name as image")->where("pastors.status", 0)->join("users", "users.id", "pastors.user_id")->leftJoin("profiles", "profiles.user_id", "pastors.user_id")->get();
        $senior = \DB::table("pastors")->where("pastors.status", 1)->select("users.firstname", "users.lastname", "profiles.name as image")->join("users", "users.id", "pastors.user_id")->leftJoin("profiles", "profiles.user_id", "pastors.user_id")->first();
        $communities = \DB::table("people")->where("user_group", 1)->orderBy("id", "desc")->offset(0)->limit(6)->get();
        $departments = \DB::table("people")->where("user_group", 2)->orderBy("id", "desc")->offset(0)->limit(6)->get();
        $articles = \DB::table("articles")->orderBy("id", "desc")->where("status", 1)->offset(0)->limit(3)->get();
        $sermons = \DB::table("sermons")->orderBy("id", "desc")->offset(0)->limit(3)->get();
        $testimonials = \DB::table("testimonials")->where("testimonials.status", 1)->select("testimonials.testimonial", "users.firstname", "users.lastname", "profiles.name as image")->join("users", "users.id", "testimonials.user_id")->leftJoin("profiles", "profiles.user_id", "testimonials.user_id")->orderBy("testimonials.id", "desc")->offset(0)->limit(4)->get();

        return view("people")->with("pastors",  $pastors)->with("senior", $senior)->with("articles", $articles)
        ->with("communities", $communities)->with("departments", $departments)->with("sermons", $sermons)->with("testimonials", $testimonials);
    }
    public function notices()
    {
        $notices = \DB::table("notices")->where("status", 0)->orderBy("id", "desc")->paginate(6);
        $articles = \DB::table("articles")->orderBy("id", "desc")->where("status", 1)->offset(0)->limit(3)->get();
        $testimonials = \DB::table("testimonials")->where("testimonials.status", 1)->select("testimonials.testimonial", "users.firstname", "users.lastname", "profiles.name as image")->join("users", "users.id", "testimonials.user_id")->leftJoin("profiles", "profiles.user_id", "testimonials.user_id")->orderBy("testimonials.id", "desc")->offset(0)->limit(4)->get();

        return view("notices")->with("notices",  $notices)->with("articles", $articles)->with("testimonials", $testimonials);
    }
    public function spiritual()
    {
        $prayers = \DB::table("prayers")->where("status", 1)->orderBy("id", "desc")->paginate(6);
        $articles = \DB::table("articles")->where("status", 1)->orderBy("id", "desc")->offset(0)->limit(3)->get();
        $testimonials = \DB::table("testimonials")->where("testimonials.status", 1)->select("testimonials.testimonial", "users.firstname", "users.lastname", "profiles.name as image")->join("users", "users.id", "testimonials.user_id")->leftJoin("profiles", "profiles.user_id", "testimonials.user_id")->orderBy("testimonials.id", "desc")->offset(0)->limit(4)->get();
        $sermons = \DB::table("sermons")->orderBy("id", "desc")->paginate(6);

        return view("spiritual")->with("prayers",  $prayers)->with("sermons", $sermons)->with("articles", $articles)->with("testimonials", $testimonials);
    }
    public function events()
    {
        $events = \DB::table("events")->orderBy("id", "desc")->paginate(6);
        $seminars = \DB::table("seminars")->orderBy("id", "desc")->paginate(6);
        $notices = \DB::table("notices")->orderBy("id", "desc")->paginate(6);
        $testimonials = \DB::table("testimonials")->where("testimonials.status", 1)->select("testimonials.testimonial", "users.firstname", "users.lastname", "profiles.name as image")->join("users", "users.id", "testimonials.user_id")->leftJoin("profiles", "profiles.user_id", "testimonials.user_id")->orderBy("testimonials.id", "desc")->offset(0)->limit(4)->get();

        //return json_encode($events);
        return view("events", ["events"=>$events, "seminars"=>$seminars, "testimonials"=>$testimonials, "notice"=>$notices]);
    }
    public function event(Request $request)
    {
        $event = \DB::table("events")->where("id", $request->id)->first();
        return view("event")->with("event",  $event);
    }
    public function seminar(Request $request)
    {
        $seminar = \DB::table("seminars")->where("id", $request->id)->first();
        return view("seminar")->with("seminar",  $seminar);
    }
    public function gallery()
    {
        $galleries = Gallery::orderBy("id", "desc")->paginate(6);
        $articles = \DB::table("articles")->where("status", 1)->orderBy("id", "desc")->offset(0)->limit(3)->get();
        $testimonials = \DB::table("testimonials")->where("testimonials.status", 1)->select("testimonials.testimonial", "users.firstname", "users.lastname", "profiles.name as image")->join("users", "users.id", "testimonials.user_id")->leftJoin("profiles", "profiles.user_id", "testimonials.user_id")->orderBy("testimonials.id", "desc")->offset(0)->limit(4)->get();
        $sermons = \DB::table("sermons")->orderBy("id", "desc")->paginate(6);

        return view("gallery")->with("galleries",  $galleries)->with("sermons", $sermons)->with("articles", $articles)->with("testimonials", $testimonials);
    }
    public function articles()
    {
        $articles = \DB::table("articles")->where("status", 1)->orderBy("id", "desc")->paginate(6);
        $testimonials = \DB::table("testimonials")->where("testimonials.status", 1)->select("testimonials.testimonial", "users.firstname", "users.lastname", "profiles.name as image")->join("users", "users.id", "testimonials.user_id")->leftJoin("profiles", "profiles.user_id", "testimonials.user_id")->orderBy("testimonials.id", "desc")->offset(0)->limit(4)->get();

        return view("articles")->with("articles", $articles)->with("testimonials", $testimonials);
    }
    public function shop()
    {
        $testimonials = \DB::table("testimonials")->where("testimonials.status", 1)->select("testimonials.testimonial", "users.firstname", "users.lastname", "profiles.name as image")->join("users", "users.id", "testimonials.user_id")->leftJoin("profiles", "profiles.user_id", "testimonials.user_id")->orderBy("testimonials.id", "desc")->offset(0)->limit(4)->get();

        return view("shop");
    }

    public function myshop()
    {
        $products = \DB::table("products")->orderBy("id", "desc")->paginate(15);

        return view("myshop")->with("products", $products);
    }

    public function community(Request $request)
    {
        $community = \DB::table("people")->where("id", $request->id)->first();
        if($community == null){
            return redirect()->to("/");
        }
        return view("community")->with("community",  $community);
    }

    public function article(Request $request)
    {
        $article = \DB::table("articles")->where("id", $request->id)->first();
        if($article == null){
            return redirect()->to("/");
        }
        return view("article")->with("article",  $article);
    }

    public function sermon(Request $request)
    {
        $sermon = \DB::table("sermons")->where("id", $request->id)->first();
        if($sermon == null){
            return redirect()->to("/");
        }
        return view("sermon")->with("sermon",  $sermon);
    }

    public function notice(Request $request)
    {
        $notice = \DB::table("notices")->where("id", $request->id)->first();
        if($notice == null){
            return redirect()->to("/");
        }
        return view("notice")->with("notice",  $notice);
    }

    public function department(Request $request)
    {
        $department = \DB::table("people")->where("id", $request->id)->first();
        if($department == null){
            return redirect()->to("/");
        }
        return view("community")->with("community",  $department);
    }

    public function prayer(Request $request)
    {
        $prayer = \DB::table("prayers")->where("id", $request->id)->first();
        if($prayer == null){
            return redirect()->to("/");
        }
        return view("prayer")->with("prayer",  $prayer);
    }

    public function registration(){
        $events = \DB::table("events")->select("id", "title")->where("eventdate", ">=", \Carbon\Carbon::now()->setTimeZone("Africa/Nairobi"))->get();
        $seminars = \DB::table("seminars")->select("id", "title")->where("end", ">=", \Carbon\Carbon::now()->setTimeZone("Africa/Nairobi"))->get();
        return view("registration")->with("events", $events)->with("seminars", $seminars);
    }

    public function saveregistration(Request $request){
        $request->validate([
            "gender"=>"required",
            "dob"=>"required",
            "fname"=>"required",
            "lname"=>"required",
            "email"=>"required|unique:users",
            "phone"=>"required|numeric|min:9",
            "country"=>"required",
            "city"=>"required",
            "gender"=>"required",
            'file.*' => 'file|mimes:png,jpeg,jpg,pdf,doc,docx,zip|max:2048',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        $user = new User();
        $user->firstname = $request->fname;
        $user->lastname = $request->lname;
        $user->email = $request->email;
        $user->status = 1;
        $user->role = 0;
        $user->password = \Hash::make("12345");

        $event_id = 0;
        if($request->registration > 0){
            $event_id = $request->seminar;
        }else{
            $event_id = $request->event;
        }
        if($user->save()){
            $user = User::where("email", $request->email)->first();
            $report = "";
            if($request->report != null){
                $report = $request->report->getClientOriginalName();
                if(\DB::table("registration")->where('report', $report)->count() > 0){
                    $report = time().".".$request->report->getClientOriginalExtension();
                }
                request()->report->move(public_path('reports'), $report);
            }

            if(\DB::table("registration")->insert(["user_id"=>$user->id, "event_id"=>$event_id, "event_type"=>
            $request->registration, "report"=>$report, "status"=>0, "rdate"=>\Carbon\Carbon::today()])){
                if(\DB::table("contacts")->insert(["user_id"=>$user->id, "phone"=>$request->phone, "city"=>$request->city,
                "country"=>$request->country, "gender"=>$request->gender, "dob"=>$request->dob])){
                    \Auth::login($user);
                    return redirect()->to("home")->with("success", "You have successfully created an seminar/event. Your password is <strong>12345</strong>");
                }else{
                    \DB::table("registration")->where("user_id", $user->id)->delete();
                    \DB::table("contacts")->where("user_id", $user->id)->delete();
                    \DB::table("users")->where("id", $user->id)->delete();
                    return redirect()->to("registration")->with("error", "Unable to register Event/Seminar");
                }
            }else{
                return back()->with('success', 'Registration successful');
            }
        }
    }

    public function checkLogin()
    {
        if (Auth::check()) {
            return response()->json(['loggedIn' => true]);
        } else {
            return response()->json(['loggedIn' => false]);
        }
    }
}
