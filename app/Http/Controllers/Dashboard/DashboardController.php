<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    public $site_settings;
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->site_settings = \DB::table("settings")->first();
        \View::share('site_settings', $this->site_settings);
        /*$this->middleware(function ($request, $next) {
            $permissions = Auth::user()->permissions;
            View::share([ 'perms' => $permissions ]);
            return $next($request);
        });*/
    }
}
