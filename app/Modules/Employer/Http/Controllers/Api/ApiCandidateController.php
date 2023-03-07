<?php

namespace Employer\Http\Controllers\Api;

use App\GlobalServices\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Candidate\Models\Candidate;
use App\Models\User;
use Candidate\Http\Requests\CandidateStoreRequest;

use Candidate\Http\Resources\CandidateResource;
use Candidate\Models\CompanyCandidate;
use Employer\Http\Resources\CompanyCandidateResource;
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


    public function getActiveCandidatesByCompany($id)
    {
        try {
            $company = Company::where('id', $id)
                ->with(['activecandidatesByCompanyID'])
                ->whereHas('activecandidatesByCompanyID', function ($q) use ($id) {
                    $q->where('company_id', $id);
                })
                ->first();
            if ($company) {
                $data = [
                    'candidates' => (isset($company) && $company->activecandidatesByCompanyID->isNotEmpty()) ?
                     CandidateResource::collection($company->activecandidatesByCompanyID) : []
                ];
                return $this->response->responseSuccess($data, "Success", 200);
            }
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }



    public function getInActiveCandidatesByCompany($id)
    {
        try {
            $company = Company::where('id', $id)
                ->with(['inactiveCandidates'])
                ->whereHas('inactiveCandidates', function ($q) use ($id) {
                    $q->where('company_id', $id);
                })
                ->first();

            $data = [
                'candidates' => (isset($company)  && $company->inactivecandidatesByCompanyID->isNotEmpty()) ?  CandidateResource::collection($company->activecandidatesByCompanyID) : []
            ];
            return $this->response->responseSuccess($data, "Success", 200);
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }


    public function getCandidatesByCompany($id)
    {
        try {
            $companycandidate = CompanyCandidate::where('company_id', $id)
            ->with('candidate')
            // ->whereNotNull('invitation_id')
            ->where('verified_status', 'verified')
            // ->where('employer_id', auth()->user()->id)
            ->get();


            if ($companycandidate) {

                $candidates = CompanyCandidateResource::collection($companycandidate);

            }

            $data = [
                'candidate' => $candidates ?? []
            ];
            return $this->response->responseSuccess($data, "Successfully fetched", 200);
        } catch (\Exception $e) {
            return $this->response->responseError($e->getMessage());
        }
    }



    public function getCompaniesByCandidateID($id)
    {
        try {
            $user = User::where('id', $id)->where('type', 'candidate')->with(['userCompanies'])->first();


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
