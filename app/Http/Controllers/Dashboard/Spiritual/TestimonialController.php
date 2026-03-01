<?php

namespace App\Http\Controllers\Dashboard\Spiritual;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Spiritual']);
    }

    public function index()
    {
            $testimonials = Testimonial::select('testimonials.id', 'testimonials.testimonial', 'testimonials.updated_at',
            'testimonials.user_id', 'users.firstname', 'users.lastname', 'testimonials.status', 'profiles.name as image')->
            join('users', 'users.id', 'testimonials.user_id')->leftJoin("profiles", "profiles.user_id", "=", "testimonials.user_id")->paginate(15);

            return view('dashboard.spiritual.testimonials')->with('testimonials', $testimonials);
    }

    public function testimonials(){
        $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
        $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
        if($perm1 != null || $perm2 != null){
            if($perm1->testimonials >0 || $perm2->testimonials > 0){
                $testimonials = Testimonial::select('testimonials.id', 'testimonials.testimonial', 'testimonials.updated_at',
                'testimonials.user_id', 'users.firstname', 'users.lastname', 'testimonials.status', 'profiles.name as image')->
                join('users', 'users.id', 'testimonials.user_id')->leftJoin("profiles", "profiles.user_id", "=", "testimonials.user_id")->paginate(15);
                return view('user.permissions.testimonials')->with('testimonials', $testimonials);
                }else{
                    return redirect()->back()->with("error", "Access denied");
                }
        }else{
            return redirect()->to("/home");
        }
    }

    public function testimonial(Request $request){
        $testimonial = Testimonial::where("testimonials.id", $request->id)->select('testimonials.id', 'testimonials.testimonial', 'testimonials.updated_at',
        'testimonials.user_id', 'users.firstname', 'users.lastname', 'testimonials.status', 'testimonials.created_at', 'profiles.name as image')->
        join('users', 'users.id', 'testimonials.user_id')->leftJoin("profiles", "profiles.user_id", "=", "testimonials.user_id")->first();
        if($testimonial != null){
            return view("admin.testimonial")->with("testimonial", $testimonial);
        }else{
            return redirect()->back()->with("error", "Invalid request!");
        }

    }

    public function save(Request $request){
        if($request->id > 0){
            //update
            $update = Testimonial::where('id', '=', $request->id)->update(array('testimonial' => $request->testimonial, 'status'=>0));
            if($update){
                return redirect()->back()->with('success', 'Your testimonial has been updated successfully');
            }else{
                return redirect()->back()->with('error', 'Unable to update. Try again');
            }
        }else{
            //insert
            $testimonial = new Testimonial();
            $testimonial->testimonial = $request->testimonial;
            $testimonial->user_id = $request->user_id;
            $testimonial->status = 0;
            if($testimonial->save()){
                redirect()->back()->with('success', 'Your testimonial has been created successfully');
            }else{
                return redirect()->back()->with('error', 'Unable to save. Try again');
            }
        }
    }

    public function activate(Request $request){
        $testimonial = Testimonial::find($request->id);
        if($testimonial != null){
            $update = Testimonial::where("id", "=", $testimonial->id)->update(["status"=>1]);
            if($update){
                return redirect()->to("/testimonials")->with('success', 'Testimonial Activated');
            }else{
                return redirect()->to("/testimonials")->with('error', 'Unable to activate testimonial');
            }
        }else{
            return redirect()->to("/testimonials")->with('error', 'Invalid request');;
        }
    }

    public function deactivate(Request $request){
        $testimonial = Testimonial::find($request->id);
        if($testimonial != null){
            $update = Testimonial::where("id", "=", $testimonial->id)->update(["status"=>0]);
            if($update){
                return redirect()->to("/testimonials")->with('success', 'Testimonial Deactivated');
            }else{
                return redirect()->to("/testimonials")->with('error', 'Unable to deactivate testimonial');
            }
        }else{
            return redirect()->to("/testimonials")->with('error', 'Invalid request');;
        }
    }
}
