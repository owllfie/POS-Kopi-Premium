<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PromoController extends Controller
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

        $query = Promo::query();

        if ($viewTrash) {
            $query->onlyTrashed();
        }

        $promos = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $menus = \App\Models\Menu::orderBy('nama_menu', 'asc')->get();

        return view('promo.index', compact('promos', 'viewTrash', 'menus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_promo' => 'required|string|max:50',
            'tipe_promo' => 'required|in:Harian,Mingguan,Bulanan,Sekali Pakai',
            'deskripsi' => 'nullable|string|max:255',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'nominal_potongan' => 'required|integer|min:0',
            'tipe_potongan' => 'required|in:persen,nominal',
            'menu_ids' => 'nullable|array',
            'menu_ids.*' => 'integer|exists:menu,id_menu',
        ]);

        Promo::create($validated);

        return back()->with('success', 'Promo baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);

        $validated = $request->validate([
            'nama_promo' => 'required|string|max:50',
            'tipe_promo' => 'required|in:Harian,Mingguan,Bulanan,Sekali Pakai',
            'deskripsi' => 'nullable|string|max:255',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'nominal_potongan' => 'required|integer|min:0',
            'tipe_potongan' => 'required|in:persen,nominal',
            'menu_ids' => 'nullable|array',
            'menu_ids.*' => 'integer|exists:menu,id_menu',
        ]);

        $promo->update($validated);

        return back()->with('success', 'Promo berhasil diperbarui.');
    }

    public function delete(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);
        $promo->delete();

        return back()->with('success', 'Promo dipindahkan ke Trash.');
    }

    public function restore(Request $request, $id)
    {
        $promo = Promo::onlyTrashed()->findOrFail($id);
        $promo->restore();

        return back()->with('success', 'Promo berhasil diaktifkan kembali.');
    }

    public function forceDelete(Request $request, $id)
    {
        $promo = Promo::onlyTrashed()->findOrFail($id);
        $promo->forceDelete();

        return back()->with('success', 'Promo berhasil dihapus secara permanen.');
    }
}
