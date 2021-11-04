<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financings', function (Blueprint $table) {
            $table->id();
            $table->string('financing_id', 5);
            $table->integer('financing_amount');
            $table->integer('yearly_margin');
            $table->integer('tenor');
            $table->integer('main_payment_periode');
            $table->integer('margin_payment_periode');
            $table->timestamp('financing_start_date');
            $table->timestamp('financing_due_date');
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
        Schema::dropIfExists('financings');
    }
}
