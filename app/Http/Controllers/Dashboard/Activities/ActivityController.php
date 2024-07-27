<?php

namespace App\Http\Controllers\Dashboard\Activities;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use DB;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
    public function index()
    {
        return view('dashboard.activities.activities');
    }
    public function getActivities(Request $request)
    {
        $activities = ActivityLog::with(['user'])
            ->where(DB::raw('DATE(activity_logs.created_at)'), Carbon::parse($request->date))->orderBy('activity_logs.created_at', 'DESC');
        if (!auth()->user()->can('View Activities')) {
            $activities->where('user_id', auth()->user()->id);
        }

        return DataTables::of($activities)
            ->filter(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('activity', 'LIKE', '%' . $request->search . '%')->orWhereHas('user', function ($query) use ($request) {
                        $query->where('name', 'LIKE', '%' . $request->search . '%');
                    });
                });
            })->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->diffForHumans();
            })->addIndexColumn()->escapeColumns([])->make();
    }
}
