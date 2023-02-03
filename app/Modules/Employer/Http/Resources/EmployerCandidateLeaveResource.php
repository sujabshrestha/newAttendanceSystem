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
            'user_id' => $this->user->id ?? null,
            'leave_type' => new LeavetypeResource($this->whenLoaded('LeaveType')),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at
        ];
    }
}
