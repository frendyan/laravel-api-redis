<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('financing_id');
            $table->timestamp('pay_date');
            $table->double('k_pokok');
            $table->double('k_margin');
            $table->double('k_total');
            $table->double('k_pokok_left');
            $table->double('k_margin_left');
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
        Schema::dropIfExists('return_schedules');
    }
}
