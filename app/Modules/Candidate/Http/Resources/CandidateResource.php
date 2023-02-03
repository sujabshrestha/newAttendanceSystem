<?php

namespace Candidate\Http\Resources;

use Carbon\Carbon;
use Employer\Http\Resources\CompanyResource;
use Employer\Http\Resources\EmployerResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,

            'contact' => $this->contact,
            'email' => $this->email,
            'address' => $this->address,
            'joining_date' => Carbon::parse($this->joining_date),


            'companies' => CompanyResource::collection($this->whenLoaded('companies')),
            'employer' => new EmployerResource($this->whenLoaded('employer'))

        ];
    }
}
