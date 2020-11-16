<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromocodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->char('promocode', 100)->unique();
            $table->bigInteger('no_rides')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->boolean('status')->nullable();
            $table->bigInteger('venue_id')->nullable()->unsigned();
            $table->foreign('venue_id')->references('id')->on('venues')->delete('cascade');
            $table->float('acceptable_radius')->nullable();
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
        Schema::dropIfExists('promo_codes');
    }
}
