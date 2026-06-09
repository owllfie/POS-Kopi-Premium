<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Kategori;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class MenuManageController extends Controller
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
        $tab = $request->input('tab', 'makanan');
        $viewTrash = $tab === 'trash';
        $categoryId = $request->input('kategori_id', 'semua');

        $query = Menu::with('kategori');

        if ($viewTrash) {
            $query->onlyTrashed();
        } else {
            if ($tab === 'makanan') {
                $query->whereHas('kategori', function ($q) {
                    $q->whereIn('kategori', ['Pastry', 'Dessert']);
                });
            } elseif ($tab === 'minuman') {
                $query->whereHas('kategori', function ($q) {
                    $q->whereIn('kategori', ['Coffee', 'Non-Coffee']);
                });
            } elseif ($tab === 'paket') {
                $query->whereHas('kategori', function ($q) {
                    $q->where('kategori', 'Paket');
                });
            }
        }

        if ($categoryId !== 'semua') {
            $query->where('id_kategori', $categoryId);
        }

        $menus = $query->orderBy('nama_menu', 'asc')->paginate(12)->withQueryString();
        
        if ($tab === 'makanan') {
            $categories = Kategori::whereIn('kategori', ['Pastry', 'Dessert'])->get();
        } elseif ($tab === 'minuman') {
            $categories = Kategori::whereIn('kategori', ['Coffee', 'Non-Coffee'])->get();
        } elseif ($tab === 'paket') {
            $categories = Kategori::where('kategori', 'Paket')->get();
        } else {
            $categories = Kategori::all();
        }

        $allFoods = [];
        $allDrinks = [];
        if ($tab === 'paket') {
            $allFoods = Menu::whereHas('kategori', function ($q) {
                $q->whereIn('kategori', ['Pastry', 'Dessert']);
            })->get();
            $allDrinks = Menu::whereHas('kategori', function ($q) {
                $q->whereIn('kategori', ['Coffee', 'Non-Coffee']);
            })->get();
        }

        $historyUpdates = \App\Models\HistoryUpdate::where('table', 'menu')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('menu.index', compact('menus', 'categories', 'viewTrash', 'tab', 'categoryId', 'allFoods', 'allDrinks', 'historyUpdates'));
    }

    public function store(Request $request)
    {
        $admin = $this->getActiveUser();
        $validated = $request->validate([
            'nama_menu' => 'required|string|max:255',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'harga' => 'required|numeric|min:0',
            'status' => 'required|in:tersedia,habis',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'paket_makanan' => 'nullable|array',
            'paket_minuman' => 'nullable|array',
            'paket_addons' => 'nullable|string|max:500',
            'kode_menu' => 'nullable|numeric|digits_between:1,20',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        if (!empty($validated['paket_makanan'])) {
            foreach ($validated['paket_makanan'] as $id => $qty) {
                if (!is_numeric($qty) || $qty < 1) {
                    return back()->withErrors(['paket_makanan' => 'Jumlah makanan tidak valid.']);
                }
                if (!Menu::where('id_menu', $id)->exists()) {
                    return back()->withErrors(['paket_makanan' => 'Makanan tidak ditemukan.']);
                }
            }
        }

        if (!empty($validated['paket_minuman'])) {
            foreach ($validated['paket_minuman'] as $id => $qty) {
                if (!is_numeric($qty) || $qty < 1) {
                    return back()->withErrors(['paket_minuman' => 'Jumlah minuman tidak valid.']);
                }
                if (!Menu::where('id_menu', $id)->exists()) {
                    return back()->withErrors(['paket_minuman' => 'Minuman tidak ditemukan.']);
                }
            }
        }

        if ($request->hasFile('foto')) {
            // Simulated upload (just saving public path)
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $validated['foto'] = 'uploads/' . $filename;
        }

        Menu::create($validated);

        return back()->with('success', 'Menu baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $menu = Menu::findOrFail($id);

        $validated = $request->validate([
            'nama_menu' => 'required|string|max:255',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'harga' => 'required|numeric|min:0',
            'status' => 'required|in:tersedia,habis',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'paket_makanan' => 'nullable|array',
            'paket_minuman' => 'nullable|array',
            'paket_addons' => 'nullable|string|max:500',
            'kode_menu' => 'nullable|numeric|digits_between:1,20',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        if (!empty($validated['paket_makanan'])) {
            foreach ($validated['paket_makanan'] as $id => $qty) {
                if (!is_numeric($qty) || $qty < 1) {
                    return back()->withErrors(['paket_makanan' => 'Jumlah makanan tidak valid.']);
                }
                if (!Menu::where('id_menu', $id)->exists()) {
                    return back()->withErrors(['paket_makanan' => 'Makanan tidak ditemukan.']);
                }
            }
        }

        if (!empty($validated['paket_minuman'])) {
            foreach ($validated['paket_minuman'] as $id => $qty) {
                if (!is_numeric($qty) || $qty < 1) {
                    return back()->withErrors(['paket_minuman' => 'Jumlah minuman tidak valid.']);
                }
                if (!Menu::where('id_menu', $id)->exists()) {
                    return back()->withErrors(['paket_minuman' => 'Minuman tidak ditemukan.']);
                }
            }
        }

        if ($request->filled('cropped_image')) {
            $base64Image = $request->input('cropped_image');
            $filename = 'menu_' . time() . '.png';
            $path = public_path('uploads/' . $filename);
            
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
            file_put_contents($path, $imageData);
            
            $validated['foto'] = 'uploads/' . $filename;
            
            if ($menu->foto && file_exists(public_path($menu->foto))) {
                @unlink(public_path($menu->foto));
            }
        } elseif ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $validated['foto'] = 'uploads/' . $filename;
            
            // Clean up old file if exists
            if ($menu->foto && file_exists(public_path($menu->foto))) {
                @unlink(public_path($menu->foto));
            }
        }

        $menu->update($validated);

        return back()->with('success', 'Menu berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $menu = Menu::findOrFail($id);
        $oldStatus = $menu->status;
        $menu->status = $menu->status === 'tersedia' ? 'habis' : 'tersedia';
        $menu->save();

        ActivityLog::create([
            'id_user' => $admin->id_user,
            'aktivitas' => 'TOGGLE_MENU_STATUS',
            'detail_aktivitas' => "Toggled status of menu {$menu->nama_menu} from {$oldStatus} to {$menu->status}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Status menu ' . $menu->nama_menu . ' diperbarui.');
    }

    public function updateStok(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $menu = Menu::findOrFail($id);
        $action = $request->input('action'); // 'plus' or 'minus'
        
        $oldStok = $menu->stok;
        if ($action === 'plus') {
            $menu->stok += 1;
        } elseif ($action === 'minus') {
            if ($menu->stok > 0) {
                $menu->stok -= 1;
            }
        }

        // Auto update status based on stock
        if ($menu->stok <= 0) {
            $menu->status = 'habis';
        } else if ($menu->stok > 0 && $menu->status === 'habis') {
            $menu->status = 'tersedia';
        }
        
        $menu->save();

        ActivityLog::create([
            'id_user' => $admin ? $admin->id_user : null,
            'aktivitas' => 'UPDATE_MENU_STOK',
            'detail_aktivitas' => "Updated stock of menu {$menu->nama_menu} from {$oldStok} to {$menu->stok}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'new_stok' => $menu->stok,
            'new_status' => $menu->status,
            'nama_menu' => $menu->nama_menu
        ]);
    }

    public function delete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return back()->with('success', 'Menu dipindahkan ke Trash.');
    }

    public function restore(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $menu = Menu::onlyTrashed()->findOrFail($id);
        $menu->restore();

        return back()->with('success', 'Menu berhasil diaktifkan kembali.');
    }

    public function forceDelete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $menu = Menu::onlyTrashed()->findOrFail($id);
        
        if ($menu->foto && file_exists(public_path($menu->foto))) {
            @unlink(public_path($menu->foto));
        }

        $menu->forceDelete();

        return back()->with('success', 'Menu dihapus secara permanen.');
    }
}
