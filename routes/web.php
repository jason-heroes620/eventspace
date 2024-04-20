<?php

use Illuminate\Support\Facades\Route;
use App\Mail\PaymentReceived;
use App\Mail\PaymentNotification;

use App\Models\Events;
use App\Models\EventPayments;
use App\Models\EventBooth;
use App\Models\PaymentEntryError;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return view('welcome');
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

        Mail::to('jason820620@gmail.com')
        ->send(new PaymentNotification($event, $payment_info));
});

Route::get('/testHandleMondayMutation/{id}', function(string $order_id) {
        // $order_id = 16;
        $payment = EventPayments::where('id', $order_id)->first();
        $event = Events::where('id', $payment->event_id)->first();
        $payment_categories = DB::table('event_payments')
                                ->leftJoin('payment_categories', 'payment_categories.payment_id', '=', 'event_payments.id')
                                ->leftJoin('categories', 'payment_categories.category_id', '=', 'categories.id')
                                ->where('event_payments.id', $order_id)
                                ->get(['categories.category']);
        $categories = [];
        foreach($payment_categories as $cat) {
            $categories[] = EventCategories::where('event_id', $payment->event_id)->where('category_id', $cat->category)->first(['monday_category_id']);
        }
        
        $event_booths = DB::table("event_payments")
                       ->leftJoin("booths", "event_payments.booth_id", '=', "booths.id")
                       ->where("event_payments.id", $order_id)
                       ->first(["booths.id"]);
        $booth = EventBooth::where("event_id", $payment->event_id)->where('booth_id', $event_booths->id)->first();

        $token = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjM0ODA5NDQzMCwiYWFpIjoxMSwidWlkIjoyNTk3MzUyMSwiaWFkIjoiMjAyNC0wNC0xN1QwNDowODo1MC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MTA0MzIzNTUsInJnbiI6InVzZTEifQ.-HHtAXfVR46gAFuic8jMK5DLB2CMone00q8qZ6ydlGE';
        $apiUrl = 'https://api.monday.com/v2';
        $headers = ['Content-Type: application/json', 'Authorization: ' . $token];

        $query = 'mutation ($item_name:String!, $columnVals: JSON!){ create_item (board_id: 6461771278, group_id: "topics", item_name: $item_name, column_values: $columnVals) { id } }';
        $vals = [
            "item_name" => $payment->organization,
            "columnVals" => json_encode(
            [
            "status" => ["label" => "Payment Received"],
            "date4" => ['date' => date('Y-m-d', strtotime($payment->created)), 'time' =>date('H:i:s', strtotime($payment->created))],
            "product_category__1" => ["labels" => $categories],
            "text" => $payment->contact_person,
            "phone" => ["phone" => $payment->contact_no, "countryShortName" => "MY"],
            "email" => ["email" => $payment->email, "text" => $payment->email],
            "text1" => $payment->organization,
            "text9" => $payment->registration,
            "text__1" => $payment->social_media_account,
            "numbers5" => $payment->participants,
            "numbers3" => $payment->booth_qty,
            "text98" => $payment->description,
            "label6__1" => ["index" => $booth->monday_booth_id],
            "checkbox__1" => $payment->plug == 'Y' ? ["checked" => "true"] : ["checked" => "false"] 
            // "checkbox__1" => $payment->plug == 'Y' ? "true" : "false"
        ])
        ];

        try{
            $data = @file_get_contents($apiUrl, false, stream_context_create([
            'http' => [
            'method' => 'POST',
            'header' => $headers,
            'content' => json_encode(['query' => $query, 'variables' => $vals]),
            ]
            ]));
            $responseContent = json_decode($data, true);

            if(array_key_exists('error_message', $responseContent)) {
                $error = new PaymentEntryError();

                $error->payment_id = $order_id;
                $error->error = $responseContent['error_message'];
                $error->save();
            }
        } catch(Exception $ex) {
            echo $ex;
        }

        echo json_encode($responseContent);
});