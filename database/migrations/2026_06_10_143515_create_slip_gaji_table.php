<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('slip_gaji', function (Blueprint $table) {
            $table->id('id_slip');
            $table->integer('id_karyawan');
            $table->tinyInteger('bulan');
            $table->integer('tahun');
            $table->integer('gaji_pokok');
            $table->integer('tunjangan')->default(0);
            $table->integer('potongan')->default(0);
            $table->integer('total_gaji');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slip_gaji');
    }
};
