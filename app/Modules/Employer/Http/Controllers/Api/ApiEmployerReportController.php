<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Candidate\Models\Attendance;
use Carbon\Carbon;
use Employer\Models\Company;
use Employer\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\CssSelector\Node\FunctionNode;

class ApiEmployerReportController extends Controller
{
    protected $response;
    public function __construct(ResponseService $response)
    {
        $this->response = $response;
    }

    public function weeklyReport($companyid,$candidate_id)
    {
        try {
            $company = Company::where('id', $companyid)->with(['govLeaves', 'specialLeaves', 'businessLeaves'])->withCount(['govLeaves' => function ($q) {
                $q->whereBetween(
                    'leave_date',
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                );
            }])->withCount(['specialLeaves' => function ($q) {
                $q->whereBetween(
                    'leave_date',
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                );
            }])->first();


            $user = auth()->user();
            $presentCount = Attendance::where('candidate_id', $candidate_id)
                ->where('company_id', $companyid)->whereBetween(
                    'created_at',
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                )
                ->where('employee_status', 'Present')
                ->count();

            // dd(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek());
            $leaveCount = Attendance::where('candidate_id', $candidate_id)
                ->where('company_id', $companyid)->whereBetween(
                    'created_at',
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                )
                ->where('employee_status', 'Leave')
                ->count();
            // dd($leaveCount);


            $businessleavedays = $company->businessLeaves->pluck('title') ?? [];
            // dd($businessleavedays);

            $startDate = Carbon::parse(Carbon::now()->startOfWeek());

            $endDate = Carbon::parse(Carbon::now());
            $businessleavedays = $startDate->diffInDaysFiltered(function (Carbon $date) use ($businessleavedays) {
                foreach ($businessleavedays as $leaveday) {
                    if ($leaveday == "Saturday") {
                        $exceptSaturday =  $date->isSaturday();
                    }
                    if ($leaveday == "Sunday") {

                        $exceptSunday =  $date->isSunday();
                    }
                    if ($leaveday == "Monday") {
                        $exceptMonday = $date->isMonday();
                    }
                    if ($leaveday == "Tuesday") {
                        $exceptTuesday = $date->isTuesday();
                    }
                    if ($leaveday == "Wednesday") {
                        $exceptWednesday = $date->isWednesday();
                    }
                    if ($leaveday == "Thursday") {
                        $exceptThursday = $date->isThursday();
                    }
                    if ($leaveday == "Friday") {
                        $exceptFriday = $date->isFriday();
                    }
                    $newdate = ($exceptSaturday ?? 0) + ($exceptMonday ?? 0) + ($exceptTuesday ?? 0) + ($exceptSunday ?? 0) +
                        ($exceptWednesday ?? 0) + ($exceptThursday ?? 0) + ($exceptFriday ?? 0);
                }
                return $newdate;
            }, $endDate);


            $absentcount = 6 - $presentCount - $leaveCount;

            $weekdata = Attendance::where('candidate_id', $candidate_id)
                    ->where('company_id', $companyid)->whereBetween(
                    'created_at',
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                )->with('breaks')
                ->get()->groupBy(function ($date) {
                    return Carbon::parse($date->created_at)->format('D');
                });

            $data = [
                'present' =>  $presentCount ?? 0,
                'absent' => $absentcount ?? 0,
                'leave' => $leaveCount ?? 0,
                'weekdata ' =>  $weekdata  ?? [],
                'businessleaveCount' => $businessleavedays ?? 0,
                'governmentLeaveCount' => $company->gov_leaves_count ?? 0,
                'specialLeaveCount' => $company->special_leaves_count ?? 0,
            ];

            return $this->response->responseSuccess($data, "Success", 200);
        } catch (Exception  $e) {
            return $this->response->responseError($e->getMessage());
        }
    }

    public function monthlyReport($companyid,$candidate_id)
    {
        try {
            $month = now()->format('m');
            // if($month==null && $month == 0){
            //     $month = now()->format('m');
            //     $startDate = Carbon::parse(Carbon::now()->startOfMonth());
            //     $endDate = Carbon::parse(Carbon::now());
            //     $totaldays = Carbon::now()->daysInMonth;
            //     $today = Carbon::today()->format('d');
            // }else{
            //     $month = $month->format('m');
            // }

            $company = Company::where('id', $companyid)->with('govLeaves', 'specialLeaves', 'businessLeaves')->withCount(['govLeaves' => function ($q) use ($month){
                $q->whereMonth('leave_date', $month);
            }])->withCount(['specialLeaves' => function ($q) use ($month) {
                $q->whereMonth('leave_date', $month);
            }])->first();
            // dd($company);

            $governmentLeavedaysCount = $company->gov_leaves_count ?? 0;
            $specialLeavedaysCount = $company->special_leaves_count ?? 0;

            $businessleavedays = $company->businessLeaves->pluck('title') ?? [];
            $startDate = Carbon::parse(Carbon::now()->startOfMonth());
            $endDate = Carbon::parse(Carbon::now());
            $totaldays = Carbon::now()->daysInMonth;
            $today = Carbon::today()->format('d');


            $businessleavedays = $startDate->diffInDaysFiltered(function (Carbon $date) use ($businessleavedays) {
                foreach ($businessleavedays as $leaveday) {
                    if ($leaveday == "Saturday") {
                        $exceptSaturday =  $date->isSaturday();
                    }
                    if ($leaveday == "Sunday") {

                        $exceptSunday =  $date->isSunday();
                    }
                    if ($leaveday == "Monday") {
                        $exceptMonday = $date->isMonday();
                    }
                    if ($leaveday == "Tuesday") {
                        $exceptTuesday = $date->isTuesday();
                    }
                    if ($leaveday == "Wednesday") {
                        $exceptWednesday = $date->isWednesday();
                    }
                    if ($leaveday == "Thursday") {
                        $exceptThursday = $date->isThursday();
                    }
                    if ($leaveday == "Friday") {
                        $exceptFriday = $date->isFriday();
                    }
                    $newdate = ($exceptSaturday ?? 0) + ($exceptMonday ?? 0) + ($exceptTuesday ?? 0) + ($exceptSunday ?? 0) +
                        ($exceptWednesday ?? 0) + ($exceptThursday ?? 0) + ($exceptFriday ?? 0);
                }
                return $newdate;
            }, $endDate);


            $user = auth()->user();
            $presentCount = Attendance::where('candidate_id', $candidate_id)
                ->where('company_id', $companyid)
                ->whereMonth('created_at', Carbon::now()->month)
                ->where('employee_status', 'Present')
                ->count();
            $leaveCount = Attendance::where('candidate_id', $candidate_id)
                ->where('company_id', $companyid)
                ->whereMonth('created_at', Carbon::now()->month)
                ->where('employee_status', 'Leave')
                ->count();

            $totalworkingdays = ($totaldays - $businessleavedays - $governmentLeavedaysCount - $specialLeavedaysCount);

            $absentcount = (int) $today  - $presentCount - $leaveCount;

            // dd($absentcount);

            $data = [
                'presentCount' => $presentCount ?? 0,
                'absentcount' => $absentcount ?? 0,
                'leavecount' => $leaveCount ?? 0,
                'totaldays' => $totaldays ?? 0,
                'businessleavedays' => $businessleavedays ?? 0,
                'governmentLeavedaysCount' => $governmentLeavedaysCount ?? 0,
                'specialLeavedaysCount' => $specialLeavedaysCount ?? 0,
                'totaldays' => $totaldays ?? 0,
            ];

            return $this->response->responseSuccess($data, "Success", 200);
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }

    public function paymentSubmit(Request $request, $company_id, $candidate_id){
        try{
            // dd($request->all());
            $payment = new Payment();
            $payment->status = 'Paid';
            $payment->paid_amount = $request->paid_amount;
            $payment->payment_date = Carbon::now();
            $payment->payment_for_month = $request->payment_for_month;
            $payment->company_id = $company_id;
            $payment->candidate_id = $candidate_id;
            $payment->employer_id = Auth::id();
            if($payment->save() == true){
                return $this->response->responseSuccessMsg("Successfully Stored", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }
}
