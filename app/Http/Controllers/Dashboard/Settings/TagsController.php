<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use DB;

class TagsController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:Manage Tags']);
    }

    public function index()
    {
        return view('dashboard.settings.tags');
    }

    public function getTags(Request $request)
    {
        return DataTables::of(Tag::orderBy('name', 'ASC'))
            ->filter(function ($query) use ($request) {
                if ($request->search) {
                    $query->where('name', 'LIKE', '%' . $request->search . '%');
                }
            })->addColumn('users_count', function ($row) {
                return DB::table('tag_user')->where('tag_id', $row->id)->count();
            })->addColumn('preview', function ($row) {
                return '<span class="badge" style="background:' . $row->color . ';color:#fff;padding:4px 10px;">' . $row->name . '</span>';
            })->addColumn('action', function ($row) {
                return '<div class="text-end text-right">' .
                    '<span class="d-none tag-id">' . $row->id . '</span>' .
                    '<span class="d-none tag-name">' . $row->name . '</span>' .
                    '<span class="d-none tag-color">' . $row->color . '</span>' .
                    '<button class="btn btn-primary btn-sm btn-edit-tag" data-toggle="modal" data-target="#tagModal">' .
                    '<i class="fas fa-edit"></i></button> ' .
                    '<button class="btn btn-danger btn-sm btn-delete-tag">' .
                    '<i class="fas fa-trash"></i></button>' .
                    '</div>';
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function addTag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:0',
            'name' => 'required|string|max:50|unique:tags,name,' . $request->id,
            'color' => 'required|string|max:7',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        if ($request->id > 0) {
            $tag = Tag::findOrFail($request->id);
            $tag->name = $request->name;
            $tag->color = $request->color;
            $tag->save();
        } else {
            Tag::create(['name' => $request->name, 'color' => $request->color]);
        }

        return response()->json(['success' => 'Tag saved successfully!']);
    }

    public function deleteTag(Request $request)
    {
        $tag = Tag::find($request->id);
        if ($tag) {
            DB::table('tag_user')->where('tag_id', $tag->id)->delete();
            $tag->delete();
            return response()->json(['success' => 'Tag deleted successfully!']);
        }
        return response()->json(['error' => 'Tag not found'], 404);
    }

    public function searchTags(Request $request)
    {
        return json_encode(
            Tag::where('name', 'LIKE', '%' . $request->q . '%')
                ->orderBy('name', 'asc')->get()
        );
    }

    public function assignTag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'tag_id' => 'required|integer|exists:tags,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $exists = DB::table('tag_user')->where('tag_id', $request->tag_id)->where('user_id', $request->user_id)->exists();
        if (!$exists) {
            DB::table('tag_user')->insert([
                'tag_id' => $request->tag_id,
                'user_id' => $request->user_id,
                'created_at' => now(),
            ]);
        }

        return response()->json(['success' => 'Tag assigned successfully!']);
    }

    public function removeUserTag(Request $request)
    {
        DB::table('tag_user')
            ->where('tag_id', $request->tag_id)
            ->where('user_id', $request->user_id)
            ->delete();

        return response()->json(['success' => 'Tag removed successfully!']);
    }
}
