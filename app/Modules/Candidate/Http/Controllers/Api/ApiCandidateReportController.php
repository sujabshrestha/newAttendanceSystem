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

            $weekStart = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            $weekEnd = Carbon::now()->endOfWeek(Carbon::SATURDAY);
            $attendanceData = DB::table('attendances')
                ->join('company_specialleaves', function ($join) use ($weekStart, $weekEnd) {
                    $join->on('attendances.company_id', '=', 'company_specialleaves.company_id')
                        ->whereBetween('company_specialleaves.leave_date', [$weekStart, $weekEnd]);
                })
                ->join('company_governmentleaves', function ($join) use ($weekStart, $weekEnd) {
                    $join->on('attendances.company_id', '=', 'company_specialleaves.company_id')
                        ->whereBetween('company_governmentleaves.leave_date', [$weekStart, $weekEnd]);
                })
                ->join('company_businessleaves', function ($join) {
                    $join->on('attendances.company_id', '=', 'company_businessleaves.company_id');
                })

                ->where('attendances.candidate_id', '=', auth()->user()->id)
                ->where('attendances.company_id', '=', $companyid)

                ->select(
                    DB::raw("Date(attendances.created_at) as attendance_day"),
                    'attendances.employee_status',
                    DB::raw("Date(company_specialleaves.leave_date) as special_date"),
                    DB::raw("Date(company_governmentleaves.leave_date) as goverment_date"),
                    DB::raw("(company_businessleaves.business_leave_id - 1) as business_date")
                )
                ->get();
            // dd($attendanceData);
            $reportData = [];
            for ($i = 0; $i <= 6; $i++) {
                $day = $weekStart->copy()->addDays($i);
                // dd($day);

                $reportData[$day->format('Y-m-d')] = 'Absent';
                foreach ($attendanceData as $data) {
                    // dd($day->format('w'));
                    // dd($data->business_date);
                    if ($day->format('Y-m-d') == $data->attendance_day) {
                        $reportData[$day->format('Y-m-d')] = $data->employee_status;
                    } elseif ($day->format('Y-m-d') == $data->special_date) {
                        $reportData[$day->format('Y-m-d')] = 'Special Holiday';
                    } elseif ($day->format('Y-m-d') == $data->goverment_date) {
                        $reportData[$day->format('Y-m-d')] = 'Government Holiday';
                    }

                    elseif ($day->format('w') == $data->business_date) {
                        // dd('sadsad');

                        $reportData[$day->format('Y-m-d')] = 'Business Holiday';
                    }

                }
            }

            dd($reportData);





            $company = Company::where('id', $companyid)->with(['govLeaves', 'specialLeaves', 'businessLeaves'])
                ->withCount(['govLeaves' => function ($q) {
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
            $present = Attendance::where('candidate_id', $user->id)
                ->where('company_id', $companyid)->whereBetween(
                    'created_at',
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                )
                ->where('employee_status', 'Present')->orWhere('employee_status', 'late')
                ->get();

            $presentCount = $present->count();

            $leave = Attendance::where('candidate_id', $user->id)
                ->where('company_id', $companyid)->whereBetween(
                    'created_at',
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                )
                ->where('employee_status', 'Leave')
                ->get();


            $leaveCount = $leave->count();
            $dateExceptAbsent = $present->merge($leave);

            $start_date = Carbon::now()->startOfWeek();
            $end_date = Carbon::now()->endOfWeek();
            $alldaysofweek = [];
            if (($start_date || $start_date != null) || $end_date) {
                for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay(1)) {
                    $day =  $date->format('Y-m-d');
                    $alldaysofweek[$day] = $present->where('created_at', $day);
                }
            }


            dd($alldaysofweek);



            $businessleavedays = $company->businessLeaves->pluck('title') ?? [];


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
            // dd("jflkdsjlkfds")
            // dd($businessleavedays);


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
                )->where('employee_status', 'Present')->orWhere('employee_status', 'late')->get()->groupBy(function ($date) {
                    return Carbon::parse($date->created_at)->format('D');
                });

            dd($weekdata);


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


            $yearlydata = Attendance::where('company_id', $companyid)->whereYear('created_at', $year)->select(DB::raw('sum(earning) as `total_earning`'), DB::raw('YEAR(created_at) year, MONTH(created_at) month'))

                ->groupby('year', 'month')
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
