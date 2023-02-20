<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Candidate\Http\Requests\RegisterRequest;
use Carbon\Carbon;
use Employer\Http\Requests\EmployerRegisterRequest;
use Employer\Http\Requests\LoginRequest;
use Employer\Http\Requests\ProfileUpdateRequest;
use Employer\Models\Employer;
use Employer\Repositories\auth\AuthEmployerInterface;
use Files\Repositories\FileInterface;
use Illuminate\Http\Request;

class ApiEmployerAuthController extends Controller
{

    protected $authEmployer, $response, $file;
    public function __construct(AuthEmployerInterface $authEmployer, ResponseService $response, FileInterface $file)
    {
        $this->authEmployer = $authEmployer;
        $this->response = $response;
        $this->file = $file;
    }


    //candidate verification with otp
    public function register(EmployerRegisterRequest $request)
    {
        try {


            $employersubmit = $this->authEmployer->register($request);
            if ($employersubmit) {
                $data = [
                    'otp' => $employersubmit['otp'] ?? null,
                    'token' => $employersubmit['token'] ?? null
                ];
                return $this->response->responseSuccess($data, "Successfully Registered. Otp Sent Successfull", 200);
            }
            return $this->response->responseError("Something went wrong", 400);
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function verifyOtp(Request $request)
    {

        try {

            $userdata = $this->authEmployer->verifyOtp($request);
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

            $passwordsubmit = $this->authEmployer->passwordSubmit($request);
            if ($passwordsubmit) {
                $user = $passwordsubmit['user'];
                $token = $passwordsubmit['token'];
                return response(['user' => $user, 'token' => $token]);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }



    public function sendSms($phone_no, $message)
    {

        $args = http_build_query(array(
            'token' => 'v2_buVrQiRyZszCEerwMB60eerIY68.LJkj',
            'from'  => 'InfoAlert',
            'to'    => $phone_no,
            'text'  => $message
        ));

        $url = "http://api.sparrowsms.com/v2/sms/";

        # Make the call using API.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Response
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status_code == 200) {
            // $response = json_decode($response);
            // if($response->response_code == 200){
            //     return true;

            // }
            return true;
        } else {
            throw new Exception('Error While Send OTP Via Sms.');
        }
    }



    public function profileUpdate(ProfileUpdateRequest $request)
    {
        try {
            $user = User::where('id', auth()->user()->id)->first();
            if ($user) {
                $user->firstname = $request->firstname;
                $user->email = $request->email;
                if ($request->uploadfile) {
                    $uploaded = $this->file->storeFile($request->uploadfile);
                    $user->image_id = $uploaded->id;
                }
                if ($user->update()) {
                    $employer = Employer::where('user_id', $user->id)->first();
                    $employer->name = $request->firstname;
                    // $employer->lastname = $request->lastname;
                    $employer->email = $request->email;
                    if ($request->uploadfile) {
                        $uploaded = $this->file->storeFile($request->uploadfile);
                        $employer->profile_id = $uploaded->id;
                    }
                    $employer->dob = Carbon::parse($request->dob);
                    if ($employer->update()) {
                        return $this->response->responseSuccessMsg("Successfully Updated");
                    }
                    return $this->response->responseError("Something went wrong while updating employer");
                }
                return $this->response->responseError("Something went wrong while updating user");
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function login(Request $request)
    {
        try {
            $candidatelogin = $this->authEmployer->login($request);
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


    public function changePhone(Request $request)
    {
        try {

            $changePhone = $this->authEmployer->changePhone($request);
            if ($changePhone) {
                $data = [
                    'otp' => $changePhone['otp']
                ];
                return $this->response->responseSuccess($data, "Successfully changed", 200);
            }
        } catch (\Exception $e) {
        }
    }



}
