<?php

namespace Database\Seeders;

use Candidate\Models\Attendance;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();'


        // $this->call(CountrySeeder::class);
        // $this->call(RoleSeeder::class);
        // $this->call(AdminSeeder::class);
        // $this->call(BusinessLeavedaySeeder::class);
        // $this->call(LeaveTypeSeeder::class);

        Attendance::factory(10)->create();

        // $this->call(AttendanceSeeder::class);
    }
}
