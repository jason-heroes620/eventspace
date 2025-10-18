<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Events;

class EventController extends Controller
{
    public function events(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($req->id)) {
                $data = $this->getEventById($req->id);
            } else {
                $data = $this->getEvents();
            }

            return $this->sendResponse($data, 200);
        } else {
            return $this->sendError('', ['error' => 'Allowed headers GET'], 405);
        }
    }

    private function getEvents()
    {
        return Events::where("status", 0)->orderBy("id")->get();
    }

    private function getEventById($id)
    {
        return Events::where("id", $id)->first();
    }
}
