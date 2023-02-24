<?php

namespace Candidate\Database\Factories;

use Candidate\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

     protected $model = Attendance::class;

    public function definition()
    {
        return [
            'candidate_id' => 10,
            'company_id' => 4,
            'employee_status' => $this->faker->randomElement('Present', 'Late'),
            'start_time' => $this->faker->time(),
            'start_time' => $this->faker->time(),
            'created_at'=> $this->faker->dateTimeBetween(-1, now()),

        ];
    }
}
