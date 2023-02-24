<?php

namespace Candidate\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CompanyGovernmentleave;
use App\Models\CompanySpecialleave;
use Candidate\Models\Leave;
use Candidate\Http\Resources\CandidateLeaveResource;
use Candidate\Models\CompanyBusinessleave;
use Employer\Repositories\candidate\CandidateInterface;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Employer\Http\Resources\LeavetypeResource;
use Employer\Models\Company;
use Employer\Models\LeaveType;
use Exception;
use Files\Repositories\FileInterface;


class ApiCandidateLeaveController extends Controller
{
    protected $response, $candidate,$file;

    public function __construct(ResponseService $response, CandidateInterface $candidate, FileInterface $file)
    {
        $this->response = $response;
        $this->file = $file;
        $this->candidate = $candidate;
    }

    public function getLeaveTypes(){
        try{
            $leaveTypes = LeaveType::get();

            if($leaveTypes){
                $leavetypes = LeavetypeResource::collection($leaveTypes);
            }
            $data = [
                'leaveTypes' => $leavetypes ?? []
            ];
            return $this->response->responseSuccess($data, "Successfully Retrieved", 200);

        } catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }


    public function allCandidateLeave($company_id){
        try{
            $user_id = auth()->id();
            $userLeaves = Leave::where('user_id',$user_id)->where('company_id',$company_id)->get();
            if($userLeaves){
                $candidateLeaves = CandidateLeaveResource::collection($userLeaves);
            }
            $data = [
                'candidateLeaves' => $candidateLeaves ?? []
            ];
            return $this->response->responseSuccess($data, "Successfully Retrieved", 200);

        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }



    private function getSundays()
    {
        return new \DatePeriod(
            Carbon::parse("first sunday of this month"),
            CarbonInterval::week(),
            Carbon::parse("first sunday of next month")
        );
    }


    public function storeCandidateLeave(Request $request, $company_id){
        try{

            // $dates = getDatesFromRange(Carbon::parse($request->start_date), Carbon::parse($request->end_date));


            // $govenmentLeaveDates = CompanyGovernmentleave::where('company_id', $company_id)
            // ->whereNotNull('leave_date')
            // ->pluck('leave_date')
            // ->toArray();

            // $specialLeaveDates = CompanySpecialleave::where('company_id', $company_id)
            // ->whereNotNull('leave_date')
            // ->pluck('leave_date')
            // ->toArray();


            // $company = Company::where('id', $company_id)
            // ->with('businessLeaves')->first();
            // $companyBusinessLeaves = $company->businessLeaves->pluck('title')->toArray();


            // $leaveDates = array_merge($govenmentLeaveDates,$specialLeaveDates);
            // $startOfMonth = Carbon::now()->startOfMonth();
            // $endOfMonth = Carbon::now()->endOfMonth();


            // $sunday = now()->startOfWeek(Carbon::SUNDAY);
            // $saturday = now()->endOfWeek(Carbon::SATURDAY);
            // $businessdates = [
            //     '0' => $sunday->format('Y-m-d'),
            //     '1' => $saturday->format('Y-m-d')
            // ];

            // $leaveDates = array_merge($leaveDates, $businessdates);

            // foreach($dates as $date){
            //     if(in_array($date, $leaveDates)){
            //         $LeaveExists = true;
            //         break;
            //     }else{
            //         $LeaveExists = false;

            //     }
            // }
            // if($LeaveExists === false){
                $user = auth()->user();
                $leave = new Leave();
                $leave->candidate_id =$user->id;
                $leave->start_date = Carbon::parse($request->start_date);
                $leave->end_date = Carbon::parse($request->end_date);
                $leave->remarks = $request->remarks;
                $leave->leave_type_id = $request->leave_type_id;
                $leave->type = $request->type;
                $leave->approved =0;
                $leave->company_id = $company_id;
                if($request->has('document')){
                    $uploadFile = $this->file->storeFile($request->document);
                    if($uploadFile){
                        $leave->document_id = $uploadFile->id;
                    }
                }
                if($leave->save() == true){
                    return $this->response->responseSuccessMsg("Successfully Created", 200);
                }
                return $this->response->responseError("Something Went Wrong While Saving. Please Try Again.");
            // }

            // throw new Exception("Date already exists in special holiday  or government holiday or business holiday ");



        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }


    public function updateCandidateLeave(Request $request, $company_id,$leave_id){
        try{
            $user = auth()->user();
            // $leave = Leave::where('user_id',$user->id)->where('company_id',$company_id)->where('id',$id);
            $leave = Leave::where('company_id',$company_id)->where('id',$leave_id)->first();
            if($leave){
                if($leave->approved == 0){
                    $leave->candidate_id =$user->candidate->id ;
                    $leave->user_id = $user->id;
                    $leave->start_date = Carbon::parse($request->start_date);
                    $leave->end_date = Carbon::parse($request->end_date);
                    $leave->remarks = $request->remarks;
                    $leave->leave_type_id = $request->leave_type_id;
                    $leave->status =$request->status ;
                    $leave->company_id = $request->company_id;
                    if($request->has('document')){
                        $uploadFile = $this->file->storeFile($request->document);
                        if($uploadFile){
                            $leave->document_id = $uploadFile->id;
                        }
                    }
                    if($leave->update() == true){
                        return $this->response->responseSuccessMsg("Successfully Created", 200);
                    }
                    return $this->response->responseError("Something Went Wrong While Updateing. Please Try Again.");

                    }
                return $this->response->responseError("Can Not Update Approved Leave Request.",404);
            }
            return $this->response->responseError("Leave Record Not Found",404);


        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }




    public function deleteCandidateLeave($company_id,$leave_id){
        try{
            $leave = Leave::where('company_id',$company_id)->where('id',$leave_id)->first();
            if($leave){
                $leave->delete();
                return $this->response->responseSuccessMsg("Successfully Deleted", 200);
            }
            return $this->response->responseError("Leave Record Not Found",404);
        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }


}
