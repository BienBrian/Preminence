<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Traits\SanitizesHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    use SanitizesHtml;
    public $site_settings;
    public function __construct()
    {
        $this->middleware(['auth']);

        try {
            $this->site_settings = \DB::table("settings")->first();
        } catch (\Exception $e) {
            Log::error('DashboardController: Could not load site settings — ' . $e->getMessage());
            $this->site_settings = null;
        }

        \View::share('site_settings', $this->site_settings);
        /*$this->middleware(function ($request, $next) {
            $permissions = Auth::user()->permissions;
            View::share([ 'perms' => $permissions ]);
            return $next($request);
        });*/
    }
}
