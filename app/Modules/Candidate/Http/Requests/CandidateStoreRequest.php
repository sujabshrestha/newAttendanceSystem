<?php

namespace Candidate\Http\Requests;

use App\Rules\CompanyCandidateExistsRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CandidateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'name' => 'required',
            // 'address' => 'required',
            'contact' =>  ['required', new CompanyCandidateExistsRule],
            'email' => ['required', new CompanyCandidateExistsRule],
            'designation' => 'required',
            'office_hour_start' => 'required|date_format:H:i|before:office_hour_end',
            'office_hour_end' => 'required|date_format:H:i|after:office_hour_start',
            'salary_type' => 'required',
            'duty_time' => 'required',
            'code' => 'required|unique:company_candidates,code',
            'salary_amount' => 'required',
            'joining_date' => 'required',

        ];
    }

    public function messages()
    {
        return [
            'name.required' => "Name is required",
            "address.required" => "Address is required",
            "contact.reuired" => "Contact is required",
            "contact.unique" => "This number has already been taken. Please try another one",
            "email.required" => "Email is required",
            "email.unique" => "This email has already been taken. Please try another one",
            "office_hour_start.required" => "Office hour start is required is required",
            "office_hour_end.required" => "Office hour end is required is required",
            "duty_time.required" => "Duty time is required",
            "code.required" => "Code is required",
            "salary_amount.required" => "Salary amount is required",
            "joining_date.required" => "Joining date is required is required",

        ];
    }


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ], 403));
    }
}
