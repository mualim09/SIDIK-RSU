<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasiensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pasiens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('fakulta_id')->constrained();
            $table->foreignId('prodi_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->string('nama_pasien');
            $table->string('jk_pasien');
            $table->string('tempat_lhr_pasien');
            $table->date('tgl_lhr_pasien');
            $table->string('no_hp_pasien');
            $table->string('alamat_pasien');
            $table->timestamp('pasien_created_at')->useCurrent();
            $table->timestamp('pasien_updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pasiens');
    }
}
