<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJadwalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jadwals', function (Blueprint $table) {
            $table->id();
            $table->string('hari')->nullable();
            $table->time('pagi_s')->nullable();
            $table->time('pagi_n')->nullable();
            $table->time('siang_s')->nullable();
            $table->time('siang_n')->nullable();
            $table->timestamp('jadwal_created_at')->useCurrent();
            $table->timestamp('jadwal_updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jadwals');
    }
}
