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
use App\Models\Booths;
use App\Models\EventBooth;
use App\Models\PaymentEntryError;
use App\Models\PaymentLogs;
use App\Models\EventCategories;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentReceived;
use App\Mail\PaymentNotification;
use App\Models\EventApplications;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Throwable;

class EventPaymentController extends Controller
{
    //
    public function payment(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $id = $this->addPayment($req->post());
            // return $this->sendResponse(['paymentId' => $id], 200);
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $this->getPayment($req->id);
            return $this->sendResponse($data, 200);
        } else {
            return $this->sendError('', ['error' => 'Allowed headers POST, GET'], 405);
        }
    }

    // private function addPayment($payment)
    // {
    //     $info = new EventPayments;

    //     $info->event_id = $payment['eventId'];
    //     $info->contact_person = $payment['contactPerson'];
    //     $info->contact_no = $payment['contactNo'];
    //     $info->email = $payment['email'];
    //     $info->organization = $payment['organization'];
    //     $info->registration = $payment['companyRegistration'];
    //     $info->participants = $payment['participants'];
    //     $info->social_media_account = empty($payment['socialMediaAccount']) ? '' : $payment['socialMediaAccount'];
    //     $info->description = $payment['productDescription'];
    //     $info->requirements = empty($payment['requirements']) ? '' : $payment['requirements'];
    //     $info->plug = $payment['plugPoints'] == 'Yes' ? 'Y' : 'N';
    //     $info->booth_id = $payment['boothId'];
    //     $info->booth_qty = $payment['noOfBooth'];
    //     $info->no_of_days = $payment['noOfDays'];
    //     $info->payment = $payment['total'];
    //     $info->payment_id = $payment['paymentId'];

    //     $info->save();
    //     $id = $info->id;

    //     foreach ($payment['categoryId'] as $cat) {
    //         $payment_categories = new PaymentCategories;
    //         $payment_categories->payment_id = $id;
    //         $payment_categories->category_id = $cat;

    //         $payment_categories->save();
    //     }

    //     return $id;
    // }

    public function paymentCode(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            if (isset($req->id) && isset($req->code)) {
                $data = $this->getPaymentByCode($req->id, $req->code);

                return $this->sendResponse($data, 200);
            } else {
                return $this->sendError('', ['error' => 'Missing required parameters'], 405);
            }
        } else {
            return $this->sendError('', ['error' => 'Allowed headers GET'], 405);
        }
    }

    private function getPaymentByCode($id, $code)
    {
        $payment = EventPayments::where('id', $id)
            ->where('application_code', $code)
            ->first();

        $application = EventApplications::where('id', $payment->application_id)
            ->where('application_code', $code)
            ->where('status', 'A')
            ->first();

        $booth = Booths::where('id', $application->booth_id)
            ->first();

        $event = Events::where('id', $application->event_id)->first();

        if (!empty($application)) {
            return ['payment' => $payment, 'application' => $application, 'event' => $event, 'booth' => $booth];
        } else {
            return null;
        }
    }

    public function eghlpaymentcallback(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!empty($req->post())) {
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

    private function paymentCallback($payment)
    {
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

        if ($TxnStatus == 0) {
            $payment_detail->status = $payment_history->status = $payment_status = 2;
            $payment_history->payment_description = "Successful payment (eGHL Payment) $CurrencyCode $Amount [BankRefNo: $BankRefNo] [TxnStatus: $TxnStatus] [Payment method: $PymtMethod] [Issuing Bank: $IssuingBank]";
        } else if ($TxnStatus == 1) {
            if ($TxnMessage == "Buyer Cancelled") {
                $payment_detail->status = $payment_history->status = $payment_status = 3;
                $payment_history->payment_description = "Payment Cancelled by Shopper(eGHL Response)";
            } else {
                $payment_detail->status = $payment_history->status = $payment_status = 4;
                $payment_history->payment_description = "Failed Payment (eGHL Response) $CurrencyCode $Amount [BankRefNo: $BankRefNo] [TxnStatus:$TxnStatus] [Payment method:$PymtMethod]";
            }
        } else if ($TxnStatus == 2) {
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
        $domain = "https://event-payment.heroes.my/paymentSummary/" . $OrderNumber . "/status/" . $payment_status;

        if ($TxnStatus == 0 && !$this->checkIfPaymentIdExists($PaymentID)) {
            try {
                $this->handlePaymentEmails($OrderNumber);
                $this->handlePaymentNotification($OrderNumber);
                $this->handleMondayMutation($OrderNumber);

                $payment_log = new PaymentLogs;
                $payment_log->payment_id = $PaymentID;
                $payment_log->save();
            } catch (Throwable $ex) {
                Log::error($ex);
            }
        }

        return redirect()->away($domain)->send();
    }

    private function checkIfPaymentIdExists($payment_id)
    {
        $payment = PaymentLogs::where('payment_id', $payment_id)->get()->count();
        if ($payment > 0) {
            return true;
        }
        return false;
    }

    private function getPayment($id)
    {
        $payment = EventPayments::where('id', $id)
            ->first();

        $application = EventApplications::where('id', $payment->application_id)
            ->where('status', 'A')
            ->first();

        if (!empty($application)) {
            $event = Events::where('id', $application->event_id)->first();

            $booth = Booths::where('id', $application->booth_id)
                ->first();

            return ['payment' => $payment, 'application' => $application, 'event' => $event, 'booth' => $booth];
        } else {
            return null;
        }
    }

    private function handlePaymentEmails($order_id)
    {
        $payment_info = EventPayments::where('id', $order_id)->first();

        $application = EventApplications::where('id', $payment_info->application_id)
            ->first();
        $event = Events::where('id', $application->event_id)->first();

        try {
            Mail::to($payment_info->email)
                ->send(new PaymentReceived($event, $payment_info));
        } catch (Throwable $ex) {
            Log::error($ex);
        }
    }

    private function handlePaymentNotification($order_id)
    {
        $payment_info = EventPayments::where('id', $order_id)->first();

        $application = EventApplications::where('id', $payment_info->application_id)
            ->first();
        $event = Events::where('id', $application->event_id)->first();

        try {
            Mail::to('purchases@heroes.my')
                ->send(new PaymentNotification($event, $payment_info));
        } catch (Throwable $ex) {
            Log::error($ex);
        }
    }

    private function handleMondayMutation($order_id)
    {
        $payment = EventPayments::where('id', $order_id)->first();
        $application = EventApplications::where('id', $payment->application_id)->first();
        $application_categories = DB::table('event_applications')
            ->leftJoin('application_categories', 'application_categories.application_id', '=', 'event_applications.id')
            ->leftJoin('categories', 'application_categories.category_id', '=', 'categories.id')
            ->where('event_applications.id', $payment->application_id)
            ->get(['categories.id']);
        $categories = [];
        foreach ($application_categories as $cat) {
            $id = EventCategories::where('event_id', $application->event_id)->where('category_id', $cat->id)->first(['monday_category_id']);
            $categories[] = $id->monday_category_id;
        }

        $event_booths = DB::table("event_payments")
            ->leftJoin('event_applications', 'event_applications.id', '=', 'event_payments.application_id')
            ->leftJoin("booths", "event_applications.booth_id", '=', "booths.id")
            ->where("event_payments.id", $order_id)
            ->first(["booths.id"]);
        $booth = EventBooth::where("event_id", $application->event_id)->where('booth_id', $event_booths->id)->first();

        $token = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjM0ODA5NDQzMCwiYWFpIjoxMSwidWlkIjoyNTk3MzUyMSwiaWFkIjoiMjAyNC0wNC0xN1QwNDowODo1MC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MTA0MzIzNTUsInJnbiI6InVzZTEifQ.-HHtAXfVR46gAFuic8jMK5DLB2CMone00q8qZ6ydlGE';
        $apiUrl = 'https://api.monday.com/v2';

        $query = 'mutation ($item_name:String!, $columnVals: JSON!){ create_item (board_id: 6461771278, group_id: "topics", item_name: $item_name, column_values: $columnVals) { id } }';
        $vals = [
            "item_name" => $application->organization,
            "columnVals" => json_encode(
                [
                    "status" => ["label" => "Payment Received"],
                    "date4" => ['date' => date('Y-m-d', strtotime($payment->created)), 'time' => date('H:i:s', strtotime($payment->created))],
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

            $data = json_decode($responseContent->getBody());
            if (!empty($data->error_message)) {
                $error = new PaymentEntryError();
                $error->payment_id = $order_id;
                $error->error = $data->error_message;
                $error->save();
            }
        } catch (Throwable $ex) {
            $error = new PaymentEntryError();

            $error->payment_id = $order_id;
            $error->error = $ex;
            $error->save();
        }
    }
}
