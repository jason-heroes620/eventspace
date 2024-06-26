<?php

namespace App\Http\Controllers;

use App\Models\EventsProductsDiscounts;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProductsController extends Controller
{
    public function index(Request $req)
    {
        if (isset($req->id)) {
            $product = $this->getProductById($req->id);
            $vendor_info = (new VendorsController)->getVendorById($product->vendor_id);
            $qr = $this->getQR($product);
            return view('product-detail', ['product' => $product, 'vendor_info' => $vendor_info, 'page' => $req->page, 'event' => $req->event, 'vendor' => $req->vendor, 'qr' => $qr]);
        } else {
            $products = $this->getProducts($req->event, $req->vendor);
            foreach ($products as $product) {
                $qr = $this->getQR($product);
                $product->qr = $qr;
            }
            return view('products', ['products' => $products, 'event' => $req->event, 'vendor' => $req->vendor]);
        }
    }

    public function products(Request $req)
    {
        $products = $this->getProducts($req->event, $req->vendor);
        return view('products', ['products' => $products, 'event' => $req->event, 'vendor' => $req->vendor]);
    }

    private function getQR($product)
    {
        $qrCode = config("custom.payment_redirect_host") . "product?id=" . $product->id . "&code=" . $product->product_code . "&product_name=" . $product->product_name . "&price=" . $product->product_price;
        $qr = QrCode::size(300)->generate(Crypt::encrypt($qrCode));

        return $qr;
    }

    private function getProducts($event = null, $vendor = null)
    {
        $products = DB::table('events_products')
            ->select(['products.id', 'products.product_name', 'products.product_description', 'products.product_code', 'products.product_price', 'products.display_price', 'products.product_image', 'vendors.organization'])
            ->leftJoin('products', 'events_products.products_id', '=', 'products.id')
            ->leftJoin('vendors', 'products.vendor_id', '=', 'vendors.id');
        if (isset($event) && isset($vendor)) {
            $products = $products->where('events_products.events_id', $event)
                ->where('products.vendor_id', $vendor);
        } else if (isset($event) && !isset($vendor)) {
            $products = $products->where('events_products.events_id', $event);
        } else if (!isset($event) && isset($vendor)) {
            $products = $products->where('products.vendor_id', $vendor);
        } else {
            $products = $products;
        }

        return $products->where('events_products.status', 0)->paginate(15);
    }

    private function getProductById($id)
    {
        return Products::find($id);
    }

    private function getProductsByShort($short)
    {
        return Products::where('product_short', $short)->where('status')->paginate(8);
    }

    public function getProductShorts($vendor_id)
    {
        $products = Products::where('status', 0);

        if ($vendor_id) {
            $products = $products->where('vendor_id', $vendor_id);
        }
        return $products->orderBy('product_short', 'ASC')->distinct()->get(['product_short']);
    }

    public function productsdiscounts(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($req->id)) {
                $data = $this->getProductsDiscounts($req->id);
            }
            return $this->sendResponse($data, 200);
        } else {
            return $this->sendError('', ['error' => 'Allowed headers GET'], 405);
        }
    }

    private function getProductsDiscounts($product_id)
    {
        $discount = EventsProductsDiscounts::where('product_id', $product_id)
            ->where('status', 0)
            ->whereDate('valid_date', '<=', now())
            ->whereDate('expiry_date', '>=', now())
            ->first();

        return $discount;
    }
}
