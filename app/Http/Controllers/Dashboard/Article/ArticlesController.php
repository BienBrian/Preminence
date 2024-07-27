<?php

namespace App\Http\Controllers\Dashboard\Article;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;

class ArticlesController extends DashboardController
{
    private $user;
    public function __construct(){
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {$articles = \DB::table('articles')->join("users", "users.id","=","articles.user_id")->
            select("articles.id", "articles.title", "articles.description", "articles.article_date",
            "articles.banner", "users.firstname", "users.lastname", "articles.status")->orderBy("articles.id", "DESC")->paginate(15);
            return view('dashboard.articles.articles')->with('articles', $articles);
    }

    public function articles(){
        $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
        $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
        if($perm1 != null || $perm2 != null){
            if($perm1->articles >0 || $perm2->articles > 0){
                $articles = \DB::table('articles')->join("users", "users.id","=","articles.user_id")->
                select("articles.id", "articles.title", "articles.description", "articles.article_date",
                "articles.banner", "users.firstname", "users.lastname", "articles.status")->paginate(15);
                return view('user.permissions.articles')->with('articles', $articles);
            }else{
                return redirect()->back()->with("error", "Access denied");
            }
        }else{
            return redirect()->to("/home");
        }
    }

    public function newarticle(){
        return view("dashboard.articles.article");
    }

    public function editarticle(Request $request){
        $comm = \DB::table('communities')->where('id', $request->id)->first();
        return view("admin.editcommunity")->with("community", $comm);
    }

    public function addarticle(Request $request){
        request()->validate([
            "title"=>'required|string|min:4',
            "description"=>'required|string|min:4',
            'banner' => 'mimes:jpeg,png,jpg|max:1048',
        ]);
        $date = \Carbon\Carbon::now()->format("Y-m-d H:i:s");
        $imageName="";
        if(!empty($request->banner)){
            $imageName = time().$request->banner->getClientOriginalName();
            if(request()->banner->move(public_path('article'), $imageName)){
                if($request->id > 0){
                    $photo = \DB::table('articles')->where('id', $request->id)->first();
                    if(file_exists(public_path()."/article/".$photo->banner)){
                        unlink(public_path()."/article/".$photo->banner);
                    }
                    //update
                    if(!\DB::table('articles')->where("id", $request->id)->update(["title"=>$request->title,
                    "description"=>$request->description, "user_id"=>\Auth::user()->id, "banner"=>$imageName,
                    "article_date"=>$date, "status"=>0])){
                        return redirect()->back()->with('error', 'Unable to update!');
                    }else{
                        return redirect()->back()->with('success', 'Update successful');
                    }
                }else{
                    //insert
                    if(!\DB::table('articles')->insert(["title"=>$request->title,
                    "description"=>$request->description, "user_id"=>\Auth::user()->id, "banner"=>$imageName,
                    "article_date"=>$date, "status"=>0])){
                        return redirect()->back()->with('error', 'Unable to save!');
                    }
                }
            }else{
                return redirect()->back()->with('error', 'Error saving communities!');
            }
        }else{
            if($request->id > 0){
                //update
                if(!\DB::table('articles')->where("id", $request->id)->update(["title"=>$request->title,
                "description"=>$request->description, "user_id"=>\Auth::user()->id, "banner"=>"",
                "article_date"=>$date, "status"=>0])){
                    return redirect()->back()->with('error', 'Unable to update!');
                }else{
                    return redirect()->back()->with('success', 'Update successful');
                }
            }else{
                //insert
                if(!\DB::table('articles')->insert(["title"=>$request->title,
                "description"=>$request->description, "user_id"=>\Auth::user()->id, "banner"=>$imageName,
                "article_date"=>$date, "status"=>0])){
                    return redirect()->back()->with('error', 'Unable to save!');
                }
            }
        }

        return redirect()->to('dashboard/articles')->with('success','Information Successfully Saved');
    }

    public function removearticle(Request $request){
        $article = \DB::table('articles')->where('id', $request->id)->first();
        if($article->banner != ""){
            if(file_exists(public_path()."/article/".$article->banner)){
                if(unlink(public_path()."/article/".$article->banner)){
                    \DB::table('articles')->where('id', $request->id)->delete();
                    return redirect()->back()->with('success', 'Article removed successfully!');
                }else{
                    return redirect()->back()->with('error', 'Unable to remove article!');
                }
            }
        }else{
            if(\DB::table('articles')->where('id', $request->id)->delete()){
                return redirect()->back()->with('success', 'Article removed successfully!');
            }else{
                return redirect()->back()->with('error', 'Invalid article id');
            }
        }
    }

    public function activate(Request $request){
        if(\DB::table("articles")->where("id", $request->id)->update(["status"=>1])){
            return redirect()->back()->with("success", "Article activated and is available for viewing");
        }else{
            return redirect()->back()->with("error", "Unable to activate article");
        }
    }

    public function deactivate(Request $request){
        if(\DB::table("articles")->where("id", $request->id)->update(["status"=>0])){
            return redirect()->back()->with("success", "Article deactivated and is not available for viewing");
        }else{
            return redirect()->back()->with("error", "Unable to activate article");
        }
    }
}
