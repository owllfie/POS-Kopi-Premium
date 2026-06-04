<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Aksess;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class AksesController extends Controller
{
    private function getActiveUser()
    {
        if (session()->has('simulated_user_id')) {
            return User::find(session('simulated_user_id'));
        }
        return Auth::user();
    }

    public function index()
    {
        // Exclude superadmin's own access rules from being editable
        $roles = Role::where('id_role', '!=', 1)->with('accesses')->get();
        
        $modules = [
            'dashboard' => 'Dashboard Utama',
            'pesanan' => 'Antrean Dapur',
            'bayar' => 'Pembayaran Kasir',
            'laporan' => 'Laporan Keuangan',
            'transaksi' => 'Riwayat Transaksi',
            'users' => 'Kelola Akun Login',
            'karyawan' => 'Kelola Karyawan',
            'menu' => 'Kelola Item Menu',
            'kategori' => 'Kelola Kategori',
            'meja' => 'Kelola Meja & QR',
            'bahan_alat' => 'Bahan',
            'properti' => 'Properti Cafe',
            'shift' => 'Kelola Shift Kasir',
            'promo' => 'Kelola Promo',
            'jabatan' => 'Kelola Jabatan Karyawan',
            'akses' => 'Kelola Hak Akses',
            'log' => 'Log Aktivitas',
            'setting' => 'Pengaturan Web',
            'backup' => 'Backup Database',
        ];

        return view('akses.index', compact('roles', 'modules'));
    }

    public function update(Request $request)
    {
        $superadmin = $this->getActiveUser();
        $accessMap = $request->input('access', []); // [role_id => [module => allowed_state]]

        foreach ($accessMap as $roleId => $modules) {
            foreach ($modules as $moduleName => $allowed) {
                Aksess::updateOrCreate(
                    [
                        'id_role' => $roleId,
                        'modul' => $moduleName,
                    ],
                    [
                        'allowed' => $allowed == '1' ? '1' : '0',
                    ]
                );
            }
        }

        ActivityLog::create([
            'id_user' => $superadmin->id_user,
            'aktivitas' => 'UPDATE_ACCESS_CONTROL',
            'detail_aktivitas' => 'Superadmin updated system-wide role permissions matrix.',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Hak akses berhasil diperbarui.');
    }
}
