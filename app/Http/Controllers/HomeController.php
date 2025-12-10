<?php

namespace App\Http\Controllers;

use App\Models\EventApplicationGroup;
use App\Models\EventApplications;
use App\Models\EventPayments;
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

        $paid = EventPayments::select('application_id')
            ->where('event_payments.status', 2)
            ->whereIn('event_payments.application_id', EventApplicationGroup::where('status', 'A')->get()->pluck('id'))
            ->distinct()
            ->count('event_payments.application_id');

        $pending = EventPayments::where('event_payments.status', 1)
            ->whereIn('event_payments.application_id', EventApplicationGroup::where('status', 'A')->get()->pluck('id'))
            ->distinct()
            ->count('event_payments.application_id');
        return view('home', compact('applications', 'paid', 'pending'));
    }
}
