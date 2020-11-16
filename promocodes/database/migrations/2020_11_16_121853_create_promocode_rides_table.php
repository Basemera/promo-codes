<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromocodeRidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promocode_rides', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('promocode_id')->nullable()->index()->unsigned();
            $table->foreign('promocode_id')->references('id')->on('promo_codes')->onDelete('cascade');
            $table->bigInteger('no_rider_used')->nullable();
            $table->bigInteger('no_rides_left')->nullable();
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
        Schema::dropIfExists('promocode_rides');
    }
}
