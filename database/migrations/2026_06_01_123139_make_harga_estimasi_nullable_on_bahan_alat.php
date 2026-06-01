<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bahan_alat', function (Blueprint $table) {
            $table->integer('harga_estimasi')->nullable()->default(null)->change();
        });

        // Hapus semua biaya bulanan untuk kategori peralatan/aset (tipe = 'alat')
        DB::table('bahan_alat')->where('tipe', 'alat')->update(['harga_estimasi' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_alat', function (Blueprint $table) {
            $table->integer('harga_estimasi')->nullable(false)->default(0)->change();
        });
    }
};
