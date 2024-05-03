<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventBooth;
use Illuminate\Support\Facades\DB;


class EventBoothController extends Controller
{
    public function eventbooth(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($req->id)) {
                $data = $this->getEventBooth($req->id);
                return $this->sendResponse($data, 200);
            }
        } else {
            return $this->sendError('', ['error' => 'Allowed headers GET'], 405);
        }
    }

    private function getEventBooth($id)
    {
        $query = DB::table('events_booths')
            ->join('booths', 'booths.id', '=', 'events_booths.booth_id')
            ->where('events_booths.event_id', '=', $id)
            ->where('booths.status', '=', '0')
            ->orderBy('orders');

        return $query->get();
    }

    public function getEventBoothPriceById($event_id, $booth_id)
    {
        $query = DB::table('events_booths')
            ->join('booths', 'booths.id', '=', 'events_booths.booth_id')
            ->where('events_booths.event_id', '=', $event_id)
            ->where('events_booths.booth_id', '=', $booth_id)
            ->where('booths.status', '=', '0')
            ->orderBy('orders');

        return $query->first(['price', 'display_price']);
    }
}
