<?php

namespace App\Http\Controllers\Dashboard\Websites;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\HomePage;
use Illuminate\Http\Request;

class HomePageSettingsController extends DashboardController
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
            $homepage = HomePage::first();
            return view('dashboard.website.homepage', compact('homepage'));
    }

    public function homepage(){
        $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
        $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
        if($perm1 != null || $perm2 != null){
            if($perm1->websites >0 || $perm2->websites > 0){
                $homepage = \DB::table('homepage')->first();
                return view('user.permissions.homepage')->with('homepage', $homepage);
            }else{
                return redirect()->back()->with("error", "Access denied");
            }
        }else{
            return redirect()->to("/home");
        }
    }

    public function addHomePage(Request $request){
        request()->validate([
            'title' => 'required|min:4',
            'description' => 'required|min:4',
        ]);
        if($request->id > 0){
            //update
            if(!HomePage::where("id", $request->id)->update(["title"=>$request->title,
                "description"=>$request->description])){
                return redirect()->back()->with('error', 'Unable to update!');
            }
        }else{
            //insert
            if(!HomePage::insert(["title"=>$request->title, "description"=>$request->description, "image"=>""])){
                return redirect()->back()->with('error', 'Unable to save!');
            }
        }
        return redirect()->back()->with("success", "Information updated successfully!");
    }
    public function uploadHomePage(Request $request){
        request()->validate([
            'homepage' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if(!empty($request->homepage)){
            $imageName = 'home.jpg';
            if(request()->homepage->move(public_path('website/homepage'), $imageName)){
                if($request->id > 0){
                    //update
                    HomePage::where("id", $request->id)->update(["image"=>$imageName]);
                }else{
                    //insert
                    if(!HomePage::insert(["title"=>"default header", "description"=>"Default Description", "image"=>$imageName])){
                        return redirect()->back()->with('error', 'Unable to save!');
                    }
                }
            }else{
                return back()->with('error', 'Unable to save image');
            }
        }else{
            return back()->with("error", "No image available for update");
        }

        return back()->with('success','You have successfully upload image.')->with('image',$imageName);
    }
}
