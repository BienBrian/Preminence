<?php

namespace App\Http\Controllers\Dashboard\Profile;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        $profile_images = \DB::table('profiles')->get();
        foreach($profile_images as $profile){
            $user = User::find($profile->user_id);
            if($user != null){
                $user->image = $profile->name;
            }else{
                if (file_exists(public_path('/profile_images/' . $profile->name))) {
                    unlink(public_path() . '/profile_images/' . $profile->name);
                }
                \DB::table('profiles')->where('id', $profile->id)->delete();
            }
        }
        $user = User::with(['roles'])->findOrFail(\Auth::user()->id);
        return view('dashboard.profile.profile', ['user' => $user]);
    }

    public function editProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:1',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $user = User::findOrFail($request->id);
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        if ($user->save()) {
            return response()->json(['success' => 'User updated successfully!']);
        } else {
            return response()->json(['error' => 'Unable to update user'], 401);
        }
    }
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|min:8|string|same:confirm_password',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        if (Hash::check($request->current_password, auth()->user()->password)) {
            //save
            $user = User::find(Auth::user()->id);
            $user->password = Hash::make($request->new_password);
            if ($user->save()) {
                return response()->json(['success' => 'Password update successfully!']);
            } else {
                return response()->json(['error' => 'Unable to update password!'], 401);
            }
        } else {
            return response()->json(['error' => 'Current Password is incorrect!'], 401);
        }
    }
    public function uploadProfilePicture(Request $request)
    {
        $folderPath = public_path('profile_images/');
        $user = User::findOrFail(Auth::user()->id);
        if ($user->image != null) {
            if (file_exists(public_path('/profile_images/' . $user->image))) {
                unlink(public_path() . '/profile_images/' . $user->image);
            }
        }
        $data = $request->image;
        list($type, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);

        $data = base64_decode($data);
        $image_name = Auth::user()->id . '.png';
        $path = $folderPath . $image_name;

        file_put_contents($path, $data);
        $user->image = $image_name;
        $user->save();
        $profile = \DB::table('profiles')->where('user_id', $user->id)->first();
        if($profile != null){
            \DB::table('profiles')->where('id', $profile->id)->update(["name"=>$image_name]);
        }else{
            \DB::table('profiles')->insert(["name"=>$image_name, "user_id"=>$user->id]);
        }

        return response()->json(['success' => 'Image Uploaded Successfully']);
    }
}
