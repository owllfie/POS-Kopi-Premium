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
        if (!Schema::hasTable('karyawan')) {
            Schema::create('karyawan', function (Blueprint $table) {
                $table->integer('id_karyawan')->autoIncrement();
                $table->string('nama_karyawan', 50);
                $table->enum('pekerjaan', [
                    'Kasir',
                    'Leader Kasir',
                    'Manager',
                    'Cleaning Service',
                    'Waiter',
                    'Chef',
                    'Stock Keeper'
                ]);
                $table->integer('gaji');
                $table->timestamps();
                $table->softDeletes();

                $table->primary('id_karyawan');
            });
        }

        // Seed default access rights for the 'karyawan' module
        // id_role 1 is superadmin, 2 is admin, 3 is manager, 4 is kasir, 5 is chef
        $exists = DB::table('aksess')->where('modul', 'karyawan')->exists();
        $rolesExist = DB::table('role')->count() > 0;
        if (!$exists && $rolesExist) {
            DB::table('aksess')->insert([
                ['id_role' => 1, 'modul' => 'karyawan', 'allowed' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 2, 'modul' => 'karyawan', 'allowed' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 3, 'modul' => 'karyawan', 'allowed' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 4, 'modul' => 'karyawan', 'allowed' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 5, 'modul' => 'karyawan', 'allowed' => '0', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
        DB::table('aksess')->where('modul', 'karyawan')->delete();
    }
};
