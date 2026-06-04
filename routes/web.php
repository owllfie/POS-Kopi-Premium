<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestMenuController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\MenuManageController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\MejaController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\AksesController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\JabatanController;

// 1. Public QR Guest Menu (bypasses auth checks but checked inside middleware)
Route::get('/menu/{qrcode_token}', [GuestMenuController::class, 'show'])->name('guest.menu');
Route::post('/menu/{qrcode_token}/order', [GuestMenuController::class, 'order'])->name('guest.order');

// 2. Authentication & Role Switcher
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/simulate-role/{role_id}', [AuthController::class, 'simulateRole'])->name('simulate.role');

// 3. Protected POS Pages (Using AksesMiddleware registered in bootstrap/app.php)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Shift Cashier start/end trigger actions (Kasir-facing)
Route::post('/dashboard/start-shift', [DashboardController::class, 'startShift'])->name('shift.start');
Route::post('/dashboard/end-shift', [DashboardController::class, 'endShift'])->name('shift.end');

Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan');
Route::post('/pesanan/{id}/status', [PesananController::class, 'updateStatus'])->name('pesanan.updateStatus');

Route::get('/pesanan/{id}/bayar', [PembayaranController::class, 'showPayment'])->name('pesanan.bayar');
Route::post('/pesanan/{id}/bayar', [PembayaranController::class, 'processPayment']);
Route::post('/pembayaran/finish', [PembayaranController::class, 'finishPayment'])->name('pembayaran.finish');
Route::post('/midtrans/notification', [PembayaranController::class, 'notification']);

Route::get('/laporan', [ReportController::class, 'index'])->name('laporan');
Route::get('/laporan/export', [ReportController::class, 'export'])->name('laporan.export');

Route::get('/transaksi', [TransactionHistoryController::class, 'index'])->name('transaksi');
Route::get('/transaksi/{id}', [TransactionHistoryController::class, 'show'])->name('transaksi.show');
Route::post('/transaksi/{id}/delete', [TransactionHistoryController::class, 'delete'])->name('transaksi.delete');
Route::post('/transaksi/{id}/restore', [TransactionHistoryController::class, 'restore'])->name('transaksi.restore');
Route::post('/transaksi/{id}/force-delete', [TransactionHistoryController::class, 'forceDelete'])->name('transaksi.forceDelete');

// Management CRUDs (Note /menu must be defined before /menu/{qrcode_token} but since it's grouped under different paths we are good. Let's make sure it doesn't conflict by adding routes carefully)
Route::prefix('menu')->name('menu.')->group(function () {
    Route::get('/', [MenuManageController::class, 'index'])->name('index');
    Route::post('/store', [MenuManageController::class, 'store'])->name('store');
    Route::post('/update/{id}', [MenuManageController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [MenuManageController::class, 'delete'])->name('delete');
    Route::post('/restore/{id}', [MenuManageController::class, 'restore'])->name('restore');
    Route::post('/force-delete/{id}', [MenuManageController::class, 'forceDelete'])->name('forceDelete');
    Route::post('/toggle-status/{id}', [MenuManageController::class, 'toggleStatus'])->name('toggleStatus');
});

Route::prefix('kategori')->name('kategori.')->group(function () {
    Route::get('/', [KategoriController::class, 'index'])->name('index');
    Route::post('/store', [KategoriController::class, 'store'])->name('store');
    Route::post('/update/{id}', [KategoriController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [KategoriController::class, 'delete'])->name('delete');
    Route::post('/restore/{id}', [KategoriController::class, 'restore'])->name('restore');
    Route::post('/force-delete/{id}', [KategoriController::class, 'forceDelete'])->name('forceDelete');
});

Route::prefix('meja')->name('meja.')->group(function () {
    Route::get('/', [MejaController::class, 'index'])->name('index');
    Route::post('/store', [MejaController::class, 'store'])->name('store');
    Route::post('/update/{id}', [MejaController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [MejaController::class, 'delete'])->name('delete');
    Route::post('/regenerate/{id}', [MejaController::class, 'regenerateQr'])->name('regenerate');
    Route::post('/restore/{id}', [MejaController::class, 'restore'])->name('restore');
    Route::post('/force-delete/{id}', [MejaController::class, 'forceDelete'])->name('forceDelete');
});

Route::prefix('promo')->name('promo.')->group(function () {
    Route::get('/', [PromoController::class, 'index'])->name('index');
    Route::post('/store', [PromoController::class, 'store'])->name('store');
    Route::post('/update/{id}', [PromoController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [PromoController::class, 'delete'])->name('delete');
    Route::post('/restore/{id}', [PromoController::class, 'restore'])->name('restore');
    Route::post('/force-delete/{id}', [PromoController::class, 'forceDelete'])->name('forceDelete');
});

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::post('/store', [UserController::class, 'store'])->name('store');
    Route::post('/update/{id}', [UserController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [UserController::class, 'delete'])->name('delete');
    Route::post('/restore/{id}', [UserController::class, 'restore'])->name('restore');
    Route::post('/force-delete/{id}', [UserController::class, 'forceDelete'])->name('forceDelete');
});

Route::prefix('karyawan')->name('karyawan.')->group(function () {
    Route::get('/', [KaryawanController::class, 'index'])->name('index');
    Route::post('/store', [KaryawanController::class, 'store'])->name('store');
    Route::post('/update/{id}', [KaryawanController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [KaryawanController::class, 'delete'])->name('delete');
    Route::post('/restore/{id}', [KaryawanController::class, 'restore'])->name('restore');
    Route::post('/force-delete/{id}', [KaryawanController::class, 'forceDelete'])->name('forceDelete');
});

Route::prefix('jabatan')->name('jabatan.')->group(function () {
    Route::get('/', [JabatanController::class, 'index'])->name('index');
    Route::post('/store', [JabatanController::class, 'store'])->name('store');
    Route::post('/update/{id}', [JabatanController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [JabatanController::class, 'delete'])->name('delete');
    Route::post('/restore/{id}', [JabatanController::class, 'restore'])->name('restore');
    Route::post('/force-delete/{id}', [JabatanController::class, 'forceDelete'])->name('forceDelete');
});

Route::prefix('shift')->name('shift.')->group(function () {
    Route::get('/', [ShiftController::class, 'index'])->name('index');
    Route::post('/update/{id}', [ShiftController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [ShiftController::class, 'delete'])->name('delete');
    Route::post('/restore/{id}', [ShiftController::class, 'restore'])->name('restore');
    Route::post('/force-delete/{id}', [ShiftController::class, 'forceDelete'])->name('forceDelete');
});

Route::prefix('akses')->name('akses.')->group(function () {
    Route::get('/', [AksesController::class, 'index'])->name('index');
    Route::post('/update', [AksesController::class, 'update'])->name('update');
});

Route::get('/log', [LogController::class, 'index'])->name('log');

Route::prefix('setting')->name('setting.')->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('index');
    Route::post('/update', [SettingController::class, 'update'])->name('update');
});

Route::prefix('backup')->name('backup.')->group(function () {
    Route::get('/', [BackupController::class, 'index'])->name('index');
    Route::post('/create', [BackupController::class, 'create'])->name('create');
    Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
    Route::post('/delete/{filename}', [BackupController::class, 'delete'])->name('delete');
});

Route::prefix('bahan-alat')->name('bahan-alat.')->group(function () {
    Route::get('/', [\App\Http\Controllers\BahanAlatController::class, 'index'])->name('index');
    Route::post('/store', [\App\Http\Controllers\BahanAlatController::class, 'store'])->name('store');
    Route::post('/update/{id}', [\App\Http\Controllers\BahanAlatController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [\App\Http\Controllers\BahanAlatController::class, 'delete'])->name('delete');
});

Route::prefix('properti')->name('properti.')->group(function () {
    Route::get('/', [\App\Http\Controllers\PropertiController::class, 'index'])->name('index');
    Route::post('/store', [\App\Http\Controllers\PropertiController::class, 'store'])->name('store');
    Route::post('/update/{id}', [\App\Http\Controllers\PropertiController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [\App\Http\Controllers\PropertiController::class, 'delete'])->name('delete');
});

Route::post('/laporan/ledger/store', [ReportController::class, 'storeLedger'])->name('laporan.ledger.store');
Route::post('/laporan/ledger/delete/{id}', [ReportController::class, 'deleteLedger'])->name('laporan.ledger.delete');
