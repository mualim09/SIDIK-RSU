<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKategoriTenkesehatansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kategori_tenkesehatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori_tenkes');
            $table->timestamp('kategori_tenkes_created_at')->useCurrent();
            $table->timestamp('kategori_tenkes_updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kategori_tenkesehatans');
    }
}
