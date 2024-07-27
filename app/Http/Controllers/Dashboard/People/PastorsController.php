<?php

namespace App\Http\Controllers\Dashboard\People;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;

class PastorsController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View People']);
    }
    public function pastors()
    {
            $pastors = \DB::table("pastors")->select("pastors.id", "users.firstname", "users.lastname", "users.email", "pastors.status")->join("users", "users.id", "=", "pastors.user_id")->paginate(15);
            return view("dashboard.people.pastors")->with("pastors", $pastors);
    }

    public function addpastor(Request $request){
        if(empty($request['contacts'])){
            return back()->with("error", "Please select a user to add");
        }else{
            foreach($request['contacts'] as $userid){
                if(\DB::table('pastors')->where("user_id", $userid)->count() ==0){
                    \DB::table("pastors")->insert(["user_id"=>$userid, "status"=>0]);
                }
            }
            return back()->with("success", "Pastors added successfully");
        }

        /*if(\Auth::user()->role == 1){
            if(\DB::table("pastors")->where("user_id", $request->pastor)->count() == 0){
                if(\DB::table("pastors")->insert(["user_id"=>$request->pastor, "status"=>0])){
                    return redirect()->back()->with("success", "Pastor Added Successfully");
                }else{
                    return redirect()->back()->with("error", "Unable to save pastor");
                }
            }else{
                return redirect()->back()->with("error", "User already added as a pastor");
            }
        }else{
            return redirect()->to("/home")->with("error", "Access denied!");
        }*/
    }
    public function removepastor(Request $request){
            if(\DB::table("pastors")->where("id", $request->id)->delete()){
                return redirect()->back()->with("success", "Successfully removed pastor!");
            }else{
                return redirect()->back()->with("error", "Unable to remove pastor");
            }

    }

    public function seniorpastor(Request $request){
            \DB::table("pastors")->where("status", 1)->update(["status"=>0]);
            if(\DB::table("pastors")->where("id", $request->id)->update(["status"=>1])){
                return redirect()->back()->with("success", "Senior Pastor update!");
            }else{
                return redirect()->back()->with("error", "Unable to updatepastor");
            }
    }
}
