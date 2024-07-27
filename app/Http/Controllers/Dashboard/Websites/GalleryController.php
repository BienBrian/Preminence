<?php

namespace App\Http\Controllers\Dashboard\Websites;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends DashboardController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
        $galleries = Gallery::select("galleries.*", "profile_categories.name")->leftJoin("profile_categories", "profile_categories.id", "=", "galleries.category")->paginate(15);
        $categories = \DB::table("profile_categories")->get();
        return view('dashboard.website.gallery', @compact('galleries', "categories"));

    }

    public function categories(){
        $categories = \DB::table("profile_categories")->paginate(15);
        return view("dashboard.website.gallery_categories", @compact("categories"));
    }

    public function viewcategory(Request $request){
        $category = \DB::table("profile_categories")->where("id", $request->id)->first();
        if($category == null){
            return redirect()->to("dashboard/website/gallery/categories")->with("error", "Invalid Category ID");
        }
        return view("dashboard.website.gallery_view", @compact("category"));
    }

    public function addcategory(Request $request){
        $request->validate([
            "name"=>"required",
        ]);
        if($request->id == 0){
            if(\DB::table('profile_categories')->insert(["name"=>$request->name, "description"=>$request->description])){
                return redirect()->back()->with("success", "Category Added Successfully!");
            }else{
                return redirect()->back()->with("error", "Unable to add category");
            }
        }
    }

    public function deletecategory(Request $request){
        if(Gallery::where("category", $request->id)->count() >0){
            return redirect()->back()->with("error", "Category already in use");
        }else{
            if(\DB::table('profile_categories')->where("id", $request->id)->delete()){
                return redirect()->to("dashboard/website/gallery/categories")->with("success", "Category removed successfully!");
            }else{
                return redirect()->back()->with("error", "Unable to remove category");
            }
        }
    }

    public function gallery(){
        $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
        $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
        if($perm1 != null || $perm2 != null){
            if($perm1->websites >0 || $perm2->websites > 0){
                $gallery = \DB::table('gallery')->paginate(15);
                return view('user.permissions.gallery')->with('gallery', $gallery);
            }else{
                return redirect()->back()->with("error", "Access denied");
            }
        }else{
            return redirect()->to("/home");
        }
    }

    public function uploadgallery(Request $request){
        request()->validate([
            'gallery' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imageName = $request->gallery->getClientOriginalName();
        if(Gallery::where('image', '=', $imageName)->count() > 0){
            $imageName = time().".".$request->gallery->getClientOriginalExtension();
        }
        if(request()->gallery->move(public_path('website/gallery'), $imageName)){
            $gallery = new Gallery();
            $gallery->description = $request->description;
            $gallery->image = $imageName;
            $gallery->category = $request->category;
            $gallery->date_uploaded = \Carbon\Carbon::now();
            if(!$gallery->save()){
                return redirect()->back()->with('error', 'Unable to save!');
            }
        }else{
            return back()->with('error', 'Unable to save image');
        }

        return back()->with('success','Gallery Uploaded.');
    }
    public function deletegallery(Request $request){
        $gallery = Gallery::FindorFail($request->id);
        if(file_exists(public_path()."/website/gallery/".$gallery->image)){
            if(unlink(public_path()."/website/gallery/".$gallery->image)){
                $gallery->delete();
                return redirect()->back()->with('success', 'Image removed successfully!');
            }else{
                return redirect()->back()->with('error', 'Unable to remove image!');
            }
        }else{
            return redirect()->back()->with('error', 'Invalid image id');
        }
    }
}
