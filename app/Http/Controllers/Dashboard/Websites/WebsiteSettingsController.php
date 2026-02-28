<?php

namespace App\Http\Controllers\Dashboard\Websites;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\Twilio;
use Illuminate\Http\Request;

class WebsiteSettingsController extends DashboardController
{
    public function __construct()
    {
       parent::__construct();
       $this->middleware(['permission:View Website Settings']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $settings = Setting::first();
        $twilio = Twilio::first();
        return view('dashboard.website.settings', @compact('settings', 'twilio'));
    }
/*
    public function settings()
    {
        $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
        $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
        if ($perm1 != null || $perm2 != null) {
            if ($perm1->websites > 0 || $perm2->websites > 0) {
                $settings = Settings::first();

                return view('user.permissions.settings')->with('settings', $settings);
            } else {
                return redirect()->back()->with("error", "Access denied");
            }
        } else {
            return redirect()->to("/home");
        }
    }*/

    public function addSettings(Request $request)
    {
        /*$request->validate([
            "sid"=>"required",
            "number"=>"required",
            "token"=>"required",
            "theme"=>"required",
            "name"=>"required",
            "slogan"=>"required",
            "address"=>"required",
            "facebook"=>'required',
            "pinterest"=>'required',
            "google"=>'required',
            "linkedin"=>'required',
            'youtube'=>'required',
            'instagram'=>'required',
            'whatsapp'=>'required',
            'twitter'=>'required',
            'aboutus'=>'required',
            'contactus'=>'required'
        ]);
        if($request->tid > 0){
            \DB::table('twilio')->where("id", $request->tid)->update(["sid"=>$request->sid, "token"=>$request->token, "number"=>$request->number]);
        }else{
            \DB::table('twilio')->insert(["sid"=>$request->sid, "token"=>$request->token, "number"=>$request->number]);
        }*/
        if ($request->id > 0) {
            //update
            $update = Setting::where('id', '=', $request->id)->update(
                array(
                    'name' => $request->name,
                    'slogan' => $request->slogan,
                    'address' => $request->address,
                    'theme' => $request->theme,
                    'facebook' => $request->facebook,
                    'twitter' => $request->twitter,
                    'google' => $request->google,
                    'linkedin' => $request->linkedin,
                    'youtube' => $request->youtube,
                    'pinterest' => $request->pinterest,
                    'instagram' => $request->instagram,
                    'whatsapp' => $request->whatsapp,
                    'aboutus' => $this->purify($request->aboutus),
                    "contactus" => $this->purify($request->contactus)
                )
            );
            if ($update) {
                return redirect()->back()->with('success', 'Your Settings has been updated successfully');
            } else {
                return redirect()->back()->with('error', 'Unable to update. Try again');
            }
        } else {
            //insert
            $settings = new Setting();
            $settings->name = $request->name;
            $settings->slogan = $request->slogan;
            $settings->address = $request->address;
            $settings->theme = $request->theme;
            $settings->facebook = $request->facebook;
            $settings->twitter = $request->twitter;
            $settings->google = $request->google;
            $settings->linkedin = $request->linkedin;
            $settings->youtube = $request->youtube;
            $settings->pinterest = $request->pinterest;
            $settings->instagram = $request->instagram;
            $settings->whatsapp = $request->whatsapp;
            $settings->icon = "";
            $settings->favicon = "";
            $settings->aboutus = $this->purify($request->aboutus);
            $settings->contactus = $this->purify($request->contactus);

            if ($settings->save()) {
                return redirect()->back()->with('success', 'Your settings has been created successfully');
            } else {
                return redirect()->back()->with('error', 'Unable to save. Try again');
            }
        }
    }

    public function activate(Request $request)
    {
        $testimonial = Testimonial::find($request->id);
        if ($testimonial != null) {
            $update = Testimonial::where("id", "=", $testimonial->id)->update(["status" => 1]);
            if ($update) {
                return redirect()->to("/testimonials")->with('success', 'Testimonial Activated');
            } else {
                return redirect()->to("/testimonials")->with('error', 'Unable to activate testimonial');
            }
        } else {
            return redirect()->to("/testimonials")->with('error', 'Invalid request');
            ;
        }
    }

    public function deactivate(Request $request)
    {
        $testimonial = Testimonial::find($request->id);
        if ($testimonial != null) {
            $update = Testimonial::where("id", "=", $testimonial->id)->update(["status" => 0]);
            if ($update) {
                return redirect()->to("/testimonials")->with('success', 'Testimonial Deactivated');
            } else {
                return redirect()->to("/testimonials")->with('error', 'Unable to deactivate testimonial');
            }
        } else {
            return redirect()->to("/testimonials")->with('error', 'Invalid request');
            ;
        }
    }

    public function uploadfavicon(Request $request)
    {
        request()->validate([
            'favicon' => 'required|image|mimes:png|max:200',
        ]);

        $imageName = 'favicon.png';
        if (request()->favicon->move(public_path('website'), $imageName)) {
            if ($request->id > 0) {
                //update
                $update = Setting::where('id', '=', $request->id)->update(array('favicon' => $imageName));
                if (!$update) {
                    return back()->with('error', 'Unable to update settings');
                }
            } else {
                //insert
                $settings = new Setting();
                $settings->name = "";
                $settings->slogan = "";
                $settings->address = "";
                $settings->theme = "";
                $settings->facebook = "";
                $settings->twitter = "";
                $settings->google = "";
                $settings->linkedin = "";
                $settings->youtube = "";
                $settings->pinterest = "";
                $settings->instagram = "";
                $settings->whatsapp = "";
                $settings->icon = "";
                $settings->favicon = $request->favicon;
                if (!$settings->save()) {
                    return redirect()->back()->with('error', 'Unable to save. Try again');
                }
            }
        } else {
            return back()->with('error', 'Unable to save image');
        }

        return back()->with('success', 'You have successfully upload image.')->with('image', $imageName);
    }

    public function uploadicon(Request $request)
    {
        request()->validate([
            'icon' => 'required|image|mimes:png|max:1000',
        ]);

        $imageName = 'icon.png';
        if (request()->icon->move(public_path('website'), $imageName)) {
            if ($request->id > 0) {
                //update
                $update = Setting::where('id', '=', $request->id)->update(array('icon' => $imageName));
                if (!$update) {
                    return back()->with('error', 'Unable to update settings');
                }
            } else {
                //insert
                $settings = new Setting();
                $settings->name = "";
                $settings->slogan = "";
                $settings->address = "";
                $settings->theme = "";
                $settings->facebook = "";
                $settings->twitter = "";
                $settings->google = "";
                $settings->linkedin = "";
                $settings->youtube = "";
                $settings->pinterest = "";
                $settings->instagram = "";
                $settings->whatsapp = "";
                $settings->icon = "";
                $settings->favicon = $request->favicon;
                if (!$settings->save()) {
                    return redirect()->back()->with('error', 'Unable to save. Try again');
                }
            }
        } else {
            return back()->with('error', 'Unable to save image');
        }

        return back()->with('success', 'You have successfully upload image.')->with('image', $imageName);
    }
}
