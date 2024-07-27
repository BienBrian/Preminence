<?php

namespace App\Http\Controllers\Dashboard\People;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;

class PeopleController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View People']);
    }

    public function newpeople(Request $request){
        $people = null;
        if(isset($request->category) && isset($request->id)){
            $people = \DB::table('people')->where("people.id", $request->id)->where("user_group", $request->category)
            ->leftJoin("users","users.id", "=", "people.leader")->select("people.id", "people.leader", "users.firstname", "users.lastname",
            "people.name", "people.description", "people.banner", "people.user_group")->first();
            //return json_encode($people);
        }
        return view("dashboard.people.people-add", ["people"=>$people]);
    }

    public function people(){
        $group = 1;
        if(request()->segment(3) == "departments"){
            $group = 2;
        }
        request()->segment(3);

        $people = \DB::table("people")->where("user_group", "=", $group)->select("people.id", "people.banner", "people.name",
        "people.description", "users.firstname", "users.lastname", "people.user_group")->
        leftJoin("users", "users.id", "=", "people.leader")->orderBy("people.name", "ASC")->paginate(15);

        return view("dashboard.people.people", ["people"=>$people]);
    }

    public function users(Request $request){
        $start = $request->limit == null?"0":intval($request->limit);
        if($request->search == null){
            return json_encode(\DB::table("users")->select("id", "firstname", "lastname", "email", "phone")->orderBy("firstname", "ASC")->skip($start)->take(10)->get());
        }else{
            return json_encode(\DB::table("users")->select("id", "firstname", "lastname", "email", "phone")->where("firstname", "LIKE", "%".$request->search."%")->orWhere("lastname", "LIKE", "%".$request->search."%")
            ->orWhere("email", "LIKE", "%".$request->search."%")->orderBy("users.id", "DESC")->skip($start)->take(10)->get());
        }
    }

    public function members(Request $request){
        $people = \DB::table("people")->where("people.id", $request->id)->leftJoin("users", "users.id", "=", "people.leader")->leftJoin("profiles", "profiles.user_id", "=", "people.leader")
        ->select("people.id", "people.banner", "people.name", "people.description", "people.user_group", "users.firstname", "people.leader", "users.lastname", "profiles.name as image", "users.email", "users.phone")->first();
        if($people == null){
            return back()->with("error", "Invalid request");
        }
        $members = \DB::table("people_members")->where("people_id", $request->id)->select("people_members.id", "people_members.status", "users.firstname", "users.lastname", "users.email", "users.phone", "profiles.name")->
        join("users", "users.id", "people_members.user_id")->leftJoin("profiles", "profiles.user_id", "=", "people_members.user_id")->orderBy('firstname', 'ASC')->paginate(15);
        return view("dashboard.people.people-members", ["people"=>$people, "members"=>$members]);
    }

    public function addmembers(Request $request){
        foreach($request['members'] as $member){
            if(\DB::table("people_members")->where("user_id", $member)->where("people_id", $request->group_id)->count() == 0){
                \DB::table('people_members')->insert(["user_id"=>$member, "people_id"=>$request->group_id, "status"=>1]);
            }
        }
        return back()->with("success", "Members successfully added");
    }

    public function activate(Request $request){
        if(\DB::table('people_members')->where("id", "=", $request->id)->update(["status"=>1])){
            return back()->with("success", "Member Activated!");
        }else{
            return back()->with("error", "unable to activate");
        }
    }

    public function deactivate(Request $request){
        if(\DB::table('people_members')->where("id", "=", $request->id)->update(["status"=>0])){
            return back()->with("success", "Member Deactivated!");
        }else{
            return back()->with("error", "unable to deactivate");
        }
    }

    public function remove(Request $request){
        if(\DB::table('people_members')->where("id", "=", $request->id)->delete()){
            return back()->with("success", "Member removed!");
        }else{
            return back()->with("error", "unable to remove member");
        }
    }

    public function addpeoplegroups(Request $request){
        request()->validate([
            "name"=>'required|string|min:2',
            "description"=>'required|string|min:4',
            'photo' => 'image|mimes:jpeg,png,jpg|max:2048',
            'user_id' => 'required|integer',
        ]);
        if($request->user_id == 0){
            return back()->with("error", "Please select a leader for your group");
        }

        if(!empty($request->photo)){
            $imageName = time().$request->photo->getClientOriginalName();
            if(request()->photo->move(public_path('peoples'), $imageName)){
                if($request->id > 0){
                    $photo = \DB::table('people')->where('id', $request->id)->first();
                    if($photo->banner != ""){
                        if(file_exists(public_path()."/peoples/".$photo->banner)){
                            unlink(public_path()."/peoples/".$photo->banner);
                        }
                    }
                    //update
                    if(!\DB::table('people')->where("id", $request->id)->update(["name"=>$request->name, "user_group"=>$request->people,
                    "description"=>$request->description, "leader"=>$request->user_id, "banner"=>$imageName])){
                        return redirect()->back()->with('error', 'Unable to update!');
                    }else{
                        if($request->people == 1){
                            return redirect()->to('dashboard/people/communities')->with("success", "Community successfully updated");
                        }else{
                            return redirect()->to('dashboard/people/departments')->with("success", "Department successfully updated");
                        }
                    }
                }else{
                    //insert
                    if(!\DB::table('people')->insert(["name"=>$request->name, "user_group"=>$request->people,
                    "description"=>$request->description, "leader"=>$request->user_id, "banner"=>$imageName])){
                        return redirect()->back()->with('error', 'Unable to save!');
                    }else{
                        if($request->people == 1){
                            return redirect()->to('dashboard/people/communities')->with("success", "Community successfully saved");
                        }else{
                            return redirect()->to('dashboard/people/departments')->with("success", "Department successfully saved");
                        }
                    }
                }
            }else{
                return redirect()->back()->with('error', 'Error saving user group!');
            }
        }else{
            if($request->id > 0){
                //update
                if(!\DB::table('people')->where("id", $request->id)->update(["name"=>$request->name, "user_group"=>$request->people,
                "description"=>$request->description, "leader"=>$request->user_id])){
                    return redirect()->back()->with('error', 'Unable to update!');
                }else{
                    if($request->people == 1){
                        return redirect()->to('dashboard/people/communities')->with("success", "Community successfully updated");
                    }else{
                        return redirect()->to('dashboard/people/departments')->with("success", "Department successfully updated");
                    }
                }
            }else{
                //insert
                if(!\DB::table('people')->insert(["name"=>$request->name, "user_group"=>$request->people,
                "description"=>$request->description, "leader"=>$request->user_id])){
                    return redirect()->back()->with('error', 'Unable to save!');
                }else{
                    if($request->people == 1){
                        return redirect()->to('dashboard/people/communities')->with("success", "Community successfully added");
                    }else{
                        return redirect()->to('dashboard/people/departments')->with("success", "Department successfully added");
                    }
                }

            }
        }

    }
}
