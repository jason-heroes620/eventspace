<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApplicationCategories;
use App\Models\EventApplications;
use App\Models\EventBooth;
use App\Http\Controllers\ApplicationCategoriesController;
use App\Http\Controllers\EventBoothController;
use App\Models\EventPayments;
use App\Models\Events;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicationApprovedResponse;
use App\Mail\ApplicationRejectedResponse;
use App\Mail\ApplicationReceived;
use Throwable;
use GuzzleHttp\Client;
use App\Models\EventCategories;
use App\Models\ApplicationError;
use App\Models\PaymentDetail;
use App\Models\ResponseEmailList;
use DateTime;
use DateTimeZone;

class EventApplicationsController extends Controller
{
    public function index(Request $req)
    {
        if (isset($req->id)) {
            $application = $this->getApplication($req->id, $req->page);
            $categories = (new ApplicationCategoriesController)->getApplicationCategories($req->id);
            $booth = (new BoothController)->getBoothById($application[0]->booth_id);
            $event_booth = (new EventBoothController)->getEventBoothPriceById($application[0]->event_id, $application[0]->booth_id);
            $booth_price = number_format((float)($event_booth->price), 2, '.', '');
            $total = number_format((float)((int)$application[0]->booth_qty * (int)$application[0]->no_of_days * (int)$event_booth->price), 2, '.', '');

            if ($application[0]->discount) {
                $total -= $application[0]->discount_value;
            }

            return view('application-detail', ['application' => $application[0], 'categories' => $categories, 'booth' => $booth, 'booth_price' => $event_booth->display_price, 'total' => $total, 'payment' => $application[2], 'payment_detail' => $application[3], 'page' => $application[1], 'eventId' => $req->event]);
        } else {
            $events = Events::all();
            $event = '';
            if (isset($req->eventId)) {
                $applications = DB::table('event_applications')
                    ->select('event_applications.id', 'event_applications.organization', 'event_applications.contact_person', 'event_applications.contact_no', 'event_applications.email', 'event_applications.application_code', 'event_applications.status', 'event_applications.created', 'payment_status.status as payment_status')
                    ->leftJoin('event_payments', 'event_applications.id', '=', 'event_payments.application_id')
                    ->leftJoin('payment_status', 'payment_status.id', '=', 'event_payments.status')
                    ->where("event_applications.event_id", $req->eventId)
                    ->orderBy('event_applications.created', 'DESC')
                    ->paginate(10);

                $event = $req->eventId;
            } else {
                // $applications = EventApplications::orderBy('created', 'DESC')->paginate(10);
                $applications = DB::table('event_applications')
                    ->select('event_applications.id', 'event_applications.organization', 'event_applications.contact_person', 'event_applications.contact_no', 'event_applications.email', 'event_applications.application_code', 'event_applications.status', 'event_applications.created', 'payment_status.status as payment_status')
                    ->leftJoin('event_payments', 'event_applications.id', '=', 'event_payments.application_id')
                    ->leftJoin('payment_status', 'payment_status.id', '=', 'event_payments.status')
                    ->orderBy('event_applications.created', 'DESC')
                    ->paginate(10);
            }
            return view('applications', compact('applications', 'events'))->with('eventId', $event);
        }
    }

    public function applications(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->addApplication($req->post());
            return $this->sendResponse($data, 200);
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $this->getApplication($req->id);
            return $this->sendResponse($data, 200);
        } else {
            return $this->sendError('', ['error' => 'Allowed headers POST, GET'], 405);
        }
    }

    private function addApplication($application)
    {
        $info = new EventApplications;

        $info->event_id = $application['eventId'];
        $info->contact_person = $application['contactPerson'];
        $info->contact_no = $application['contactNo'];
        $info->email = $application['email'];
        $info->organization = $application['organization'];
        $info->registration = $application['companyRegistration'];
        $info->participants = $application['participants'];
        $info->social_media_account = empty($application['socialMediaAccount']) ? '' : $application['socialMediaAccount'];
        $info->description = $application['productDescription'];
        $info->requirements = empty($application['requirements']) ? '' : $application['requirements'];
        $info->plug = $application['plugPoints'] == 'Yes' ? 'Y' : 'N';
        $info->booth_id = $application['boothId'];
        $info->booth_qty = $application['noOfBooth'];
        $info->no_of_days = $application['noOfDays'] ?? $this->getEventDays($application['eventId']);

        $application_code =
            $this->getApplicationCode(6);
        $info->application_code = $application_code;

        $info->save();
        $id = $info->id;

        if (isset($application['categoryId'])) {
            foreach ($application['categoryId'] as $cat) {
                $application_categories = new ApplicationCategories;
                $application_categories->application_id = $id;
                $application_categories->category_id = $cat;

                $application_categories->save();
            }
        }

        try {
            $this->sendApplicationReceivedEmail($id);
            $this->handleMondayMutation($id);
        } catch (Throwable $ex) {
            Log::error($ex);
        }


        return (['id' => $id, 'application_code' => $application_code]);
    }

    private function getApplication($id, $page = null)
    {
        $result = EventApplications::where('id', $id)->first();
        $payment = EventPayments::where('application_id', $id)->first();

        if ($payment && $payment->payment_reference) {
            $payment['path'] =  asset('storage/' . $payment->payment_reference);
        }

        $detail = array();
        if ($payment && $payment->status == 2) {
            $detail = PaymentDetail::where('payment_id', $payment->id)
                ->orderBy('created', 'DESC')
                ->first();
            Log::info($detail);
        }

        if ($page) {
            return [$result, $page, $payment, $detail];
        }
        return [$result, 1, $payment, $detail];
    }

    private function getEventDays($event_id)
    {
        $event_day = EventBooth::where('event_id', $event_id)->first(['max_day']);
        return (int)$event_day->max_day;
    }

    private function getApplicationCode($n)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public function updateStatus(Request $req)
    {
        $status = (object)array("status" => 'N', 'message' => "");
        $response = $this->setUpdateStatus($status, $req->post(), $req->id);

        if ($response != "error") {
            return $this->sendResponse(['message' => $response], 200);
        } else {
            return $this->sendError("There was an error updating status", "", 405);
        }
    }

    private function setUpdateStatus($status, $post, $application_id)
    {
        if ($post["status"] == 'reject') {
            $status->status = 'R';
            $status->message = "Application has been updated to REJECTED";
        } else if ($post["status"] == "approve") {
            $status->status = 'A';
            $status->message = "Application has been updated to APPROVED";
        } else {
            $status->status = 'N';
        }

        try {
            DB::table('event_applications')
                ->where('id', $application_id)
                ->update(['status' => $status->status]);

            $application = EventApplications::where('id', $application_id)->first();
            $event = Events::where('id', $application->event_id)
                ->first();

            $payment_exists = EventPayments::where('application_id', $application->id)
                ->where('application_code', $application->application_code)
                ->get();

            if ($payment_exists && $status->status === 'A') {
                $event_booth = (new EventBoothController)->getEventBoothPriceById($application->event_id, $application->booth_id);
                $total = number_format((float)((int)$application->booth_qty * (int)$application->no_of_days * (int)$event_booth->price), 2, '.', '');

                if ($application->discount) {
                    $total -= $application->discount_value;
                }
                $application->payment = $total;

                $payment = new EventPayments();
                $payment->application_id = $application->id;
                $payment->application_code = $application->application_code;
                $payment->payment_total = $total;
                $payment->status = 1;
                $payment->save();

                $id = $payment->id;
                $payment_link = config('custom.payment_redirect_host') . "/payment/" . $id . "/code/" . $application->application_code;
                $application->reference_link = config('custom.payment_redirect_host') . "/payment-reference/" . $application->application_code;
                // send successful email
                $this->sendNotificationEmail($status->status, $event, $application, $payment_link);
            }

            if ($status->status === 'R') {
                // send rejected email
                $this->sendNotificationEmail($status->status, $event, $application, '');
            }
            return $status->message;
        } catch (Exception $ex) {
            Log::error($ex);
            return "error";
        }
    }

    private function sendApplicationReceivedEmail($application_id)
    {
        $application = EventApplications::where('id', $application_id)
            ->first();
        $event = Events::where('id', $application->event_id)->first()
            ->first();
        $email_list = ResponseEmailList::where('response_email_type', 'NA')->get();
        try {
            Mail::to($email_list)
                ->later(now()->addMinutes(2), new ApplicationReceived($event, $application));
        } catch (Throwable $ex) {
            Log::error($ex);
        }
    }

    private function sendNotificationEmail($type, $event, $application, $payment_link)
    {
        try {
            if ($type === 'A') {
                Mail::to($application->email)
                    ->later(now()->addMinutes(5), new ApplicationApprovedResponse($event, $application, $payment_link));
            } else {
                Mail::to($application->email)
                    ->later(now()->addMinute(10), new ApplicationRejectedResponse($event, $application));
            }
        } catch (Throwable $ex) {
            Log::error($ex);
        }
    }

    private function handleMondayMutation($application_id)
    {
        $application = EventApplications::where('id', $application_id)->first();
        $application_categories = DB::table('event_applications')
            ->leftJoin('application_categories', 'application_categories.application_id', '=', 'event_applications.id')
            ->leftJoin('categories', 'application_categories.category_id', '=', 'categories.id')
            ->where('event_applications.id', $application_id)
            ->get(['categories.id']);
        $categories = [];


        foreach ($application_categories as $cat) {
            Log::info($cat->id);
            if ($cat->id != null && $cat->id !== '') {
                $id = EventCategories::where('event_id', $application->event_id)->where('category_id', $cat->id)->first(['monday_category_id']);
                $categories[] = $id->monday_category_id;
            }
        }


        $event_booths = DB::table("event_applications")
            ->leftJoin("booths", "event_applications.booth_id", '=', "booths.id")
            ->where("event_applications.id", $application_id)
            ->first(["booths.id"]);
        $booth = EventBooth::where("event_id", $application->event_id)->where('booth_id', $event_booths->id)->first();
        $event = Events::where('id', $application->event_id)->first();

        $token = config('custom.monday_token');
        $apiUrl = 'https://api.monday.com/v2';

        $query = 'mutation ($item_name:String!, $columnVals: JSON!){ create_item (board_id:' . $event->monday_board_id . ', group_id: "topics", item_name: $item_name, column_values: $columnVals) { id } }';
        $date = new DateTime($application->created);
        $date->setTimezone(new DateTimeZone('UTC'));

        Log::info('categories' . count($categories));
        $vals = [
            "item_name" => $application->organization,
            "columnVals" => json_encode(
                [
                    "status" => ["label" => "Pending"],
                    "date4" => ['date' => $date->format('Y-m-d'), 'time' => $date->format('H:i:s')],
                    "product_category__1" => ["ids" => count($categories) > 0 ? $categories : [14]],
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
            Log::info($query);
            Log::info($vals);
            $data = json_decode($responseContent->getBody()->getContents());
            if (isset($data->error_message)) {
                $error = new ApplicationError();
                $error->application_id = $application->id;
                $error->error_message = $data->error_message;
                $error->save();
            } else {
                Log::info($responseContent->getBody());
                $id = $data->data->create_item->id;
                // DB::table('event_applications')
                //     ->update(
                //         [
                //             'id' => $application_id
                //         ],
                //         ['monday_id' => $id]
                //     );
                $event_applications = EventApplications::find($application_id);
                $event_applications->monday_id = $id;
                $event_applications->save();
            }
        } catch (Throwable $ex) {
            Log::error($ex);
        }
    }
}
