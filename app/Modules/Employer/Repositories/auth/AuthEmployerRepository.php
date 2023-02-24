<?php

namespace Employer\Repositories\auth;

use App\Models\User;
use App\Models\UserOtp;
use Carbon\Carbon;
use Employer\Models\Employer;
use Employer\Repositories\auth\AuthEmployerInterface;
use Exception;
use Files\Repositories\FileInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class AuthEmployerRepository implements AuthEmployerInterface
{

    protected $file = null;
    public function __construct(FileInterface $file)
    {
        $this->file = $file;
    }



    function sendSms($phone, $message)
    {

        $args = array(
            'apikey' => 'diwZp392vfj329fff3@zzvcne2308fE3f29fhnd249',
            'from'  => 'InfoAlert',
            'contacts'  => [$phone],
            "message_type" => "plain",
            'message'  => $message,
            "sender_id" => [
                "nt" => "MD_Alert",
                "ncell" => "MD_Alert",
                "smart" => "MD_Alert"
            ],
            "billing_type" =>  "bulk",
            "tag" => "TAG1"
        );


        $args = json_encode($args);

        $url = "https://api.easyservice.com.np/api/v1/sms/send";

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
            $response = json_decode($response);
            return $response;
        } else {
            throw new Exception('Error While Send OTP Via Sms.');
        }
    }

    public function register($request)
    {

        $user = User::where('phone', $request->phone)
            ->where('type', 'employer')
            ->first();


        if (isset($user) && $user->getRoleNames()->count() == 0 && ($user->getRoleNames()->contains('employer') == false)) {
            $user->assignRole('employer');
        }




        if (!$user) {
            $user = User::create([
                'phone' => $request->phone,
                'password' => bcrypt($request->phone),
                'type' => 'employer',
            ]);


            if ($user) {
                $user->assignRole('employer');

                $user->otp()->updateOrCreate([
                    'user_id' => $user->id
                ], [
                    'otp' => str_pad(rand(0, pow(10, 4)-1), 4, '0', STR_PAD_LEFT)
                ]);

                $employer = new Employer();
                $employer->code = 'E-' . Str::random(20);
                $employer->phone = $request->phone;
                $employer->user_id = $user->id;

                if ($employer->save()) {
                    // $message = "Please verify using otp: " . $user->otp->otp;
                    // $sendSms =  $this->sendSms($user->phone, $message);
                    // if ($sendSms) {
                        return [
                            'otp' => $user->otp->otp
                        ];
                    // }
                }
                throw new Exception("Something went wrong");
            }

            throw new Exception("Something went wrong while creating candidate");
        }
        $token =  $user->createToken('API Token')->accessToken;


        $user->otp()->updateOrCreate([
            'user_id' => $user->id
        ], [
            'otp' => str_pad(rand(0, pow(10, 4)-1), 4, '0', STR_PAD_LEFT)
        ]);
        $otp = $user->otp->otp;

        // $message = "Please verify using otp: " . $otp;
        // $sendSms =  $this->sendSms($user->phone, $message);
        // if ($sendSms) {



            return [
                'otp' => $otp,
                'token' => $token
            ];
        // }
    }



    public function verifyOtp($request)
    {
        $user = User::where('phone', $request->phone)->where('type', 'employer')->first();
        if ($user) {
            $useropt = UserOtp::where('user_id', $user->id)
                ->where('otp', $request->otp)->first();
            if ($useropt) {

                $user->password = bcrypt($request->phone);
                $user->update();

                // $dbtimestamp = strtotime($useropt->updated_at);
                $token = $user->createToken('API Token')->accessToken;

                // if (time() - $dbtimestamp > 15 * 60) {
                //     throw new \Exception('OTP Has Expired. Please Request New OTP and Verify.');
                // }
                if ($request->otp == $useropt->otp) {
                    $user->otp = $request->otp;
                    return [
                        'user' => $user,
                        'token' =>  $token
                    ];
                }
            }
            throw new Exception("OTP not found. Please Request New OTP and Verify.");
        }
        throw new Exception("User not found");
    }


    public function passwordSubmit($request)
    {
        $user = User::where('phone', $request->phone)->with('otp')->first();
        if ($user) {
            $user->password = bcrypt($request->password);
            $user->update();

            $user->otp->delete();
            $token = $user->createToken('API Token')->accessToken;
            return [
                'user' => $user,
                'token' =>  $token
            ];
        }
        throw new Exception("user not found");
    }


    public function login($request)
    {
        $data = [
            'phone' => $request->phone,
            'password' => $request->password
        ];

        if (!auth()->attempt($data)) {
            throw new Exception('Incorrect Details.Please try again');
        }

        $token = auth()->user()->createToken('API Token')->accessToken;

        return [
            'user' => auth()->user(),
            'token' => $token
        ];
    }



    public function changePhone($request)
    {


        $user = User::where('id', auth()->user()->id)->first();
        if ($user) {

            $otherUsers = User::where('id', '!=', $user->id)
                ->where('type', 'employer')
                ->where('phone', $request->old_phone)->exists();

            if ($otherUsers == true) {
                throw new Exception("Phone number already exists");
            } else {

                $user->phone = $request->new_phone;
                if ($user->update() == true) {
                    $otp = $user->otp->updateOrCreate([
                        'user_id' => $user->id
                    ], [
                        'otp' => rand(0000, 9999)
                    ]);

                    $message = "Please verify using otp: " . $otp;
                    $sendSms =  $this->sendSms($user->phone, $message);
                    if ($sendSms) {



                        return [
                            'otp' => $otp,
                            // 'token' => $token
                        ];
                    }
                }
            }
        }
    }
}
