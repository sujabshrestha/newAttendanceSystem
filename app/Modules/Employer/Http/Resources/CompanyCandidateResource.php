<?php

namespace Employer\Http\Resources;

use Carbon\Carbon;
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
        // dd($this->pivot);

        return [
            'id' => $this->candidate_id,
            'name' => $this->candidate->firstname ?? null,
            'code' => $this->code ?? null,
            'contact' => $this->candidate->phone ?? null,
            'email' => $this->candidate->email ?? null,
            'address' => $this->candidate->address ?? null,
            'joining_date' => Carbon::parse($this->joining_date),



        ];



    }



}
