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


            $leaveDates = array_merge($businessleaveDates, $govenmentLeaveDates);
            $holidays = array_merge($leaveDates, $specialLeaveDates);


            $current_date_time = new DateTime("now");
            $current_date = $current_date_time->format("Y-m-d");


            if (in_array($current_date, $leaveDates) == true) {
                return $this->response->responseError("Today is holiday can't login");
            }


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
                    'candidate_id' =>auth()->user()->id,
                    'company_id' => $companyid,
                    'created_at' => $current_date
                ],[
                    'employee_status' => $employee_status,
                   'start_time' => Carbon::now()
                ] );
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
                    $attendance = Attendance::where('id', $attendanceid)->first();
                    $attendance->company_id = $companyid;
                    $attendance->end_time = Carbon::now();
                    $attendance->earning = $request->earning;
                    if ($attendance->update()) {
                        return $this->response->responseSuccess("Successfully Updated");
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
}
