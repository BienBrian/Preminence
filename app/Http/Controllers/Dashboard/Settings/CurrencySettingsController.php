<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CurrencySettingsController extends Controller
{
    public function __construct()
    {
    $this->middleware(['auth', 'verified']);
    $this->middleware(['permission:View Currency Settings']);
    }

    public function index(){
        return view('dashboard.settings.currency_settings');
    }
}
