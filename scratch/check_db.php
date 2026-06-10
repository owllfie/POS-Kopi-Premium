<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Aksess;

$permissions = Aksess::all();
foreach ($permissions as $p) {
    echo "ID: {$p->id_akses} | Role: {$p->id_role} | Modul: {$p->modul} | Allowed: {$p->allowed}\n";
}
