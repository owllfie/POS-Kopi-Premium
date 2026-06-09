<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meja;
use App\Models\Kategori;
use App\Models\Menu;
use App\Models\DetailPesanan;
use App\Models\ActivityLog;

class GuestMenuController extends Controller
{
    public function show($qrcode_token)
    {
        $meja = Meja::where('qrcode_token', $qrcode_token)->first();
        
        if (!$meja) {
            return response()->view('errors.404_meja', [
                'message' => 'QR Code meja tidak valid atau meja tidak ditemukan.'
            ], 404);
        }

        // Get all active categories and their menu items
        $categories = Kategori::with(['menus' => function($q) {
            $q->orderBy('nama_menu', 'asc');
        }])->get();

        // Get currently pending orders for this table (in-progress orders)
        $rawOrders = DetailPesanan::where('id_meja_temp', $meja->id_meja)
            ->whereNull('id_pesanan') // Not yet checked out / paid
            ->with('menu')
            ->orderBy('created_at', 'desc')
            ->get();

        $currentOrders = $rawOrders->groupBy(function($item) {
            return $item->id_menu . '_' . $item->status . '_' . trim($item->catatan);
        })->map(function($group) {
            $first = $group->first();
            $combined = clone $first;
            $combined->jumlah = $group->sum('jumlah');
            return $combined;
        });

        return view('menu.show', compact('meja', 'categories', 'currentOrders'));
    }

    public function order(Request $request, $qrcode_token)
    {
        $meja = Meja::where('qrcode_token', $qrcode_token)->first();
        if (!$meja) {
            return back()->with('error', 'Meja tidak ditemukan.');
        }

        $cart = $request->input('cart'); // Array of [id_menu => [qty, note]]
        
        if (empty($cart) || !is_array($cart)) {
            return back()->with('error', 'Keranjang belanja Anda kosong.');
        }

        foreach ($cart as $id_menu => $item) {
            $qty = intval($item['qty'] ?? 0);
            if ($qty <= 0) continue;

            $menu = Menu::find($id_menu);
            if (!$menu || $menu->status === 'habis') {
                continue; // Skip invalid or unavailable items
            }

            // Create pending detail_pesanan record
            DetailPesanan::create([
                'id_pesanan' => null, // Draft
                'id_menu' => $menu->id_menu,
                'jumlah' => $qty,
                'harga_satuan' => $menu->harga,
                'subtotal' => $qty * $menu->harga,
                'catatan' => $item['note'] ?? null,
                'status' => 'menunggu',
                'id_meja_temp' => $meja->id_meja,
            ]);
        }

        // Update table status to occupied
        $meja->status = 'terisi';
        $meja->save();

        // Log guest activity
        ActivityLog::create([
            'id_user' => null, // Guest
            'aktivitas' => 'GUEST_ORDER',
            'detail_aktivitas' => 'Table ' . $meja->nomor_meja . ' placed an order.',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('guest.menu', $qrcode_token)->with('success', 'Pesanan Anda berhasil dikirim ke dapur! Silakan tunggu.');
    }
}
