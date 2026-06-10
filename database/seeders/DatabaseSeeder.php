<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
use App\Models\Aksess;
use App\Models\Kategori;
use App\Models\Menu;
use App\Models\Meja;
use App\Models\Shift;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['id_role' => 1, 'role' => 'superadmin'],
            ['id_role' => 2, 'role' => 'admin'],
            ['id_role' => 3, 'role' => 'manager'],
            ['id_role' => 4, 'role' => 'kasir'],
            ['id_role' => 5, 'role' => 'chef'],
            ['id_role' => 6, 'role' => 'stock keeper'],
        ];
        foreach ($roles as $r) {
            Role::create($r);
        }

        // 2. Users
        $users = [
            [
                'id_user' => 1,
                'username' => 'superadmin',
                'email' => 'superadmin@pos.com',
                'password' => 'password',
                'id_role' => 1,
            ],
            [
                'id_user' => 2,
                'username' => 'admin',
                'email' => 'admin@pos.com',
                'password' => 'password',
                'id_role' => 2,
            ],
            [
                'id_user' => 3,
                'username' => 'manager',
                'email' => 'manager@pos.com',
                'password' => 'password',
                'id_role' => 3,
            ],
            [
                'id_user' => 4,
                'username' => 'kasir',
                'email' => 'kasir@pos.com',
                'password' => 'password',
                'id_role' => 4,
            ],
            [
                'id_user' => 5,
                'username' => 'chef',
                'email' => 'chef@pos.com',
                'password' => 'password',
                'id_role' => 5,
            ],
            [
                'id_user' => 6,
                'username' => 'stockkeeper',
                'email' => 'stockkeeper@pos.com',
                'password' => 'password',
                'id_role' => 6,
            ],
        ];
        foreach ($users as $u) {
            User::create([
                'id_user' => $u['id_user'],
                'username' => $u['username'],
                'email' => $u['email'],
                'password' => Hash::make($u['password']),
                'id_role' => $u['id_role'],
            ]);
        }

        // 3. Access Rights Configuration
        $modules = [
            'dashboard', 'pesanan', 'bayar', 'laporan', 'transaksi', 
            'users', 'menu', 'kategori', 'meja', 'shift', 'akses', 
            'log', 'setting', 'backup', 'bahan_alat', 'properti', 'karyawan',
            'promo', 'jabatan'
        ];
 
        // Seeding rules for each role
        // Superadmin gets everything (bypassed in model anyway, but seeded for completeness)
        foreach ($modules as $mod) {
            Aksess::create(['id_role' => 1, 'modul' => $mod, 'allowed' => '1']);
        }
 
        // Admin gets CRUDs and Operations, but not system configs
        $adminAllowed = ['dashboard', 'pesanan', 'bayar', 'laporan', 'transaksi', 'users', 'menu', 'kategori', 'meja', 'shift', 'bahan_alat', 'properti', 'karyawan', 'jabatan', 'promo'];
        foreach ($modules as $mod) {
            $allowed = in_array($mod, $adminAllowed) ? '1' : '0';
            Aksess::create(['id_role' => 2, 'modul' => $mod, 'allowed' => $allowed]);
        }

        // Manager gets Reports and History only
        $managerAllowed = ['dashboard', 'laporan', 'transaksi', 'bahan_alat', 'properti'];
        foreach ($modules as $mod) {
            $allowed = in_array($mod, $managerAllowed) ? '1' : '0';
            Aksess::create(['id_role' => 3, 'modul' => $mod, 'allowed' => $allowed]);
        }

        // Cashier gets operations
        $kasirAllowed = ['pesanan', 'bayar'];
        foreach ($modules as $mod) {
            $allowed = in_array($mod, $kasirAllowed) ? '1' : '0';
            Aksess::create(['id_role' => 4, 'modul' => $mod, 'allowed' => $allowed]);
        }

        // Chef gets queue view only
        $chefAllowed = ['pesanan'];
        foreach ($modules as $mod) {
            $allowed = in_array($mod, $chefAllowed) ? '1' : '0';
            Aksess::create(['id_role' => 5, 'modul' => $mod, 'allowed' => $allowed]);
        }

        // Stock Keeper gets bahan_alat and properti only
        $stockKeeperAllowed = ['bahan_alat', 'properti'];
        foreach ($modules as $mod) {
            $allowed = in_array($mod, $stockKeeperAllowed) ? '1' : '0';
            Aksess::create(['id_role' => 6, 'modul' => $mod, 'allowed' => $allowed]);
        }

        // 4. Categories
        \Illuminate\Support\Facades\DB::table('kategori')->delete();
        $categories = [
            ['id_kategori' => 1, 'kategori' => 'Coffee'],
            ['id_kategori' => 2, 'kategori' => 'Non-Coffee'],
            ['id_kategori' => 3, 'kategori' => 'Pastry'],
            ['id_kategori' => 4, 'kategori' => 'Dessert'],
            ['id_kategori' => 5, 'kategori' => 'Paket'],
        ];
        foreach ($categories as $cat) {
            Kategori::create($cat);
        }

        // 5. Menu Items (Coffee/Bakery themed matching brown palette)
        $menus = [
            // Coffee
            ['nama_menu' => 'Espresso Single', 'id_kategori' => 1, 'harga' => 18000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780202148.png'],
            ['nama_menu' => 'Americano Classico', 'id_kategori' => 1, 'harga' => 22000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780202255.png'],
            ['nama_menu' => 'Velvet Cappuccino', 'id_kategori' => 1, 'harga' => 28000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780202416.png'],
            ['nama_menu' => 'Hazelnut Latte Macchiato', 'id_kategori' => 1, 'harga' => 32000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780202521.png'],
            ['nama_menu' => 'Caramel Fudge Macchiato', 'id_kategori' => 1, 'harga' => 34000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780202588.png'],
            
            // Non-Coffee
            ['nama_menu' => 'Uji Matcha Latte', 'id_kategori' => 2, 'harga' => 30000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780202664.png'],
            ['nama_menu' => 'Belgian Double Chocolate', 'id_kategori' => 2, 'harga' => 28000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780202734.png'],
            ['nama_menu' => 'Earl Grey Milk Tea', 'id_kategori' => 2, 'harga' => 24000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780202820.png'],
            ['nama_menu' => 'Fresh Lemon Iced Tea', 'id_kategori' => 2, 'harga' => 20000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780202907.png'],
            
            // Pastry
            ['nama_menu' => 'Flaky Butter Croissant', 'id_kategori' => 3, 'harga' => 22000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780203112.png'],
            ['nama_menu' => 'Pain au Chocolat', 'id_kategori' => 3, 'harga' => 26000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780203250.png'],
            ['nama_menu' => 'Warm Cinnamon Roll', 'id_kategori' => 3, 'harga' => 24000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780203456.png'],
            ['nama_menu' => 'Almond Cream Croissant', 'id_kategori' => 3, 'harga' => 28000, 'status' => 'habis', 'foto' => 'uploads/menu_1780203527.png'],
            
            // Dessert
            ['nama_menu' => 'Classico Tiramisu Cup', 'id_kategori' => 4, 'harga' => 35000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780203593.png'],
            
            // Custom Coffee
            ['nama_menu' => 'Kopi O', 'id_kategori' => 1, 'harga' => 15000, 'status' => 'tersedia', 'foto' => 'uploads/1780203740_kopi o.jpg'],
            
            // Cheesecake & Fudge Cake
            ['nama_menu' => 'New York Baked Cheesecake', 'id_kategori' => 4, 'harga' => 38000, 'status' => 'tersedia', 'foto' => 'uploads/menu_1780203896.png'],
            ['nama_menu' => 'Dark Chocolate Fudge Cake', 'id_kategori' => 4, 'harga' => 36000, 'status' => 'tersedia', 'foto' => null],
            
            // Custom Pastry & Dessert
            ['nama_menu' => 'Curry Puff', 'id_kategori' => 3, 'harga' => 12000, 'status' => 'tersedia', 'foto' => 'uploads/1780204161_curry puff.jpg'],
            ['nama_menu' => 'Egg Tart', 'id_kategori' => 4, 'harga' => 14000, 'status' => 'tersedia', 'foto' => 'uploads/1780204355_egg tart.jpg'],
        ];
        foreach ($menus as $m) {
            Menu::create($m);
        }


        // 6. Tables (Meja)
        for ($i = 1; $i <= 8; $i++) {
            Meja::create([
                'nomor_meja' => $i,
                'qrcode_token' => 'table-' . $i . '-' . bin2hex(random_bytes(4)),
                'status' => 'kosong',
            ]);
        }

        // 7. Historical Shifts and Orders for Dashboard Charting
        // Shift 1: Completed Yesterday
        $shiftYesterday = Shift::create([
            'id_user' => 4, // kasir
            'jam_mulai' => Carbon::now()->subDay()->setTime(8, 0, 0),
            'jam_selesai' => Carbon::now()->subDay()->setTime(16, 0, 0),
            'cash_masuk' => 380000,
            'qris_masuk' => 420000,
            'total_masuk' => 800000,
        ]);

        // Shift 2: Active Today
        $shiftToday = Shift::create([
            'id_user' => 4,
            'jam_mulai' => Carbon::now()->setTime(8, 0, 0),
            'jam_selesai' => null, // Active
            'cash_masuk' => 120000,
            'qris_masuk' => 240000,
            'total_masuk' => 360000,
        ]);

        // Historical Orders (Yesterday and Today)
        // Order 1 (Yesterday) - Cash
        $order1 = Pesanan::create([
            'kode_struk' => 'STR-' . Carbon::now()->subDay()->format('Ymd') . '-001',
            'id_meja' => 1,
            'metode_pembayaran' => 'cash',
            'total_harga' => 22000 + 28000 + 24000, // 74000
            'pajak' => 7400, // 10%
            'total_bayar' => 81400,
            'id_user' => 4,
            'created_at' => Carbon::now()->subDay()->setTime(10, 15, 0),
        ]);
        DetailPesanan::create([
            'id_pesanan' => $order1->id_pesanan,
            'id_menu' => 2, // Americano
            'jumlah' => 1,
            'harga_satuan' => 22000,
            'subtotal' => 22000,
            'status' => 'selesai',
        ]);
        DetailPesanan::create([
            'id_pesanan' => $order1->id_pesanan,
            'id_menu' => 3, // Cappuccino
            'jumlah' => 1,
            'harga_satuan' => 28000,
            'subtotal' => 28000,
            'status' => 'selesai',
        ]);
        DetailPesanan::create([
            'id_pesanan' => $order1->id_pesanan,
            'id_menu' => 12, // Cinnamon Roll
            'jumlah' => 1,
            'harga_satuan' => 24000,
            'subtotal' => 24000,
            'status' => 'selesai',
        ]);

        // Order 2 (Yesterday) - QRIS
        $order2 = Pesanan::create([
            'kode_struk' => 'STR-' . Carbon::now()->subDay()->format('Ymd') . '-002',
            'id_meja' => 3,
            'metode_pembayaran' => 'qris',
            'total_harga' => 32000 + 35000, // 67000
            'pajak' => 6700,
            'total_bayar' => 73700,
            'id_user' => 4,
            'created_at' => Carbon::now()->subDay()->setTime(14, 30, 0),
        ]);
        DetailPesanan::create([
            'id_pesanan' => $order2->id_pesanan,
            'id_menu' => 4, // Hazelnut Latte
            'jumlah' => 1,
            'harga_satuan' => 32000,
            'subtotal' => 32000,
            'status' => 'selesai',
        ]);
        DetailPesanan::create([
            'id_pesanan' => $order2->id_pesanan,
            'id_menu' => 14, // Tiramisu
            'jumlah' => 1,
            'harga_satuan' => 35000,
            'subtotal' => 35000,
            'status' => 'selesai',
        ]);

        // Order 3 (Today) - QRIS
        $order3 = Pesanan::create([
            'kode_struk' => 'STR-' . Carbon::now()->format('Ymd') . '-001',
            'id_meja' => 2,
            'metode_pembayaran' => 'qris',
            'total_harga' => 34000 + 26000, // 60000
            'pajak' => 6000,
            'total_bayar' => 66000,
            'id_user' => 4,
            'created_at' => Carbon::now()->setTime(9, 10, 0),
        ]);
        DetailPesanan::create([
            'id_pesanan' => $order3->id_pesanan,
            'id_menu' => 5, // Caramel Macchiato
            'jumlah' => 1,
            'harga_satuan' => 34000,
            'subtotal' => 34000,
            'status' => 'selesai',
        ]);
        DetailPesanan::create([
            'id_pesanan' => $order3->id_pesanan,
            'id_menu' => 11, // Pain au Chocolat
            'jumlah' => 1,
            'harga_satuan' => 26000,
            'subtotal' => 26000,
            'status' => 'selesai',
        ]);

        // Seed default Web setting into activity log just to have something in activity logs
        ActivityLog::create([
            'id_user' => 1,
            'aktivitas' => 'SYSTEM_BOOT',
            'detail_aktivitas' => 'System seeded with default data successfully.',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
