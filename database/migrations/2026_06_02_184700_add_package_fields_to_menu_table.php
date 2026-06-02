<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu', function (Blueprint $table) {
            $table->text('paket_makanan')->nullable()->after('status');
            $table->text('paket_minuman')->nullable()->after('paket_makanan');
            $table->string('paket_addons', 500)->nullable()->after('paket_minuman');
        });

        // Insert 'Paket' category if not exists
        $exists = DB::table('kategori')->where('kategori', 'Paket')->exists();
        if (!$exists) {
            DB::table('kategori')->insert([
                'kategori' => 'Paket',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('menu', function (Blueprint $table) {
            $table->dropColumn(['paket_makanan', 'paket_minuman', 'paket_addons']);
        });
        
        DB::table('kategori')->where('kategori', 'Paket')->delete();
    }
};
