<?php

namespace App\Http\Controllers\Dashboard\People;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

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

        $people = \DB::table("people")->where("user_group", "=", $group)
            ->select("people.id", "people.banner", "people.name",
                "people.description", "users.firstname", "users.lastname", "people.user_group",
                \DB::raw("(SELECT COUNT(*) FROM people_members WHERE people_members.people_id = people.id) as member_count"))
            ->leftJoin("users", "users.id", "=", "people.leader")
            ->orderBy("people.name", "ASC")->paginate(15);

        $totalGroups = \DB::table("people")->count();
        $totalMembers = \DB::table("people_members")->count();
        $communitiesCount = \DB::table("people")->where("user_group", 1)->count();
        $departmentsCount = \DB::table("people")->where("user_group", 2)->count();

        return view("dashboard.people.people", [
            "people" => $people,
            "totalGroups" => $totalGroups,
            "totalMembers" => $totalMembers,
            "communitiesCount" => $communitiesCount,
            "departmentsCount" => $departmentsCount,
        ]);
    }

    public function users(Request $request){
        $start = $request->limit == null?"0":intval($request->limit);
        if($request->search == null){
            return json_encode(\DB::table("users")->select("id", "firstname", "lastname", "email", "phone")->orderBy("firstname", "ASC")->skip($start)->take(10)->get());
        }else{
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            return json_encode(\DB::table("users")->select("id", "firstname", "lastname", "email", "phone")->where("firstname", "LIKE", "%".$search."%")->orWhere("lastname", "LIKE", "%".$search."%")
            ->orWhere("email", "LIKE", "%".$search."%")->orderBy("users.id", "DESC")->skip($start)->take(10)->get());
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

    public function getMembersDataTable(Request $request)
    {
        $query = \DB::table("people_members")
            ->where("people_id", $request->id)
            ->select(
                "people_members.id",
                "people_members.status",
                "users.firstname",
                "users.lastname",
                "users.email",
                "users.phone",
                "profiles.name as image"
            )
            ->join("users", "users.id", "people_members.user_id")
            ->leftJoin("profiles", "profiles.user_id", "=", "people_members.user_id")
            ->orderBy('users.firstname', 'ASC');

        return DataTables::of($query)
            ->addColumn('member_name', function ($item) {
                $img = $item->image ? asset('profile_images/' . $item->image) : asset('profile_images/default.jpg');
                return '<div class="d-flex align-items-center">
                    <img src="' . $img . '" class="rounded-circle mr-2" style="width:32px;height:32px;object-fit:cover;">
                    <span class="font-weight-bold">' . e($item->firstname) . ' ' . e($item->lastname) . '</span>
                </div>';
            })
            ->addColumn('status_badge', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }
                return '<span class="badge badge-warning">Inactive</span>';
            })
            ->addColumn('action', function ($item) {
                $actions = '';
                if ($item->status == 0) {
                    $actions .= '<a href="#" onclick="return postAction(\'' . url('dashboard/people/member/activate/' . $item->id) . '\')" class="btn btn-success btn-sm" title="Activate"><i class="fas fa-check"></i></a> ';
                } else {
                    $actions .= '<a href="#" onclick="return postAction(\'' . url('dashboard/people/member/deactivate/' . $item->id) . '\')" class="btn btn-outline-warning btn-sm" title="Deactivate"><i class="fas fa-ban"></i></a> ';
                }
                $actions .= '<a href="#" onclick="return postAction(\'' . url('dashboard/people/member/remove/' . $item->id) . '\', \'Remove this member?\')" class="btn btn-danger btn-sm" title="Remove"><i class="fas fa-trash"></i></a>';
                return '<div class="text-right">' . $actions . '</div>';
            })
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search != '') {
                    $query->where(function ($q) use ($request) {
                        $q->where('users.firstname', 'like', '%' . $request->search . '%')
                          ->orWhere('users.lastname', 'like', '%' . $request->search . '%')
                          ->orWhere('users.email', 'like', '%' . $request->search . '%');
                    });
                }
            })
            ->escapeColumns([])
            ->make(true);
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

        $label = $request->people == 1 ? 'Community' : 'Department';

        if(!empty($request->photo)){
            $imageName = time() . '.' . $request->photo->getClientOriginalExtension();
            if(request()->photo->move(public_path('peoples'), $imageName)){
                if($request->id > 0){
                    $photo = \DB::table('people')->where('id', $request->id)->first();
                    if($photo->banner != ""){
                        if(file_exists(public_path()."/peoples/".$photo->banner)){
                            unlink(public_path()."/peoples/".$photo->banner);
                        }
                    }
                    if(!\DB::table('people')->where("id", $request->id)->update(["name"=>$request->name, "user_group"=>$request->people,
                    "description"=>$this->purify($request->description), "leader"=>$request->user_id, "banner"=>$imageName])){
                        return redirect()->back()->with('error', 'Unable to update!');
                    }
                    return redirect()->to('dashboard/people/members/'.$request->id)->with("success", "$label successfully updated");
                }else{
                    $newId = \DB::table('people')->insertGetId(["name"=>$request->name, "user_group"=>$request->people,
                    "description"=>$this->purify($request->description), "leader"=>$request->user_id, "banner"=>$imageName]);
                    if(!$newId){
                        return redirect()->back()->with('error', 'Unable to save!');
                    }
                    return redirect()->to('dashboard/people/members/'.$newId)->with("success", "$label successfully created. You can now add members.");
                }
            }else{
                return redirect()->back()->with('error', 'Error saving user group!');
            }
        }else{
            if($request->id > 0){
                if(!\DB::table('people')->where("id", $request->id)->update(["name"=>$request->name, "user_group"=>$request->people,
                "description"=>$this->purify($request->description), "leader"=>$request->user_id])){
                    return redirect()->back()->with('error', 'Unable to update!');
                }
                return redirect()->to('dashboard/people/members/'.$request->id)->with("success", "$label successfully updated");
            }else{
                $newId = \DB::table('people')->insertGetId(["name"=>$request->name, "user_group"=>$request->people,
                "description"=>$this->purify($request->description), "leader"=>$request->user_id]);
                if(!$newId){
                    return redirect()->back()->with('error', 'Unable to save!');
                }
                return redirect()->to('dashboard/people/members/'.$newId)->with("success", "$label successfully created. You can now add members.");
            }
        }
    }
}
