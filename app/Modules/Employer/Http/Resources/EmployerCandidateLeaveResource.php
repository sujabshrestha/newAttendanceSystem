<?php

namespace Employer\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployerCandidateLeaveResource extends JsonResource
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
            'leave_id' => $this->id,
            'candidate_id' => $this->candidate->id ?? null,
            'leave_type' => new LeavetypeResource($this->whenLoaded('LeaveType')),
            'start_date' => $this->start_date,
            'approved' => $this->approved,
            // 'approved' => $this->when($this->approved,function ($q){

            //     if($q == 0){
            //         return  'not approved';
            //     }else{
            //         return 'approved';
            //     }
            // }),
            'end_date' => $this->end_date,
            'created_at' => $this->created_at
        ];
    }
}
