<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventCategoriesController extends Controller
{
    public function eventCategories(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // $data = $this->getEventCategories($req->id);
            $data = $this->getEventCategoriesV2($req->id);
            return $this->sendResponse($data, 200);
        } else {
            return $this->sendError('', ['error' => 'Allowed headers GET'], 405);
        }
    }

    private function getEventCategories($id)
    {
        $query = DB::table('event_categories')
            ->join('events', 'events.id', '=', 'event_categories.event_id')
            ->join('categories', 'categories.id', '=', 'event_categories.category_id')
            ->where('events.id', '=', $id)
            ->where('categories.status', '=', '0')
            ->orderBy('categories.orders');

        return $query->get(['event_categories.id', 'categories.id', 'categories.category']);
    }

    private function getEventCategoriesV2($id)
    {
        $query = DB::table('event_categories2')
            ->join('categories', 'categories.id', '=', 'event_categories2.category_id')
            ->where('event_categories2.event_group_id', '=', $id)
            ->where('categories.status', '=', '0')
            ->orderBy('categories.orders');

        return $query->get(['event_categories2.id', 'categories.id', 'categories.category']);
    }
}
