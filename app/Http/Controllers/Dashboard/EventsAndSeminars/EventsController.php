<?php

namespace App\Http\Controllers\Dashboard\EventsAndSeminars;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;

class EventsController extends DashboardController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Events & Notices']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $events = \DB::table('events')->orderBy('id', 'desc')->paginate(15);
        return view('dashboard.events_and_notices.events')->with('events', $events);
    }

    public function addevent(Request $request){
        request()->validate([
            'title'=>'required|string|min:4',
            'description'=>'required|string|min:4',
            'date'=>'required|string|min:4',
            'banner'=>'image|mimes:jpeg,png,jpg|max:1024',
            'location'=>'required|string|min:4',
            'budget'=>'required|numeric',
            'theme'=>'required|string|min:4',
            'entry'=>'required|numeric'
        ]);
        $event = \DB::table('events')->where('id', $request->id)->first();
        if($request->id > 0){
            //update
            $banner = $event->banner;
            if($request->hasFile('banner')){
                if(file_exists(public_path()."/event/".$event->banner)){
                    \File::delete(public_path()."/event/".$event->banner);
                }
                $extension = strtolower($request->banner->getClientOriginalExtension());
                $banner = time() . '_' . \Str::random(8) . '.' . $extension;
                $request->banner->move(public_path('event'), $banner);
            }

            $date = \Carbon\Carbon::parse($request->date)->format('Y-m-d');
            $time = \Carbon\Carbon::parse($request->date)->format('h:i A');

            $update = \DB::table('events')->where('id', '=', $request->id)->update(array('title' => $request->title, 'banner'=>$banner, 'description' => $this->purify($request->description),
                "time"=>$time, "location"=>$request->location, "eventdate"=>$date, "cost"=>$request->budget, "entry"=>$request->entry, "theme"=>$request->theme, "eventtype"=>$request->eventtype));
            if($update){
                return redirect()->back()->with('success', 'Your event has been updated successfully');
            }else{
                return redirect()->back()->with('error', 'Unable to save event');
            }
        }else{
            //insert
            $banner = "";
            if($request->hasFile('banner')){
                $extension = strtolower($request->banner->getClientOriginalExtension());
                $banner = time() . '_' . \Str::random(8) . '.' . $extension;
                $request->banner->move(public_path('event'), $banner);
            }

            $date = \Carbon\Carbon::parse($request->date)->format('Y-m-d');
            $time = \Carbon\Carbon::parse($request->date)->format('h:i A');

            $update = \DB::table('events')->insert(array('title' => $request->title, 'banner'=>$banner, 'description' => $this->purify($request->description),
            "time"=>$time, "location"=>$request->location, "eventdate"=>$date, "cost"=>$request->budget, "entry"=>$request->entry, "theme"=>$request->theme, "eventtype"=>$request->eventtype));
            if($update){
                return redirect()->back()->with('success', 'Your event has been saved successfully');
            }else{
                return redirect()->back()->with('error', 'Unable to save event');
            }
        }
    }

    public function getEvent(Request $request){
        return json_encode(\DB::table('events')->where('id', $request->id)->first());
    }

    public function removeEvent(Request $request){
        $event = \DB::table("events")->where("id", $request->id)->first();
        if(file_exists(public_path()."/event/".$event->banner) && $event->banner != ""){
            if(unlink(public_path()."/event/".$event->banner)){
                if(\DB::table("events")->where("id", $request->id)->delete()){
                    return redirect()->back()->with('success', 'Event removed successfully!');
                }else{
                    return redirect()->back()->with('error', 'Unable to remove event!');
                }
            }else{
                return redirect()->back()->with('error', 'Unable to remove event image!');
            }
        }else{
            if(\DB::table("events")->where("id", $request->id)->delete()){
                return redirect()->back()->with('success', 'Event removed successfully!');
            }else{
                return redirect()->back()->with('error', 'Unable to remove event!');
            }
        }
    }
}
