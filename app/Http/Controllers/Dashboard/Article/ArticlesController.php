<?php

namespace App\Http\Controllers\Dashboard\Article;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;
use App\Traits\CompressesImages;
use Yajra\DataTables\DataTables;

class ArticlesController extends DashboardController
{
    use CompressesImages;

    private $user;
    public function __construct(){
        parent::__construct();
        $this->middleware(['permission:View Articles']);
    }

    public function index()
    {
        $total = \DB::table('articles')->count();
        $published = \DB::table('articles')->where('status', 1)->count();
        $drafts = \DB::table('articles')->where('status', 0)->count();
        $featured = \DB::table('articles')->where('is_featured', 1)->count();
        $categories = \DB::table('article_categories')->orderBy('sort_order')->get();

        return view('dashboard.articles.articles', compact('total', 'published', 'drafts', 'featured', 'categories'));
    }

    public function getArticlesDataTable(Request $request)
    {
        $query = \DB::table('articles')
            ->join('users', 'users.id', '=', 'articles.user_id')
            ->leftJoin('article_categories', 'article_categories.id', '=', 'articles.category_id')
            ->select(
                'articles.id',
                'articles.title',
                'articles.description',
                'articles.article_date',
                'articles.banner',
                'articles.is_featured',
                'articles.reading_time',
                'articles.views',
                'users.firstname',
                'users.lastname',
                'articles.status',
                'article_categories.name as category_name',
                'article_categories.color as category_color'
            )->orderBy('articles.id', 'DESC');

        return DataTables::of($query)
            ->addColumn('preview', function ($item) {
                $img = $item->banner == '' ? asset('website/default.jpg') : asset('article/' . $item->banner);
                $title = \Str::limit($item->title, 50);
                $snippet = \Str::words(strip_tags($item->description), 10, '...');
                $star = $item->is_featured ? '<i class="fas fa-star text-warning ml-1" title="Featured"></i>' : '';
                return '<div class="d-flex align-items-center">
                    <img src="' . $img . '" class="mr-2 rounded" style="width:60px;height:40px;object-fit:cover;">
                    <div><strong>' . e($title) . '</strong>' . $star . '<br><small class="text-muted">' . e($snippet) . '</small></div>
                </div>';
            })
            ->addColumn('author', function ($item) {
                return '<span class="small font-weight-bold">' . e($item->firstname) . ' ' . e($item->lastname) . '</span>';
            })
            ->addColumn('category', function ($item) {
                if ($item->category_name) {
                    return '<span class="badge" style="background-color:' . e($item->category_color) . ';color:#fff;">' . e($item->category_name) . '</span>';
                }
                return '<span class="badge badge-light">Uncategorized</span>';
            })
            ->addColumn('status_badge', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge badge-success">Published</span>';
                }
                return '<span class="badge badge-warning">Draft</span>';
            })
            ->addColumn('stats', function ($item) {
                return '<span class="small"><i class="fas fa-eye text-muted"></i> ' . $item->views . '</span><br>
                    <span class="small"><i class="fas fa-clock text-muted"></i> ' . $item->reading_time . ' min</span>';
            })
            ->addColumn('date', function ($item) {
                return '<span class="small">' . \Carbon\Carbon::parse($item->article_date)->format('d M, Y') . '</span>';
            })
            ->addColumn('action', function ($item) {
                $actions = '<a href="' . url('dashboard/articles/edit/' . $item->id) . '" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i></a> ';
                if ($item->status == 0) {
                    $actions .= '<a href="#" onclick="return postAction(\'' . url('dashboard/articles/activate/' . $item->id) . '\')" class="btn btn-success btn-sm" title="Activate"><i class="fas fa-check"></i></a> ';
                } else {
                    $actions .= '<a href="#" onclick="return postAction(\'' . url('dashboard/articles/deactivate/' . $item->id) . '\')" class="btn btn-warning btn-sm" title="Deactivate"><i class="fas fa-ban"></i></a> ';
                }
                $actions .= '<a href="#" onclick="return postAction(\'' . url('dashboard/articles/remove/' . $item->id) . '\', \'Are you sure you want to delete this article?\')" class="btn btn-danger btn-sm btn-delete-article" title="Delete"><i class="fas fa-trash"></i></a>';
                return '<div class="text-right">' . $actions . '</div>';
            })
            ->filter(function ($query) use ($request) {
                if ($request->has('msearch') && $request->msearch != '') {
                    $query->where('articles.title', 'like', '%' . $request->msearch . '%');
                }
                if ($request->has('category_filter') && $request->category_filter != '') {
                    $query->where('articles.category_id', $request->category_filter);
                }
            })
            ->escapeColumns([])
            ->make(true);
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
        $categories = \DB::table('article_categories')->orderBy('sort_order')->get();
        $users = \DB::table('users')->select('id', 'firstname', 'lastname')->where('status', 1)->orderBy('firstname')->get();
        return view("dashboard.articles.article", compact('categories', 'users'));
    }

    public function editarticle(Request $request){
        $article = \DB::table('articles')->where('id', $request->id)->first();
        if ($article == null) {
            return redirect()->to('dashboard/articles')->with('error', 'Article not found');
        }
        $categories = \DB::table('article_categories')->orderBy('sort_order')->get();
        $tags = \DB::table('article_tags')->where('article_id', $request->id)->pluck('tag')->implode(', ');
        $users = \DB::table('users')->select('id', 'firstname', 'lastname')->where('status', 1)->orderBy('firstname')->get();
        return view("dashboard.articles.article", compact('article', 'categories', 'tags', 'users'));
    }

    public function addarticle(Request $request){
        request()->validate([
            "title"=>'required|string|min:4',
            "description"=>'required|string|min:4',
            'banner' => 'mimes:jpeg,png,jpg|max:1048',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:article_categories,id',
            'image_caption' => 'nullable|string|max:255',
            'scripture_reference' => 'nullable|string|max:255',
        ]);

        $date = \Carbon\Carbon::now()->format("Y-m-d H:i:s");

        // Generate slug
        $slug = \Str::slug($request->title);
        $slugExists = \DB::table('articles')->where('slug', $slug);
        if ($request->id > 0) {
            $slugExists->where('id', '!=', $request->id);
        }
        if ($slugExists->count() > 0) {
            $slug = $slug . '-' . time();
        }

        // Calculate reading time
        $wordCount = str_word_count(strip_tags($request->description));
        $readingTime = max(1, (int) ceil($wordCount / 200));

        // Common data for both insert and update
        $data = [
            "title" => $request->title,
            "description" => $this->purify($request->description),
            "user_id" => $request->user_id ?: \Auth::user()->id,
            "article_date" => $date,
            "slug" => $slug,
            "category_id" => $request->category_id ?: null,
            "excerpt" => $request->excerpt,
            "image_caption" => $request->image_caption,
            "scripture_reference" => $request->scripture_reference,
            "reading_time" => $readingTime,
        ];

        $imageName = "";
        if (!empty($request->banner)) {
            $imageName = time() . '_' . \Str::random(8) . '.' . $request->banner->getClientOriginalExtension();
            if ($this->compressAndSave($request->banner, public_path('article'), $imageName)) {
                if ($request->id > 0) {
                    $photo = \DB::table('articles')->where('id', $request->id)->first();
                    if ($photo->banner && file_exists(public_path() . "/article/" . $photo->banner)) {
                        unlink(public_path() . "/article/" . $photo->banner);
                    }
                    $data["banner"] = $imageName;
                    \DB::table('articles')->where("id", $request->id)->update($data);
                } else {
                    $data["banner"] = $imageName;
                    $data["status"] = 0;
                    \DB::table('articles')->insert($data);
                }
            } else {
                return redirect()->back()->with('error', 'Error saving article!');
            }
        } else {
            if ($request->id > 0) {
                \DB::table('articles')->where("id", $request->id)->update($data);
            } else {
                $data["banner"] = $imageName;
                $data["status"] = 0;
                \DB::table('articles')->insert($data);
            }
        }

        // Sync tags
        $articleId = $request->id > 0 ? $request->id : \DB::getPdo()->lastInsertId();
        \DB::table('article_tags')->where('article_id', $articleId)->delete();
        if ($request->tags) {
            $tagList = array_filter(array_map('trim', explode(',', $request->tags)));
            foreach ($tagList as $tag) {
                if ($tag) {
                    \DB::table('article_tags')->insert([
                        'article_id' => $articleId,
                        'tag' => $tag,
                    ]);
                }
            }
        }

        if ($request->id > 0) {
            return redirect()->back()->with('success', 'Article updated successfully');
        }
        return redirect()->to('dashboard/articles')->with('success', 'Article saved successfully');
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

    public function toggleFeatured(Request $request){
        $article = \DB::table('articles')->where('id', $request->id)->first();
        if ($article) {
            \DB::table('articles')->where('id', $request->id)->update([
                'is_featured' => !$article->is_featured
            ]);
            return response()->json(['success' => true, 'is_featured' => !$article->is_featured]);
        }
        return response()->json(['success' => false], 404);
    }

    public function categories(){
        $categories = \DB::table('article_categories')->orderBy('sort_order')->get();
        return response()->json($categories);
    }

    public function addCategory(Request $request){
        $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'nullable|string|max:7',
        ]);

        $slug = \Str::slug($request->name);
        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color ?? '#4e73df',
            'updated_at' => now(),
        ];

        if ($request->id > 0) {
            \DB::table('article_categories')->where('id', $request->id)->update($data);
        } else {
            $data['created_at'] = now();
            $data['sort_order'] = \DB::table('article_categories')->max('sort_order') + 1;
            \DB::table('article_categories')->insert($data);
        }

        return redirect()->back()->with('success', 'Category saved');
    }

    public function deleteCategory(Request $request){
        \DB::table('articles')->where('category_id', $request->id)->update(['category_id' => null]);
        \DB::table('article_categories')->where('id', $request->id)->delete();
        return redirect()->back()->with('success', 'Category deleted');
    }
}
