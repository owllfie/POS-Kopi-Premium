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
        if (!Schema::hasTable('absensi')) {
            $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';
            Schema::create('absensi', function (Blueprint $table) use ($isSqlite) {
                $table->integer('id_absensi')->autoIncrement();
                $table->integer('id_karyawan');
                $table->date('tanggal');
                $table->time('waktu_masuk');
                $table->string('status', 20)->default('Hadir');
                $table->timestamps();

                if (!$isSqlite) {
                    $table->primary('id_absensi');
                    $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawan')->onDelete('cascade');
                }

                // Enforce one clock-in per employee per day
                $table->unique(['id_karyawan', 'tanggal']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
