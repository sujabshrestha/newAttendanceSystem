<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('candidate_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->enum('verified_status', ['verified', 'not_verified'])->defaul('not_verified');
            $table->enum('status', ['Active', 'Inactive'])->defaul('Inactive');
            $table->time('office_hour_start')->nullable();
            $table->string('code')->unique()->nullable();

            $table->string('designation')->nullable();

            $table->date('joining_date')->nullable();

            $table->unsignedDouble('allow_late_attendance')->nullable();

            $table->time('office_hour_end')->nullable();
            $table->string('duty_time')->nullable();
            $table->string('salary_amount')->nullable();
            $table->enum('salary_type', ['monthly', 'weekly', 'daily']);
            $table->unsignedDouble('overtime',2,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_candidates');
    }
}
