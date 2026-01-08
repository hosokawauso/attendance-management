<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectRequestRestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_correct_request_rests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correct_request_id')->constrained('attendance_correct_requests')->cascadeOnDelete();

            $table->time('requested_start_rest')->nullable();
            $table->time('requested_end_rest')->nullable();

            // 休憩1,2,3.....用
            $table->unsignedTinyInteger('sort_order')->default(0);

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
        Schema::dropIfExists('attendance_correct_request_rests');
    }
}
