<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SalesReportController extends Controller
{
    public function salesreport(Request $req)
    {
        $total_qty = $total_sales = 0;
        $sales = $this->getDailySales($req->salesDate);
        foreach ($sales as $sale) {
            $total_qty += $sale->quantity;
            $total_sales += $sale->sales;
        }
        return view('salesreport', ['sales' => $sales, 'totalQty' => $total_qty, 'totalSales' => $total_sales])->with('salesDate', $req->salesDate);
    }

    private function getDailySales($salesDate)
    {
        $data = DB::table('event_orders')
            ->leftJoin('event_order_products', 'event_orders.id', '=', 'event_order_products.event_order_id')
            ->leftJoin('products', 'products.id', '=', 'event_order_products.product_id')
            ->leftJoin('vendors', 'vendors.id', '=', 'products.vendor_id')
            ->where('event_orders.status', 2);
        if ($salesDate) {
            $data = $data->whereDate('event_orders.created', $salesDate);
        }

        $sales = $data->selectRaw('products.id, products.product_name, vendors.organization, sum(event_order_products.quantity) as quantity, sum(event_order_products.price) as sales, event_orders.created')
            ->groupBy('products.id', 'products.product_name', 'vendors.organization', 'event_orders.created')
            ->get();

        return $sales;
    }
}
