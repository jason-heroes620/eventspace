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
        return view('vendors', ['vendors' => $vendors, 'shorts' => $vendor_shorts])->with('selectedShort', $req->short);
    }

    public function getVendorById($id)
    {
        return Vendors::where('id', $id)->first();
    }

    private function getVendorProducts($id)
    {
        $products = Products::where('vendor_id', $id)->where('status', 0)->paginate(8);

        foreach ($products as $product) {
            $product->qr = $this->getQR($product);
        }

        return $products;
    }

    private function getQR($product)
    {
        $qrCode = config("custom.payment_redirect_host") . "product?id=" . $product->id . "&code=" . $product->product_code . "&product_name=" . $product->product_name . "&price=" . $product->product_price;
        $qr = QrCode::size(300)->generate(Crypt::encrypt($qrCode, 'H'));

        return $qr;
    }

    private function getVendorsByShort($short)
    {
        if ($short) {
            return Vendors::where('vendor_short', $short)->where('status', 0)->orderBy('organization')->paginate(15);
        } else {
            return Vendors::where('status', 0)->orderBy('organization')->paginate(15);
        }
    }

    private function getVendorShorts()
    {
        return Vendors::distinct()->orderBy('vendor_short', 'ASC')->get(['vendor_short']);
    }
}
