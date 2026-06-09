<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Meja;
use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
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
        $user = $this->getActiveUser();
        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->role->role;

        if ($role === 'kasir') {
            $firstTable = Meja::orderBy('nomor_meja', 'asc')->first();
            $mejaId = $firstTable ? $firstTable->id_meja : 1;
            return redirect()->route('pesanan.bayar', $mejaId);
        }

        if ($role === 'chef') {
            // Chef view: only pending/cooking detail lines, grouped by table
            $rawKitchenItems = DetailPesanan::whereNull('id_pesanan')
                ->whereIn('status', ['menunggu', 'dimasak'])
                ->with(['menu', 'mejaTemp'])
                ->orderBy('created_at', 'asc')
                ->get();

            $kitchenItems = $rawKitchenItems->groupBy('id_meja_temp')->map(function($details) {
                return $details->groupBy(function($item) {
                    return $item->id_menu . '_' . $item->status . '_' . trim($item->catatan);
                })->map(function($group) {
                    $first = $group->first();
                    $combined = clone $first;
                    $combined->jumlah = $group->sum('jumlah');
                    $combined->id_detail = $group->pluck('id_detail')->implode(',');
                    return $combined;
                });
            });

            $menus = Menu::with('kategori')->orderBy('nama_menu', 'asc')->get();
            $categories = \App\Models\Kategori::all();

            return view('pesanan.chef', compact('kitchenItems', 'menus', 'categories'));
        }

        if ($role === 'stock keeper') {
            return redirect()->route('bahan-alat.index');
        }

        // For other roles, since Antrean Pesanan is removed, redirect to dashboard
        return redirect()->route('dashboard');
    }

    // Chef updates item status (menunggu -> dimasak -> selesai)
    public function updateStatus(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $ids = explode(',', $id);
        $items = DetailPesanan::whereIn('id_detail', $ids)->with('menu')->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'Item pesanan tidak ditemukan.');
        }

        $status = $request->input('status');
        if (!in_array($status, ['dimasak', 'selesai'])) {
            return back()->with('error', 'Status tidak valid.');
        }

        foreach ($items as $item) {
            $oldStatus = $item->status;
            $item->status = $status;
            $item->save();

            // Log kitchen action
            ActivityLog::create([
                'id_user' => $user ? $user->id_user : null,
                'aktivitas' => 'KITCHEN_STATUS_UPDATE',
                'detail_aktivitas' => 'Chef updated detail ID ' . $item->id_detail . ' (' . $item->menu->nama_menu . ') status from ' . $oldStatus . ' to ' . $status,
                'ip_address' => $request->ip(),
            ]);
        }

        $menuNames = $items->pluck('menu.nama_menu')->unique()->implode(', ');
        return back()->with('success', 'Status menu ' . $menuNames . ' berhasil diperbarui.');
    }
}
