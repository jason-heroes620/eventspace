<?php

namespace App\Http\Controllers;

use App\Models\Vendors;
use App\Models\Products;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VendorsController extends Controller
{
    public function index(Request $req)
    {
        if (isset($req->id)) {
            $vendor = $this->getVendorById($req->id);
            $products = $this->getVendorProducts($req->id);
            return view('vendor', ['vendor' => $vendor, 'products' => $products])->with('selectedShort', $req->short);
        } else {
            $vendors = $this->getVendors();
            $vendor_shorts = $this->getVendorShorts();

            $vendors = $this->loadProducts($vendors);
            return view('vendors', ['vendors' => $vendors, 'shorts' => $vendor_shorts])->with('selectedShort', $req->short);
        }
    }

    public function vendorsfilter(Request $req)
    {
        $vendors = $this->getVendorsByShort($req->short);
        $vendor_shorts = $this->getVendorShorts();

        $vendors = $this->loadProducts($vendors);
        return view('vendors', ['vendors' => $vendors, 'shorts' => $vendor_shorts])->with('selectedShort', $req->short);
    }

    private function loadProducts($vendors)
    {
        foreach ($vendors as $vendor) {
            $products = $this->getVendorProducts($vendor->id);
            foreach ($products as $product) {
                $qrCode = "https://events.heroes.my/product?id=" . $product->id . "&code=" . $product->product_code . "&product_name=" . $product->product_name . "&price=" . $product->product_price;
                $qr = QrCode::size(250)->generate(Crypt::encrypt($qrCode));
                $product->qr = $qr;
            }
            $vendor->products = $products;
        }
        return $vendors;
    }

    private function getVendors()
    {
        return Vendors::paginate(10);
    }

    public function getVendorById($id)
    {
        return Vendors::where('id', $id)->first();
    }

    private function getVendorProducts($id)
    {
        return Products::where('vendor_id', $id)->where('status', 0)->get();
    }

    private function getVendorsByShort($short)
    {
        if ($short) {
            return Vendors::where('vendor_short', $short)->where('status', 0)->paginate(10);
        } else {
            return Vendors::where('status', 0)->paginate(10);
        }
    }

    private function getVendorShorts()
    {
        return Vendors::distinct()->orderBy('vendor_short', 'ASC')->get(['vendor_short']);
    }
}
