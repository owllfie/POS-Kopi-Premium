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
        Schema::table('bahan_alat', function (Blueprint $table) {
            // Using string type for tipe column to accommodate 'bahan', 'alat', and 'properti' dynamically
            $table->string('tipe', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_alat', function (Blueprint $table) {
            $table->enum('tipe', ['bahan', 'alat'])->change();
        });
    }
};
