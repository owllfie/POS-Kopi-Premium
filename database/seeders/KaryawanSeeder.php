<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Kasir (3)
            ['nama_karyawan' => 'Budi Santoso', 'pekerjaan' => 'Kasir', 'gaji' => 3500000],
            ['nama_karyawan' => 'Siti Aminah', 'pekerjaan' => 'Kasir', 'gaji' => 3500000],
            ['nama_karyawan' => 'Rizky Pratama', 'pekerjaan' => 'Kasir', 'gaji' => 3500000],
            
            // Chef (3)
            ['nama_karyawan' => 'Agus Wijaya', 'pekerjaan' => 'Chef', 'gaji' => 5500000],
            ['nama_karyawan' => 'Dewi Lestari', 'pekerjaan' => 'Chef', 'gaji' => 5500000],
            ['nama_karyawan' => 'Bambang Pamungkas', 'pekerjaan' => 'Chef', 'gaji' => 5500000],
            
            // Leader Kasir (1)
            ['nama_karyawan' => 'Hendra Setiawan', 'pekerjaan' => 'Leader Kasir', 'gaji' => 4500000],
            
            // Manager (1)
            ['nama_karyawan' => 'Andi Wijaya', 'pekerjaan' => 'Manager', 'gaji' => 7000000],
            
            // Waiter (2)
            ['nama_karyawan' => 'Fitri Handayani', 'pekerjaan' => 'Waiter', 'gaji' => 3200000],
            ['nama_karyawan' => 'Eko Prasetyo', 'pekerjaan' => 'Waiter', 'gaji' => 3200000],
            
            // Cleaning Service (1)
            ['nama_karyawan' => 'Supriyanto', 'pekerjaan' => 'Cleaning Service', 'gaji' => 3000000],
            
            // Stock Keeper (2)
            ['nama_karyawan' => 'Ahmad Fauzi', 'pekerjaan' => 'Stock Keeper', 'gaji' => 3500000],
            ['nama_karyawan' => 'Sri Wahyuni', 'pekerjaan' => 'Stock Keeper', 'gaji' => 3500000],
        ];

        foreach ($data as $item) {
            DB::table('karyawan')->insert(array_merge($item, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
