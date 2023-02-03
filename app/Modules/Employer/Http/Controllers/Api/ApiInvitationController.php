<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AllCandidateInvitationResource;
use App\Http\Resources\UserResource;
use App\Models\Invitation;
use App\Models\User;
use Candidate\Http\Resources\CandidateResource;
use Employer\Http\Resources\InvitationResource;
use Employer\Http\Resources\LeavetypeResource;
use Employer\Models\LeaveType;
use Illuminate\Support\Facades\Auth;

class ApiInvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     protected $response;
     public function __construct(ResponseService $response)
     {

         $this->response = $response;
     }


    public function index($company_id)
    {
        try{
            $user = Auth::user();
            $invitations = Invitation::where('company_id',$company_id)
                            ->with('candidate')
                            ->latest()->get();
            // $invitations = LeaveType::where('company_id',$company_id)->latest()->get();
            $data = [
                'invitations' => InvitationResource::collection($invitations)
            ];

            return $this->response->responseSuccess($data, "Success", 200);


        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }

    public function allCandidates($company_id)
    {
        try{
            $user = Auth::user();

            $users = User::where('type','candidate')->with('candidate')
                            ->whereDoesntHave('receivedCompanyInvitation')
                            ->latest()->get();

            $data = [
                'users' => AllCandidateInvitationResource::collection($users),

            ];
            return $this->response->responseSuccess($data, "Success", 200);
        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }




    public function store(Request $request,$company_id)
    {
        try{
            // dd($request->all());
            $user = Auth()->user();
            $invitation = new Invitation();
            $invitation->employer_id =$user->id;
            $invitation->candidate_id =$request->candidate_id;
            $invitation->status =$request->status;
            $invitation->company_id = $company_id;

            if($invitation->save() == true){
                return $this->response->responseSuccessMsg("Successfully Created", 200);
            }
            return $this->response->responseError("Something Went Wrong While Saving. Please Try Again.");

        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {

    }


    public function update(Request $request,$company_id ,$invitation_id)
    {
        try{
            $user_id = Auth()->id();
            $invitation = Invitation::where('company_id',$company_id)->where('id',$invitation_id)->first();
            if($invitation){
                $invitation->employer_id =$user_id;
                $invitation->candidate_id =$request->candidate_id;
                $invitation->status =$request->status;
                $invitation->company_id = $company_id;
                if($invitation->update() == true){
                    return $this->response->responseSuccessMsg("Successfully Updated", 200);
                }
                return $this->response->responseError("Something Went Wrong While Updating. Please Try Again.");
            }
            return $this->response->responseError("Invitation Type Not Found",404);

        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }


    }


    public function destroy($company_id,$invitation_id)
    {
        try{
            $invitation = Invitation::where('company_id',$company_id)->where('id',$invitation_id)->first();
            if($invitation){
                $invitation->delete();
                return $this->response->responseSuccessMsg("Successfully Deleted", 200);
            }
            return $this->response->responseError("Invitation Record Not Found",404);
        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }
}
