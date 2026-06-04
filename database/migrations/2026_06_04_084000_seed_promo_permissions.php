<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add access permission rules for the 'promo' module
        $exists = DB::table('aksess')->where('modul', 'promo')->exists();
        if (!$exists) {
            DB::table('aksess')->insert([
                ['id_role' => 1, 'modul' => 'promo', 'allowed' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 2, 'modul' => 'promo', 'allowed' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 3, 'modul' => 'promo', 'allowed' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 4, 'modul' => 'promo', 'allowed' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 5, 'modul' => 'promo', 'allowed' => '0', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        DB::table('aksess')->where('modul', 'promo')->delete();
    }
};
