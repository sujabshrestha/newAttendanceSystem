<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyCandidateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // dd($this->candidate->attendances);
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'candidate_id' => $this->candidaite_id,
            'canidate_id' => $this->candidate->firstname,
            'attendance' =>  $this->checkAttendanceToday($this),
        ];
    }


    private function checkAttendanceToday($data)
    {

        $attendance = $data->candidate->attendances->first();
        if( $attendance){
            return $attendance->employee_status;
        }
        return "absent";

    }
}
