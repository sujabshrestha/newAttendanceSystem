<?php

namespace Employer\Repositories\candidate;

use App\GlobalServices\ResponseService;
use Candidate\Models\Candidate;
use App\Models\User;
use Candidate\Mail\CandidateCreatedMail;
use Candidate\Models\CompanyCandidate;
use Employer\Repositories\candidate\CandidateInterface;
use Carbon\Carbon;
use CMS\Models\NagarikWadaPatra;
use Employer\Models\Company;
use Exception;
use Files\Repositories\FileInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class CandidateRepository implements CandidateInterface {

    protected $file =null, $response;
    public function __construct(FileInterface $file, ResponseService $response)
    {
        $this->file = $file;
        $this->response = $response;
    }


    public function store($request, $id){

        $company = Company::where('id', $id)->first();
        if($company){

            $user = new User();
            $user->name =$request->firstname;

            $user->email =$request->email;
            $user->phone = $request->contact;
            $user->email_verified_at = Carbon::now();
            $user->password = bcrypt('testing1234');
            $user->type = 'candidate';
            if(!$user->save()){
                throw new Exception("Something went wrong while creating user");
            }

            $candidate = new Candidate();
            $candidate->firstname = $request->firstname;
            $user->lastname =$request->lastname;
            if($request->code){
                $candidate->code = $request->code;
            }else{
                $candidate->code = Str::random(20);
            }
            $candidate->status = 'Active';
            $candidate->address = $request->address;
            $candidate->contact = $request->contact;
            $candidate->email = $request->email;
            $candidate->dob = $request->dob;
            $candidate->user_id = $user->id;
            $candidate->employer_id = auth()->user()->id;
            if($candidate->save()){


                $companycandidate = new CompanyCandidate();
                $companycandidate->company_id = $id;
                $companycandidate->candidate_id = $candidate->id;
                $companycandidate->user_id = $user->id;
                $companycandidate->office_hour_start = $request->office_hour_start;
                $companycandidate->office_hour_end = $request->office_hour_end;
                $companycandidate->salary_type = $request->salary_type;
                $companycandidate->duty_time = $request->duty_time;
                $companycandidate->verified_status = 'not_verified';
                $companycandidate->status = 'Inactive';
                $companycandidate->salary_amount = $request->salary_amount;
                $companycandidate->overtime = $request->overtime;
                if(!$companycandidate->save()){
                    throw new Exception("Something went wrong while storing ccompany candiate");
                }

                $details = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'password' => 'testing1234'
                ];

                // $mail = new CandidateCreatedMail($details);
                // Mail::to($user->email)->send($mail);
                return true;

            }
            throw new Exception("Something went wrong while storing candidate please try again later");

        }
        throw new Exception("Company not found");
    }



    public function update($request, $id){

    }

    public function getCandidatesByCompany($id){
        $candidates = Candidate::where('company_id', $id)->get();
        return $candidates;
    }



}
