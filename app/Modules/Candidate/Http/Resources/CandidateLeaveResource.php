<?php

namespace Candidate\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateLeaveResource extends JsonResource
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
           
            'reason' => $this->reason,
            'status' => $this->status,
            'approved' => $this->approved,


            // Relationship Datas
            // 'leave_type' => $this->name,
            // 'document' => $this->code,
            // 'company' => Carbon::parse($this->joining_date),


            // 'companies' => CompanyResource::collection($this->whenLoaded('companies')),
            // 'employer' => new EmployerResource($this->whenLoaded('employer'))

        ];
    }
}
