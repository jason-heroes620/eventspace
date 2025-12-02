<?php

namespace App\Http\Controllers;

use App\Models\EventApplicationGroup;
use App\Models\EventApplications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    function index()
    {
        $applications = EventApplicationGroup::query();

        $applications = $applications->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $paid = EventApplicationGroup::leftJoin('event_payments', 'event_payments.application_id', 'event_application_group.id')
            ->where('event_application_group.status', 'A')
            ->where('event_payments.status', 2)
            ->distinct()
            ->count('event_application_group.id');
        $pending = EventApplicationGroup::select(DB::raw('count(*) as pending'))
            ->leftJoin('event_payments', 'event_payments.application_id', 'event_application_group.id')
            ->where('event_application_group.status', 'A')
            ->where('event_payments.status', 1)
            ->distinct()
            ->count('event_application_group.id');
        return view('home', compact('applications', 'paid', 'pending'));
    }
}
