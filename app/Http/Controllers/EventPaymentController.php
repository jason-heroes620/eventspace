<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Models\EventPayments;
use App\Models\PaymentCategories;
use App\Models\PaymentDetail;
use App\Models\PaymentHistory;

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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!empty($req->post())) {
                $this->paymentCallback($req->post());
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
        $TxnID = $payment['TxnID'];
        $PymtMethod = $payment['PymtMethod'];
        $TxnStatus = $payment['TxnStatus'];
        $AuthCode = (!empty($payment['AuthCode'])) ? $payment['AuthCode'] : "";
        $TxnMessage = $payment['TxnMessage'];
        $IssuingBank = (!empty($payment['IssuingBank'])) ? $payment['IssuingBank'] : "";
        $HashValue = $payment['HashValue'];
        $HashValue2 = $payment['HashValue2'];
        $BankRefNo = $payment['BankRefNo'];

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
        $domain = "https://event-payment.heroes.my/paymentSummary/".$OrderNumber."/".$payment_status;        
        // return Redirect::to($domain);
        return redirect()->away($domain);
    }

    private function getPayment($id) {
         $query = DB::table('event_payments')
         ->leftJoin('events', 'events.id', '=', 'event_payments.event_id')
         ->where('event_payments.id', '=', $id);
         
         return $query->get(['event_payments.id', 'event_payments.payment', 'event_payments.contact_person', 'event_payments.email', 'events.event_name']);
    }
}
