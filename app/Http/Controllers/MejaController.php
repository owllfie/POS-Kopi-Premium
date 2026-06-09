<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meja;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MejaController extends Controller
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

        $query = Meja::where('nomor_meja', '!=', 99);

        if ($tab === 'trash') {
            $query->onlyTrashed();
        }

        $mejas = $query->orderBy('nomor_meja', 'asc')->paginate(15)->withQueryString();
        
        $historyUpdates = \App\Models\HistoryUpdate::where('table', 'meja')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('meja.index', compact('mejas', 'tab', 'historyUpdates'));
    }

    public function store(Request $request)
    {
        $admin = $this->getActiveUser();
        $validated = $request->validate([
            'nomor_meja' => 'required|integer|min:1|not_in:99',
        ], [
            'nomor_meja.not_in' => 'Nomor meja 99 dicadangkan untuk sistem Takeaway dan tidak dapat dibuat manual.',
        ]);

        // Auto-generate UUID for QR code token
        $validated['qrcode_token'] = 'table-' . $validated['nomor_meja'] . '-' . Str::uuid();
        $validated['status'] = 'kosong';

        Meja::create($validated);

        return back()->with('success', 'Meja baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $meja = Meja::findOrFail($id);

        $validated = $request->validate([
            'nomor_meja' => 'required|integer|min:1|not_in:99',
            'status' => 'required|string|in:kosong,terisi',
        ], [
            'nomor_meja.not_in' => 'Nomor meja 99 dicadangkan untuk sistem Takeaway.',
        ]);

        $meja->update($validated);

        return back()->with('success', 'Detail meja berhasil diperbarui.');
    }

    public function regenerateQr(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $meja = Meja::findOrFail($id);
        $oldToken = $meja->qrcode_token;
        $meja->qrcode_token = 'table-' . $meja->nomor_meja . '-' . Str::uuid();
        $meja->save();

        ActivityLog::create([
            'id_user' => $admin->id_user,
            'aktivitas' => 'REGENERATE_QR',
            'detail_aktivitas' => "Regenerated QR token for Table {$meja->nomor_meja}. Old token invalidated.",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'QR Code Meja ' . $meja->nomor_meja . ' berhasil diperbarui.');
    }

    public function delete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $meja = Meja::findOrFail($id);
        $meja->delete();

        return back()->with('success', 'Meja dipindahkan ke Trash.');
    }

    public function restore(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $meja = Meja::onlyTrashed()->findOrFail($id);
        $meja->restore();

        return back()->with('success', 'Meja berhasil diaktifkan kembali.');
    }

    public function forceDelete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $meja = Meja::onlyTrashed()->findOrFail($id);
        $meja->forceDelete();

        return back()->with('success', 'Meja berhasil dihapus secara permanen.');
    }
}
