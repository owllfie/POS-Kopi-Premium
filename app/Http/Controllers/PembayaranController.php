<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Shift;
use App\Models\ActivityLog;
use App\Models\Promo;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Midtrans\Transaction;

class PembayaranController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    private function getActiveUser()
    {
        if (session()->has('simulated_user_id')) {
            return User::find(session('simulated_user_id'));
        }
        return Auth::user();
    }

    private function getSettings()
    {
        $path = storage_path('app/settings.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }
        return [
            'nama_restoran' => 'Kopi Premium',
            'pajak' => 10,
            'mata_uang' => 'IDR',
            'footer' => 'Terima kasih atas kunjungan Anda!',
        ];
    }

    public function showPayment($meja_id)
    {
        $meja = Meja::find($meja_id);
        if (!$meja) {
            $firstTable = Meja::orderBy('nomor_meja', 'asc')->first();
            if ($firstTable) {
                return redirect()->route('pesanan.bayar', $firstTable->id_meja);
            }
            return redirect()->route('dashboard')->with('error', 'Meja tidak ditemukan.');
        }

        $user = $this->getActiveUser();
        $activeShift = null;
        if ($user && $user->role->role === 'kasir') {
            $activeShift = Shift::with('user')
                ->where('id_user', $user->id_user)
                ->whereNull('jam_selesai')
                ->first();
        }

        // Get all pending detail items for this table
        $pendingItems = DetailPesanan::where('id_meja_temp', $meja->id_meja)
            ->whereNull('id_pesanan')
            ->with('menu')
            ->get();

        $subtotal = $pendingItems->sum('subtotal');
        
        $settings = $this->getSettings();
        $pajakPersen = $settings['pajak'];
        $pajak = round(($subtotal * $pajakPersen) / 100);
        $totalBayar = $subtotal + $pajak;

        // Fetch active promos
        $now = now();
        $activePromos = Promo::where('status', 'Aktif')
            ->where(function($q) use ($now) {
                $q->whereNull('start_time')
                  ->orWhere('start_time', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('end_time')
                  ->orWhere('end_time', '>=', $now);
            })
            ->get();

        $allTables = Meja::orderBy('nomor_meja', 'asc')->get();
        $menus = Menu::where('status', 'tersedia')->get();
        $categories = \App\Models\Kategori::all();

        return view('pembayaran.show', compact('meja', 'pendingItems', 'subtotal', 'pajakPersen', 'pajak', 'totalBayar', 'activePromos', 'allTables', 'activeShift', 'menus', 'categories'));
    }

    public function scanBarcode(Request $request, $meja_id)
    {
        $request->validate([
            'barcode' => 'required_without:id_menu',
            'id_menu' => 'required_without:barcode',
        ]);

        $meja = Meja::find($meja_id);
        if (!$meja) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Meja tidak ditemukan.'], 404);
            }
            return back()->with('error', 'Meja tidak ditemukan.');
        }

        $menu = null;
        if ($request->has('id_menu')) {
            $menu = Menu::find($request->id_menu);
        } elseif ($request->has('barcode')) {
            $menu = Menu::where('kode_menu', $request->barcode)->first();
            if (!$menu && is_numeric($request->barcode)) {
                $menu = Menu::find($request->barcode);
            }
        }

        if (!$menu) {
            $identifier = $request->id_menu ?: $request->barcode;
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Menu "' . $identifier . '" tidak ditemukan.'], 404);
            }
            return back()->with('error', 'Menu "' . $identifier . '" tidak ditemukan.');
        }

        if ($menu->status === 'habis') {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Menu "' . $menu->nama_menu . '" sedang habis.'], 400);
            }
            return back()->with('error', 'Menu "' . $menu->nama_menu . '" sedang habis.');
        }

        // Look for existing pending item for this table and menu
        $existingItem = DetailPesanan::where('id_meja_temp', $meja->id_meja)
            ->where('id_menu', $menu->id_menu)
            ->whereNull('id_pesanan')
            ->first();

        if ($existingItem) {
            $existingItem->jumlah += 1;
            $existingItem->subtotal = $existingItem->jumlah * $existingItem->harga_satuan;
            $existingItem->save();
        } else {
            DetailPesanan::create([
                'id_pesanan' => null,
                'id_menu' => $menu->id_menu,
                'jumlah' => 1,
                'harga_satuan' => $menu->harga,
                'subtotal' => $menu->harga,
                'catatan' => null,
                'status' => 'menunggu',
                'id_meja_temp' => $meja->id_meja,
            ]);
        }

        // Mark table as occupied
        if ($meja->status !== 'terisi') {
            $meja->status = 'terisi';
            $meja->save();
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Menu "' . $menu->nama_menu . '" berhasil ditambahkan.'
            ]);
        }

        return back()->with('success', 'Menu "' . $menu->nama_menu . '" berhasil ditambahkan.');
    }

    public function processPayment(Request $request, $meja_id)
    {
        $user = $this->getActiveUser();
        $meja = Meja::find($meja_id);

        if (!$meja) {
            return redirect()->route('pesanan')->with('error', 'Meja tidak ditemukan.');
        }

        $pendingItems = DetailPesanan::where('id_meja_temp', $meja->id_meja)
            ->whereNull('id_pesanan')
            ->get();

        if ($pendingItems->isEmpty()) {
            return redirect()->route('pesanan')->with('error', 'Tidak ada pesanan aktif.');
        }

        $request->validate([
            'metode_pembayaran' => 'required|in:cash,qris',
            'nominal_bayar' => 'required_if:metode_pembayaran,cash|numeric|min:0',
            'id_promo' => 'nullable|exists:promo,id_promo',
        ]);

        $subtotal = $pendingItems->sum('subtotal');
        
        // Calculate discount from database
        $discount = 0;
        $idPromo = $request->input('id_promo');
        if ($idPromo) {
            $promo = Promo::find($idPromo);
            if ($promo && $promo->status === 'Aktif') {
                $eligibleSubtotal = $subtotal;
                if ($promo->menu_ids && count($promo->menu_ids) > 0) {
                    $eligibleSubtotal = $pendingItems->whereIn('id_menu', $promo->menu_ids)->sum('subtotal');
                }

                $discount = $promo->nominal_potongan;
                if ($discount > $eligibleSubtotal) {
                    $discount = $eligibleSubtotal;
                }
            }
        }

        $taxableAmount = $subtotal - $discount;
        $settings = $this->getSettings();
        $pajakVal = round(($taxableAmount * $settings['pajak']) / 100);
        $totalBayar = $taxableAmount + $pajakVal;
        
        $metode = $request->input('metode_pembayaran');

        if ($metode === 'cash') {
            $nominal = $request->input('nominal_bayar');
            if ($nominal < $totalBayar) {
                return back()->with('error', 'Nominal bayar kurang dari total belanja.');
            }
        }

        // Generate receipt code: STR-YYYYMMDD-XXX
        $todayStr = Carbon::now()->format('Ymd');
        $todayOrdersCount = Pesanan::whereDate('created_at', Carbon::today())->count();
        $sequence = str_pad($todayOrdersCount + 1, 3, '0', STR_PAD_LEFT);
        $kodeStruk = "STR-{$todayStr}-{$sequence}";

        if ($metode === 'qris') {
            $params = [
                'transaction_details' => [
                    'order_id' => $kodeStruk . '-' . time(), // Unique order id
                    'gross_amount' => (int) $totalBayar,
                ],
                'customer_details' => [
                    'first_name' => $user->username,
                    'email' => $user->email ?? ($user->username . '@pos.com'),
                ],
                'item_details' => $pendingItems->map(function($item) {
                    return [
                        'id' => 'item-' . $item->id_menu,
                        'price' => (int) $item->harga_satuan,
                        'quantity' => $item->jumlah,
                        'name' => $item->menu->nama_menu,
                    ];
                })->toArray()
            ];

            // Add promo discount as negative item
            if ($discount > 0) {
                $params['item_details'][] = [
                    'id' => 'DISC-PROMO',
                    'price' => -(int) $discount,
                    'quantity' => 1,
                    'name' => 'Diskon Promo',
                ];
            }

            // Add tax as item
            $params['item_details'][] = [
                'id' => 'TAX',
                'price' => (int) $pajakVal,
                'quantity' => 1,
                'name' => 'Pajak (' . $settings['pajak'] . '%)',
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
                return response()->json([
                    'status' => 'success',
                    'snap_token' => $snapToken,
                    'order_id' => $params['transaction_details']['order_id']
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
        }

        return $this->finalizeOrder($request, $meja, $user, $metode, $subtotal, $pajakVal, $totalBayar, $kodeStruk, $pendingItems, $idPromo, $discount);
    }

    public function finishPayment(Request $request)
    {
        $order_id = $request->input('order_id');
        $meja_id = $request->input('meja_id');
        $idPromo = $request->input('id_promo');
        
        $status = Transaction::status($order_id);
        
        if (in_array($status->transaction_status, ['capture', 'settlement'])) {
            $meja = Meja::find($meja_id);
            $user = $this->getActiveUser();
            
            // Extract kodeStruk from order_id (STR-YYYYMMDD-XXX-TIMESTAMP)
            $parts = explode('-', $order_id);
            $kodeStruk = "{$parts[0]}-{$parts[1]}-{$parts[2]}";
            
            $pendingItems = DetailPesanan::where('id_meja_temp', $meja->id_meja)
                ->whereNull('id_pesanan')
                ->get();

            if ($pendingItems->isEmpty()) {
                // Order might already be finalized by notification
                $pesanan = Pesanan::where('kode_struk', $kodeStruk)->first();
                if ($pesanan) {
                    return response()->json(['status' => 'success', 'redirect' => route('dashboard'), 'pesanan_id' => $pesanan->id_pesanan]);
                }
                return response()->json(['status' => 'error', 'message' => 'No pending items found.'], 400);
            }

            $subtotal = $pendingItems->sum('subtotal');
            
            // Calculate discount
            $discount = 0;
            if ($idPromo) {
                $promo = Promo::find($idPromo);
                if ($promo && $promo->status === 'Aktif') {
                    $eligibleSubtotal = $subtotal;
                    if ($promo->menu_ids && count($promo->menu_ids) > 0) {
                        $eligibleSubtotal = $pendingItems->whereIn('id_menu', $promo->menu_ids)->sum('subtotal');
                    }

                    $discount = $promo->nominal_potongan;
                    if ($discount > $eligibleSubtotal) {
                        $discount = $eligibleSubtotal;
                    }
                }
            }

            $taxableAmount = $subtotal - $discount;
            $settings = $this->getSettings();
            $pajakVal = round(($taxableAmount * $settings['pajak']) / 100);
            $totalBayar = $taxableAmount + $pajakVal;

            $this->finalizeOrder($request, $meja, $user, 'qris', $subtotal, $pajakVal, $totalBayar, $kodeStruk, $pendingItems, $idPromo, $discount);
            
            $pesanan = Pesanan::where('kode_struk', $kodeStruk)->first();
            return response()->json([
                'status' => 'success',
                'redirect' => route('dashboard'),
                'pesanan_id' => $pesanan->id_pesanan
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Payment not settled.'], 400);
    }

    private function finalizeOrder($request, $meja, $user, $metode, $subtotal, $pajakVal, $totalBayar, $kodeStruk, $pendingItems, $idPromo = null, $discount = 0)
    {
        // Check if already finalized
        $existing = Pesanan::where('kode_struk', $kodeStruk)->first();
        if ($existing) {
             if ($request->ajax()) {
                return $existing;
            }
            return redirect()->route('dashboard')->with('success', 'Pembayaran sudah diproses.')->with('print_receipt_id', $existing->id_pesanan);
        }

        // Create Pesanan
        $pesanan = Pesanan::create([
            'kode_struk' => $kodeStruk,
            'id_promo' => $idPromo,
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => $metode,
            'total_harga' => $subtotal,
            'diskon' => $discount,
            'pajak' => $pajakVal,
            'total_bayar' => $totalBayar,
            'id_user' => $user->id_user,
        ]);

        // Associate detail items to this pesanan & release from temp meja
        foreach ($pendingItems as $item) {
            $item->id_pesanan = $pesanan->id_pesanan;
            $item->id_meja_temp = null;
            $item->save();
        }

        // Release table status to vacant
        $meja->status = 'kosong';
        $meja->save();

        // Update shift statistics of current cashier
        $activeShift = Shift::where('id_user', $user->id_user)
            ->whereNull('jam_selesai')
            ->first();

        if ($activeShift) {
            if ($metode === 'cash') {
                $activeShift->cash_masuk += $totalBayar;
            } else {
                $activeShift->qris_masuk += $totalBayar;
            }
            $activeShift->total_masuk = $activeShift->cash_masuk + $activeShift->qris_masuk;
            $activeShift->save();
        }

        // Log cashier checkout action
        ActivityLog::create([
            'id_user' => $user->id_user,
            'aktivitas' => 'CONFIRM_PAYMENT',
            'detail_aktivitas' => "Processed payment for Table {$meja->nomor_meja}. Bill: Rp " . number_format($totalBayar, 0, ',', '.') . " via {$metode}.",
            'ip_address' => $request->ip(),
        ]);

        if ($request->ajax()) {
            session()->flash('print_receipt_id', $pesanan->id_pesanan);
            return $pesanan;
        }

        return redirect()->route('pesanan')->with('success', 'Pembayaran berhasil dikonfirmasi.')->with('print_receipt_id', $pesanan->id_pesanan);
    }

    public function notification(Request $request)
    {
        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }

        $transaction = $notif->transaction_status;
        $order_id = $notif->order_id;

        if (in_array($transaction, ['capture', 'settlement'])) {
            // Extract info from order_id (STR-YYYYMMDD-XXX-TIMESTAMP)
            $parts = explode('-', $order_id);
            $kodeStruk = "{$parts[0]}-{$parts[1]}-{$parts[2]}";

            $pesanan = Pesanan::where('kode_struk', $kodeStruk)->first();
            if (!$pesanan) {
                // In this POS, we primarily rely on finishPayment (client-side trigger)
                // for immediate feedback. Webhook is backup.
                // To support webhook fully, we'd need to store more context about the pending order.
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
