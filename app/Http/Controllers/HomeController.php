<?php

namespace App\Http\Controllers;

use App\Models\EventApplications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    function index()
    {
        $applications = EventApplications::query();

        $applications = $applications->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        return view('home', ['applications' => $applications]);
    }
}
