<?php

namespace Candidate\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Candidate\Http\Resources\CandidateInvitationResource;
use Candidate\Models\Leave;
use Candidate\Http\Resources\CandidateLeaveResource;
use Candidate\Models\CompanyCandidate;
use Employer\Repositories\candidate\CandidateInterface;
use Employer\Http\Resources\LeavetypeResource;
use Employer\Models\Company;
use Employer\Models\LeaveType;
use Files\Repositories\FileInterface;

class ApiCandidateInvitationController extends Controller
{
    protected $response, $candidate,$file;

    public function __construct(ResponseService $response, CandidateInterface $candidate, FileInterface $file)
    {
        $this->response = $response;
        $this->file = $file;
        $this->candidate = $candidate;
    }


    public function allCandidateInvitations(){
        try{
            $user_id = Auth()->id();
            $invitations = Invitation::where('candidate_id',$user_id)
            ->where('status','Not-Approved')
                            ->with('company','employer')->latest()->get();

            if($invitations){

                $candidateInvitations = CandidateInvitationResource::collection($invitations);

            }

            $data = [
                'candidateInvitations' =>$candidateInvitations ?? []
            ];
            return $this->response->responseSuccess($data, "Successfully Retrieved", 200);

        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }



    public function updateCandidateInvitation(Request $request,$invitation_id){
        try{


            $user = auth()->user();
            // $leave = Leave::where('user_id',$user->id)->where('company_id',$company_id)->where('id',$id);
            $invitation = Invitation::where('id',$invitation_id)->where('candidate_id',$user->id)->first();

            if($invitation){

                if($invitation->status == "Not-Approved"){
                    $invitation->status =$request->status;

                    if($invitation->update() == true){
                        $companycandidate = CompanyCandidate::updateOrCreate([
                            'company_id' => $invitation->company_id,
                            'candidate_id' => $user->id,
                        ], [

                            'verified_status' => 'verified',
                            'status' => 'Active',

                        ]);

                        // $company = Company::where('id', $invitation->company_id)->first();
                        // $companycandidate  = new CompanyCandidate();
                        // $companycandidate->status = 'Active';
                        // $companycandidate->verified_status = 'verified';
                        // $companycandidate->candidate_id = $user->id;
                        // $companycandidate->company_id = $company->id;
                        // $companycandidate->user_id = $user->id;
                        // $companycandidate->office_hour_start = $company->office_hour_start;
                        // $companycandidate->office_hour_end = $company->office_hour_end;
                        // $companycandidate->save();


                        return $this->response->responseSuccessMsg("Successfully Created", 200);
                    }
                    return $this->response->responseError("Something Went Wrong While Updateing. Please Try Again.");

                    }
                return $this->response->responseError("Can Not Update Approved Join Request.",404);
            }
            return $this->response->responseError("Join Request Not Found",404);


        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }

}
