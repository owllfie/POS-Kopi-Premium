<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Shift;
use App\Models\ActivityLog;
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
            return redirect()->route('pesanan')->with('error', 'Meja tidak ditemukan.');
        }

        // Get all pending detail items for this table
        $pendingItems = DetailPesanan::where('id_meja_temp', $meja->id_meja)
            ->whereNull('id_pesanan')
            ->with('menu')
            ->get();

        if ($pendingItems->isEmpty()) {
            return redirect()->route('pesanan')->with('error', 'Tidak ada pesanan aktif untuk Meja ' . $meja->nomor_meja);
        }

        $subtotal = $pendingItems->sum('subtotal');
        
        $settings = $this->getSettings();
        $pajakPersen = $settings['pajak'];
        $pajak = round(($subtotal * $pajakPersen) / 100);
        $totalBayar = $subtotal + $pajak;

        return view('pembayaran.show', compact('meja', 'pendingItems', 'subtotal', 'pajakPersen', 'pajak', 'totalBayar'));
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
        ]);

        $subtotal = $pendingItems->sum('subtotal');
        $settings = $this->getSettings();
        $pajakVal = round(($subtotal * $settings['pajak']) / 100);
        $totalBayar = $subtotal + $pajakVal;
        
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
                        'id' => $item->id_menu,
                        'price' => (int) $item->harga_satuan,
                        'quantity' => $item->jumlah,
                        'name' => $item->menu->nama_menu,
                    ];
                })->toArray()
            ];

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

        return $this->finalizeOrder($request, $meja, $user, $metode, $subtotal, $pajakVal, $totalBayar, $kodeStruk, $pendingItems);
    }

    public function finishPayment(Request $request)
    {
        $order_id = $request->input('order_id');
        $meja_id = $request->input('meja_id');
        
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
            $settings = $this->getSettings();
            $pajakVal = round(($subtotal * $settings['pajak']) / 100);
            $totalBayar = $subtotal + $pajakVal;

            $this->finalizeOrder($request, $meja, $user, 'qris', $subtotal, $pajakVal, $totalBayar, $kodeStruk, $pendingItems);
            
            $pesanan = Pesanan::where('kode_struk', $kodeStruk)->first();
            return response()->json([
                'status' => 'success',
                'redirect' => route('dashboard'),
                'pesanan_id' => $pesanan->id_pesanan
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Payment not settled.'], 400);
    }

    private function finalizeOrder($request, $meja, $user, $metode, $subtotal, $pajakVal, $totalBayar, $kodeStruk, $pendingItems)
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
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => $metode,
            'total_harga' => $subtotal,
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
