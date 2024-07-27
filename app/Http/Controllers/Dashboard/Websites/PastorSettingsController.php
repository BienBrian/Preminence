<?php

namespace App\Http\Controllers\Dashboard\Websites;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\HomePage;
use Illuminate\Http\Request;

class PastorSettingsController extends DashboardController
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
    /*
    public function index()
    {
        $homepage = HomePage::first();
        return view('dashboard.website.homepage', @compact('homepage'));
    }*/

    public function getMessage()
    {
        $message = \DB::table('pastorsmessage')->first();
        return view('dashboard.website.pastorsmessage', @compact('message', $message));
    }

    public function pastorsmessage()
    {
        $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
        $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
        if ($perm1 != null || $perm2 != null) {
            if ($perm1->websites > 0 || $perm2->websites > 0) {
                $message = \DB::table('pastorsmessage')->first();
                return view('user.permissions.pastorsmessage')->with('message', $message);
            } else {
                return redirect()->back()->with("error", "Access denied");
            }
        } else {
            return redirect()->to("/home");
        }
    }
    public function addPastorMessage(Request $request)
    {
        request()->validate([
            'title' => 'required|min:4',
            'description' => 'required|min:4',
        ]);
        if ($request->id > 0) {
            //update
            if (
                !\DB::table('pastorsmessage')->where("id", $request->id)->update([
                    "title" => $request->title,
                    "description" => $request->description
                ])
            ) {
                return redirect()->back()->with('error', 'Unable to update!');
            }
        } else {
            //insert
            if (!\DB::table('pastorsmessage')->insert(["title" => $request->title, "description" => $request->description, "image" => ""])) {
                return redirect()->back()->with('error', 'Unable to save!');
            }
        }
        return redirect()->back()->with("success", "Information updated successfully!");
    }

    public function uploadimage(Request $request)
    {
        $data = $request->image;
        $message = \DB::table('pastorsmessage')->first();

        list($type, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);

        $data = base64_decode($data);
        $image_name = time() . '.png';

        $path = public_path() . "/website/pastors/" . $image_name;
        if ($message->image != "") {
            if (file_exists(public_path() . "/website/pastors/" . $message->image)) {
                unlink(public_path() . "/website/pastors/" . $message->image);
            }
        }
        file_put_contents($path, $data);
        if ($message != null) {
            $update = \DB::table('pastorsmessage')->where("id", $message->id)->update(["image" => $image_name]);
            if (!$update) {
                return response()->json(['success' => '<p class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Error Saving image</p>']);
            }
        } else {
            $save = \DB::table('pastorsmessage')->insert(["title" => "", "description" => "", "image" => $image_name]);
            if (!$save) {
                return response()->json(['success' => '<p class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Error Saving image</p>']);
            }
        }
        return response()->json(['success' => '<p class="text-center text-success"><i class="fas fa-check"></i> Successfully uploaded</p>']);
    }
}
