<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jabatan;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class JabatanController extends Controller
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

        $query = Jabatan::query();

        if ($viewTrash) {
            $query->onlyTrashed();
        }

        $jabatans = $query->orderBy('nama_jabatan', 'asc')->paginate(10)->withQueryString();

        return view('jabatan.index', compact('jabatans', 'viewTrash'));
    }

    public function store(Request $request)
    {
        $admin = $this->getActiveUser();
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:50|unique:jabatan,nama_jabatan',
            'gaji_standar' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        Jabatan::create($validated);

        ActivityLog::create([
            'id_user' => $admin ? $admin->id_user : null,
            'aktivitas' => 'CREATE_JABATAN',
            'detail_aktivitas' => "Added position {$validated['nama_jabatan']} with standard salary Rp " . number_format($validated['gaji_standar']),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Jabatan baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $jabatan = Jabatan::findOrFail($id);

        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:50|unique:jabatan,nama_jabatan,' . $id . ',id_jabatan',
            'gaji_standar' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $jabatan->update($validated);

        ActivityLog::create([
            'id_user' => $admin ? $admin->id_user : null,
            'aktivitas' => 'UPDATE_JABATAN',
            'detail_aktivitas' => "Updated position {$jabatan->nama_jabatan} details",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function delete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $jabatan = Jabatan::findOrFail($id);
        $jabatan->delete();

        ActivityLog::create([
            'id_user' => $admin ? $admin->id_user : null,
            'aktivitas' => 'DELETE_JABATAN',
            'detail_aktivitas' => "Soft-deleted position {$jabatan->nama_jabatan}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Jabatan dipindahkan ke Trash.');
    }

    public function restore(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $jabatan = Jabatan::onlyTrashed()->findOrFail($id);
        $jabatan->restore();

        ActivityLog::create([
            'id_user' => $admin ? $admin->id_user : null,
            'aktivitas' => 'RESTORE_JABATAN',
            'detail_aktivitas' => "Restored position {$jabatan->nama_jabatan}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Jabatan berhasil diaktifkan kembali.');
    }

    public function forceDelete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $jabatan = Jabatan::onlyTrashed()->findOrFail($id);
        $name = $jabatan->nama_jabatan;
        $jabatan->forceDelete();

        ActivityLog::create([
            'id_user' => $admin ? $admin->id_user : null,
            'aktivitas' => 'FORCE_DELETE_JABATAN',
            'detail_aktivitas' => "Permanently deleted position {$name}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Jabatan berhasil dihapus secara permanen.');
    }
}
