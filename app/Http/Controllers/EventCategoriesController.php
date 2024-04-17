<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EventCategories;

class EventCategoriesController extends Controller
{
    public function eventcategories(Request $req) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $this->getEventCategories($req->id);
            return $this->sendResponse($data, 200);
        } else {
            return $this->sendError('', ['error' => 'Allowed headers GET'], 405);
        }
    }

    private function getEventCategories($id) {
        $query = DB::table('event_categories')
        ->join('events', 'events.id', '=', 'event_categories.event_id')
        ->join('categories', 'categories.id', '=', 'event_categories.category_id')
        ->where('events.id', '=', $id)
        ->where('categories.status', '=', '0')
        ->orderBy('categories.orders');

        return $query->get(['event_categories.id', 'categories.id', 'categories.category']); 
    }
}
