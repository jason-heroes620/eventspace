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
use App\Models\EventApplicationGroup;
use App\Models\EventDeposit;
use App\Models\EventGroups;
use App\Models\PaymentDetail;
use App\Models\ResponseEmailList;
use DateInterval;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;

class EventApplicationsController extends Controller
{
    public function index(Request $req)
    {
        $total = 0;
        $require_deposit = false;
        $deposit = 0;

        if (isset($req->id)) {
            $application = $this->getApplicationV2($req->id, $req->page);
            $categories = (new ApplicationCategoriesController)->getApplicationCategories($req->id);

            foreach ($application[0]->items as $item) {
                $event_booth = EventBooth::where('id', $item->booth_id)->first();
                $booth = (new BoothController)->getBoothById($event_booth->booth_id);

                $booth_price = number_format((float)($event_booth->price), 2, '.', '');
                $subTotal = number_format((float)((int)$item->booth_qty * (int)$item->no_of_days * (float)$event_booth->price), 2, '.', '');
                $item->subTotal = $subTotal;
                $total += $subTotal;

                $item->event_date = Events::where('id', $item->event_id)->first()->event_date;
                $item->booth_type = $booth->booth_type;

                $event = Events::where('id', $item->event_id)->first();
                if ($event->require_deposit == 'Y') {
                    $require_deposit = true;
                    $deposit =  $deposit = EventDeposit::whereNull('end_date')->where('event_deposit_status', true)->where('start_date', '<=', date('Y-m-d'))->first()->event_deposit;
                    $total += $deposit;
                }
            }

            if ($application[0]->discount) {
                $total -= $application[0]->discount_value;
            }

            return view('application-detail', ['application' => $application[0], 'categories' => $categories, 'booth' => $booth, 'booth_price' => $event_booth->display_price, 'total' => $total, 'payment' => $application[2], 'payment_detail' => $application[3], 'page' => $application[1], 'eventId' => $req->event, 'require_deposit' => $require_deposit, 'deposit' => $deposit]);
        } else {
            $events = Events::all();
            $event = '';
            if (isset($req->eventId)) {
                $applications = DB::table('event_application_group')
                    ->select('event_application_group.id', 'organization', 'contact_person', 'contact_no', 'email', 'event_application_group.application_code', 'event_application_group.status', 'event_application_group.created', 'payment_status.status as payment_status')
                    ->leftJoin('event_payments', 'event_application_group.id', '=', 'event_payments.application_id')
                    ->leftJoin('payment_status', 'payment_status.id', '=', 'event_payments.status')
                    ->where("event_application_group.event_group_id", $req->eventId)
                    ->orderBy('event_application_group.created', 'DESC')
                    ->paginate(10);

                $event = $req->eventId;
            } else {
                // $applications = EventApplications::orderBy('created', 'DESC')->paginate(10);
                $applications = DB::table('event_application_group')
                    ->select('event_application_group.id', 'organization', 'contact_person', 'contact_no', 'email', 'event_application_group.application_code', 'event_application_group.status', 'event_application_group.created', 'payment_status.status as payment_status')
                    ->leftJoin('event_payments', 'event_application_group.id', '=', 'event_payments.application_id')
                    ->leftJoin('payment_status', 'payment_status.id', '=', 'event_payments.status')
                    ->orderBy('event_application_group.created', 'DESC')
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

    public function applicationsV2(Request $req)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->addApplicationV2($req->post());
            return $this->sendResponse($data, 200);
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $this->getApplicationV2($req->id);
            return $this->sendResponse($data, 200);
        } else {
            return $this->sendError('', ['error' => 'Allowed headers POST, GET'], 405);
        }
    }

    private function addApplication($application)
    {
        $info = new EventApplications;

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

    private function addApplicationV2($application)
    {
        $info = new EventApplicationGroup();

        $info->event_group_id = $application['eventGroupId'];
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

        $applicationItems = new EventApplications();
        foreach (json_decode($application['items']) as $item) {
            $applicationItems = new EventApplications();
            $applicationItems->event_application_group_id = $id;
            $applicationItems->event_id = $item->eventId;
            $applicationItems->booth_id = $item->eventBoothId;
            $applicationItems->booth_qty = $item->numberOfBooths;
            $applicationItems->no_of_days = $this->getEventDays($item->eventId);

            $applicationItems->save();
        }

        try {
            $this->sendApplicationReceivedEmail($id);
            // $this->handleMondayMutation($id);
            $this->handleMondayMutationV2($id);
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

    private function getApplicationV2($id, $page = null)
    {
        $result = EventApplicationGroup::where('id', $id)->first();
        $payment = EventPayments::where('application_id', $id)->first();

        if ($payment && $payment->payment_reference) {
            $payment['path'] =  asset('storage/' . $payment->payment_reference);
        }

        $result->items = EventApplications::where('event_application_group_id', $id)->get();

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
        Log::info('Getting event days for event id ' . $event_id);
        $event_day = EventBooth::where('event_id', $event_id)->first()->max_day;
        return (int)$event_day;
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

    public  function setUpdateStatus($status, $post, $applicationGroupId)
    {
        $user = Auth::user();
        $total = 0.00;
        $deposit = 0;
        $subTotal = 0;
        $require_deposit = false;

        if ($post["status"] == 'reject') {
            $status->status = 'R';
            $status->message = "Application has been updated to REJECTED";
        } else if ($post["status"] == "approve") {
            $status->status = 'A';
            $status->message = "Application has been updated to APPROVED";
        } else if ($post["status"] == "cancel") {
            $status->status = 'C';
            $status->message = "Application has been updated to CANCEL";
        } else {
            $status->status = 'N';
        }

        $applicationGroup = EventApplicationGroup::where('id', $applicationGroupId)->first();
        Log::info('status updated. ORI =>' . $applicationGroup->status . ' to => ' . $status->status . " by => " . $user->name);

        $event = EventGroups::where('event_group_id', $applicationGroup->event_group_id)->first();
        try {
            $applicationGroup->update(['status' => $status->status]);

            $payment_exists = EventPayments::where('application_id', $applicationGroupId)
                ->where('application_code', $applicationGroup->application_code)
                ->get();

            if ($payment_exists && $status->status === 'A') {
                // $event_booth = (new EventBoothController)->getEventBoothPriceById($application->event_id, $application->booth_id);
                // $subTotal = (float)((int)$application->booth_qty * (int)$application->no_of_days * (int)$event_booth->price);
                // $total += $subTotal;

                $applicationBooth = $this->getApplicationBoothByGroupId($applicationGroupId);
                $total += $applicationBooth['total'];
                $subTotal = $applicationBooth['subTotal'];

                if ($applicationBooth['require_deposit']) {
                    $require_deposit = $applicationBooth['require_deposit'];
                    $deposit = $applicationBooth['deposit'];
                }


                if ($applicationGroup->discount) {
                    $total -= $applicationGroup->discount_value;
                }

                $items = $applicationBooth['items'];

                $dateTime = new DateTime($applicationGroup->created);
                $interval = new DateInterval('P7D');
                $applicationGroup->due_date = $dateTime->add($interval)->format('d M Y');

                $applicationGroup->payment = number_format($total, 2, '.', '');

                $payment = EventPayments::updateOrCreate([
                    "application_code" => $applicationGroup->application_code,
                    "application_id" => $applicationGroup->id,
                ], [
                    "payment_total" => $total,
                    "status" => 1
                ]);

                $applicationGroup->payment = number_format($total, 2, '.', '');

                $id = $payment->id;
                $payment_link = config('custom.payment_redirect_host') . "/payment/" . $id . "/code/" . $applicationGroup->application_code;
                $reference_link = config('custom.payment_redirect_host') . "/payment-reference/" . $applicationGroup->application_code;

                // send successful email
                $this->sendNotificationEmail($status->status, $event, $applicationGroup, $payment_link, $total, $reference_link, $items, $require_deposit, $deposit, $subTotal);
            }

            if ($status->status === 'R') {
                // send rejected email
                $this->sendNotificationEmail($status->status, $event, $applicationGroup, '', '', '', '', '', '', '');
            }
            return $status->message;
        } catch (Exception $ex) {
            Log::error($ex);
            return "error";
        }
    }

    private function sendApplicationReceivedEmail($application_id)
    {
        Log::info('application id' . $application_id);
        $application = EventApplicationGroup::where('id', $application_id)
            ->first();
        $event = EventGroups::where('event_group_id', $application->event_group_id)->first();
        Log::info('event id' . $event);
        $email_list = ResponseEmailList::where('response_email_type', 'NA')->get();
        try {
            Mail::to($email_list)
                ->later(now()->addMinutes(1), new ApplicationReceived($event, $application));
        } catch (Throwable $ex) {
            Log::error($ex);
        }
    }

    private function sendNotificationEmail($type, $event, $application, $payment_link, $total, $reference_link, $items, $require_deposit, $deposit, $subTotal)
    {
        try {
            $bcc = ['admin.test@heroes.my'];
            if ($event->event_group === 'What The Pets')
                array_push($bcc, 'lencerz@gmail.com');
            if ($type === 'A') {
                Mail::to($application->email)
                    ->bcc($bcc)
                    ->later(
                        now()->addMinutes(0),
                        new ApplicationApprovedResponse(
                            $event,
                            $application,
                            $payment_link,
                            $total,
                            $reference_link,
                            $require_deposit,
                            $items,
                            $deposit,
                            $event->due_date,
                            $subTotal,
                        )
                    );
                // ->later(now(), new ApplicationApprovedResponse($event, $application, $payment_link, $total, $reference_link));
            } else {
                Mail::to($application->email)
                    ->later(now()->addMinute(5), new ApplicationRejectedResponse($event, $application));
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

        $event_booth = (new EventBoothController)->getEventBoothPriceById($application->event_id, $application->booth_id);
        // $booth = (new BoothController)->getBoothById($application->booth_id);
        $booth = EventBooth::where('id', $application->booth_id)->first()->monday_booth_id;
        $event = Events::where('id', $application->event_id)->first();

        $total = number_format((float)((int)$application->booth_qty * (int)$application->no_of_days * (float)$event_booth->price), 2, '.', '');

        if ($application->discount) {
            $total -= $application->discount_value;
        }

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
                    "label6__1" => ["index" => $booth],
                    "dropdown8__1" => $application->plug == 'Y' ? ["ids" => [1]] : ["ids" => [2]],
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
                $event_applications = EventApplications::find($application_id);
                $event_applications->monday_id = $id;
                $event_applications->save();
            }
        } catch (Throwable $ex) {
            Log::error($ex);
        }
    }

    private function handleMondayMutationV2($application_group_id)
    {
        $applicationGroup = EventApplicationGroup::where('id', $application_group_id)->first();
        $application_categories = DB::table('event_application_group')
            ->leftJoin('application_categories', 'application_categories.application_id', '=', 'event_application_group.id')
            ->leftJoin('categories', 'application_categories.category_id', '=', 'categories.id')
            ->where('event_application_group.id', $application_group_id)
            ->get(['categories.id']);
        $categories = [];

        foreach ($application_categories as $cat) {
            Log::info($cat->id);
            if ($cat->id != null && $cat->id !== '') {
                $id = EventCategories::where('event_group_id', $applicationGroup->event_group_id)->where('category_id', $cat->id)->first(['monday_category_id']);
                $categories[] = $id->monday_category_id;
            }
        }

        $total = 0;
        $application = EventApplications::where('event_application_group_id', $application_group_id)->get();

        $token = config('custom.monday_token');
        $apiUrl = 'https://api.monday.com/v2';
        foreach ($application as $app) {
            Log::info('Application ID:');
            Log::info($app->id);
            $event_booth = (new EventBoothController)->getEventBoothPriceById($app->event_id, $app->booth_id);
            $booth = EventBooth::where('id', $app->booth_id)->first()->monday_booth_id;
            $event = Events::where('id', $app->event_id)->first();

            $total += number_format((float)((int)$app->booth_qty * (int)$app->no_of_days * (float)$event_booth->price), 2, '.', '');

            $query = 'mutation ($item_name:String!, $columnVals: JSON!){ create_item (board_id:' . $event->monday_board_id . ', group_id: "' . $event->monday_group_id . '" , item_name: $item_name, column_values: $columnVals) { id } }';
            $date = new DateTime($applicationGroup->created);
            $date->setTimezone(new DateTimeZone('UTC'));

            Log::info('categories' . count($categories));
            $vals = [
                "item_name" => $applicationGroup->organization,
                "columnVals" => json_encode(
                    [
                        "status" => ["label" => "Pending"],
                        "date4" => ['date' => $date->format('Y-m-d'), 'time' => $date->format('H:i:s')],
                        "product_category__1" => ["ids" => count($categories) > 0 ? $categories : [14]],
                        "text" => $applicationGroup->contact_person,
                        "phone" => ["phone" => $applicationGroup->contact_no, "countryShortName" => "MY"],
                        "email" => ["email" => $applicationGroup->email, "text" => $applicationGroup->email],
                        "text1" => $applicationGroup->organization,
                        "text9" => $applicationGroup->registration,
                        "text__1" => $applicationGroup->social_media_account,
                        "text3__1" => $event->event_location,
                        "event_date__1" => $event->event_date,
                        "event_time__1" => $event->event_time,
                        "numbers5" => $applicationGroup->participants,
                        "numbers3" => $app->booth_qty,
                        "text98" => $applicationGroup->description,
                        "label6__1" => ["index" => $booth],
                        "dropdown8__1" => $applicationGroup->plug == 'Y' ? ["ids" => [1]] : ["ids" => [2]],
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
                    $error->application_id = $app->id;
                    $error->error_message = $data->error_message;
                    $error->save();
                } else {
                    Log::info($responseContent->getBody());
                    $id = $data->data->create_item->id;

                    $event_applications = EventApplications::find($app->id);
                    $event_applications->monday_id = $id;
                    $event_applications->save();
                }
            } catch (Throwable $ex) {
                Log::error('Error creating item in Monday.com for ' . $applicationGroup->id . ', ' . $app->id . ', ' .  $ex->getMessage());
                Log::error($ex);
            }
        }

        if ($applicationGroup->discount) {
            $total -= $applicationGroup->discount_value;
        }
    }

    private function getApplicationBoothByGroupId($id)
    {
        $total = 0;
        $deposit = 0;
        $subTotal = 0;
        $require_deposit = false;

        $application = EventApplications::where('event_application_group_id', $id)->get();
        $items = [];
        foreach ($application as &$item) {
            $event_booth = EventBooth::where('id', $item->booth_id)->first();
            $booth = (new BoothController)->getBoothById($event_booth->booth_id);

            $booth_price = number_format((float)($event_booth->price), 2, '.', '');
            $item['subTotal'] = number_format((float)((int)$item->booth_qty * (int)$item->no_of_days * (float)$event_booth->price), 2, '.', '');
            $subTotal += $item['subTotal'];
            $item['event_date'] = Events::where('id', $item->event_id)->first()->event_date;
            $item['booth_type'] = $booth->booth_type;
            $total += $item['subTotal'];

            $event = Events::where('id', $item->event_id)->first();
            if ($event->require_deposit == 'Y') {
                $require_deposit = true;
                $deposit =  $deposit = EventDeposit::whereNull('end_date')->where('event_deposit_status', true)->where('start_date', '<=', date('Y-m-d'))->first()->event_deposit;
            }
            $items[] = $item;
        }
        $total += $deposit;

        return compact('subTotal', 'total', 'deposit', 'require_deposit', 'application', 'items');
    }

    public function updateDiscount(Request $req)
    {
        $discount = (object)array("discount" => $req->discount);

        try {
            EventApplicationGroup::where('id', $req->id)->update(['discount_value' => $req->discount, 'discount' => 'Discount']);
            return $this->sendResponse(['message' => 'Discount updated successfully'], 200);
        } catch (Exception $e) {
            Log::error($e);
            return $this->sendError("There was an error updating discount", "", 405);
        }
    }
}
