<?php

namespace Candidate\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Candidate\Models\Candidate;
use App\Models\User;
use Candidate\Http\Requests\CandidateRegisterRequest;
use Candidate\Http\Requests\RegisterRequest;
use Candidate\Repositories\auth\AuthCandidateInterface;
use Candidate\Http\Requests\ProfileUpdateRequest;
use Carbon\Carbon;
use Files\Repositories\FileInterface;
use Illuminate\Http\Request;

class ApiCandidateAuthController extends Controller
{

    protected $authCandidate, $response, $file;
    public function __construct(AuthCandidateInterface $authCandidate, ResponseService $response, FileInterface $file)
    {
        $this->authCandidate = $authCandidate;
        $this->response = $response;
        $this->file = $file;
    }


    //candidate verification with otp
    public function register(CandidateRegisterRequest $request)
    {
        try {

            $candidatesubmit = $this->authCandidate->register($request);
            if ($candidatesubmit) {
                $data = [
                    'otp' => $candidatesubmit['otp'] ?? null,
                    'token' => $candidatesubmit['token'] ?? null
                ];
                return $this->response->responseSuccess($data,"Successfully Registered. Otp Sent Successfull", 200);
            }
            return $this->response->responseError("Something went wrong", 400);
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function verifyOtp(Request $request)
    {

        try {
            $userdata = $this->authCandidate->verifyOtp($request);
            if ($userdata) {
                $data = [
                    'user' => new UserResource($userdata['user']),
                    'token' => $userdata['token']
                ];
                return $this->response->responseSuccess($data, "Successfully Verified", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }

    public function passwordSubmit(Request $request)
    {
        try {

            $passwordsubmit = $this->authCandidate->passwordSubmit($request);
            if ($passwordsubmit) {
                $user = $passwordsubmit['user'];
                $token = $passwordsubmit['token'];
                return response(['user' => $user, 'token' => $token]);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function login(Request $request)
    {
        try {
            $candidatelogin = $this->authCandidate->login($request);
            if ($candidatelogin) {
                $data = [
                    'user' => $candidatelogin['user'],
                    'token' => $candidatelogin['token']
                ];
                return $this->response->responseSuccess($data, "Success", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }


        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($data)) {
            return response(['error_message' => 'Incorrect Details.
            Please try again']);
        }

        $token = auth()->user()->createToken('API Token')->accessToken;

        return response(['user' => auth()->user(), 'token' => $token]);
    }



    public function profileUpdate(ProfileUpdateRequest $request){
        try{

            $user = User::where('id', auth()->user()->id)->candidateCheck()->first();


            if($user){
                $user->name = $request->firstname;
                $user->email = $request->email;
                if($request->uploadfile){
                    $uploaded = $this->file->storeFile($request->uploadfile);
                    $user->image_id = $uploaded->id;
                }
                if($user->update()){
                    $candidate = Candidate::where('user_id', $user->id)->first();

                    $candidate->firstname = $request->firstname;
                    $candidate->lastname = $request->lastname;
                    $candidate->email = $request->email;
                    if($request->uploadfile){
                        $uploaded = $this->file->storeFile($request->uploadfile);
                        $candidate->profile_id = $uploaded->id;
                    }
                    $candidate->dob = Carbon::parse($request->dob);
                    if($candidate->update()){
                        return $this->response->responseSuccessMsg("Successfully Updated");
                    }
                    return $this->response->responseError("Something went wrong while updating candidate");
                }
                return $this->response->responseError("Something went wrong while updating user");
            }
        }catch(\Exception $e){
            return $this->response->responseError($e->getMessage());
        }
    }


    public function logout()
    {
        try {
            $user = auth()->user()->token();
            $user->revoke();
            return $this->response->responseSuccessMsg("Successfully logged out");
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }



    public function changePhone(Request $request){
        try {

            $changePhone = $this->authCandidate->changePhone($request);
            if($changePhone ){
                $data = [
                    'otp' => $changePhone['otp']
                ];
                return $this->response->responseSuccess($data, "Successfully changed", 200);
            }

        } catch (\Exception $e) {

    }
}


}
