<?php

namespace App\Http\Controllers;

use App\Mail\RefundNotification;
use App\Models\EventApplicationGroup;
use App\Models\EventDepositRefund;
use App\Models\EventPaymentReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EventDepositRefundController extends Controller
{
    public function storeRefundFile(Request $request, $code)
    {
        $request->validate([
            'document' => 'required|mimes:pdf,jpg,jpeg,png,docx|max:2048',
        ]);

        // 2. Store the file in 'storage/app/public/uploads'
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('refund_upload', 'public');
            EventDepositRefund::create([
                'application_code' => $code,
                'refund_file' => $path,
                'refund_amount' => $request->input('refund_amount')
            ]);

            return back()->with('success', 'File uploaded successfully!');
        }

        return back()->withErrors(['document' => 'File upload failed.']);
    }

    public function sendRefundEmail($code)
    {
        $application = EventApplicationGroup::where('application_code', $code)->first();
        $payment = EventPaymentReference::where('application_code', $code)
            ->where('bank', "!=", null)
            ->first();
        $deposit = EventDepositRefund::where('application_code', $code)->first();
        $deposit_file = $deposit->refund_file;

        if ($application) {
            try {
                Mail::mailer('refund')
                    ->bcc(['felicia.n@heroes.my', 'jason.w@heroes.my'])
                    ->to($application->email)
                    ->later(now()->addMinutes(0), new RefundNotification($application, $payment, $deposit, $deposit_file));
            } catch (\Exception $e) {
                Log::error('Refund email failed: ' . $e->getMessage());
                return $this->sendResponse(['message' => 'Refund email failed.'], 405);
            }
        }
        return $this->sendResponse(['message' => 'Refund email sent successfully!'], 200);
    }
}
