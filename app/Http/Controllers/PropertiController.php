<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanAlat;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PropertiController extends Controller
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
        $kategori = $request->input('kategori', 'semua');

        $query = BahanAlat::whereIn('tipe', ['properti', 'alat']);

        if ($tab === 'trash') {
            $query->onlyTrashed();
        }

        if ($kategori !== 'semua') {
            $query->where('kategori', $kategori);
        }

        $items = $query->orderBy('nama_item', 'asc')->paginate(9)->withQueryString();

        // Get unique categories for properties and tools
        $categories = BahanAlat::whereIn('tipe', ['properti', 'alat'])
            ->select('kategori')
            ->distinct()
            ->pluck('kategori');

        $historyUpdates = \App\Models\HistoryUpdate::where('table', 'bahan_alat')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('properti.index', compact('items', 'categories', 'kategori', 'tab', 'historyUpdates'));
    }

    public function store(Request $request)
    {
        $user = $this->getActiveUser();
        $validated = $request->validate([
            'nama_item' => 'required|string|max:255',
            'tipe' => 'required|in:properti,alat',
            'kategori' => 'required|string|max:255',
            'harga_estimasi' => 'nullable|numeric|min:0', // used as monthly billing cost / monthly depreciation share
            'stok' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        if (!isset($validated['stok'])) {
            $validated['stok'] = 1.0;
        }
        if (!isset($validated['satuan'])) {
            $validated['satuan'] = 'bulan';
        }

        $item = BahanAlat::create($validated);

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'CREATE_PROPERTY_ITEM',
            'detail_aktivitas' => "Added property item {$item->nama_item} (type: {$item->tipe})",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Properti / Peralatan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $item = BahanAlat::whereIn('tipe', ['properti', 'alat'])->findOrFail($id);

        $validated = $request->validate([
            'nama_item' => 'required|string|max:255',
            'tipe' => 'required|in:properti,alat',
            'kategori' => 'required|string|max:255',
            'harga_estimasi' => 'nullable|numeric|min:0',
            'stok' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        if (!isset($validated['stok'])) {
            $validated['stok'] = 1.0;
        }
        if (!isset($validated['satuan'])) {
            $validated['satuan'] = 'bulan';
        }

        $item->update($validated);

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'UPDATE_PROPERTY_ITEM',
            'detail_aktivitas' => "Updated property item {$item->nama_item} (type: {$item->tipe})",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Properti / Peralatan berhasil diperbarui.');
    }

    public function delete(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $item = BahanAlat::whereIn('tipe', ['properti', 'alat'])->findOrFail($id);
        $name = $item->nama_item;
        $item->delete();

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'DELETE_PROPERTY_ITEM',
            'detail_aktivitas' => "Soft-deleted property item {$name}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Properti / Peralatan berhasil dihapus.');
    }

    public function restore(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $item = BahanAlat::onlyTrashed()->whereIn('tipe', ['properti', 'alat'])->findOrFail($id);
        $item->restore();

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'RESTORE_PROPERTY_ITEM',
            'detail_aktivitas' => "Restored property item {$item->nama_item} (type: {$item->tipe})",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Properti / Peralatan berhasil dikembalikan.');
    }

    public function forceDelete(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $item = BahanAlat::onlyTrashed()->whereIn('tipe', ['properti', 'alat'])->findOrFail($id);
        $name = $item->nama_item;
        $item->forceDelete();

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'FORCE_DELETE_PROPERTY_ITEM',
            'detail_aktivitas' => "Force-deleted property item {$name}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Properti / Peralatan berhasil dihapus secara permanen.');
    }
}
