<?php

namespace App\Http\Controllers\Dashboard\EventsAndSeminars;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;

class SeminarController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Events & Notices']);
    }
    public function index()
    {
        $seminars = \DB::table('seminars')->orderBy('id', 'desc')->paginate(15);
        return view('dashboard.events_and_notices.seminars')->with('seminars', $seminars);
    }

    public function addseminar(Request $request){
        request()->validate([
            'title'=>'required|string|min:4',
            'description'=>'required|string|min:4',
            'startdate'=>'required|string|min:4',
            'enddate'=>'required|string|min:4',
            'banner'=>'image|mimes:jpeg,png,jpg|max:1024',
            'location'=>'required|string|min:4',
            'budget'=>'required|numeric',
            'theme'=>'required|string|min:4',
            'entry'=>'required|numeric'
        ]);
        $event = \DB::table('seminars')->where('id', $request->id)->first();
        if($request->id > 0){
            //update
            $banner = $event->banner;
            if($request->has('banner')){
                if(file_exists(public_path()."/seminar/".$event->banner)){
                    \File::delete(public_path()."/seminar/".$event->banner);
                }
                $banner = $request->banner->getClientOriginalName();
                if(\DB::table('seminars')->where('banner', $banner)->count() > 0){
                    $banner = time()."_".$request->banner->getClientOriginalName();
                }
                $request->banner->move(public_path('seminar'), $banner);
            }

            $startdate = \Carbon\Carbon::parse($request->startdate)->format('Y-m-d H:i:s');
            $enddate = \Carbon\Carbon::parse($request->enddate)->format('Y-m-d H:i:s');

            $update = \DB::table('seminars')->where('id', '=', $request->id)->update(array('title' => $request->title, 'banner'=>$banner, 'description' => $this->purify($request->description),
                "start"=>$startdate, "location"=>$request->location, "end"=>$enddate, "cost"=>$request->budget, "entry"=>$request->entry, "theme"=>$request->theme));
            if($update){
                return redirect()->back()->with('success', 'Seminar has been updated successfully');
            }else{
                return redirect()->back()->with('error', 'Unable to update seminar');
            }
        }else{
            //insert
            $banner = "";
            if($request->has('banner')){
                $banner = $request->banner->getClientOriginalName();
                if(\DB::table('seminars')->where('banner', $banner)->count() > 0){
                    $banner = time()."_".$request->banner->getClientOriginalName();
                }
                $request->banner->move(public_path('seminar'), $banner);
            }

            $startdate = \Carbon\Carbon::parse($request->startdate)->format('Y-m-d H:i:s');
            $enddate = \Carbon\Carbon::parse($request->enddate)->format('Y-m-d H:i:s');

            $save = \DB::table('seminars')->insert(array('title' => $request->title, 'banner'=>$banner, 'description' => $this->purify($request->description),
            "start"=>$startdate, "location"=>$request->location, "end"=>$enddate, "cost"=>$request->budget, "entry"=>$request->entry, "theme"=>$request->theme));
            if($save){
                return redirect()->back()->with('success', 'Your Seminar has been saved successfully');
            }else{
                return redirect()->back()->with('error', 'Unable to save seminar');
            }
        }
    }

    public function getSeminar(Request $request){
        return json_encode(\DB::table('seminars')->where('id', $request->id)->first());
    }
}
