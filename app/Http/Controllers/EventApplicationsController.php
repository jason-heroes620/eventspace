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
use Throwable;

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

            return view('application-detail', ['application' => $application[0], 'categories' => $categories, 'booth' => $booth, 'booth_price' => $booth_price, 'total' => $total, 'page' => $application[1]]);
        } else {
            $applications = EventApplications::orderBy('created', 'DESC')->paginate(10);
            return view('applications', compact('applications'));
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
        $info->no_of_days = $this->getEventDays($application['eventId']);

        $application_code =
            $this->getApplicationCode(6);
        $info->application_code = $application_code;

        $info->save();
        $id = $info->id;

        foreach ($application['categoryId'] as $cat) {
            $application_categories = new ApplicationCategories;
            $application_categories->application_id = $id;
            $application_categories->category_id = $cat;

            $application_categories->save();
        }
        // send response letter with code

        return (['id' => $id, 'application_code' => $application_code]);
    }

    private function getApplication($id, $page = null)
    {
        $result = EventApplications::where('id', $id)->first();
        $url = parse_url($page);

        if (isset($url["query"])) {
            $query = $url["query"];
            $pages = explode('=', $query);
            return [$result, $pages[1]];
        }
        return [$result, 1];
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
                ->updateOrInsert(
                    [
                        'id' => $application_id
                    ],
                    ['status' => $status->status]
                );

            $application = EventApplications::where('id', $application_id)->first();
            $event = Events::where('id', $application->event_id)
                ->first();

            $payment_exists = EventPayments::where('application_id', $application->id)
                ->where('application_code', $application->application_code)
                ->get();

            if ($payment_exists && $status->status === 'A') {
                $event_booth = (new EventBoothController)->getEventBoothPriceById($application->event_id, $application->booth_id);
                $total = number_format((float)((int)$application->booth_qty * (int)$application->no_of_days * (int)$event_booth->price), 2, '.', '');

                $payment = new EventPayments();
                $payment->application_id = $application->id;
                $payment->application_code = $application->application_code;
                $payment->payment_total = $total;
                $payment->status = 1;
                $payment->save();

                $id = $payment->id;
                $payment_link = "https://event-payment.heroes.my/payment/" . $id . "/code/" . $application->application_code;
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

    private function sendNotificationEmail($type, $event, $application, $payment_link)
    {
        try {
            if ($type === 'A') {
                Mail::to($application->email)
                    ->send(new ApplicationApprovedResponse($event, $application, $payment_link));
            } else {
                Mail::to($application->email)
                    ->later(now()->addMinute(1), new ApplicationRejectedResponse($event, $application));
            }
        } catch (Throwable $ex) {
            Log::error($ex);
        }
    }
}
