<?php

namespace App\Http\Controllers;

use App\Imports\ProductsImport;
use App\Models\EventApplications;
use App\Models\EventProducts;
use App\Models\Products;
use App\Models\Vendors;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Sheet;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExcelImportController extends Controller
{
    // Displays the file upload form.
    public function showUploadForm()
    {
        return view('upload');
    }

    // Handles the file upload and Excel import.
    public function import(Request $request)
    {
        // dd($request->file());
        // Ensure that the 'file' field is present, is a file and is either in xlsx or xls format
        // $request->validate([
        //     'file' => 'required|file|mimes:xlsx,xls',
        // ]);

        $file = $request->file('file');

        try {
            if ($file) {
                // dd($file);
                $array = Excel::toArray(new ProductsImport, $file);

                $vendor_id = $this->addVendor($array[0]);
                $products = $this->addProducts($vendor_id, $array[1]);

                dd($products);
            }
        } catch (Throwable $ex) {
            Log::error($ex);
        }
    }

    private function addVendor($vendor)
    {
        $company_registration = $vendor[1][2];
        $v = Vendors::where('company_registration', $company_registration)->first();

        $path = '/public/img/' . $company_registration;
        if (!Storage::exists($path)) {
            Storage::makeDirectory($path);
        }

        if ($v) {
            return $v->id;
        } else {
            $data = $this->existApplication($company_registration);
            if ($data) {
                $v = new Vendors;
                $v->organization = $data->organization;
                $v->company_registration = $data->registration;
                $v->contact_person = $data->contact_person;
                $v->contact_no = $data->contact_no;
                $v->email = $data->email;
                $v->vendor_short = strtoupper(substr($vendor[1][1], 0, 1));
                $v->save();

                return $v->id;
            } else {
                $v = new Vendors;
                $v->organization = $vendor[1][1];
                $v->company_registration = $vendor[1][2];
                $v->vendor_short = strtoupper(substr($vendor[1][1], 0, 1));
                $v->save();

                return $v->id;
            }
        }
    }

    private function addProducts($vendor_id, $products)
    {
        foreach ($products as $i => $product) {
            if ($i !== 0) {
                $id = Products::where('product_name', $product[1])->where('product_description', $product[2])->where('vendor_id', $vendor_id)->where('status', 0)->first();
                if (!$id) {
                    $vendor = Vendors::where('id', $vendor_id)->first();
                    $prod = new Products;
                    $prod->product_name = $product[1];
                    $prod->vendor_id = $vendor_id;
                    $prod->product_description = $product[2];
                    $prod->product_price = number_format((float)($product[4]), 2);
                    $prod->display_price = 'RM' . number_format((float)($product[4]), 2);
                    $prod->product_image = $vendor->company_registration . "/" . $product[5];
                    $prod->product_short = strtoupper(substr($product[1], 0, 1));
                    $prod->product_code = $this->generateCode(8);
                    $prod->save();

                    $eventProducts = new EventProducts;
                    $eventProducts->events_id = 1;
                    $eventProducts->products_id = $prod->id;
                    $eventProducts->save();
                }
            }
        }
        return Products::where('vendor_id', $vendor_id)->get();
    }

    private function existApplication($company_registration)
    {
        $data = EventApplications::where('registration', $company_registration)->where('status', 0)->first();
        return $data;
    }

    private function generateCode($n)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }
}
