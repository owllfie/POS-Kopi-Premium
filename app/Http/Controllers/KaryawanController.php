<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\Jabatan;
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
        $tab = $request->input('tab', 'active');
        if ($request->input('trash') === '1') {
            $tab = 'trash';
        }

        $query = Karyawan::with('jabatan');

        if ($tab === 'trash') {
            $query->onlyTrashed();
        }

        $karyawans = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $jabatans = Jabatan::orderBy('nama_jabatan', 'asc')->get();

        $historyUpdates = \App\Models\HistoryUpdate::where('table', 'karyawan')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('karyawan.index', compact('karyawans', 'jabatans', 'tab', 'historyUpdates'));
    }

    public function store(Request $request)
    {
        $admin = $this->getActiveUser();
        $validated = $request->validate([
            'nama_karyawan' => 'required|string|max:50',
            'id_jabatan' => 'required|exists:jabatan,id_jabatan',
            'gaji' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $jabatan = Jabatan::findOrFail($validated['id_jabatan']);
        $validated['pekerjaan'] = $jabatan->nama_jabatan;

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = 'karyawan_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $validated['foto'] = 'uploads/' . $filename;
        }

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
            'id_jabatan' => 'required|exists:jabatan,id_jabatan',
            'gaji' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $jabatan = Jabatan::findOrFail($validated['id_jabatan']);
        $validated['pekerjaan'] = $jabatan->nama_jabatan;

        if ($request->hasFile('foto')) {
            if ($karyawan->foto && file_exists(public_path($karyawan->foto))) {
                @unlink(public_path($karyawan->foto));
            }
            $file = $request->file('foto');
            $filename = 'karyawan_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $validated['foto'] = 'uploads/' . $filename;
        }

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

    public function faceScan(Request $request)
    {
        $karyawans = Karyawan::whereNotNull('foto')->with('jabatan')->get();
        return view('karyawan.face_scan', compact('karyawans'));
    }
}
