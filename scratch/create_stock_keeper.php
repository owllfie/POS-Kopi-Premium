<?php

// Bootstrapping Laravel application console environment
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\Aksess;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// 1. Create Stock Keeper role if it doesn't exist
$role = Role::updateOrCreate(
    ['id_role' => 6],
    ['role' => 'stock keeper']
);

echo "Role 'stock keeper' (ID 6) created/verified.\n";

// 2. Create Stock Keeper user if it doesn't exist
$user = User::updateOrCreate(
    ['id_user' => 6],
    [
        'username' => 'stockkeeper',
        'email' => 'stockkeeper@pos.com',
        'password' => Hash::make('password'),
        'id_role' => 6
    ]
);

echo "User 'stockkeeper' (ID 6) created/verified.\n";

// 3. Set Access Rights
$modules = [
    'dashboard', 'pesanan', 'bayar', 'laporan', 'transaksi', 
    'users', 'menu', 'kategori', 'meja', 'shift', 'akses', 
    'log', 'setting', 'backup', 'bahan_alat', 'properti', 'karyawan',
    'promo', 'jabatan'
];

$stockKeeperAllowed = ['bahan_alat', 'properti'];

foreach ($modules as $mod) {
    $allowed = in_array($mod, $stockKeeperAllowed) ? '1' : '0';
    
    Aksess::updateOrCreate(
        [
            'id_role' => 6,
            'modul' => $mod
        ],
        [
            'allowed' => $allowed
        ]
    );
}

echo "Access rights for Stock Keeper configured (only 'bahan_alat' and 'properti' allowed).\n";
