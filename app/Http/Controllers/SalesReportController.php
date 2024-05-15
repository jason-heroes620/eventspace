<?php

namespace App\Http\Controllers;

use App\Models\Events;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function salesreport(Request $req)
    {
        $total_qty = $total_sales = 0;
        $sales = $this->getSalesReport($req->salesDate);
        if ($sales) {
            foreach ($sales as $sale) {
                $total_qty += $sale->quantity;
                $total_sales += (float)$sale->sales;
            }
        }
        return view('salesreport', ['sales' => $sales, 'totalQty' => $total_qty, 'totalSales' => number_format($total_sales, 2, '.', ',')])->with('salesDate', $req->salesDate);
    }

    public function dailysales()
    {
        $events = Events::get(['id', 'event_name']);

        return view('dailySales', compact('events'));
    }

    private function getSalesReport($salesDate)
    {
        $sales = array();

        if ($salesDate) {
            $sales = DB::table('event_orders')
                ->leftJoin('event_order_products', 'event_orders.id', '=', 'event_order_products.event_order_id')
                ->leftJoin('products', 'products.id', '=', 'event_order_products.product_id')
                ->leftJoin('vendors', 'vendors.id', '=', 'products.vendor_id')
                ->where('event_orders.status', 2)
                ->whereDate('event_orders.created', $salesDate)
                ->selectRaw('products.id, products.product_name, vendors.organization, sum(event_order_products.quantity) as quantity, sum(event_order_products.price) as sales, event_orders.created')
                ->groupBy('products.id', 'products.product_name', 'vendors.organization', 'event_orders.created')
                ->get();
        }

        return $sales;
    }

    private function getEventDates($event_id)
    {
        $dates = Events::find($event_id)->select('event_start_date, event_end_date');
        return $dates;
    }
}
