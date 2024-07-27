<?php

namespace App\Http\Controllers\Dashboard\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class NotificationsController extends Controller
{
    public function __construct()
    {
    $this->middleware(['auth', 'verified']);
    }
    public function index()
    {
        $notifications = Notification::where('status', 1)->count();
        return view('dashboard.notifications.notifications', @compact('notifications'));
    }
    public function getNotifications(Request $request)
    {
        return DataTables::of(Notification::where('status', 1)->orderBy('created_at', 'DESC'))
            ->editColumn('notification', function ($row) {
                return \Str::limit($row->notification, 50, '...');
            })->editColumn('status', function ($row) {
                return $row->status?"<span class='badge badge-primary'>Active</span>":"<span class='badge badge-primary'>Inactive</span>";
            })->editColumn('created_at', function ($row) {
            return Carbon::parse($row->created_at)->diffForHumans();
        })->addColumn('action', function ($row) {
            $actionBtn = '<div style="white-space: nowrap;" class="text-end">
                    <a href="'.url('dashboard/notifications/view/'.$row->id).'" class="btn btn-primary btn-sm">'.
                    '<span class="d-none d-sm-block"><i class="fas fa-eye"></i> View</span><span class="d-block d-sm-none"><i class="fas fa-eye"></i></span></a> ';
            return $actionBtn;
        })->addIndexColumn()->escapeColumns([])->make();
    }

    public function notification(Request $request){
        $notification = Notification::find($request->id);
        if($notification == null){
            return redirect()->to('dashboard/notifications');
        }
        return view('dashboard.notifications.notification', @compact('notification'));
    }
}
