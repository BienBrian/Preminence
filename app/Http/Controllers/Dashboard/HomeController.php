<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
    }

    private function tid(): ?int
    {
        return config('app.tenant_id');
    }

    public function index()
    {
        return view('dashboard.home');
    }

    public function years(Request $request)
    {
        $tid  = $this->tid();
        $year = intval($request->years);
        $dashboard = DB::table('funds')->where('funds.tenant_id', $tid)->where("sources.ftype", 0)
            ->join("sources", "sources.id", "funds.source")
            ->select(DB::raw('sum(funds.amount) as totals'), DB::raw('YEAR(funds.created_at) year, MONTH(funds.created_at) month'))
            ->whereYear('funds.created_at', $year)
            ->groupby(DB::raw('YEAR(funds.created_at)'), DB::raw('MONTH(funds.created_at)'))
            ->get();
        return json_encode($dashboard);
    }

    public function expenditure(Request $request)
    {
        $tid  = $this->tid();
        $year = intval($request->years);
        $dashboard = DB::table('funds')->where('funds.tenant_id', $tid)->where("sources.ftype", 1)
            ->join("sources", "sources.id", "funds.source")
            ->select(DB::raw('sum(funds.amount) as totals'), DB::raw('YEAR(funds.created_at) year, MONTH(funds.created_at) month'))
            ->whereYear('funds.created_at', $year)
            ->groupby(DB::raw('YEAR(funds.created_at)'), DB::raw('MONTH(funds.created_at)'))
            ->get();
        return json_encode($dashboard);
    }

    public function myfunds(Request $request)
    {
        $tid  = $this->tid();
        $year = intval($request->years);
        $dashboard = DB::table('funds')->where('tenant_id', $tid)->where("source", $request->id)
            ->select(DB::raw('sum(amount) as totals'), DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
            ->whereYear('created_at', $year)
            ->groupby(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->get();
        return json_encode($dashboard);
    }
}
