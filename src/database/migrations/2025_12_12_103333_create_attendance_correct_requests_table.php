<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_correct_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stamp_id')->constrained('stamps')->cascadedOnDeleted();
            $table->foreignId('staff_id')->constrained('staffs')->cascadedOnDelete();

            // 0:未申請, 1:承認待ち, 3:承認済み
            $table->unsignedTinyInteger('status')->default(0);

             // 申請したい内容
            $table->time('requested_start_work');
            $table->time('requested_end_work');
            $table->string('requested_remarks', 255);

            $table->foreignId('approved_by')->nullable()->constrained('staffs')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(['stamp_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_correct_requests');
    }
}
