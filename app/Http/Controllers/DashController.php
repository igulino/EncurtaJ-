<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

class DashController extends Controller
{
    public function index()
    {
        Log::info('log-test', ['time' => microtime(true)]);
        return view('Dashboard');
    }
}