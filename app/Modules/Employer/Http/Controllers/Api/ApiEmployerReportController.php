<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyCandidateResource;
use App\Http\Resources\YearlyEarningResource;
use App\Models\CompanyGovernmentleave;
use App\Models\CompanySpecialleave;
use App\Models\User;
use Candidate\Http\Resources\CandidateResource;
use Candidate\Models\Attendance;
use Candidate\Models\Candidate;
use Candidate\Models\CompanyBusinessleave;
use Candidate\Models\CompanyCandidate;
use Candidate\Models\Leave;
use Carbon\Carbon;
use Employer\Http\Resources\CompanyCandidateDailyAttendanceReport;
use Employer\Models\Company;
use Employer\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\CssSelector\Node\FunctionNode;

class ApiEmployerReportController extends Controller
{
    protected $response;
    public function __construct(ResponseService $response)
    {
        $this->response = $response;
    }


    public function currentDayReport($id)
    {
        try {


            $companyCandidates = CompanyCandidate::where('company_id', $id)
                // ->where('status', 'Active')
                ->where('verified_status', 'verified')
                ->with([
                    'candidate', 'companyCandidateAttendaces'

                ])
                ->get();

            // $candidates = User::where()



            if ($companyCandidates && $companyCandidates->count() > 0) {
                $companyCandidates =  CompanyCandidateDailyAttendanceReport::collection($companyCandidates);
            }

            // dd($companyCandidate);
            $absentCount = CompanyCandidate::where('company_id', $id)
                ->where('status', 'Active')
                ->where('verified_status', 'verified')->with(
                    'candidate',
                    function ($q) {
                        $q->whereDoesntHave('todayattendances');
                    }
                )->count();



            $presentCount = CompanyCandidate::where('company_id', $id)
                ->where('status', 'Active')
                ->where('verified_status', 'verified')
                ->whereHas(
                    'candidate',
                    function ($q) {
                        $q->whereHas('attendances', function ($q) {
                            $q->whereDate('created_at', today())
                                ->where('employee_status', 'Present');
                        });
                    }
                )->count();



            $lateCount = CompanyCandidate::where('company_id', $id)
                ->where('status', 'Active')
                ->where('verified_status', 'verified')
                ->whereHas(
                    'candidate',
                    function ($q) {
                        $q->whereHas('attendances', function ($q) {
                            $q->whereDate('created_at', today())
                                ->where('employee_status', 'Late');
                        });
                    }
                )->count();

            $totalattendee = CompanyCandidate::where('company_id', $id)
                ->where('verified_status', 'verified')
                ->count();

            // dd($companyCandidate);



            // $company = Company::where('id', $id)->with(['attendances' => function($q){
            //     $q->whereDate('created_at', Carbon::today())->with('candidate');
            // }])
            // ->whereHas('attendances', function($q){
            //     $q->whereDate('created_at', Carbon::today())
            //    ;
            // })
            // ->with('attendances.candidate')
            // ->first();
            // dd($company->attendances);
            // $present = Attendance::where('company_id', $id)->where('created_at', today())
            //     ->where('employee_status', 'Present')
            //     ->count();
            // $late = Attendance::where('company_id', $id)->where('created_at', today())
            //     ->where('employee_status', 'Late')
            //     ->count();
            // $companyCandidates = $company->candidates;
            $data = [
                'total_attendee' =>  $totalattendee ?? 0,
                'present' => $presentCount ?? 0,
                'late' =>  $lateCount  ?? 0,
                'absent' => $absentCount ?? 0,
                'candidates' => $companyCandidates ?? [],
                // 'candidates' => CandidateResource::collection($companyCandidates)
            ];
            return $this->response->responseSuccess($data, "Success", 200);
        } catch (Exception  $e) {
            return $this->response->responseError($e->getMessage());
        }
    }



    public function activeCompanyCandidates($id)
    {
        try {


            $companyCandidates = CompanyCandidate::where('company_id', $id)
                ->where('status', 'Active')
                ->where('verified_status', 'verified')

                ->with([
                    'candidate', 'activecompanyCandidateAttendaces'

                ])
                ->whereHas('activecompanyCandidateAttendaces', function ($q) {
                    $q->whereIn('employee_status', ['Present', 'Late']);
                })
                ->get();




            if ($companyCandidates && $companyCandidates) {
                $candidates =  CompanyCandidateDailyAttendanceReport::collection($companyCandidates);
            }
            $data = [
                'candidates' => $candidates ?? []
            ];
            return $this->response->responseSuccess($data, "Success", 200);
        } catch (Exception  $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function inactiveCompanyCandidates($id)
    {
        try {
            $companyCandidates = CompanyCandidate::where('company_id', $id)
                ->where('status', 'Active')
                ->where('verified_status', 'verified')

                ->with([
                    'candidate', 'activecompanyCandidateAttendaces'

                ])
                ->whereDoesntHave('activecompanyCandidateAttendaces')
                ->get();

            if ($companyCandidates) {
                $candidates =  CompanyCandidateDailyAttendanceReport::collection($companyCandidates);
            }
            $data = [
                'candidates' => $candidates ?? []
            ];

            return $this->response->responseSuccess($data, "Success", 200);
        } catch (Exception  $e) {
            return $this->response->responseError($e->getMessage());
        }
    }



    function weeksInMonth($numOfDaysInMonth)
    {
        $daysInWeek = 7;
        $result = $numOfDaysInMonth / $daysInWeek;
        $numberOfFullWeeks = floor($result);
        // $numberOfRemaningDays = ($result - $numberOfFullWeeks) * 7;
        // return 'Weeks: ' . $numberOfFullWeeks . ' -  Days: ' . $numberOfRemaningDays;
        return $numberOfFullWeeks;
    }




    public function dailyReport($companyid, $candidateid){
        try{

            $weekStart = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            $weekEnd = Carbon::now()->endOfWeek(Carbon::SATURDAY);
            $candidate = CompanyCandidate::where('candidate_id', $candidateid)->first();
            $attendances = Attendance::where('candidate_id', $candidateid)->where('company_id', $companyid)->whereBetween('created_at', [$weekStart, $weekEnd])->get();

            $companyBusinessLeave = CompanyBusinessleave::where('company_id', $candidate->company_id)->get()
            ->pluck('business_leave_id')->toArray();

            $companySpecialLeave = CompanySpecialleave::where('company_id', $candidate->company_id)
            ->whereBetween('leave_date', [$weekStart, $weekEnd])->get() ->pluck('leave_date')->toArray();
            $companyGovermentLeave = CompanyGovernmentleave::where('company_id', $candidate->company_id)
            ->whereBetween('leave_date', [$weekStart, $weekEnd])->get() ->pluck('leave_date')->toArray();
            $reportData = [];
            for ($i = 0; $i <= 6; $i++) {
                $day = $weekStart->copy()->addDays($i);
                $WeekDayNumber = $day->format('w')+1;
                $checkAttendance = checkAttendance($day, $attendances);
                $checkBusinessLeave = checkBusinessLeave($WeekDayNumber, $companyBusinessLeave);
                $checkSpecialHoliday = checkSpecialHoliday($day->format('Y-m-d'), $companySpecialLeave);
                $checkGovermentHoliday = checkGovermentHoliday($day->format('Y-m-d'), $companyGovermentLeave);
                if($checkAttendance){
                    $reportData[ $day->format('Y-m-d')] = $checkAttendance;
                }elseif($checkBusinessLeave){
                    $reportData[ $day->format('Y-m-d')] = $checkBusinessLeave;
                }elseif($checkSpecialHoliday){
                    $reportData[ $day->format('Y-m-d')] = $checkSpecialHoliday;
                }elseif($checkGovermentHoliday){
                    $reportData[ $day->format('Y-m-d')] = $checkGovermentHoliday;
                }else{
                    $reportData[ $day->format('Y-m-d')] = "Absent";
                }
            }

            // dd($reportData);

            $attendance = Attendance::where('candidate_id', $candidateid)
            ->where('company_id', $companyid)
            ->where('created_at', Carbon::today())
            ->where(function($q){
                $q->whereIn('employee_status', ['Present', 'late', 'Leave']);
            })
            ->first();

            if($attendance){
                $startime = $attendance->start_time;
                $endtime = $attendance->end_time;
                $totalearning = $attendance->earning + $attendance->overtime_earning;
                $status = 'present';
            }else{

                $status = 'absent';
            }


            // $startdate = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            // $enddate = Carbon::now()->endOfWeek(Carbon::SATURDAY);


            // $week_dates = getDatesFromRange($startdate->format('Y-m-d'), $enddate->format('Y-m-d'));
            // foreach($week_dates as $date){

            //     if(Carbon::parse($date)->isSunday()){
            //         $status = 'holiday';
            //     }elseif(Carbon::parse($date)->isSaturday()){
            //         $status = 'holiday';
            //     }
            //     elseif(isset($attendance) && $attendance->created_at->format('Y-m-d') == $date){
            //         $status = "present";
            //     }else{
            //         $status = "absent";
            //     }

            //     $newweek_dates[$date] = [
            //         'status' => $status
            //     ];
            // }




            $data = [
                'weekly_datas' => $reportData,
                'start_time' => isset($startime) ? $startime : null,
                'end_time' => isset($endtime) ? $endtime : null,
                'break_time' => isset($attendance) ? $attendance->breakDuration : null,
                'attendance_duration' => isset($attendance) ? $attendance->attendanceDuration : null,
                'earning' => isset($attendance) ? $attendance->earning  : null,
                'overtime_earning' => isset($attendance) ? $attendance->overtime_earning  : null,
                'totalearning' => $totalearning  ?? null,

            ];
            return $this->response->responseSuccess($data, "Success", 200);
            // dd($newweek_dates);

        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }



    public function weeklyReport($companyid, $candidate_id)
    {
        try {

            $weekStart = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            $weekEnd = Carbon::now()->endOfWeek(Carbon::SATURDAY);
            $candidate = CompanyCandidate::where('candidate_id', $candidate_id)->first();
            $attendances = Attendance::where('candidate_id', $candidate_id)->where('company_id', $companyid)->whereBetween('created_at', [$weekStart, $weekEnd])->get();
            $companyBusinessLeave = CompanyBusinessleave::where('company_id', $candidate->company_id)->get()
            ->pluck('business_leave_id')->toArray();

            $companySpecialLeave = CompanySpecialleave::where('company_id', $candidate->company_id)
            ->whereBetween('leave_date', [$weekStart, $weekEnd])->get() ->pluck('leave_date')->toArray();
            $companyGovermentLeave = CompanyGovernmentleave::where('company_id', $candidate->company_id)
            ->whereBetween('leave_date', [$weekStart, $weekEnd])->get() ->pluck('leave_date')->toArray();
            $reportData = [];
            for ($i = 0; $i <= 6; $i++) {
                $day = $weekStart->copy()->addDays($i);
                $WeekDayNumber = $day->format('w')+1;
                $checkAttendance = checkAttendance($day, $attendances);
                $checkBusinessLeave = checkBusinessLeave($WeekDayNumber, $companyBusinessLeave);
                $checkSpecialHoliday = checkSpecialHoliday($day->format('Y-m-d'), $companySpecialLeave);
                $checkGovermentHoliday = checkGovermentHoliday($day->format('Y-m-d'), $companyGovermentLeave);
                if($checkAttendance){
                    $reportData[ $day->format('Y-m-d')] = $checkAttendance;
                }elseif($checkBusinessLeave){
                    $reportData[ $day->format('Y-m-d')] = $checkBusinessLeave;
                }elseif($checkSpecialHoliday){
                    $reportData[ $day->format('Y-m-d')] = $checkSpecialHoliday;
                }elseif($checkGovermentHoliday){
                    $reportData[ $day->format('Y-m-d')] = $checkGovermentHoliday;
                }else{
                    $reportData[ $day->format('Y-m-d')] = "Absent";
                }
            }
            // dd($reportData);




            $reportDataCollection = collect($reportData);

            $absentdates = array_filter($reportData, function ($var) {
                return ($var == "Absent");
            });


            foreach ($absentdates as $key => $value) {
                $attendance = Attendance::updateOrCreate([
                    'candidate_id' => $candidate_id,
                    'company_id' => $companyid,
                    'created_at' => Carbon::parse($key)
                ], [
                    'employee_status' => "Absent"
                ]);
            }

            $counts = array_count_values($reportData);

            $presentCount = $counts['Present'] ?? 0;
            $absentCount = $counts['Absent'] ?? 0;
            $leaveCount = $counts['leave'] ?? 0;
            $businessleaveCount = $counts['Business Holiday'] ?? 0;
            $governmentleaveCount = $counts['Government Holiday'] ?? 0;
            $specialleaveCount = $counts['Special Holiday'] ?? 0;

            // dd($reportDataCollection->where())

            $companyCandidate = CompanyCandidate::where('company_id', $companyid)
                ->where('candidate_id', $candidate_id)->first();

            // if($companyCandidate){
                $candidateMonthlySalary = $companyCandidate->salary_amount;

                $numberOfDaysInMonth = Carbon::now()->daysInMonth;
                $weekInCurrentMonth = (int) $this->weeksInMonth($numberOfDaysInMonth);

                $daysInCurrentMonth = (int) Carbon::parse(today())->daysInMonth;

                $salaryInWeek = (float)$candidateMonthlySalary / $weekInCurrentMonth;
                $salaryPerDay = (float)$candidateMonthlySalary / $daysInCurrentMonth;

                $salaryCountingdays = 7 - $absentCount;

                $currentweekSalary = floor($salaryCountingdays * $salaryPerDay);

                $data = [
                    'present' =>  $presentCount ?? 0,
                    'absent' => $absentcount ?? 0,
                    'leave' => $leaveCount ?? 0,
                    'weekdata ' =>  $reportDataCollection   ?? [],
                    'businessleaveCount' => $businessleaveCount ?? 0,
                    'governmentLeaveCount' => $governmentleaveCount ?? 0,
                    'specialLeaveCount' => $specialleaveCount ?? 0,
                    'current_week_salary' => $currentweekSalary
                ];

                return $this->response->responseSuccess($data, "Success", 200);
            // }
            // return $this->response->responseError("Company not found");

        } catch (Exception  $e) {
            return $this->response->responseError($e->getMessage());
        }
    }

    public function monthlyReport($companyid, $candidate_id, $month = null)
    {
        try {


            if ($month == null && $month == 0) {
                $month = now()->format('m');
                // $startDate = Carbon::parse(Carbon::now()->startOfMonth());
                // $endDate = Carbon::parse(Carbon::now());
                $totaldays = Carbon::now()->daysInMonth;
                // $today = Carbon::today()->format('d');
                $monthStart = Carbon::now()->startOfMonth();
                $monthEnd = Carbon::now();
            } else {
                $year = date("Y");
                $date = $year . '/' . $month . '/' . '1';
                $totaldays = Carbon::parse($date)->daysInMonth;


                $monthStart = Carbon::createFromFormat('Y/m/d', $date)
                    ->firstOfMonth()
                    ->format('Y-m-d');
                $monthStart = Carbon::parse($monthStart);


                $monthEnd = Carbon::createFromFormat('Y/m/d', $date)
                    ->endOfMonth()
                    ->format('Y-m-d');
                $monthEnd = Carbon::parse($monthEnd);
            }
            // dd($candidate_id, $companyid);

            // dd($monthStart, $monthEnd);

            $attendanceData = DB::table('attendances')
                ->join('company_specialleaves', function ($join) use ($monthStart, $monthEnd) {
                    $join->on('attendances.company_id', '=', 'company_specialleaves.company_id')
                        ->whereBetween('company_specialleaves.leave_date', [$monthStart, $monthEnd]);
                })
                ->join('company_governmentleaves', function ($join) use ($monthStart, $monthEnd) {
                    $join->on('attendances.company_id', '=', 'company_specialleaves.company_id')
                        ->whereBetween('company_governmentleaves.leave_date', [$monthStart, $monthEnd]);
                })
                ->join('company_businessleaves', function ($join) {
                    $join->on('attendances.company_id', '=', 'company_businessleaves.company_id');
                })

                ->where('attendances.candidate_id', '=', $candidate_id)
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
            for ($i = 0; $i <= (int) ($totaldays - 1); $i++) {
                $day = $monthStart->copy()->addDays($i);
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
                    } elseif ($day->format('w') == $data->business_date) {
                        // dd('sadsad');

                        $reportData[$day->format('Y-m-d')] = 'Business Holiday';
                    }
                }
            }






            $reportDataCollection = collect($reportData);


            $counts = array_count_values($reportData);

            $presentCount = $counts['Present'] ?? 0;
            $absentCount = $counts['Absent'] ?? 0;
            $leaveCount = $counts['leave'] ?? 0;
            $businessleaveCount = $counts['Business Holiday'] ?? 0;
            $governmentleaveCount = $counts['Government Holiday'] ?? 0;
            $specialleaveCount = $counts['Special Holiday'] ?? 0;



            $company = Company::where('id', $companyid)
                ->where('status', 'Active')
                // ->where('employer_id', auth()->user()->id)
                ->first();


            // dd($candidate_id);
            $Sickleaves = Leave::where('candidate_id', $candidate_id)
                ->with('LeaveType')
                ->whereHas('LeaveType', function ($q) {
                    $q->where('title', 'Sick');
                })
                ->where('approved', 1)
                ->whereMonth('created_at',$month)
                ->get();

            $counter = 0;
            foreach($Sickleaves as $sickleave){
                $counter = $counter + (int) Carbon::parse($sickleave->start_date)->diffInDays(Carbon::parse($sickleave->end_date));
            }




            $company_totalavailablesickleave =  $company->leave_duration ?? null;

            if(isset($company_totalavailablesickleave)  && isset($counter)){
                $totalsickdaysleft = $company_totalavailablesickleave - $counter;
            }






            // $company = Company::where('id', $companyid)->with('govLeaves', 'specialLeaves', 'businessLeaves')->withCount(['govLeaves' => function ($q) use ($month){
            //     $q->whereMonth('leave_date', $month);
            // }])->withCount(['specialLeaves' => function ($q) use ($month) {
            //     $q->whereMonth('leave_date', $month);
            // }])->first();

            // $governmentLeavedaysCount = $company->gov_leaves_count ?? 0;
            // $specialLeavedaysCount = $company->special_leaves_count ?? 0;

            // $businessleavedays = $company->businessLeaves->pluck('title') ?? [];
            // $startDate = Carbon::parse(Carbon::now()->startOfMonth());
            // $endDate = Carbon::parse(Carbon::now());
            // $totaldays = Carbon::now()->daysInMonth;
            // $today = Carbon::today()->format('d');


            // $businessleavedays = $startDate->diffInDaysFiltered(function (Carbon $date) use ($businessleavedays) {
            //     foreach ($businessleavedays as $leaveday) {
            //         if ($leaveday == "Saturday") {
            //             $exceptSaturday =  $date->isSaturday();
            //         }
            //         if ($leaveday == "Sunday") {

            //             $exceptSunday =  $date->isSunday();
            //         }
            //         if ($leaveday == "Monday") {
            //             $exceptMonday = $date->isMonday();
            //         }
            //         if ($leaveday == "Tuesday") {
            //             $exceptTuesday = $date->isTuesday();
            //         }
            //         if ($leaveday == "Wednesday") {
            //             $exceptWednesday = $date->isWednesday();
            //         }
            //         if ($leaveday == "Thursday") {
            //             $exceptThursday = $date->isThursday();
            //         }
            //         if ($leaveday == "Friday") {
            //             $exceptFriday = $date->isFriday();
            //         }
            //         $newdate = ($exceptSaturday ?? 0) + ($exceptMonday ?? 0) + ($exceptTuesday ?? 0) + ($exceptSunday ?? 0) +
            //             ($exceptWednesday ?? 0) + ($exceptThursday ?? 0) + ($exceptFriday ?? 0);
            //     }
            //     return $newdate;
            // }, $endDate);


            // $user = auth()->user();
            // $presentCount = Attendance::where('candidate_id', $candidate_id)
            //     ->where('company_id', $companyid)
            //     ->whereMonth('created_at', Carbon::now()->month)
            //     ->where('employee_status', 'Present')
            //     ->count();
            // $leaveCount = Attendance::where('candidate_id', $candidate_id)
            //     ->where('company_id', $companyid)
            //     ->whereMonth('created_at', Carbon::now()->month)
            //     ->where('employee_status', 'Leave')
            //     ->count();

            // $totalworkingdays = ($totaldays - $businessleavedays - $governmentLeavedaysCount - $specialLeavedaysCount);

            // $absentcount = (int) $today  - $presentCount - $leaveCount;

            // dd($absentcount);

            $data = [
                'presentCount' => $presentCount ?? 0,
                'absentcount' => $absentCount ?? 0,
                'leavecount' => $leaveCount ?? 0,
                'totaldays' => $totaldays ?? 0,
                'businessleavedays' => $businessleaveCount ?? 0,
                'governmentLeavedaysCount' => $governmentleaveCount ?? 0,
                'specialLeavedaysCount' => $specialleaveCount ?? 0,
                'salary' => 20000,
                'total_leave' => $company_totalavailablesickleave ?? 7,
                'total_available' => $totalsickdaysleft ?? 0,
                'taken' => $counter ?? 0


            ];

            return $this->response->responseSuccess($data, "Success", 200);
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function yearlyReport($companyid, $candidate_id, $year = null)
    {
        try {

            if (!$year) {
                $year = date('Y');
            }

            // $month = DB::table('attendances')
            // ->select('candidate_id', 'company_id', DB::raw('SUM(earning) AS total_earning'))
            // ->get();


            // $month = Attendance::select(DB::raw('earning as earnings'), 'candidate_id', 'company_id', 'candidate_id')
            // ->select(DB::raw('company_id,candidate_id'))
            // ->selectRaw('year(created_at) year, monthname(created_at) month')
            //  ->groupBy('year', 'month')
            // ->get();
            // dd($month);



            // dd($month);
            // $month = DB::table('attendances')
            // ->where('company_id', $companyid)
            // ->where('candidate_id', $candidate_id)
            // ->select(DB::raw('SUM(earning) as total_earning, DATE(created_at) as created_date'))
            // ->groupBy(DB::raw('DATE(created_at)'))
            // ->get();
            // dd($month);


            // $monthly = Attendance::select('id')->whereYear('created_at',2023)
            // ->select(DB::raw("(sum(earning)) as earning"), DB::raw("GROUP_CONCAT(DISTINCT candidate_id) as candidate"),
            //     DB::raw("GROUP_CONCAT(DISTINCT company_id) as company"))


            // ->groupBy(DB::raw("DATE_FORMAT(created_at,'%M')"))
            // ->get();
            // dd($monthly);

            $datas = Attendance::where('candidate_id', $candidate_id)
                ->where('company_id', $companyid)
                // ->select('company_id', 'candidate_id')

                // ->selectRaw('year(created_at) year, monthname(created_at) month, sum(earning) as total_earning ')
                ->select(
                    DB::raw("(sum(earning)) as total_earning"),
                    DB::raw("GROUP_CONCAT(DISTINCT candidate_id) as candidate"),
                    DB::raw("GROUP_CONCAT(DISTINCT company_id) as company"),
                    DB::raw("DATE_FORMAT(created_at,'%M') as month")
                )



                // ->groupBy('year', 'month')
                ->groupBy(DB::raw("DATE_FORMAT(created_at,'%M')"))
                // ->orderBy('year', 'desc')
                ->get();
            // ->groupBy(function ($date) {
            //     return Carbon::parse($date->created_at)->format('M');
            // });
            dd($datas->pluck('month')->toArray());

            // foreach($datas as $data ){
            //     if($data->month )
            // }


            $data = [
                'datas' => YearlyEarningResource::collection($datas)
            ];
            return $this->response->responseSuccess($data, "Success", 200);
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function paymentSubmit(Request $request, $company_id, $candidate_id)
    {
        try {

            $payment = new Payment();
            $payment->status = 'Paid';
            $payment->paid_amount = $request->paid_amount;
            $payment->payment_date = Carbon::now();
            $payment->payment_for_month = $request->payment_for_month;
            $payment->company_id = $company_id;
            $payment->candidate_id = $candidate_id;
            $payment->employer_id = Auth::id();
            if ($payment->save() == true) {
                return $this->response->responseSuccessMsg("Successfully Stored", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }



    public function checkPayment($companyid, $candidateid, $month)
    {
        try {

            $candidatePayment = Payment::where('company_id', $companyid)
                ->where('candidate_id', $candidateid)
                ->whereMonth('payment_for_month', $month)
                ->exists();



            if ($candidatePayment) {
                $status = 'paid';
            } else {
                $status = 'unpaid';
            }
            return $this->response->responseSuccess($status, "Successfully fetched", 200);
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }
}
