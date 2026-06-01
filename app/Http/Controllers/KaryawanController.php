<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class KaryawanController extends Controller
{
    private function getActiveUser()
    {
        if (session()->has('simulated_user_id')) {
            return User::find(session('simulated_user_id'));
        }
        return Auth::user();
    }

    public function index(Request $request)
    {
        $viewTrash = $request->input('trash', '0') === '1';

        $query = Karyawan::query();

        if ($viewTrash) {
            $query->onlyTrashed();
        }

        $karyawans = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $pekerjaanList = [
            'Kasir',
            'Leader Kasir',
            'Manager',
            'Cleaning Service',
            'Waiter',
            'Chef',
            'Stock Keeper'
        ];

        return view('karyawan.index', compact('karyawans', 'pekerjaanList', 'viewTrash'));
    }

    public function store(Request $request)
    {
        $admin = $this->getActiveUser();
        $validated = $request->validate([
            'nama_karyawan' => 'required|string|max:50',
            'pekerjaan' => 'required|in:Kasir,Leader Kasir,Manager,Cleaning Service,Waiter,Chef,Stock Keeper',
            'gaji' => 'required|numeric|min:0',
        ]);

        Karyawan::create($validated);

        ActivityLog::create([
            'id_user' => $admin ? $admin->id_user : null,
            'aktivitas' => 'CREATE_KARYAWAN',
            'detail_aktivitas' => "Added worker {$validated['nama_karyawan']} as {$validated['pekerjaan']}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $karyawan = Karyawan::findOrFail($id);

        $validated = $request->validate([
            'nama_karyawan' => 'required|string|max:50',
            'pekerjaan' => 'required|in:Kasir,Leader Kasir,Manager,Cleaning Service,Waiter,Chef,Stock Keeper',
            'gaji' => 'required|numeric|min:0',
        ]);

        $karyawan->update($validated);

        ActivityLog::create([
            'id_user' => $admin ? $admin->id_user : null,
            'aktivitas' => 'UPDATE_KARYAWAN',
            'detail_aktivitas' => "Updated worker {$karyawan->nama_karyawan} details",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function delete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->delete();

        ActivityLog::create([
            'id_user' => $admin ? $admin->id_user : null,
            'aktivitas' => 'DELETE_KARYAWAN',
            'detail_aktivitas' => "Soft-deleted worker {$karyawan->nama_karyawan}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Karyawan dipindahkan ke Trash.');
    }

    public function restore(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $karyawan = Karyawan::onlyTrashed()->findOrFail($id);
        $karyawan->restore();

        ActivityLog::create([
            'id_user' => $admin ? $admin->id_user : null,
            'aktivitas' => 'RESTORE_KARYAWAN',
            'detail_aktivitas' => "Restored worker {$karyawan->nama_karyawan}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Karyawan berhasil diaktifkan kembali.');
    }

    public function forceDelete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $karyawan = Karyawan::onlyTrashed()->findOrFail($id);
        $name = $karyawan->nama_karyawan;
        $karyawan->forceDelete();

        ActivityLog::create([
            'id_user' => $admin ? $admin->id_user : null,
            'aktivitas' => 'FORCE_DELETE_KARYAWAN',
            'detail_aktivitas' => "Permanently deleted worker {$name}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Karyawan berhasil dihapus secara permanen.');
    }
}
