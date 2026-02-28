<?php

namespace App\Http\Controllers\Dashboard\Spiritual;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Prayer;
use Illuminate\Http\Request;

class PrayersController extends DashboardController
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $prayers = \DB::table('prayers')->select('prayers.id', 'prayers.status', 'prayers.title', 'prayers.description',
            'users.firstname', 'users.lastname', 'prayers.created_at')->join('users', 'users.id', '=', 'prayers.user_id')->paginate(15);
            return view('dashboard.spiritual.prayers')->with('prayers', $prayers);
    }

    public function prayers(){
        $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
        $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
        if($perm1 != null || $perm2 != null){
            if($perm1->sermons >0 || $perm2->sermons > 0){
                $prayers = \DB::table('prayers')->select('prayers.id', 'prayers.status', 'prayers.title', 'prayers.description',
                'users.firstname', 'users.lastname', 'prayers.created_at')->join('users', 'users.id', '=', 'prayers.user_id')->paginate(15);
                return view('user.permissions.prayers')->with('prayers', $prayers);
            }else{
                return redirect()->back()->with("error", "Access denied");
            }
        }else{
            return redirect()->to("/home");
        }
    }

    public function addprayer(Request $request){
        request()->validate([
            "title"=>'required|string|min:4',
            "description" =>'required|string|min:4',
            "status"=>'required|numeric|max:1'
        ]);

        if($request->id > 0){
            //update
            $update = Prayer::where('id', $request->id)->update(["title"=>$request->title, "description"=>$request->description, "status"=>$request->status]);
            if($update){
                return redirect()->back()->with("success", "prayer request successfully updated");
            }else{
                return redirect()->back()->with("error", "unable to update");
            }
        }else{
            //insert
            $prayer = new Prayer();
            $prayer->title = $request->title;
            $prayer->description = $request->description;
            $prayer->status = $request->status;
            $prayer->user_id = \Auth::user()->id;
            if($prayer->save()){
                return redirect()->back()->with("success", "prayer request successfully sent");
            }else{
                return redirect()->back()->with("error", "unable to send message");
            }
        }
    }

    public function getprayer(Request $request){
        return json_encode(Prayer::find($request->id));
    }

    public function deleteprayer(Request $request){
        $prayer = Prayer::where("id", $request->id)->where("user_id", \Auth::user()->id)->first();
        if($prayer != null){
            if($prayer->delete()){
                return redirect()->back()->with("success", "Deleted prayer request successfully!");
            }else{
                return redirect()->back()->with("error", "Unable to remove prayer!");
            }
        }else if(\Auth::user()->hasRole('Super Admin') || \Auth::user()->can('Manage Prayers')){
            $prayer = Prayer::where("id", $request->id)->first();
            if($prayer && $prayer->delete()){
                return redirect()->back()->with("success", "Deleted prayer request successfully!");
            }else{
                return redirect()->back()->with("error", "Unable to remove prayer!");
            }
        }else{
            return redirect()->back()->with("error", "Access denied!");
        }
    }
}
