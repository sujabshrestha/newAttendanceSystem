<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Candidate\Models\Candidate;
use App\Models\User;
use Candidate\Http\Requests\CandidateStoreRequest;

use Candidate\Http\Resources\CandidateResource;
use Employer\Repositories\candidate\CandidateInterface;
use Employer\Http\Resources\CompanyResource;
use Employer\Models\Company;
use Illuminate\Support\Str;

class ApiCandidateController extends Controller
{
    protected $response, $candidate;

    public function __construct(ResponseService $response, CandidateInterface $candidate)
    {
        $this->response = $response;
        $this->candidate = $candidate;
    }

    public function store(CandidateStoreRequest $request, $id)
    {
        try {
            $candidate = $this->candidate->store($request, $id);
            if ($candidate) {
                return $this->response->responseSuccessMsg("Successfully Created", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        try {
            // dd($request->all());
            $candidate = $this->candidate->update($request, $id);
            if ($candidate) {
                return $this->response->responseSuccessMsg("Successfully Created", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function getCandidatesByCompany($id)
    {
        try {

            $candidates = Candidate::with(['companies' =>  function ($q) use ($id) {
                $q->where('company_id', $id);
            }, 'user', 'employer'])->get();

            if ($candidates) {
                $data = [
                    'candidate' => CandidateResource::collection($candidates)
                ];
                return $this->response->responseSuccess($data, "Successfully Created", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }



    public function getCompaniesByCandidateID($id)
    {
        try {
            $user = User::where('id', $id)->candidateCheck()->with(['userCompanies'])->first();


            if ($user) {
                $data = [
                    'companies' => CompanyResource::collection($user->userCompanies)
                ];

                return $this->response->responseSuccess($data, "Success", 200);
            }
            return $this->response->responseError("Candidate not found");
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }
}
