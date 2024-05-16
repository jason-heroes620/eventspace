<?php

use Illuminate\Support\Facades\Route;
use App\Mail\PaymentReceived;
use App\Mail\PaymentNotification;
use App\Mail\ApplicationReceived;

use App\Models\Booths;
use App\Models\Events;
use App\Models\EventCategories;
use App\Models\EventPayments;
use App\Models\EventBooth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventApplicationsController;
use App\Http\Controllers\EventOrdersController;
use App\Http\Controllers\EventProductsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\VendorsController;
use App\Models\EventApplications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\ApplicationError;
use App\Models\ResponseEmailList;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\ImageManager;


use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\SalesReportController;
use App\Models\Products;




Route::get('/', function () {
    return view('login');
});

Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerPost'])->name('register');

    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginPost'])->name('login');

    Route::get('/vendors', [VendorsController::class, 'index'])->name('vendors');
    Route::get('/vendor/{id}', [VendorsController::class, 'index'])->name('vendor');
    Route::post('/vendor/{id}', [VendorsController::class, 'index'])->name('vendor');
    Route::post('/vendors', [VendorsController::class, 'index'])->name('vendors');

    Route::get('/eventproducts', [EventProductsController::class, 'eventproducts'])->name('eventproducts');
    Route::post('/eventproducts', [EventProductsController::class, 'eventproducts'])->name('eventproducts');

    Route::get('products', [ProductsController::class, 'index'])->name('products');
    Route::get('products/{id}', [ProductsController::class, 'index'])->name('product-detail');
    Route::get('products/event/{event}', [ProductsController::class, 'index'])->name('products-event');
    Route::get('products/event/{event}/vendor/{vendor}', [ProductsController::class, 'index'])->name('products-event-vendor');
    Route::get('products/vendor/{vendor}', [ProductsController::class, 'index'])->name('products-vendor');

    Route::post('products', [ProductsController::class, 'index']);

    Route::get('orders', [EventOrdersController::class, 'orders']);

    Route::get('/upload', [ExcelImportController::class, 'showUploadForm'])->name('excel.uploadform');
    Route::post('/import', [ExcelImportController::class, 'import'])->name('excel.import');

    Route::get('/salesreport', [SalesReportController::class, 'salesreport'])->name('salesreport');
    Route::post('/salesreport', [SalesReportController::class, 'salesreport'])->name('salesreport');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/applications', [EventApplicationsController::class, 'index'])->name('applications');
    Route::get('/applications/event/{eventId}', [EventApplicationsController::class, 'index'])->name('event-applications');
    Route::get('/applications/{id}', [EventApplicationsController::class, 'index'])->name('application-detail');
    Route::delete('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/applications/{id}', [EventApplicationsController::class, 'updateStatus'])->name('updateStatus');

    Route::get('/dailysales', [SalesReportController::class, 'dailysales'])->name('dailysales');
    Route::post('/dailysales', [SalesReportController::class, 'dailysales'])->name('dailysales');

    Route::get('/vendorsales', [SalesReportController::class, 'vendorsales'])->name('vendorsales');


    Route::view('/{any?}', 'dashboard')->where('any', '.*');
});

Route::get('/test-image', function () {
    $manager = new ImageManager(
        new Intervention\Image\Drivers\Gd\Driver()
    );
    $products = DB::table('products')->where('compressed_product_image', null)->where('product_image', '!=', '')->orderBy('product_name')->get();

    foreach ($products as $product) {
        // dd($product);
        print_r($product->product_name . '<br />');
        $image = asset('storage') . '/img/' . $product->product_image;
        $image_name = explode('/', $image);
        //dd($image_name);
        // dd($image_name);
        $path = '/public/img/' . $image_name[sizeof($image_name) - 2] . '/compressed/';
        // dd($path);
        try {
            if (!Storage::exists($path)) {
                Storage::makeDirectory($path);
            } else {
                print_r('path exist' . '<br />');
            }
        } catch (Exception $ex) {
            dd($ex);
        }

        $imageM = $manager->read(public_path() . '/storage/img/' . $image_name[sizeof($image_name) - 2] . '/' . $image_name[sizeof($image_name) - 1]);
        //$new_path = $path . 'compressed_' . $image_name[sizeof($image_name) - 1];
        // dd(public_path());
        $imageM->resize(300, 200, function ($const) {
            $const->aspectRatio();
        })->save(public_path() . '/storage/img/' . $image_name[sizeof($image_name) - 2] . '/compressed/compressed_' . $image_name[sizeof($image_name) - 1]);

        DB::table('products')
            ->where('id', $product->id)
            ->update(['compressed_product_image' => $image_name[sizeof($image_name) - 2] . '/compressed/' . 'compressed_' . $image_name[sizeof($image_name) - 1]]);
    }
});

Route::get('/test-mail', function () {

    // $event = App\Models\Events::find(1);
    // $payment = App\Models\EventPayments::find(8);

    // return (new PaymentReceived($event, $payment))->render();

    $application_id = 47;

    $application = EventApplications::where('id', $application_id)
        ->first();
    $event = Events::where('id', $application->event_id)->first()
        ->first();
    $email_list = ResponseEmailList::where('response_email_type', 'TE')->get();
    echo config("custom.payment_redirect_host");

    try {
        Mail::to($email_list)
            ->send(new ApplicationReceived($event, $application));
    } catch (Throwable $ex) {
        Log::error($ex);
    }
});

Route::get('/send-mail', function () {

    // $event = App\Models\Events::find(1);
    // $payment = App\Models\EventPayments::find(8);

    // return (new PaymentReceived($event, $payment))->render();

    $order_id = 16;
    $payment_info = EventPayments::where('id', $order_id)->first();

    $application = EventApplications::where('id', $payment_info->application_id)
        ->first();
    $event = Events::where('id', $application->event_id)->first();
    $booth = Booths::where('id', $application->booth_id)
        ->first();

    try {
        Mail::to($application->email)
            ->send(new PaymentReceived($event, $application, $booth));
    } catch (Throwable $ex) {
        Log::error($ex);
    }
});

Route::get('/notification-mail', function () {

    // $event = App\Models\Events::find(1);
    // $payment = App\Models\EventPayments::find(8);

    // return (new PaymentNotification($event, $payment))->render();

    $order_id = 16;
    $payment_info = EventPayments::where('id', $order_id)->first();

    $application = EventApplications::where('id', $payment_info->application_id)
        ->first();
    $event = Events::where('id', $application->event_id)->first();


    try {
        Mail::to('purchases@heroes.my')
            ->send(new PaymentNotification($event, $application, $payment_info));
    } catch (Throwable $ex) {
        Log::error($ex);
    }
});

Route::get('/testHandleMondayMutation/{id}', function (string $application_id) {
    $application = EventApplications::where('id', $application_id)->first();
    $application_categories = DB::table('event_applications')
        ->leftJoin('application_categories', 'application_categories.application_id', '=', 'event_applications.id')
        ->leftJoin('categories', 'application_categories.category_id', '=', 'categories.id')
        ->where('event_applications.id', $application_id)
        ->get(['categories.id']);
    $categories = [];
    foreach ($application_categories as $cat) {
        $id = EventCategories::where('event_id', $application->event_id)->where('category_id', $cat->id)->first(['monday_category_id']);
        $categories[] = $id->monday_category_id;
    }

    $event_booths = DB::table("event_applications")
        ->leftJoin("booths", "event_applications.booth_id", '=', "booths.id")
        ->where("event_applications.id", $application_id)
        ->first(["booths.id"]);
    $booth = EventBooth::where("event_id", $application->event_id)->where('booth_id', $event_booths->id)->first();
    $event = Events::where('id', $application->event_id)->first();

    $token = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjM0ODA5NDQzMCwiYWFpIjoxMSwidWlkIjoyNTk3MzUyMSwiaWFkIjoiMjAyNC0wNC0xN1QwNDowODo1MC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MTA0MzIzNTUsInJnbiI6InVzZTEifQ.-HHtAXfVR46gAFuic8jMK5DLB2CMone00q8qZ6ydlGE';
    $apiUrl = 'https://api.monday.com/v2';

    $query = 'mutation ($item_name:String!, $columnVals: JSON!){ create_item (board_id: 6461771278, group_id: "topics", item_name: $item_name, column_values: $columnVals) { id } }';
    $date = new DateTime($application->created);
    $date->setTimezone(new DateTimeZone('UTC'));
    $vals = [
        "item_name" => $application->organization,
        "columnVals" => json_encode(
            [
                "status" => ["label" => "Pending"],
                "date4" => ['date' => $date->format('Y-m-d'), 'time' => $date->format('H:i:s')],
                "product_category__1" => ["ids" => $categories],
                "text" => $application->contact_person,
                "phone" => ["phone" => $application->contact_no, "countryShortName" => "MY"],
                "email" => ["email" => $application->email, "text" => $application->email],
                "text1" => $application->organization,
                "text9" => $application->registration,
                "text__1" => $application->social_media_account,
                "text3__1" => $event->event_location,
                "event_date__1" => $event->event_date,
                "event_time__1" => $event->event_time,
                "numbers5" => $application->participants,
                "numbers3" => $application->booth_qty,
                "text98" => $application->description,
                "label6__1" => ["index" => $booth->monday_booth_id],
                "dropdown8__1" => $application->plug == 'Y' ? ["ids" => [1]] : ["ids" => [2]]
            ]
        )
    ];

    try {
        $guzzleClient = new Client(array('headers' => array('Content-Type' => 'application/json', 'Authorization' => $token)));
        $responseContent = $guzzleClient->post($apiUrl, ['body' =>  json_encode(['query' => $query, 'variables' => $vals])]);


        // $data = @file_get_contents($apiUrl, false, stream_context_create([
        //     'http' => [
        //         'method' => 'POST',
        //         'header' => $headers,
        //         'content' => json_encode(['query' => $query, 'variables' => $vals]),
        //     ]
        // ]));
        // $responseContent = json_decode($data, true);


        // if(array_key_exists('error_message', $responseContent) || $responseContent == null) {
        //     $error = new PaymentEntryError();

        //     $error->payment_id = $order_id;
        //     $error->error = array_key_exists('error_message', $responseContent) ? $responseContent['error_message'] : null;
        //     $error->save();
        // }
        // $data = json_decode($responseContent->getBody());
        // if (!empty($data->error_message)) {
        //     echo $data->error_message;
        // }

        $data = json_decode($responseContent->getBody()->getContents());
        if (isset($data->error_message)) {
            $error = new ApplicationError();
            $error->application_id = $application->id;
            $error->error_message = $data->error_message;
            $error->save();
        } else {
            $id = $data->data->create_item->id;
            DB::table('event_applications')
                ->updateOrInsert(
                    [
                        'id' => $application_id
                    ],
                    ['monday_id' => $id]
                );
        }
        // $data = $responseContent->getBody();
        // echo $data->data->create_item->id;
    } catch (Exception $ex) {
        echo $ex;
    }
});

Route::get("/random", function () {
    $n = 6;
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    echo $randomString;
});
