<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationCategoriesController extends Controller
{
    public function applicationCategories(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $this->getApplicationCategories($req->id);
            return $this->sendResponse($data, 200);
        } else {
            return $this->sendError('', ['error' => 'Allowed headers GET'], 405);
        }
    }

    public function getApplicationCategories($id)
    {
        $query = DB::table('application_categories')
            ->join('categories', 'categories.id', '=', 'application_categories.category_id')
            ->where('application_categories.application_id', '=', $id)
            ->where('categories.status', '=', '0')
            ->orderBy('categories.orders');

        return $query->get(['categories.category']);
    }
}
