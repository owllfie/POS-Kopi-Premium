<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create jabatan table
                if (!Schema::hasTable('jabatan')) {
            $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';
            Schema::create('jabatan', function (Blueprint $table) use ($isSqlite) {
                $table->integer('id_jabatan')->autoIncrement();
                $table->string('nama_jabatan', 50)->unique();
                $table->integer('gaji_standar')->default(0);
                $table->string('deskripsi', 255)->nullable();
                $table->timestamps();
                $table->softDeletes();

                if (!$isSqlite) $table->primary('id_jabatan');
            });
        }

        // 2. Populate default positions (jabatan)
        $defaultPositions = [
            ['nama_jabatan' => 'Kasir', 'gaji_standar' => 2500000, 'deskripsi' => 'Melayani transaksi pembayaran kasir restoran', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'Leader Kasir', 'gaji_standar' => 4000000, 'deskripsi' => 'Supervisi kasir dan verifikasi shift keuangan', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'Manager', 'gaji_standar' => 3000000, 'deskripsi' => 'Manajemen operasional cafe dan keuangan', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'Cleaning Service', 'gaji_standar' => 1000000, 'deskripsi' => 'Menjaga kebersihan dan kenyamanan restoran', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'Waiter', 'gaji_standar' => 2500000, 'deskripsi' => 'Melayani pesanan pelanggan di meja makan', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'Chef', 'gaji_standar' => 2500000, 'deskripsi' => 'Mengolah masakan di dapur utama', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'Stock Keeper', 'gaji_standar' => 3200000, 'deskripsi' => 'Pengawasan stok gudang dan logistik', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($defaultPositions as $pos) {
            $exists = DB::table('jabatan')->where('nama_jabatan', $pos['nama_jabatan'])->exists();
            if (!$exists) {
                DB::table('jabatan')->insert($pos);
            }
        }

        // 3. Add id_jabatan to karyawan table
        Schema::table('karyawan', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawan', 'id_jabatan')) {
                $table->integer('id_jabatan')->nullable()->after('nama_karyawan');
                $table->foreign('id_jabatan')->references('id_jabatan')->on('jabatan')->onDelete('set null');
            }
        });

        // 4. Map existing karyawan's 'pekerjaan' (enum/string) to corresponding 'id_jabatan'
        $karyawans = DB::table('karyawan')->get();
        foreach ($karyawans as $k) {
            if (!empty($k->pekerjaan)) {
                $jabatan = DB::table('jabatan')->where('nama_jabatan', $k->pekerjaan)->first();
                if ($jabatan) {
                    DB::table('karyawan')->where('id_karyawan', $k->id_karyawan)->update([
                        'id_jabatan' => $jabatan->id_jabatan
                    ]);
                }
            }
        }

        // 5. Seed access rights for 'jabatan' module
        $existsAccess = DB::table('aksess')->where('modul', 'jabatan')->exists();
        $rolesExist = DB::table('role')->count() > 0;
        if (!$existsAccess && $rolesExist) {
            DB::table('aksess')->insert([
                ['id_role' => 1, 'modul' => 'jabatan', 'allowed' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 2, 'modul' => 'jabatan', 'allowed' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 3, 'modul' => 'jabatan', 'allowed' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 4, 'modul' => 'jabatan', 'allowed' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['id_role' => 5, 'modul' => 'jabatan', 'allowed' => '0', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            if (Schema::hasColumn('karyawan', 'id_jabatan')) {
                $table->dropForeign(['id_jabatan']);
                $table->dropColumn(['id_jabatan']);
            }
        });

        Schema::dropIfExists('jabatan');
        DB::table('aksess')->where('modul', 'jabatan')->delete();
    }
};
