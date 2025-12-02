<?php

namespace App\Http\Controllers;

use App\Models\TermsAndConditions;
use Illuminate\Http\Request;

class TermsAndConditionsController extends Controller
{
    public function tnc(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($req->id)) {
                $data = $this->getTNCById($req->id);
            }

            return $this->sendResponse($data, 200);
        } else {
            return $this->sendError('', ['error' => 'Allowed headers GET'], 405);
        }
    }

    private function getTNCById($id)
    {
        return TermsAndConditions::where('event_group_id', $id)->first();
    }
}
