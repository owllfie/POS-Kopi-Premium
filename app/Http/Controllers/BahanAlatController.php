<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanAlat;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class BahanAlatController extends Controller
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

        $query = BahanAlat::where('tipe', 'bahan');

        if ($tab === 'trash') {
            $query->onlyTrashed();
        }

        if ($kategori !== 'semua') {
            $query->where('kategori', $kategori);
        }

        $items = $query->orderBy('nama_item', 'asc')->paginate(9)->withQueryString();

        // Get unique categories for filters for cooking materials only
        $categories = BahanAlat::where('tipe', 'bahan')
            ->select('kategori')
            ->distinct()
            ->pluck('kategori');

        $historyUpdates = \App\Models\HistoryUpdate::where('table', 'bahan_alat')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('bahan_alat.index', compact('items', 'categories', 'kategori', 'tab', 'historyUpdates'));
    }

    public function store(Request $request)
    {
        $user = $this->getActiveUser();
        $validated = $request->validate([
            'nama_item' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:50',
            'harga_estimasi' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['tipe'] = 'bahan';

        $item = BahanAlat::create($validated);

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'CREATE_INVENTORY_ITEM',
            'detail_aktivitas' => "Added inventory ingredient {$item->nama_item}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Bahan berhasil ditambahkan ke inventaris.');
    }

    public function update(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $item = BahanAlat::where('tipe', 'bahan')->findOrFail($id);

        $validated = $request->validate([
            'nama_item' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:50',
            'harga_estimasi' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $item->update($validated);

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'UPDATE_INVENTORY_ITEM',
            'detail_aktivitas' => "Updated inventory ingredient {$item->nama_item}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Detail bahan berhasil diperbarui.');
    }

    public function updateStok(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $item = BahanAlat::where('tipe', 'bahan')->findOrFail($id);
        $action = $request->input('action'); // 'plus' or 'minus'
        
        $oldStok = $item->stok;
        if ($action === 'plus') {
            $item->stok += 1;
        } elseif ($action === 'minus') {
            if ($item->stok > 0) {
                $item->stok -= 1;
            }
        }
        
        $item->save();

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'UPDATE_INVENTORY_STOK',
            'detail_aktivitas' => "Updated stock of ingredient {$item->nama_item} from {$oldStok} to {$item->stok}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'new_stok' => $item->stok
        ]);
    }

    public function delete(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $item = BahanAlat::where('tipe', 'bahan')->findOrFail($id);
        $name = $item->nama_item;
        $item->delete();

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'DELETE_INVENTORY_ITEM',
            'detail_aktivitas' => "Soft-deleted inventory ingredient {$name}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Bahan berhasil dihapus dari inventaris.');
    }

    public function restore(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $item = BahanAlat::onlyTrashed()->where('tipe', 'bahan')->findOrFail($id);
        $item->restore();

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'RESTORE_INVENTORY_ITEM',
            'detail_aktivitas' => "Restored inventory ingredient {$item->nama_item}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Bahan berhasil dikembalikan ke inventaris.');
    }

    public function forceDelete(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $item = BahanAlat::onlyTrashed()->where('tipe', 'bahan')->findOrFail($id);
        $name = $item->nama_item;
        $item->forceDelete();

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'FORCE_DELETE_INVENTORY_ITEM',
            'detail_aktivitas' => "Force-deleted inventory ingredient {$name}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Bahan berhasil dihapus secara permanen.');
    }
}
