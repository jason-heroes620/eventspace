<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Models\EventPayments;
use App\Models\ResponseEmailList;
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
use App\Models\EventApplicationGroup;
use App\Models\EventApplications;
use App\Models\EventDeposit;
use App\Models\EventGroups;
use App\Models\EventPaymentReference;
use Exception;
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
            $data = $this->getPaymentV2($req->id);
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
                $data = $this->getPaymentByCodeV2($req->id, $req->code);

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

        if (!empty($payment)) {
            $application = EventApplications::where('id', $payment->application_id)
                ->where('application_code', $code)
                ->where('status', 'A')
                ->first();

            $booth = Booths::where('id', $application->booth_id)
                ->first();

            $event = Events::where('id', $application->event_id)->first();
            return ['payment' => $payment, 'application' => $application, 'event' => $event, 'booth' => $booth];
        } else {
            return null;
        }
    }

    private function getPaymentByCodeV2($id, $code)
    {
        $payment = EventPayments::where('id', $id)
            ->where('application_code', $code)
            ->first();


        if (!empty($payment)) {
            $application = EventApplicationGroup::where('id', $payment->application_id)
                ->where('application_code', $code)
                ->where('status', 'A')
                ->first();

            $applicationEvent = EventApplications::where('event_group_id', $application->id)->get();
            foreach ($applicationEvent as $event) {
                $event['event'] = Events::where('id', $event->event_id)->first()->only('event_name', 'event_date');
                $event['booth'] = EventBooth::leftJoin('booths', 'booths.id', 'event_booth.booth_id')
                    ->where('event_booth.event_id', $event->event_id)
                    ->where('event_booth.booth_id', $event->booth_id)
                    ->first()->booth_type;
            }


            return ['payment' => $payment, 'application' => $application, 'applicationEvent' => $applicationEvent];
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
        $payment_history->save();

        // update event_payment status
        DB::table('event_payments')
            ->updateOrInsert(
                [
                    'id' => $OrderNumber
                ],
                ['status' => $payment_status]
            );
        $domain = config("custom.payment_redirect_host") . "/paymentSummary/" . $OrderNumber . "/status/" . $payment_status;

        if ($TxnStatus == 0 && !$this->checkIfPaymentIdExists($PaymentID)) {
            try {
                $this->handlePaymentEmails($OrderNumber);
                $this->handlePaymentNotification($OrderNumber);
                // $this->handleMondayMutation($OrderNumber);

                $payment_log = new PaymentLogs;
                $payment_log->payment_ref = $PaymentID;
                $payment_log->save();
            } catch (Throwable $ex) {
                Log::error($ex);
            }
        }

        return redirect()->away($domain)->send();
    }

    private function checkIfPaymentIdExists($payment_id)
    {
        $payment = PaymentLogs::where('payment_ref', $payment_id)->get()->count();
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

    private function getPaymentV2($id)
    {
        $payment = EventPayments::where('id', $id)
            ->first();

        $application = EventApplicationGroup::where('id', $payment->application_id)
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
        // to vendors
        $payment_info = EventPayments::where('id', $order_id)->first();

        $application = EventApplications::where('id', $payment_info->application_id)
            ->first();
        $event = Events::where('id', $application->event_id)->first();
        $booth = Booths::where('id', $application->booth_id)
            ->first();

        try {
            Mail::to($application->email)
                ->later(now()->addMinutes(5), new PaymentReceived($event, $application, $booth));
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
        $email_list = ResponseEmailList::where('response_email_type', 'PR')->get();
        try {
            Mail::to($email_list)
                ->later(now()->addMinutes(5), new PaymentNotification($event, $application, $payment_info));
        } catch (Throwable $ex) {
            Log::error($ex);
        }
    }

    // private function handleMondayMutation($order_id)
    // {
    //     $payment = EventPayments::where('id', $order_id)->first();
    //     $application = EventApplications::where('id', $payment->application_id)->first();
    //     $application_categories = DB::table('event_applications')
    //         ->leftJoin('application_categories', 'application_categories.application_id', '=', 'event_applications.id')
    //         ->leftJoin('categories', 'application_categories.category_id', '=', 'categories.id')
    //         ->where('event_applications.id', $payment->application_id)
    //         ->get(['categories.id']);
    //     $categories = [];
    //     foreach ($application_categories as $cat) {
    //         $id = EventCategories::where('event_id', $application->event_id)->where('category_id', $cat->id)->first(['monday_category_id']);
    //         $categories[] = $id->monday_category_id;
    //     }

    //     $event_booths = DB::table("event_payments")
    //         ->leftJoin('event_applications', 'event_applications.id', '=', 'event_payments.application_id')
    //         ->leftJoin("booths", "event_applications.booth_id", '=', "booths.id")
    //         ->where("event_payments.id", $order_id)
    //         ->first(["booths.id"]);
    //     $booth = EventBooth::where("event_id", $application->event_id)->where('booth_id', $event_booths->id)->first();

    //     $token = config('custom.monday_token');
    //     $apiUrl = 'https://api.monday.com/v2';

    //     $query = 'mutation ($item_name:String!, $columnVals: JSON!){ create_item (board_id: 6461771278, group_id: "topics", item_name: $item_name, column_values: $columnVals) { id } }';
    //     $vals = [
    //         "item_name" => $application->organization,
    //         "columnVals" => json_encode(
    //             [
    //                 "status" => ["label" => "Pending"],
    //                 "date4" => ['date' => date('Y-m-d', strtotime($payment->created)), 'time' => date('H:i:s', strtotime($payment->created))],
    //                 "product_category__1" => ["ids" => $categories],
    //                 "text" => $application->contact_person,
    //                 "phone" => ["phone" => $application->contact_no, "countryShortName" => "MY"],
    //                 "email" => ["email" => $application->email, "text" => $application->email],
    //                 "text1" => $application->organization,
    //                 "text9" => $application->registration,
    //                 "text__1" => $application->social_media_account,
    //                 "numbers5" => $application->participants,
    //                 "numbers3" => $application->booth_qty,
    //                 "text98" => $application->description,
    //                 "label6__1" => ["index" => $booth->monday_booth_id],
    //                 "checkbox__1" => $application->plug == 'Y' ? ["checked" => "true"] : ["checked" => "false"]
    //             ]
    //         )
    //     ];

    //     try {
    //         $guzzleClient = new Client(array('headers' => array('Content-Type' => 'application/json', 'Authorization' => $token)));
    //         $responseContent = $guzzleClient->post($apiUrl, ['body' =>  json_encode(['query' => $query, 'variables' => $vals])]);

    //         $data = json_decode($responseContent->getBody());
    //         if (!empty($data->error_message)) {
    //             $error = new PaymentEntryError();
    //             $error->payment_id = $order_id;
    //             $error->error = $data->error_message;
    //             $error->save();
    //         }
    //     } catch (Throwable $ex) {
    //         $error = new PaymentEntryError();

    //         $error->payment_id = $order_id;
    //         $error->error = $ex;
    //         $error->save();
    //     }
    // }

    public function paymentReference(Request $request, $code)
    {
        $application = EventApplications::select('id', 'event_id', 'organization', 'contact_person', 'contact_no', 'email', 'booth_qty', 'booth_id', 'no_of_days', 'discount_value', 'discount')
            ->where('application_code', $code)->first();

        $event = Events::select('event_name', 'event_date', 'require_deposit')
            ->where('id', $application->event_id)->first();

        $booth = EventBooth::select('price', 'booth_type')
            ->leftJoin('booths', 'booths.id', 'events_booths.booth_id')
            ->where('events_booths.id', $application->booth_id)->first();
        $payment = $application->no_of_days * $application->booth_qty * $booth->price;

        $deposit = null;

        if ($event->require_deposit === 'Y') {

            $deposit = EventDeposit::whereNull('end_date')->where('event_deposit_status', true)->where('start_date', '<=', date('Y-m-d'))->first();

            if ($deposit) {

                $payment += $deposit->event_deposit;
            }
        }
        if ($application->discount) {
            $payment -= $application->discount_value;
        }

        return response()->json(compact('application', 'booth', 'event', 'payment'));
    }

    public function paymentReferenceV2(Request $request, $code)
    {
        $applicationGroup = EventApplicationGroup::select('id', 'organization', 'contact_person', 'contact_no', 'email', 'discount_value', 'discount')
            ->where('application_code', $code)->first();
        $payment = EventPayments::where('application_code', $code)->first()->payment_total;

        return response()->json(compact('applicationGroup', 'payment'));
    }

    public function paymentReferenceUpdate(Request $request, $code)
    {
        $application = EventApplications::where('application_code', $code)->first();
        $event = Events::where('id', $application->event_id)->first();
        $file = "";

        $data = $request->all();
        if ($request->hasFile('reference')) {
            $file = $request->file('reference');
            $data['payment_reference'] = $file->store('payment_reference', 'public');
        }

        try {
            $payment = EventPayments::where('application_code', $code)->update(
                [
                    'payment_total' => $data['payment_total'],
                    'reference_no' => $data['reference_no'],
                    'payment_reference' => $data['payment_reference'],
                    'created' => date("Y-m-d H:i:s"),
                    'status' => 2,
                    'bank' => $data['bank'] ?? '',
                    'account_name' => $data['accountName'] ?? '',
                    'account_no' => $data['accountNo'] ?? '',
                    'bank_id' => $data['bankId'] ?? '',
                ]
            );


            $token = config('custom.monday_token');
            $apiUrl = 'https://api.monday.com/v2/file';

            $query = 'mutation ($file:File!){ add_file_to_column (item_id: ' . $application->monday_id . ', column_id: "files3__1", file: $file) { id } }';
            Log::info($query);

            $multipartData = [
                [

                    'name'     => 'variables[file]',
                    'contents' => fopen($file->getPathname(), 'r'), // Open the file as a stream
                    'filename' => $file->getClientOriginalName(), // Original filename
                    'Mime-Type' => $file->getMimeType(), // Mime type of the file
                ],
                [
                    'name' => 'query',
                    'contents' => $query
                ]
            ];
            try {
                $guzzleClient = new Client(array('headers' => array('Authorization' => $token)));
                $responseContent = $guzzleClient->post($apiUrl, ['multipart' => $multipartData]);

                $data = json_decode($responseContent->getBody());
                if (!empty($data->error_message)) {
                    $error = new PaymentEntryError();
                    $error->payment_id = $payment->id;
                    $error->error = $data->error_message;
                    $error->save();
                }
            } catch (Throwable $ex) {
                $error = new PaymentEntryError();

                $error->payment_id = $payment->id;
                $error->error = $ex;
                $error->save();
            }

            $payment_info = EventPayments::where('application_code', $code)->first();

            $application = EventApplications::where('id', $application->id)
                ->first();
            $event = Events::where('id', $application->event_id)->first();
            $email_list = ResponseEmailList::where('response_email_type', 'PR')->get();
            try {
                Mail::to($email_list)
                    ->later(now()->addMinutes(5), new PaymentNotification($event, $application, $payment_info));
            } catch (Throwable $ex) {
                Log::error($ex);
            }

            return response()->json(["success", "Payment reference has been received"], 200);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(["error", "Error saving payment reference, please try again."], 400);
        }
    }

    public function paymentReferenceUpdateV2(Request $request, $code)
    {
        $application = EventApplicationGroup::where('application_code', $code)->first();
        $file = "";

        $data = $request->all();
        if ($request->hasFile('reference')) {
            $file = $request->file('reference');
            $data['payment_reference'] = $file->store('payment_reference', 'public');
        }

        try {
            $payment = EventPayments::where('application_code', $code)->update(
                [
                    // 'payment_total' => $data['payment_total'],
                    // 'reference_no' => $data['reference_no'],
                    // 'payment_reference' => $data['payment_reference'],
                    'created' => date("Y-m-d H:i:s"),
                    'status' => 2,
                    // 'bank' => $data['bank'] ?? '',
                    // 'account_name' => $data['accountName'] ?? '',
                    // 'account_no' => $data['accountNo'] ?? '',
                    // 'bank_id' => $data['bankId'] ?? '',
                ]
            );

            EventPaymentReference::create([
                'application_code' => $code,
                'reference_no' => $data['reference_no'],
                'payment_reference' => $data['payment_reference'],
                'bank' => $data['bank'] ?? '',
                'account_name' => $data['accountName'] ?? '',
                'account_no' => $data['accountNo'] ?? '',
                'bank_id' => $data['bankId'] ?? '',
                'payment_amount' => $data['payment_total']
            ]);

            $token = config('custom.monday_token');
            $apiUrl = 'https://api.monday.com/v2/file';

            $applicationMondayId = EventApplications::select('monday_id')
                ->where('event_applications.event_application_group_id', $application->id)->get();
            foreach ($applicationMondayId as $id) {
                $query = 'mutation ($file:File!){ add_file_to_column (item_id: ' . $id->monday_id . ', column_id: "files3__1", file: $file) { id } }';
                Log::info($query);

                $multipartData = [
                    [
                        'name'     => 'variables[file]',
                        'contents' => fopen($file->getPathname(), 'r'), // Open the file as a stream
                        'filename' => $file->getClientOriginalName(), // Original filename
                        'Mime-Type' => $file->getMimeType(), // Mime type of the file
                    ],
                    [
                        'name' => 'query',
                        'contents' => $query
                    ]
                ];
                try {
                    $guzzleClient = new Client(array('headers' => array('Authorization' => $token)));
                    $responseContent = $guzzleClient->post($apiUrl, ['multipart' => $multipartData]);

                    $data = json_decode($responseContent->getBody());
                    if (!empty($data->error_message)) {
                        $error = new PaymentEntryError();
                        $error->payment_id = $payment->id;
                        $error->error = $data->error_message;
                        $error->save();
                    }
                } catch (Throwable $ex) {
                    $error = new PaymentEntryError();

                    $error->payment_id = $payment->id;
                    $error->error = $ex;
                    $error->save();
                }
            }

            $payment_info = EventPayments::where('application_code', $code)->first();

            $event = EventGroups::where('event_group_id', $application->event_group_id)->first();
            $email_list = ResponseEmailList::where('response_email_type', 'PR')->get();

            try {
                Mail::to($email_list)
                    ->later(now()->addMinutes(2), new PaymentNotification($event, $application, $payment_info));
            } catch (Throwable $ex) {
                Log::error($ex);
            }

            return response()->json(["success", "Payment reference has been received"], 200);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(["error", "Error saving payment reference, please try again."], 400);
        }
    }
}
