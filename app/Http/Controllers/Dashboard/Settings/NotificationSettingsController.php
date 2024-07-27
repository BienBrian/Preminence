<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Events\NotificationEvent;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class NotificationSettingsController extends Controller
{
    public function __construct()
    {
    $this->middleware(['auth', 'verified']);
    $this->middleware(['permission:View Notification Settings']);
    }
    public function index()
    {
        return view('dashboard.settings.notification_settings');
    }
    public function getNotifications(Request $request)
    {
        return DataTables::of(Notification::with('user')->orderBy('created_at', 'DESC'))
            ->filter(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('notification', 'LIKE', '%' . $request->search . '%');
                });
                if($request->status != -1){
                    $query = $query->where('status', $request->status);
                }
            })->editColumn('notification', function ($row) {
                return \Str::limit($row->notification, 30, '...');
            })->editColumn('status', function ($row) {
                return $row->status?"<span class='badge badge-primary'>Active</span>":"<span class='badge badge-secondary'>Inactive</span>";
            })->editColumn('created_at', function ($row) {
            return Carbon::parse($row->created_at)->diffForHumans();
        })->addColumn('action', function ($row) {
            $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                '<span class="d-none id">' . $row->id . '</span>' .
                '<span class="d-none notification">' . $row->notification . '</span>' .
                '<span class="d-none status">' . $row->status . '</span>
                    <button class="btn-edit btn btn-primary btn-sm" data-toggle="modal" data-target="#userModal">'.
                    '<span class="d-none d-sm-block"><i class="fas fa-edit"></i> Edit</span><span class="d-block d-sm-none"><i class="fas fa-edit"></i></span></button> ';
            return $actionBtn;
        })->addIndexColumn()->escapeColumns([])->make();
    }
    public function addNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:0',
            'notification' => 'required|string',
            'status' => 'required|integer|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $notification = new Notification;
        if ($request->id > 0) {
            $notification = Notification::find($request->id);
        }
        $notification->notification = $request->notification;
        $notification->user_id = auth()->user()->id;
        $notification->status = $request->status;
        if ($notification->save()) {
            if($notification->status){
                $id = $notification->id;
                $name = $notification->notification;
                $date = Carbon::parse($notification->created_at)->diffForHumans();
                $count = Notification::where('status', true)->count();
                event(new NotificationEvent($id, $name,$date, $count));
            }
            return response()->json(['success' => "Notification updated successfully!"]);
        } else {
            return response()->json(['error' => 'Unable to update Notification'], 401);
        }
    }
}
