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

            return view('vendor', ['vendor' => $vendor, 'products' => $products]);
        } else {
            $vendors = $this->getVendorsByShort($req->s);
            $vendor_shorts = $this->getVendorShorts();
            return view('vendors', ['vendors' => $vendors, 'shorts' => $vendor_shorts])->with('s', $req->s);
        }
    }

    public function vendorsfilter(Request $req)
    {
        $vendors = $this->getVendorsByShort($req->s);
        $vendor_shorts = $this->getVendorShorts();

        //$vendors = $this->loadProducts($vendors);
        return view('vendors', ['vendors' => $vendors, 'shorts' => $vendor_shorts])->with('selectedShort', $req->short);
    }

    private function loadProducts($vendors)
    {
        // foreach ($vendors as $vendor) {
        //     $products = Products::where('vendor_id', $vendor->id)->get();
        //     $vendor->products = $products;
        // }
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
        return Products::where('vendor_id', $id)->where('status', 0)->paginate();
    }

    private function getVendorsByShort($short)
    {
        if ($short) {
            return Vendors::where('vendor_short', $short)->where('status', 0)->orderBy('organization')->paginate(10);
        } else {
            return Vendors::where('status', 0)->orderBy('organization')->paginate(10);
        }
    }

    private function getVendorShorts()
    {
        return Vendors::distinct()->orderBy('vendor_short', 'ASC')->get(['vendor_short']);
    }
}
