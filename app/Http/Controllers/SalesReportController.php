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
        $sales = $this->getSalesReport($req->eventId, $req->salesDate);
        if ($sales) {
            foreach ($sales as $sale) {
                $total_qty += $sale->quantity;
                $total_sales += (float)$sale->sales;
            }
        }
        return view('salesreport', ['sales' => $sales, 'totalQty' => $total_qty, 'totalSales' => number_format($total_sales, 2, '.', ',')])->with('salesDate', $req->salesDate);
    }

    public function dailysales(Request $req)
    {
        return view('dailysales');
    }

    public function sales(Request $req)
    {
        if ($req->type === 'summary' || $req->type === '') {
            $sales = $this->getSalesReport($req->eventId, $req->date);
        } else if ($req->type === 'detail') {
            $sales = $this->getDetailSalesReport($req->eventId, $req->date);
        }

        return $this->sendResponse($sales, 200);
    }

    public function vendorsalesbyvendorid(Request $req)
    {
        $data = $this->getVendorSalesById($req->eventId, $req->vendorId);
        return $this->sendResponse($data, 200);
    }

    private function getDetailSalesReport($event_id = 1, $salesDate)
    {
        $sales = array();

        if ($salesDate) {
            $sales = DB::table('event_orders')
                ->leftJoin('event_order_products', 'event_orders.id', '=', 'event_order_products.event_order_id')
                ->leftJoin('event_order_payment_details', 'event_orders.id', '=', 'event_order_payment_details.event_order_id')
                ->leftJoin('products', 'products.id', '=', 'event_order_products.product_id')
                ->leftJoin('vendors', 'vendors.id', '=', 'products.vendor_id')
                ->where('event_orders.status', 2)
                ->whereDate('event_orders.created', $salesDate)
                ->where('event_order_payment_details.status', 2)
                ->selectRaw('products.id, products.product_name, vendors.organization, event_order_products.quantity, event_order_products.total, event_order_payment_details.payment_ref, event_order_payment_details.payment_method, event_order_payment_details.issuing_bank, event_orders.created')
                ->groupBy('products.id', 'products.product_name', 'vendors.organization', 'event_order_products.quantity', 'event_order_products.total', 'event_order_payment_details.payment_ref', 'event_order_payment_details.payment_method', 'event_order_payment_details.issuing_bank', 'event_orders.created')
                ->orderBy('event_orders.created', 'ASC')
                ->get();
        }

        return $sales;
    }

    private function getSalesReport($event_id = 1, $salesDate)
    {
        $sales = array();

        if ($salesDate) {
            $sales = DB::table('event_orders')
                ->leftJoin('event_order_products', 'event_orders.id', '=', 'event_order_products.event_order_id')
                ->leftJoin('products', 'products.id', '=', 'event_order_products.product_id')
                ->leftJoin('vendors', 'vendors.id', '=', 'products.vendor_id')
                ->where('event_orders.status', 2)
                ->whereDate('event_orders.created', $salesDate)
                ->selectRaw('products.id, products.product_name, vendors.organization, sum(event_order_products.quantity) as quantity, sum(event_order_products.total) as sales, event_orders.created')
                ->groupBy('products.id', 'products.product_name', 'vendors.organization', 'event_orders.created')
                ->orderBy('event_orders.created', 'ASC')
                ->get();
        }

        return $sales;
    }

    private function getEventDates($event_id)
    {
        $dates = Events::find($event_id)->select('event_start_date, event_end_date');
        return $dates;
    }

    public function vendorsales()
    {
        return view('vendorsales');
    }

    public function vendorsalesreport(Request $req)
    {
        $sales = $this->getVendorSales($req->id);
        return $this->sendResponse($sales, 200);
    }

    private function getVendorSales($event_id)
    {
        if ($event_id) {
            $sales = DB::table('events_products')
                ->selectRaw('vendors.id, vendors.organization, sum(event_order_products.total) as total')
                ->leftJoin('products', 'events_products.products_id', '=', 'products.id')
                ->leftjoin('event_order_products', 'event_order_products.product_id', '=', 'products.id')
                ->leftJoin('event_orders', 'event_orders.id', '=', 'event_order_products.event_order_id')
                ->leftJoin('vendors', 'products.vendor_id', '=', 'vendors.id')
                ->where('events_products.events_id', $event_id)
                ->where('event_orders.status', 2)
                ->groupBy('vendors.id', 'vendors.organization')
                ->orderBy('vendors.organization', 'ASC')
                ->get();

            return $sales;
        }
    }

    private function getVendorSalesById($event, $vendor)
    {
        $data = DB::table('event_orders')
            ->leftJoin('event_order_products', 'event_orders.id', '=', 'event_order_products.event_order_id')
            ->leftJoin('events_products', 'events_products.products_id', 'event_order_products.product_id')
            ->leftJoin('products', 'event_order_products.product_id', '=', 'products.id')
            ->leftJoin('vendors', 'vendors.id', '=', 'products.vendor_id')
            ->where('vendors.id', $vendor)
            ->where('events_products.events_id', $event)
            ->where('event_orders.status', 2)
            ->orderBy('Product', 'ASC')
            ->get(['products.product_name as Product', 'event_order_products.quantity as Quantity', 'event_order_products.price as Price', 'event_order_products.total as Total']);


        return $data;
    }

    public function discrepancy(Request $req)
    {
        $data = $this->getDiscrepancies($req->eventId);

        return $this->sendResponse($data, 200);
    }

    private function getDiscrepancies($event_id)
    {
        $data = DB::table('event_order_discrepancies')
            ->leftJoin('products', 'products.id', '=', 'event_order_discrepancies.product_id')
            ->leftJoin('vendors', 'products.vendor_id', '=', 'vendors.id')
            ->selectRaw('vendors.organization, sum(event_order_discrepancies.total) as total, sum(event_order_discrepancies.discrepancy_amount) as discrepancy')
            ->groupBy('vendors.organization')
            ->orderBy('vendors.organization', 'ASC')
            ->get();

        return $data;
    }
}
