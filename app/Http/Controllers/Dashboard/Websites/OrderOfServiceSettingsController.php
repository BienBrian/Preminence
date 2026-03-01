<?php

namespace App\Http\Controllers\Dashboard\Websites;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;

class OrderOfServiceSettingsController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Website Settings']);
    }

    public function orderofservice(){

        $services = \DB::table('orderofservice')->paginate(15);
        return view('dashboard.website.orderofservice', @compact("services"));
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
            $verse = \DB::table('weeklyverse')->first();
            return view('dashboard.website.weeklyverse', @compact("verse"));

    }

    public function addverse(Request $request){
        request()->validate([
            'verse'=>'required|string',
            'description'=>'required|string',
            'version'=>'required|string',
        ]);

        if($request->id > 0){
            //update
            if(\DB::table('weeklyverse')->where("id", $request->id)->update(["verse"=>$request->verse, "description"=>$this->purify($request->description),
            "version"=>$request->version])){
                return redirect()->back()->with("success", "Verse succesfully updated!");
            }else{
                return redirect()->back()->with("error", "Unable to save!");
            }
        }else{
            //insert
            if(\DB::table('weeklyverse')->insert(["verse"=>$request->verse, "description"=>$this->purify($request->description),
            "version"=>$request->version])){
                return redirect()->back()->with("success", "Verse succesfully added!");
            }else{
                return redirect()->back()->with("error", "Unable to save!");
            }
        }
    }
}
