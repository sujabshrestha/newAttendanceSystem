<?php

namespace Candidate\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyCandidateIndexResource extends JsonResource
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
            'office_hour_start' => $this->pivot->office_hour_start ?? $this->office_hour_start ?? null,
            'office_hour_end' => $this->pivot->office_hour_end ?? $this->office_hour_end ?? null,
        ];
    }
}
