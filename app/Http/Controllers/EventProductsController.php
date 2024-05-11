<?php

namespace App\Http\Controllers;

use App\Models\Events;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventProductsController extends Controller
{
    public function eventproducts(Request $req)
    {
        $events = $this->getEvents();
        $products = array();
        $shorts = $this->getProductShort($req->eventId);
        $products = $this->getEventProductsByShort($req->eventId, $req->s);
        return view('eventproducts', compact('products', 'events', 'shorts'))->with('eventId', $req->eventId)->with('s', $req->s);
    }

    // private function getEventProductList($eventId)
    // {
    //     $products = array();
    //     if ($eventId) {
    //         $event = Events::find($eventId);
    //         $products = $event->products()->orderBy('product_name', 'ASC')->paginate(2);
    //     } else {
    //         $products = DB::table('events_products')
    //             ->leftJoin('events', 'events.id', '=', 'events_products.events_id')
    //             ->leftJoin('products', 'events_products.products_id', '=', 'products.id')
    //             ->where('events.status', 0)
    //             ->where('events_products.status', 0)
    //             ->orderBy('products.product_name', 'ASC')
    //             ->paginate(2);
    //     }

    //     foreach ($products as $product) {
    //         $product->qr = $this->getQR($product);
    //         $product->organization = Products::find($product->id)->vendor()->first(['organization']);
    //     }
    //     return $products;
    // }

    private function getQR($product)
    {
        $qrCode = config("custom.payment_redirect_host") . "product?id=" . $product->id . "&code=" . $product->product_code . "&product_name=" . $product->product_name . "&price=" . $product->product_price;
        $qr = QrCode::size(300)->generate(Crypt::encrypt($qrCode));

        return $qr;
    }

    // public function eventproductsfilter(Request $req)
    // {
    //     $events = $this->getEvents();
    //     $products = array();
    //     $shorts = $this->getProductShort($req->eventId);
    //     $products = $this->getEventProductsByShort($req->eventId, $req->s);

    //     return view('eventproducts', compact('products', 'events', 'shorts'))->with('eventId', $req->eventId)->with('s', $req->s);
    // }

    private function getEventProductsByShort($eventId, $short)
    {
        if ($eventId) {
            $event = Events::find($eventId);
            $products = $event->productsByShort($short)->orderBy('product_name', 'ASC')->paginate(10);
        } else {
            $products = DB::table('events_products')
                ->leftJoin('events', 'events.id', '=', 'events_products.events_id')
                ->leftJoin('products', 'events_products.products_id', '=', 'products.id')
                ->where('events.status', 0)
                ->where('events_products.status', 0);
            if ($short) {
                $products = $products->where('products.product_short', $short);
            }

            $products = $products->orderBy('products.product_name', 'ASC')
                ->paginate(10);
        }
        foreach ($products as $product) {
            $product->qr = $this->getQR($product);
            $product->organization = Products::find($product->id)->vendor()->first(['organization']);
        }
        return $products;
    }

    private function getEvents()
    {
        return Events::where('status', 0)->get();
    }

    private function getProductShort($eventId)
    {
        $shorts = DB::table('events_products')
            ->leftJoin('events', 'events.id', '=', 'events_products.events_id')
            ->leftJoin('products', 'events_products.products_id', '=', 'products.id')
            ->where('events.status', 0)
            ->where('events_products.status', 0);
        if ($eventId) {
            $shorts = $shorts->where('events_products.events_id', $eventId);
        }

        $shorts = $shorts->orderBy('products.product_short', 'ASC')
            ->distinct()
            ->get(['products.product_short']);

        return $shorts;
    }
}
