<?php

namespace Candidate\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Candidate\Http\Resources\CandidateYearlyResource;
use Candidate\Models\Attendance;
use Candidate\Models\Leave;
use Carbon\Carbon;
use Employer\Models\Company;
use Exception;
use Illuminate\Support\Facades\DB;

class ApiCandidateReportController extends Controller
{

    protected $response;
    public function __construct(ResponseService $response)
    {
        $this->response = $response;
    }



    public function weeklyReport($companyid)
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
            $presentCount = Attendance::where('candidate_id', $user->id)
                ->where('company_id', $companyid)->whereBetween(
                    'created_at',
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                )
                ->where('employee_status', 'Present')
                ->count();

            // dd(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek());
            $leaveCount = Attendance::where('candidate_id', $user->id)
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

            // dd($presentCount, $leaveCount, $absentcount);

            // dd(Carbon::now()->startOfWeek(),Carbon::now()->endOfWeek());

            // $previousweekdata =  Attendance::where('candidate_id', $user->id)
            //     ->where('company_id', $companyid)->whereBetween(
            //         'created_at',
            //         [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]
            //     )
            //     ->get();
            // dd($previousweekdata);

            $weekdata = Attendance::where('candidate_id', $user->id)
                ->where('company_id', $companyid)->whereBetween(
                    'created_at',
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                )->get()->groupBy(function ($date) {
                    return Carbon::parse($date->created_at)->format('D');
                });


            // $data =  DB::table('leaves')
            //     ->whereRaw('WEEK(start_date) = WEEK(CURDATE()) OR WEEK(end_date) = WEEK(CURDATE())')
            //     ->get();

            // $data = DB::table('attendances')
            //   ->join('leaves','attendances.candidate_id','=','leaves.user_id')
            //   ->whereRaw('WEEK(attendances.created_at) = WEEK(CURDATE()) OR (WEEK(leaves.start_date) = WEEK(CURDATE()) AND WEEK(leaves.end_date) = WEEK(CURDATE()))')
            //   ->get();
            // dd($data);


            //leave this week of candidate
            // $leaveWeek = Leave::where('user_id', $user->id)->where('company_id', $companyid)
            //     ->approved()
            //     ->whereBetween(
            //         'start_date',
            //         [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
            //     )->orWhereBetween(
            //         'end_date',
            //         [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
            //     )
            //     ->get();



            // if ($leaveWeek) {

            //     $startdate =  Carbon::parse($leaveWeek->start_date);
            //     $enddate = Carbon::parse($leaveWeek->end_date);
            //     $leaveCount = $startdate->diffInDays($enddate);
            // }


            // $absentcount = 6 - ($presentCount ?? 0) - ($leaveCount ?? 0);


            // dd($weeklydata);
            // dd($presentCount);

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





    public function monthlyReport($companyid)
    {
        try {
            $company = Company::where('id', $companyid)->with('govLeaves', 'specialLeaves', 'businessLeaves')->withCount(['govLeaves' => function ($q) {
                $q->whereMonth('leave_date', now()->format('m'));
            }])->withCount(['specialLeaves' => function ($q) {
                $q->whereMonth('leave_date', now()->format('m'));
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
            $presentCount = Attendance::where('candidate_id', $user->id)
                ->where('company_id', $companyid)
                ->whereMonth('created_at', Carbon::now()->month)
                ->where('employee_status', 'Present')
                ->count();
            $leaveCount = Attendance::where('candidate_id', $user->id)
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



    public function yearlyReport($companyid, $year)
    {
        try {
            // dd($companyid, $year);
            // $query =Attendance::select(

            //     DB::raw("(sum(earning)) as total_earning"),
            //     DB::raw("(DATE_FORMAT(created_at, '%m-%Y')) as month_year")
            //     )
            //     ->orderBy('created_at')
            //     ->groupBy(DB::raw("DATE_FORMAT(created_at, '%m-%Y')"))
            //     ->get();


             $yearlydata = Attendance::where('company_id', $companyid)->whereYear('created_at', $year)->select(DB::raw('sum(earning) as `total_earning`'),DB::raw('YEAR(created_at) year, MONTH(created_at) month'))

             ->groupby('year','month')
             ->get();

             $data = [
                'data' => CandidateYearlyResource::collection($yearlydata)
             ];
             return $this->response->responseSuccess($data, "Success", 200);
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }
}
