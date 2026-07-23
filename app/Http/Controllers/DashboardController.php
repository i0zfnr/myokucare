<?php

namespace App\Http\Controllers;

use App\Services\OkuDataService;

class DashboardController extends Controller
{
    public function index(OkuDataService $service)
    {
        return view('dashboard', ['stats' => $service->dashboard()]);
    }
}
