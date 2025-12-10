<?php

namespace App\Http\Controllers;

use App\Models\EventApplicationGroup;
use App\Models\EventBooth;
use App\Models\EventDeposit;
use App\Models\EventGroups;
use App\Models\EventPayments;
use App\Models\Events;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function reports(Request $req)
    {
        $eventGroups = EventGroups::orderBy('event_group', 'ASC')->get();
        $deposit = 0.00;

        foreach ($eventGroups as $eventGroup) {
            $events = Events::select('event_name', 'event_date', 'id')
                ->where('event_group_id', $eventGroup->event_group_id)
                ->orderBy('event_start_date', 'DESC')
                ->get();

            foreach ($events as &$event) {
                $applications = EventApplicationGroup::select('booth_qty', 'no_of_days', 'price', 'discount', 'discount_value', 'event_application_group.id')
                    ->leftJoin('event_applications', 'event_applications.event_application_group_id', '=', 'event_application_group.id')
                    ->leftJoin('events_booths', 'events_booths.id', 'event_applications.booth_id')
                    ->where('event_application_group.event_group_id', $eventGroup->event_group_id)
                    ->where('event_applications.event_id', $event->id)
                    ->where('event_application_group.status', 'A')
                    ->get();
                // Log::info('list');
                // Log::info($applications);
                if ($event->require_deposit == 'Y') {
                    $deposit = EventDeposit::whereNull('end_date')->where('event_deposit_status', true)->where('start_date', '<=', date('Y-m-d'))->first()->event_deposit;
                }

                foreach ($applications as &$application) {
                    $application['subTotal'] = $application->booth_qty * $application->no_of_days * $application->price;
                    $application['discount'] = $event['subTotal'] - $application->discount_value;

                    $application['payment_total'] = 0;
                    $payment = EventPayments::where('application_id', $application->id)
                        ->where('status', 2)->first();
                    if ($payment)
                        $application['payment'] = $payment->payment_total;

                    $event['application'] = $application;
                }
                Log::info('event');
                Log::info($event);
                // $event_booth = EventBooth::where('id', $event->booth_id)->first();
                // $booth = (new BoothController)->getBoothById($event_booth->booth_id);

                // $booth_price = number_format((float)($event_booth->price), 2, '.', '');
                // $subTotal = number_format((float)((int)$event->booth_qty * (int)$event->no_of_days * (float)$event_booth->price), 2, '.', '');
                // $event->subTotal = $subTotal;
                // $total += $subTotal;

                // $event->event_date = Events::where('id', $event->event_id)->first()->event_date;
                // $event->booth_type = $booth->booth_type;

                // $event = Events::where('id', $event->event_id)->first();
                // if ($event->require_deposit == 'Y') {
                //     $require_deposit = true;
                //     $deposit =  $deposit = EventDeposit::whereNull('end_date')->where('event_deposit_status', true)->where('start_date', '<=', date('Y-m-d'))->first()->event_deposit;
                //     $total += $deposit;
                // }

            }
            unset($application);
            unset($event);
        }

        Log::info($eventGroups);
        return view('reports', compact('eventGroups'))->with('eventGroupId', $req->eventGroupId);
    }
}
