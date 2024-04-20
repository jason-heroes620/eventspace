<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Models\EventPayments;
use App\Models\PaymentCategories;
use App\Models\PaymentDetail;
use App\Models\PaymentHistory;
use App\Models\Events;
use App\Models\EventBooth;
use App\Models\PaymentEntryError;
use App\Models\EventCategories;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentReceived;
use App\Mail\PaymentNotification;

class EventPaymentController extends Controller
{
    //
    public function payment(Request $req) {
         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $this->addPayment($req->post());
            return $this->sendResponse(['paymentId' => $id], 200);
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $this->getPayment($req->id);
            return $this->sendResponse($data, 200);
        } else {
            return $this->sendError('', ['error' => 'Allowed headers POST, GET'], 405);
        }
    }

    private function addPayment($payment) {
        $info = new EventPayments;

        $info->event_id = $payment['eventId'];
        $info->contact_person = $payment['contactPerson'];
        $info->contact_no = $payment['contactNo'];
        $info->email = $payment['email'];
        $info->organization = $payment['organization'];
        $info->registration = $payment['companyRegistration'];
        $info->participants = $payment['participants'];
        $info->social_media_account = empty($payment['socialMediaAccount']) ? '' : $payment['socialMediaAccount'];
        $info->description = $payment['productDescription'];
        $info->requirements = empty($payment['requirements']) ? '' : $payment['requirements'];
        $info->plug = $payment['plugPoints'] == 'Yes' ? 'Y' : 'N';
        $info->booth_id = $payment['boothId'];
        $info->booth_qty = $payment['noOfBooth'];
        $info->no_of_days = $payment['noOfDays'];
        $info->payment = $payment['total'];
        $info->payment_id = $payment['paymentId'];

        $info->save();
        $id = $info->id;

        foreach($payment['categoryId'] as $cat) {
            $payment_categories = new PaymentCategories;
            $payment_categories->payment_id = $id;
            $payment_categories->category_id = $cat;
            
            $payment_categories->save();
        }

        return $id;
    }

    public function eghlpaymentcallback(Request $req) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
            if(!empty($req->post())) {
                $this->paymentCallback($req->post());
            } else {
                $payment['TransactionType'] = $req->TransactionType;
                $payment['PaymentID']       = $req->PaymentID;
                $payment['ServiceID']       = $req->ServiceID;
                $payment['OrderNumber']     = $req->OrderNumber;
                $payment['Amount']          = $req->Amount;
                $payment['CurrencyCode']    = $req->CurrencyCode;
                $payment['TxnID']           = $req->TxnID;
                $payment['PymtMethod']      = $req->PymtMethod;
                $payment['TxnStatus']       = $req->TxnStatus;
                $payment['AuthCode']        = $req->AuthCode;
                $payment['TxnMessage']      = $req->TxnMessage;
                $payment['IssuingBank']     = $req->IssuingBank;
                $payment['HashValue']       = $req->HashValue;
                $payment['HashValue2']      = $req->HashValue2;
                $payment['BankRefNo']       = $req->BankRefNo;

                $this->paymentCallback($payment);
            }
        } else {
            return $this->sendError('', ['error' => 'Allowed headers POST'], 405);
        }
    }

    private function paymentCallback($payment) {
        $TransactionType = $payment['TransactionType'];
        $PaymentID = $payment['PaymentID'];
        $ServiceID = $payment['ServiceID'];
        $OrderNumber = $payment['OrderNumber'];
        $Amount = $payment['Amount'];
        $CurrencyCode = $payment['CurrencyCode'];
        $TxnID = !empty($payment['TxnID']) ? $payment['TxnID'] : '';
        $PymtMethod = $payment['PymtMethod'];
        $TxnStatus = $payment['TxnStatus'];
        $AuthCode = (!empty($payment['AuthCode'])) ? $payment['AuthCode'] : "";
        $TxnMessage = $payment['TxnMessage'];
        $IssuingBank = (!empty($payment['IssuingBank'])) ? $payment['IssuingBank'] : "";
        $HashValue = $payment['HashValue'];
        $HashValue2 = $payment['HashValue2'];
        $BankRefNo = !empty($payment['BankRefNo']) ? $payment['BankRefNo'] : '';

        $payment_detail = new PaymentDetail;
        $payment_history = new PaymentHistory;

        $payment_status = 1;

        if($TxnStatus == 0) {
            $payment_detail->status = $payment_history->status = $payment_status = 2;
            $payment_history->payment_description = "Successful payment (eGHL Payment) $CurrencyCode $Amount [BankRefNo: $BankRefNo] [TxnStatus: $TxnStatus] [Payment method: $PymtMethod] [Issuing Bank: $IssuingBank]";
        } else if($TxnStatus == 1) {
             if($TxnMessage == "Buyer Cancelled") {
                $payment_detail->status = $payment_history->status = $payment_status = 3;
                $payment_history->payment_description = "Payment Cancelled by Shopper(eGHL Response)";
             } else {
                $payment_detail->status = $payment_history->status = $payment_status = 4;
                $payment_history->payment_description = "Failed Payment (eGHL Response) $CurrencyCode $Amount [BankRefNo: $BankRefNo] [TxnStatus:$TxnStatus] [Payment method:$PymtMethod]";
             }
        } else if($TxnStatus == 2) {
            $payment_detail->status = $payment_history->status = $payment_status = 1;
            $payment_history->payment_description = "Pending Payment (eGHL Response) $CurrencyCode $Amount [BankRefNo: $BankRefNo] [TxnStatus:$TxnStatus] [Payment method:$PymtMethod]";
        }

        $payment_detail->payment_id = $OrderNumber;
        $payment_detail->payment_ref = $PaymentID;
        $payment_detail->payment_method = $PymtMethod;
        $payment_detail->issuing_bank = $IssuingBank;
        $payment_detail->bank_ref = $BankRefNo;
        $payment_detail->save();

        // insert payment_history
        $payment_history->payment_id = $OrderNumber;
        $payment_history->payment_description = $TxnMessage;
        $payment_history->save();
        
        // update event_payment status
        DB::table('event_payments')
                ->updateOrInsert(
                    [
                        'id' => $OrderNumber
                    ],
                    ['status' => $payment_status]
                );
        $domain = "https://event-payment.heroes.my/paymentSummary/".$OrderNumber."/status/".$payment_status;        

        if($TxnStatus == 0) {
            $this->handlePaymentEmails($OrderNumber);
            $this->handlePaymentNotification($OrderNumber);
            $this->handleMondayMutation($OrderNumber);
        }

        return redirect()->away($domain)->send();
    }

    private function getPayment($id) {
         $query = DB::table('event_payments')
         ->leftJoin('events', 'events.id', '=', 'event_payments.event_id')
         ->where('event_payments.id', '=', $id);
         
         return $query->get(['event_payments.id', 'event_payments.payment', 'event_payments.contact_person', 'event_payments.email', 'events.event_name']);
    }

    private function handlePaymentEmails($order_id) {
        $payment_info = EventPayments::where('id', $order_id)->first();

        $event = Events::where('id', $payment_info->event_id)
                 ->first();

        Mail::to($payment_info->email)
        ->send(new PaymentReceived($event, $payment_info));
    }

    private function handlePaymentNotification($order_id) {
        $payment_info = EventPayments::where('id', $order_id)->first();

        $event = Events::where('id', $payment_info->event_id)
                 ->first();

        Mail::to('purchases@heroes.my')
        ->send(new PaymentNotification($event, $payment_info));
    }

    private function handleMondayMutation($order_id) {
        $payment = EventPayments::where('id', $order_id)->first();
        $event = Events::where('id', $payment->event_id)->first();
        $payment_categories = DB::table('event_payments')
                                ->leftJoin('payment_categories', 'payment_categories.payment_id', '=', 'event_payments.id')
                                ->leftJoin('categories', 'payment_categories.category_id', '=', 'categories.id')
                                ->where('event_payments.id', $order_id)
                                ->get(['categories.id']);
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
        } catch(error) {
           
        }
    }
}
