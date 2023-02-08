<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Employer\Http\Resources\EmployerCandidateLeaveDetailsResource;
use App\Models\User;
use Candidate\Models\Attendance;
use Candidate\Models\Leave;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Employer\Http\Resources\EmployerCandidateLeaveResource;

class ApiEmployerCandidateLeaveController extends Controller
{

    protected $response;

    public function __construct(ResponseService $response)
    {
        $this->response = $response;

    }

    public function all($companyid = null){
        try{

            $leaves = Leave::where('company_id', $companyid)->with(['candidate','LeaveType'])->get();

            $data = [
                'candidates' =>EmployerCandidateLeaveResource::collection($leaves)
            ];
            return $this->response->responseSuccess($data, "Succcess", 200);



        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }


    public function leaveDetail($id){
        try{
            $user = auth()->user();
            $leave = Leave::where('id', $id)->with(['candidate','LeaveType'])->first();
            $data = [
                'leavedetail' => new EmployerCandidateLeaveDetailsResource($leave)
            ];
            return $this->response->responseSuccess($data, "Succcess", 200);
        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }



    function getDatesFromRange($start, $end, $format='Y-m-d') {
        return array_map(function($timestamp) use($format) {
            return date($format, $timestamp);
        },
        range(strtotime($start) + ($start < $end ? 4000 : 8000), strtotime($end) + ($start < $end ? 8000 : 4000), 86400));
    }


    public function leaveApproval($id){
        try{
            $leave = Leave::where('id', $id)->with(['candidate', 'company', 'leaveType'])->first();
            // $startDate = Carbon::createFromFormat('Y-m-d', $leave->start_date);
            // $endDate = Carbon::createFromFormat('Y-m-d', $leave->end_date);
            // dd($startDate, $endDate);
            if($leave){
                $leave->approved = 1;
                if($leave->update() == true){
                    $dateRange = $this->getDatesFromRange($leave->start_date, $leave->end_date);

                    foreach($dateRange as $date){

                        $attendance = new Attendance();
                        $attendance->candidate_id = $leave->candidate->id;
                        $attendance->company_id = $leave->company->id;
                        $attendance->candidate_id = $leave->candidate->id;
                        $attendance->leave_type_id = $leave->leaveType->id;
                        $attendance->employee_status = "Leave";
                        $attendance->leave_id = $leave->id;
                        $attendance->created_at = Carbon::parse($date);
                        $attendance->save();
                    }

                    return $this->response->responseSuccessMsg("Successfully Updated");
                }
                return $this->response->responseError("Something went wrong while updating leave");
            }
        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }


}
