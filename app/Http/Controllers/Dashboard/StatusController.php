<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function __construct(){
    $this->middleware(['auth','verified']);
    }

    public function index(){
        if(auth()->user()->status == 1 && auth()->user()->approval_status == 1){
            return redirect()->to('dashboard/home');
        }
        return view('dashboard.status');
    }
}
