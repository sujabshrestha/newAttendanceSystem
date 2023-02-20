<?php

namespace App\Http\Requests;

use App\Rules\EmployerCompanyExistsRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CompanyStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // dd(request()->name);

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
            // 'firstname' => 'required',
            // 'lastname' => 'required',
            'email' => [ 'required',new EmployerCompanyExistsRule ],
            'phone' => ['required', new EmployerCompanyExistsRule ] ,
            'office_hour_start' => 'required|before:office_hour_end',
            'office_hour_end' => 'required|after:office_hour_start',
            // 'uploadfile' => 'image|mimes:jpeg,jpg,png,gif|max:2048'

        ];
    }




    public function messages()
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Email is incorrrect',
            'phone.required' => 'Phone is required',
            'office_hour_start.required' => 'Office hour start is required',
            'office_hour_end.required' => 'Office hour end  is required',
            'phone.digits' => 'Phone must be of exact 10 digits',


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
