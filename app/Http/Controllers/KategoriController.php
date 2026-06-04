<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class KategoriController extends Controller
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

        $query = Kategori::withCount('menus');

        if ($tab === 'trash') {
            $query->onlyTrashed();
        }

        $categories = $query->orderBy('kategori', 'asc')->paginate(10)->withQueryString();

        $historyUpdates = \App\Models\HistoryUpdate::where('table', 'kategori')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('kategori.index', compact('categories', 'tab', 'historyUpdates'));
    }

    public function store(Request $request)
    {
        $admin = $this->getActiveUser();
        $validated = $request->validate([
            'kategori' => 'required|string|max:50',
        ]);

        Kategori::create($validated);

        return back()->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $cat = Kategori::findOrFail($id);

        $validated = $request->validate([
            'kategori' => 'required|string|max:50',
        ]);

        $cat->update($validated);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function delete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $cat = Kategori::findOrFail($id);
        
        // Soft delete all menus belonging to this category
        $cat->menus()->delete();
        
        $cat->delete();

        return back()->with('success', 'Kategori dan menu di dalamnya dipindahkan ke Trash.');
    }

    public function restore(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $cat = Kategori::onlyTrashed()->findOrFail($id);
        $cat->restore();
        
        // Restore all soft-deleted menus belonging to this category
        \App\Models\Menu::onlyTrashed()->where('id_kategori', $id)->restore();

        return back()->with('success', 'Kategori dan menu di dalamnya berhasil dikembalikan.');
    }

    public function forceDelete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $cat = Kategori::onlyTrashed()->findOrFail($id);
        
        // Force delete all menus belonging to this category (including those already trashed)
        \App\Models\Menu::withTrashed()->where('id_kategori', $id)->forceDelete();
        
        $cat->forceDelete();

        return back()->with('success', 'Kategori dan menu di dalamnya berhasil dihapus secara permanen.');
    }
}
