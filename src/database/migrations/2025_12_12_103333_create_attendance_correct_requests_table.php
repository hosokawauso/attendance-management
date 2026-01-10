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
            $table->foreignId('stamp_id')->constrained('stamps')->cascadeOnDeleted();
            $table->foreignId('staff_id')->constrained('staffs')->cascadeOnDelete();

            // 1:承認待ち, 2:承認済み
            $table->unsignedTinyInteger('status')->default(0);

             // 申請したい内容
            $table->time('requested_start_work')->nullable();
            $table->time('requested_end_work')->nullable();
            $table->string('requested_remarks', 255);
            $table->string('admin_comment', 255);

            $table->foreignId('approved_by')->nullable()->constrained('staffs')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique('stamp_id');
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
