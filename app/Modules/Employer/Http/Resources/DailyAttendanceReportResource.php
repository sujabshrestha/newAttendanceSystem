<?php

namespace Employer\Http\Resources;

use Candidate\Http\Resources\CandidateResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyAttendanceReportResource extends JsonResource
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
            'attendance_duration' => $this->attendance_duration ?? 0,
            'break_duration' => $this->break_duration ?? 0,
            'employee_status' => $this->employee_status,
            'earning' =>$this->earning,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            // 'leave_type_id' => new LeavetypeResource($this->whenLoaded('candidate')),
            'candidate' => new CandidateResource($this->whenLoaded('candidate')),

         ];
    }
}
