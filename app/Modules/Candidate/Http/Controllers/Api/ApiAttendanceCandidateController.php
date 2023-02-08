<?php

namespace Candidate\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Candidate\Models\Attendance;
use App\Http\Controllers\Controller;
use Candidate\Models\AttendanceBreak;
use Candidate\Models\CompanyCandidate;
use Carbon\Carbon;
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


            $company = CompanyCandidate::where('company_id', $companyid)
                ->where('candidate_id', auth()->user()->id)->first();


            if ($company) {
                $attendance = new Attendance();
                if (Carbon::parse($company->office_hour_start) > Carbon::now()) {
                    // dd("true");
                    $attendance->employee_status = "Present";
                } else {
                    // dd("false");
                    $attendance->employee_status = "Late";
                }
                $attendance->candidate_id = auth()->user()->id;
                $attendance->company_id = $companyid;
                $attendance->start_time = Carbon::now();

                if ($attendance->save()) {
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
