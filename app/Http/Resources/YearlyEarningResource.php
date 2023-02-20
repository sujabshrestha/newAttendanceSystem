<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class YearlyEarningResource extends JsonResource
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
            [
                'month' => $this->checkMonth($request),
                'total_earning' => $this->checkEarning($request)
            ],

        ];
    }


    private function checkMonth($request)
    {
        switch ($this->month) {
            case ('January'):
                return "JAN";
                break;

            case ('February'):

                return "FEB";
                break;
            case ('February'):

                return "FEB";
                break;

            default:
                $msg = 'Something went wrong.';
        }
    }


    private function checkEarning($request)
    {
        if ($this->month == "January") {
            if ($this->total_earning != null) {
                return $this->total_earning;
            }
            return 0;
        }
    }
}
