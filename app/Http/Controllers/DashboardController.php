<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\Meja;
use App\Models\Shift;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function getActiveUser()
    {
        if (session()->has('simulated_user_id')) {
            return User::find(session('simulated_user_id'));
        }
        return Auth::user();
    }

    public function index()
    {
        $user = $this->getActiveUser();
        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->role->role;

        if ($role === 'superadmin' || $role === 'admin') {
            return $this->adminDashboard($user);
        } elseif ($role === 'manager') {
            return $this->managerDashboard($user);
        } elseif ($role === 'kasir') {
            return redirect()->route('pesanan');
        }

        if ($role === 'stock keeper') {
            return redirect()->route('bahan-alat.index');
        }

        // Chef doesn't have a dashboard, redirects to /pesanan
        return redirect()->route('pesanan');
    }

    private function adminDashboard($user)
    {
        $today = Carbon::today();
        
        // Summary Cards
        $totalPendapatanHariIni = Pesanan::whereDate('created_at', $today)->sum('total_bayar');
        $totalTransaksiHariIni = Pesanan::whereDate('created_at', $today)->count();
        $jumlahMejaAktif = Meja::where('status', 'terisi')->count();
        $totalMenuTersedia = Menu::where('status', 'tersedia')->count();

        // Top Menu Items today
        $today = Carbon::today();
        $topMenuItems = DetailPesanan::select('id_menu', DB::raw('SUM(jumlah) as total_qty'))
            ->whereHas('pesanan', function($q) use ($today) {
                $q->whereDate('created_at', $today);
            })
            ->groupBy('id_menu')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get();

        // Recent Transactions
        $recentTransactions = Pesanan::with(['meja', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Revenue Chart Data (Last 7 Days)
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('D');
            $chartData[] = Pesanan::whereDate('created_at', $date)->sum('total_bayar');
        }

        return view('dashboard.admin', compact(
            'totalPendapatanHariIni',
            'totalTransaksiHariIni',
            'jumlahMejaAktif',
            'totalMenuTersedia',
            'topMenuItems',
            'recentTransactions',
            'chartLabels',
            'chartData'
        ));
    }

    private function managerDashboard($user)
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Summary Cards
        $pendapatanHariIni = Pesanan::whereDate('created_at', $today)->sum('total_bayar');
        $pendapatanMingguIni = Pesanan::where('created_at', '>=', $startOfWeek)->sum('total_bayar');
        $pendapatanBulanIni = Pesanan::where('created_at', '>=', $startOfMonth)->sum('total_bayar');
        $totalTransaksiBulanIni = Pesanan::where('created_at', '>=', $startOfMonth)->count();

        // Daily shift/sales details for today (Penjualan Harian)
        $todaySales = [
            'total_transaksi' => Pesanan::whereDate('created_at', $today)->count(),
            'total_pendapatan' => Pesanan::whereDate('created_at', $today)->sum('total_bayar'),
            'cash_masuk' => Pesanan::whereDate('created_at', $today)->where('metode_pembayaran', 'cash')->sum('total_bayar'),
            'qris_masuk' => Pesanan::whereDate('created_at', $today)->where('metode_pembayaran', 'qris')->sum('total_bayar'),
        ];

        // Today's shifts details
        $todayShifts = Shift::with('user')
            ->whereDate('jam_mulai', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        // Today's Shift Logs from ActivityLog
        $todayShiftLogs = ActivityLog::with('user')
            ->whereIn('aktivitas', ['START_SHIFT', 'END_SHIFT'])
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        // Revenue trend data (Daily last 7 days)
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('d M');
            $chartData[] = Pesanan::whereDate('created_at', $date)->sum('total_bayar');
        }

        return view('dashboard.manager', compact(
            'pendapatanHariIni',
            'pendapatanMingguIni',
            'pendapatanBulanIni',
            'totalTransaksiBulanIni',
            'todaySales',
            'todayShifts',
            'todayShiftLogs',
            'chartLabels',
            'chartData'
        ));
    }

    private function kasirDashboard($user)
    {
        // Check for active shift
        $activeShift = Shift::where('id_user', $user->id_user)
            ->whereNull('jam_selesai')
            ->first();

        if (!$activeShift) {
            return view('dashboard.kasir_start_shift');
        }

        // Active shift variables
        $today = Carbon::today();
        $shiftStart = $activeShift->jam_mulai;
        
        // Today's shift transactions
        $transactions = Pesanan::where('id_user', $user->id_user)
            ->where('created_at', '>=', $shiftStart)
            ->get();

        $totalTransaksi = $transactions->count();
        $totalPendapatan = $transactions->sum('total_bayar');
        
        $cashMasuk = $transactions->where('metode_pembayaran', 'cash')->sum('total_bayar');
        $qrisMasuk = $transactions->where('metode_pembayaran', 'qris')->sum('total_bayar');

        // Update shift totals live
        $activeShift->cash_masuk = $cashMasuk;
        $activeShift->qris_masuk = $qrisMasuk;
        $activeShift->total_masuk = $cashMasuk + $qrisMasuk;
        $activeShift->save();

        // Recent shift orders
        $recentOrders = Pesanan::where('id_user', $user->id_user)
            ->where('created_at', '>=', $shiftStart)
            ->with(['meja', 'details.menu'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.kasir', compact(
            'activeShift',
            'totalTransaksi',
            'totalPendapatan',
            'cashMasuk',
            'qrisMasuk',
            'recentOrders'
        ));
    }

    // Start shift action
    public function startShift(Request $request)
    {
        $user = $this->getActiveUser();
        $request->validate([
            'kas_awal' => 'required|numeric|min:0',
        ]);

        // Double check no active shift
        $activeShift = Shift::where('id_user', $user->id_user)
            ->whereNull('jam_selesai')
            ->first();

        if ($activeShift) {
            return redirect()->route('pesanan')->with('error', 'Shift Anda sudah aktif.');
        }

        Shift::create([
            'id_user' => $user->id_user,
            'jam_mulai' => Carbon::now(),
            'cash_masuk' => 0, // In addition to kas_awal, shift tracks cash from sales
            'qris_masuk' => 0,
            'total_masuk' => 0,
        ]);

        ActivityLog::create([
            'id_user' => $user->id_user,
            'aktivitas' => 'START_SHIFT',
            'detail_aktivitas' => 'Started shift with opening cash Rp ' . number_format($request->kas_awal, 0, ',', '.'),
            'ip_address' => $request->ip(),
        ]);

        // Store kas awal in session
        session(['kas_awal_' . $user->id_user => $request->kas_awal]);

        return redirect()->route('pesanan')->with('success', 'Shift berhasil dimulai.');
    }

    // End shift action
    public function endShift(Request $request)
    {
        $user = $this->getActiveUser();
        $request->validate([
            'kas_di_tangan' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        $activeShift = Shift::where('id_user', $user->id_user)
            ->whereNull('jam_selesai')
            ->first();

        if (!$activeShift) {
            return redirect()->route('pesanan')->with('error', 'Tidak ada shift aktif.');
        }

        $kasAwal = session('kas_awal_' . $user->id_user, 0);
        $expectedCash = $kasAwal + $activeShift->cash_masuk;
        $kasDiTangan = $request->kas_di_tangan;
        $selisih = $kasDiTangan - $expectedCash;

        // Close shift
        $activeShift->jam_selesai = Carbon::now();
        $activeShift->save();

        ActivityLog::create([
            'id_user' => $user->id_user,
            'aktivitas' => 'END_SHIFT',
            'detail_aktivitas' => 'Ended shift. Cash count: Rp ' . number_format($kasDiTangan, 0, ',', '.') . 
                                  ', Expected: Rp ' . number_format($expectedCash, 0, ',', '.') . 
                                  ', Selisih: Rp ' . number_format($selisih, 0, ',', '.') .
                                  ($request->note ? ' (Note: ' . $request->note . ')' : ''),
            'ip_address' => $request->ip(),
        ]);

        // Clean session
        session()->forget('kas_awal_' . $user->id_user);

        return redirect()->route('pesanan')->with('success', 'Shift ditutup. Selisih kas: Rp ' . number_format($selisih, 0, ',', '.'));
    }

    public function recentLogsLazy(Request $request)
    {
        $user = $this->getActiveUser();
        if (!$user) {
            return response('Unauthenticated', 401);
        }

        // Add 600ms delay so the beautiful skeleton shimmer loader is visible during demonstration
        usleep(600000);

        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('dashboard.partials.logs', compact('logs'));
    }
}

