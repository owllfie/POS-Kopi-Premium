<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Aksess;

$modules = [
    'dashboard', 'pesanan', 'bayar', 'laporan', 'transaksi', 
    'users', 'menu', 'kategori', 'meja', 'shift', 'akses', 
    'log', 'setting', 'backup', 'bahan_alat', 'properti', 'karyawan',
    'promo', 'jabatan'
];

$kasirAllowed = ['pesanan', 'bayar'];

foreach ($modules as $mod) {
    $allowed = in_array($mod, $kasirAllowed) ? '1' : '0';
    Aksess::updateOrCreate(
        ['id_role' => 4, 'modul' => $mod],
        ['allowed' => $allowed]
    );
}
echo "Cashier permissions synchronized successfully.\n";
