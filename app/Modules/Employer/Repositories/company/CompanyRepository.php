<?php

namespace Employer\Repositories\company;

use Carbon\Carbon;
use Employer\Models\Company;
use Employer\Repositories\company\CompanyInterface;
use Exception;
use Files\Repositories\FileInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class CompanyRepository implements CompanyInterface
{

    protected $file = null;
    public function __construct(FileInterface $file)
    {
        $this->file = $file;
    }


    public function getAllCompanies()
    {
        $companies = Company::latest()->get();
        return $companies;
    }

    public function activeCompaniesByEmployerID($id){
        $companies = Company::where('employer_id', $id)->where('status', 'Active')->latest()->get();
        return $companies;

    }


    public function inactiveCompaniesByEmployerID($id){
        $companies = Company::where('employer_id', $id)->where('status', 'Inactive')->latest()->get();
        return $companies;

    }


    public function getCompanyById($id)
    {
        $company = Company::where('id', $id)->first();
        return $company;
    }


    public function getCompanyByIdWithCandidates($id)
    {
        $company = Company::where('id', $id)->with('candidates')->first();
        return $company;
    }

    public function getCompaniesByEmployerId()
    {
        $user = auth()->user();
        $companies = Company::where('employer_id', $user->id)
        ->withCount('candidates')
        ->latest()->get();

        return $companies;
    }


    public function store($request)
    {


        $company = new Company();
        $company->name = $request->name;
        $company->code = $request->code;

        $company->calculation_type = $request->calculation_type;
        $company->email = $request->email;
        $company->phone = $request->phone;
        $company->network_ip = $request->network_ip;
        $company->address = $request->address;
        // $company->duty_time = $request->duty_time;
        // $company->overtime = $request->overtime;
        // $company->salary_amount = $request->salary_amount;
        $company->salary_type = $request->salary_type;
        $company->working_days = $request->working_days;
        $company->office_hour_start = Carbon::parse($request->office_hour_start)->format('H:i');
        $company->office_hour_end = Carbon::parse($request->office_hour_end)->format('H:i');

        $company->leave_duration_type = $request->leave_duration_type;
        // $company->probation_duration_type = $request->probation_duration_type;
        $company->leave_duration = $request->leave_duration;
        $company->probation_duration = $request->probation_duration;

        $company->employer_id = Auth::id();
        if ($company->save()) {
            $company->businessLeaves()->attach($request->business_leave);


            foreach ($request->government_leavedates as $leavedate) {



                $company->govLeaves()->create([
                    'leave_date' => isset($leavedate['leave_date']) ?  Carbon::parse($leavedate['leave_date']) : null
                ]);
            }


            foreach ($request->special_leavedates as $leavedate) {
                $company->specialLeaves()->create([
                    'leave_date' => isset($leavedate['leave_date']) ?  Carbon::parse($leavedate['leave_date']) : null
                ]);
            }


            return $company;
        }
        throw new Exception("Something went wrong please try again later");
    }


    public function update($request, $id)
    {

        $company = Company::where('id', $id)->first();
        if ($company) {
            $company->name = $request->name;
            $company->code = $request->code;

            $company->calculation_type = $request->calculation_type;
            $company->email = $request->email;
            $company->phone = $request->phone;
            $company->network_ip = $request->network_ip;
            $company->address = $request->address;

        //     $company->duty_time = $request->duty_time;
        // $company->overtime = $request->overtime;
        // $company->salary_amount = $request->salary_amount;


            $company->salary_type = $request->salary_type;
            $company->working_days = $request->working_days;
            $company->office_hour_start = Carbon::parse($request->office_hour_start)->format('H:i');
            $company->office_hour_end = Carbon::parse($request->office_hour_start)->format('H:i');
            $company->employer_id = Auth::id();

            $company->leave_duration_type = $request->leave_duration_type;
            // $company->probation_duration_type = $request->probation_duration_type;
            $company->leave_duration = $request->leave_duration;
            $company->probation_duration = $request->probation_duration;
            if ($company->update()) {

                $company->businessLeaves()->sync($request->business_leave);

                foreach ($request->government_leavedates as $leavedate) {
                    $company->govLeaves()->create([
                        'leave_date' => isset($leavedate['leave_date']) ?  Carbon::parse($leavedate['leave_date']) : null
                    ]);
                }


                foreach ($request->special_leavedates as $leavedate) {
                    $company->specialLeaves()->create([
                        'leave_date' => isset($leavedate['leave_date']) ?  Carbon::parse($leavedate['leave_date']) : null
                    ]);
                }


                return $company;
            }
            throw new Exception("Something went wrong please try again later");
        }
        throw new Exception("Comapny not found!!!");
    }
}
