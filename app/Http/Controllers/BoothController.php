<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booths;

class BoothController extends Controller
{
    // Get all booths
    public function booths(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($req->id)) {
                $data = $this->getBoothById($req->id);
            } else {
                $data = $this->getBooths();
            }

            return $this->sendResponse($data, 200);
        } else {
            return $this->sendError('', ['error' => 'Allowed headers GET'], 405);
        }
    }

    private function getBooths()
    {
        return Booths::where("status", 0)->orderBy("orders")->get();
    }

    public function getBoothById($id)
    {
        return Booths::where('id', $id)->first();
    }
}
