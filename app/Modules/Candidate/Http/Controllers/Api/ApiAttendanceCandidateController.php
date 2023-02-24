<?php

namespace Candidate\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Candidate\Models\Attendance;
use App\Http\Controllers\Controller;
use App\Models\CompanyGovernmentleave;
use App\Models\CompanySpecialleave;
use Candidate\Models\AttendanceBreak;
use Candidate\Models\CompanyBusinessleave;
use Candidate\Models\CompanyCandidate;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateTime;
use Employer\Models\Company;
use Illuminate\Http\Request;

class ApiAttendanceCandidateController extends Controller
{


    protected $response;
    public function __construct(ResponseService $response)
    {

        $this->response = $response;
    }
    public function attendace()
    {
    }


    public function attendanceBreakStore($id, $breakid = null)
    {
        try {

            $attendance = Attendance::where('id', $id)->first();
            if ($attendance) {
                $attendancebreak = new AttendanceBreak();
                $attendancebreak->attendance_id = $attendance->id;
                $attendancebreak->candidate_id = auth()->user()->id;
                $attendancebreak->start_time = Carbon::now();
                if ($attendancebreak->save()) {
                    $data = [
                        'attendance_id ' => $attendance->id,
                        'break_id' => $attendancebreak->id
                    ];
                    return $this->response->responseSuccess($data, "Successfully Stored", 200);
                }
                return $this->response->responseError("Something went wrong try again later", 403);
            }
            return $this->response->responseError("Attendance not found");
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }



    public function attendanceBreakUpdate($breakid)
    {
        try {


            $attendancebreak = AttendanceBreak::where('id', $breakid)->first();
            if ($attendancebreak) {
                if ($attendancebreak->end_time != null) {
                    return $this->response->responseError("End time already exists");
                }

                $attendancebreak->candidate_id = auth()->user()->id;
                $attendancebreak->end_time = Carbon::now();
                if ($attendancebreak->update()) {
                    return $this->response->responseSuccessMsg("Successfully Updated", 200);
                }
                return $this->response->responseError("Something went wrong try again later", 403);
            }
            return $this->response->responseError("Attendance not found");
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }



    public function attendanceStore($companyid, Request $request)
    {
        try {

            $weekStartDate = now()->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
            $weekEndDate = now()->endOfWeek(Carbon::SATURDAY)->format('Y-m-d');

            $company = Company::where('id', $companyid)->with('businessLeaves')->first();
            $companyBusinessLeaves = $company->businessLeaves->pluck('title')->toArray();

            $businessleaveDates = [];

            $currentWeekDateRange = getDatesFromRange($weekStartDate, $weekEndDate);


            foreach ($currentWeekDateRange as $date) {

                foreach ($companyBusinessLeaves as $leave) {

                    if (Carbon::parse($date)->format('l') == $leave) {
                        array_push($businessleaveDates, $date);
                    }
                }
            }

            $govenmentLeaveDates = CompanyGovernmentleave::where('company_id', $companyid)
                ->whereNotNull('leave_date')
                ->pluck('leave_date')
                ->toArray();

            $specialLeaveDates = CompanySpecialleave::where('company_id', $companyid)
                ->whereNotNull('leave_date')
                ->pluck('leave_date')
                ->toArray();




            // $leaveDates = array_merge($businessleaveDates, $govenmentLeaveDates);
            // $holidays = array_merge($leaveDates, $specialLeaveDates);

            $current_date = Carbon::now()->format("Y-m-d");


            // $current_date = Carbon::parse('2023-02-19')->format("Y-m-d");


            if (in_array($current_date, $specialLeaveDates) == true) {
                return $this->response->responseError("Today is Special holiday can't login");
            }

            if (in_array($current_date, $govenmentLeaveDates) == true) {
                return $this->response->responseError("Today is Government holiday can't login");
            }


            if (in_array($current_date, $businessleaveDates) == true) {
                return $this->response->responseError("Today is business holiday can't login");
            }

            // dd($leaveDates);
            // dd(in_array($current_date, $leaveDates));

            // if (in_array($current_date, $leaveDates) == true) {
            //     return $this->response->responseError("Today is holiday can't login");
            // }


            $company = CompanyCandidate::where('company_id', $companyid)
                ->where('candidate_id', auth()->user()->id)
                ->first();




            if ($company) {
                if (Carbon::parse($company->office_hour_start) > Carbon::now()) {
                    // dd("true");
                    $employee_status = "Present";
                } else {
                    // dd("false");
                    $employee_status = "Late";
                }

                $attendance = Attendance::updateOrCreate([
                    'candidate_id' => auth()->user()->id,
                    'company_id' => $companyid,
                    'created_at' => $current_date
                ], [
                    'employee_status' => $employee_status,
                    'start_time' => Carbon::now()
                ]);



                // $attendance = new Attendance();

                // $attendance->candidate_id = auth()->user()->id;
                // $attendance->company_id = $companyid;
                // $attendance->start_time = Carbon::now();

                if ($attendance) {
                    $data = [
                        'attendance_id' => $attendance->id
                    ];
                    return $this->response->responseSuccess($data, "Successfully stored", 200);
                };
            }

            return $this->response->responseError("Something went wrong");
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function attendanceUpdate($companyid = null, $attendanceid = null, Request $request)
    {
        try {

            if ($attendanceid != null) {
                if ($companyid != null) {


                    $companycandidate = CompanyCandidate::where('company_id', $companyid)
                        ->where('candidate_id', auth()->user()->id)
                        ->first();

                    $attendance = Attendance::where('id', $attendanceid)->first();

                    if ($companycandidate) {

                        $startTime = $attendance->start_time;
                        // $attendance->end_time = Carbon::now();
                        // $attendance->update();


                        $finishTime = Carbon::parse($attendance->end_time);
                        $workingtime =  $finishTime->diffInSeconds($startTime);

                        $workinghour = Carbon::parse($companycandidate->office_hour_start)
                            ->diffInHours($companycandidate->office_hour_end);


                        if ($companycandidate->salary_type == 'monthly') {
                            $monthlysalary = $companycandidate->salary_amount;
                            $daysInMonth = Carbon::now()->daysInMonth;
                            $monthlySalaryInSec = ((float)$monthlysalary / ($daysInMonth * (int) $workinghour * 60 * 60));
                            $monthlySalaryInHour = ((float)$monthlysalary / ($daysInMonth *  (int) $workinghour));


                            // $workingtime =  $finishTime->diff($startTime)->format('%H:%I:%S');
                            // dd($workingtime);

                            // $scheduleTime =Carbon::createFromTimestampUTC($workingtime)->diffInSeconds();


                            $totalHourWorked = Carbon::parse($workingtime)->format('H');
                            $totalMinWorked = Carbon::parse($workingtime)->format('I');
                            $totalSecWorked = Carbon::parse($workingtime)->format('s');
                            $totalIncomeInDay =  $workingtime * $monthlySalaryInSec;

                            $this->overtime($companycandidate, $companyid, $monthlySalaryInHour, $attendance);
                        }


                        if ($companycandidate->salary_type == "weekly") {
                            $weeklySalary = $companycandidate->salary_amount;

                            $weeklySalaryInSec =  ((float)$weeklySalary / (7 * (int) $workinghour * 60 * 60));
                            $totalIncomeInDay =  $workingtime * $weeklySalaryInSec;
                            $monthlySalaryInHour = ((float)$weeklySalary / (7 *  (int) $workinghour));


                            $this->overtime($companycandidate, $companyid, $monthlySalaryInHour, $attendance);
                        }

                        if ($companycandidate->salary_type == "daily") {
                            $dailysalaryInSec = $companycandidate->salary_amount / ((int) $workinghour * 60 * 60);
                            $dailysalaryInHour = $companycandidate->salary_amount / ((int) $workinghour);
                            $totalIncomeInDay = $workingtime * $dailysalaryInSec;

                            $this->overtime($companycandidate, $companyid, $monthlySalaryInHour, $attendance);
                        }
                    }



                    $attendance = Attendance::where('id', $attendanceid)->first();
                    $attendance->company_id = $companyid;
                    $attendance->end_time = Carbon::now();
                    $attendance->earning = round($totalIncomeInDay, 4);
                    if ($attendance->update()) {
                        return $this->response->responseSuccessMsg("Successfully Updated");
                    }
                    return $this->response->responseError("Something went wrong");
                }
                return $this->response->responseError("Company id is required");
            }
            return $this->response->responseError("Attendance id is required");
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    private function overtime($companycandidate = null, $companyid = null, $monthlySalaryInHour = null, $attendance = null)
    {
        //companycandidate total working hours in seconds
        //  dd($companycandidate, $companyid, $monthlySalaryInHour);
        $workingHoursInSec = Carbon::parse($companycandidate->office_hour_start)
            ->diffInSeconds(Carbon::parse($companycandidate->office_hour_end));

        $workingHoursInHour = Carbon::parse($companycandidate->office_hour_start)
            ->diffInHours(Carbon::parse($companycandidate->office_hour_end));


        if ($attendance->end_time != null) {
            $today_workinghours_insec = Carbon::parse($attendance->start_time)
                ->diffInSeconds(Carbon::parse($attendance->end_time));
            $today_workinghours_inhour = Carbon::parse($attendance->start_time)
                ->diffInHours(Carbon::parse($attendance->end_time));
            // dd($today_workinghours_insec);
            if ($today_workinghours_insec > $workingHoursInSec) {
                $difference_workinghours_insec = $today_workinghours_insec - $workingHoursInSec;

                $difference_workinghours_inhour = $today_workinghours_inhour - $workingHoursInHour;
                // dd($difference_workinghours_inhour);
                // dd(CarbonInterval::seconds($difference_workinghours_insec)->cascade());

                if (isset($companycandidate->overtime)) {
                    $overtimerate = $companycandidate->overtime;
                } else {
                    $overtimerate = 0;
                }

                // dd(
                //     $difference_workinghours_inhour,
                //     $difference_workinghours_inhour * $overtimerate * $monthlySalaryInHour,
                //     $overtimerate
                // );

                $attendance = Attendance::updateOrCreate([
                    'candidate_id' => auth()->user()->id,
                    'company_id' => $companyid,
                    'created_at' => Carbon::today()
                ], [
                    'overtime_earning' => $difference_workinghours_inhour * $overtimerate * $monthlySalaryInHour
                ]);

                return true;
            }
        }
    }
}
