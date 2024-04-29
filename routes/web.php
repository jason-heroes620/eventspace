<?php

use Illuminate\Support\Facades\Route;
use App\Mail\PaymentReceived;
use App\Mail\PaymentNotification;

use App\Models\Events;
use App\Models\EventCategories;
use App\Models\EventPayments;
use App\Models\EventBooth;
use App\Models\PaymentEntryError;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventApplicationsController;
use App\Models\EventApplications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\ApplicationError;

Route::get('/', function () {
    return view('login');
});

Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerPost'])->name('register');

    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginPost'])->name('login');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/applications', [EventApplicationsController::class, 'index'])->name('applications');
    Route::get('/applications/{id}', [EventApplicationsController::class, 'index'])->name('application-detail');
    Route::delete('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/applications/{id}', [EventApplicationsController::class, 'updateStatus'])->name('updateStatus');
});


Route::get('/send-mail', function () {

    // $event = App\Models\Events::find(1);
    // $payment = App\Models\EventPayments::find(8);

    // return (new PaymentReceived($event, $payment))->render();

    $order_id = 16;
    $payment_info = EventPayments::where('id', $order_id)->first();

    $event = Events::where('id', $payment_info->event_id)
        ->first();

    Mail::to($payment_info->email)
        ->send(new PaymentReceived($event, $payment_info));
});

Route::get('/notification-mail', function () {

    // $event = App\Models\Events::find(1);
    // $payment = App\Models\EventPayments::find(8);

    // return (new PaymentNotification($event, $payment))->render();

    $order_id = 16;
    $payment_info = EventPayments::where('id', $order_id)->first();

    $event = Events::where('id', $payment_info->event_id)
        ->first();

    try {
        Mail::to('abc@eff.ccdvb')
            ->send(new PaymentNotification($event, $payment_info));
    } catch (Throwable $ex) {
        Log::error($ex);
    }
    echo 'Test';
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

    $token = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjM0ODA5NDQzMCwiYWFpIjoxMSwidWlkIjoyNTk3MzUyMSwiaWFkIjoiMjAyNC0wNC0xN1QwNDowODo1MC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MTA0MzIzNTUsInJnbiI6InVzZTEifQ.-HHtAXfVR46gAFuic8jMK5DLB2CMone00q8qZ6ydlGE';
    $apiUrl = 'https://api.monday.com/v2';

    $query = 'mutation ($item_name:String!, $columnVals: JSON!){ create_item (board_id: 6461771278, group_id: "topics", item_name: $item_name, column_values: $columnVals) { id } }';
    $vals = [
        "item_name" => $application->organization,
        "columnVals" => json_encode(
            [
                "status" => ["label" => "Pending"],
                "date4" => ['date' => date('Y-m-d', strtotime($application->created)), 'time' => date('H:i:s', strtotime($application->created))],
                "product_category__1" => ["ids" => $categories],
                "text" => $application->contact_person,
                "phone" => ["phone" => $application->contact_no, "countryShortName" => "MY"],
                "email" => ["email" => $application->email, "text" => $application->email],
                "text1" => $application->organization,
                "text9" => $application->registration,
                "text__1" => $application->social_media_account,
                "numbers5" => $application->participants,
                "numbers3" => $application->booth_qty,
                "text98" => $application->description,
                "label6__1" => ["index" => $booth->monday_booth_id],
                "checkbox__1" => $application->plug == 'Y' ? ["checked" => "true"] : ["checked" => "false"]
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
