<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stamps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')
            ->constrained('staffs')
            ->cascadeOnUpdate()
            ->restrictOnDelete();

            $table->date('stamp_date')->index();
            $table->time('start_work')->nullable();
            $table->time('end_work')->nullable();
            $table->unsignedInteger('total_work')->default(0);;
            $table->tinyInteger('status')->unusigned()->default(0)->comment('0:未承認, 1:承認待ち, 2:承認済み');
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('stamps');
    }
}
