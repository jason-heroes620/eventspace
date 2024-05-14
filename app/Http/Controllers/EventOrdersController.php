<?php

namespace App\Http\Controllers;

use App\Models\EventOrderProducts;
use App\Models\EventOrderPaymentDetails;
use App\Models\EventOrders;
use App\Models\EventOrderPaymentHistory;
use App\Models\PaymentLogs;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class EventOrdersController extends Controller
{
    public function viewOrder(Request $req)
    {
        if (isset($product)) {
        }
    }

    public function orders(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $data = $this->addOrder($req->post());
            $resp = config("custom.cart_order_redirect_host") . "/orders/" . $data['order_id'] . '/code/' . $data['order_code'];
            return $this->sendResponse($resp, 200);
        } else if ($_SERVER['REQUEST_METHOD'] === "GET") {
            if (isset($req->id) && isset($req->code)) {
                $data = $this->getOrderById($req->id, $req->code);
                if ($data) {
                    return $this->sendResponse($data, 200);
                } else {
                    return $this->sendResponse(null, 200);
                }
            } else {
                return $this->sendResponse(null, 200);
            }
        } else {
            return $this->sendError('', ['error' => 'Allowed headers POST, GET'], 405);
        }
    }

    private function addOrder($order)
    {
        $o = new EventOrders;
        $o->total = $order['total'];
        $o->order_code = $order_code = $this->getOrderCode(8);

        $o->save();
        $orderId  = $o->id;

        foreach ($order['carts'] as $order) {
            $item = new EventOrderProducts;
            $item->event_order_id = $orderId;
            $item->product_id = $order['productId'];
            $item->product_name = $order['product'];
            $item->quantity = $order['qty'];
            $item->price = $order['price'];
            $item->total = $order['subTotal'];

            $item->save();
        }

        return ['order_id' => $orderId, 'order_code' => $order_code];
    }

    private function getOrderById($id, $code)
    {
        $order = EventOrders::where('id', $id)->where('order_code', $code)->first();
        if ($order) {
            $order_products = EventOrderProducts::where('event_order_id', $order->id)->get();
            return ['order' => $order, 'order_products' => $order_products];
        } else {
            return null;
        }
    }

    private function getOrderCodeById($id)
    {
        $order = EventOrders::where('id', $id)->first();
        if ($order) {
            return $order->order_code;
        } else {
            return null;
        }
    }

    private function getOrderCode($n)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
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

        $payment_detail = new EventOrderPaymentDetails;
        $payment_history = new EventOrderPaymentHistory;

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

        $order_code = $this->getOrderCodeById($OrderNumber);
        $payment_detail->event_order_id = $OrderNumber;
        $payment_detail->payment_ref = $PaymentID;
        $payment_detail->payment_method = $PymtMethod;
        $payment_detail->issuing_bank = $IssuingBank;
        $payment_detail->bank_ref = $BankRefNo;
        $payment_detail->save();

        // insert payment_history
        $payment_history->event_order_id = $OrderNumber;
        $payment_history->save();

        // update event_payment status
        DB::table('event_orders')
            ->updateOrInsert(
                [
                    'id' => $OrderNumber
                ],
                ['status' => $payment_status]
            );
        $domain = config("custom.cart_order_redirect_host") . "/paymentSummary/" . $OrderNumber . "/code/" . $order_code;

        if ($TxnStatus == 0 && !$this->checkIfPaymentIdExists($PaymentID)) {
            try {
                // $this->handlePaymentEmails($OrderNumber);
                // $this->handlePaymentNotification($OrderNumber);

                // Notes: PaymentID from payment gateway as the payment id in PaymentLogs
                // search Event Order Payment Details 'payment_ref' to link the 'payment_id' in Payment Logs
                $payment_log = new PaymentLogs;
                $payment_log->payment_ref = $PaymentID;
                $payment_log->save();
            } catch (Throwable $ex) {
                Log::error($ex);
            }
        }

        // deduct qty from stock
        try {
            $this->deductQtyFromStock($OrderNumber);
        } catch (Throwable $ex) {
            Log::error($ex);
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

    private function deductQtyFromStock($order_id)
    {
        $event_products = DB::table('event_order_products')->where('event_order_id', $order_id)->get();

        foreach ($event_products as $product) {
            $item = Products::find($product->product_id);
            $item->stock = $item->stock - $product->quantity;
            $item->save();
        }
    }
}
