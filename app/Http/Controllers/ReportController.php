<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\KeuanganTransaksi;
use App\Models\BahanAlat;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
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
        $type = $request->input('type', 'mingguan');
        if ($type === 'harian') {
            $type = 'mingguan';
        }
        $dateStr = $request->input('date', Carbon::today()->format('Y-m-d'));
        $cashierId = $request->input('kasir_id', 'semua');
        $paymentMethod = $request->input('metode', 'semua');
        $tab = $request->input('tab', 'pos'); // 'pos', 'laba-rugi', 'neraca', 'arus-kas', 'ledger'

        $date = Carbon::parse($dateStr);
        $startOfMonth = $date->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $date->copy()->endOfMonth()->format('Y-m-d');

        // ==========================================
        // 1. POS Performance Report (Existing Logic)
        // ==========================================
        $posQuery = Pesanan::with(['meja', 'user']);

        if ($cashierId !== 'semua') {
            $posQuery->where('id_user', $cashierId);
        }

        if ($paymentMethod !== 'semua') {
            $posQuery->where('metode_pembayaran', $paymentMethod);
        }

        $labels = [];
        $data = [];

        if ($type === 'harian') {
            $posQuery->whereDate('created_at', $date);
            $reportTitle = "Laporan Harian — " . $date->format('d M Y');
            
            for ($hour = 8; $hour <= 22; $hour += 2) {
                $start = $date->copy()->setTime($hour, 0, 0);
                $end = $date->copy()->setTime($hour + 1, 59, 59);
                $labels[] = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                
                $hourlyQuery = Pesanan::whereBetween('created_at', [$start, $end]);
                if ($cashierId !== 'semua') $hourlyQuery->where('id_user', $cashierId);
                if ($paymentMethod !== 'semua') $hourlyQuery->where('metode_pembayaran', $paymentMethod);
                
                $data[] = $hourlyQuery->sum('total_bayar');
            }
        } elseif ($type === 'mingguan') {
            $start = $date->copy()->subDays(6)->startOfDay();
            $end = $date->copy()->endOfDay();
            $posQuery->whereBetween('created_at', [$start, $end]);
            $reportTitle = "Laporan Mingguan — Periode " . $start->format('d M') . " s/d " . $end->format('d M Y');
            
            for ($i = 6; $i >= 0; $i--) {
                $day = $date->copy()->subDays($i);
                $labels[] = $day->format('d M');
                
                $dailyQuery = Pesanan::whereDate('created_at', $day);
                if ($cashierId !== 'semua') $dailyQuery->where('id_user', $cashierId);
                if ($paymentMethod !== 'semua') $dailyQuery->where('metode_pembayaran', $paymentMethod);
                
                $data[] = $dailyQuery->sum('total_bayar');
            }
        } else {
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();
            $posQuery->whereBetween('created_at', [$start, $end]);
            $reportTitle = "Laporan Bulanan — " . $date->format('F Y');
            
            $current = $start->copy();
            $todayLimit = Carbon::today()->endOfDay();
            while ($current->lte($end)) {
                $weekStart = $current->copy()->startOfDay();
                if ($weekStart->gt($todayLimit)) {
                    break;
                }
                $weekEnd = $current->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                if ($weekEnd->gt($end)) {
                    $weekEnd = $end->copy()->endOfDay();
                }
                
                $labels[] = $weekStart->format('d M') . ' - ' . $weekEnd->format('d M');
                
                $weeklyQuery = Pesanan::whereBetween('created_at', [$weekStart, $weekEnd]);
                if ($cashierId !== 'semua') $weeklyQuery->where('id_user', $cashierId);
                if ($paymentMethod !== 'semua') $weeklyQuery->where('metode_pembayaran', $paymentMethod);
                
                $data[] = $weeklyQuery->sum('total_bayar');
                
                $current = $weekEnd->copy()->addDay()->startOfDay();
            }
        }

        $totalOmset = (clone $posQuery)->sum('total_bayar');
        $totalPajak = (clone $posQuery)->sum('pajak');
        $totalBersih = (clone $posQuery)->sum('total_harga');
        
        $cashCount = (clone $posQuery)->where('metode_pembayaran', 'cash')->sum('total_bayar');
        $qrisCount = (clone $posQuery)->where('metode_pembayaran', 'qris')->sum('total_bayar');

        // Paginate POS transactions
        $transactions = $posQuery->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $cashiers = User::whereHas('role', function($q) {
            $q->where('role', 'kasir');
        })->get();

        // ==========================================
        // 2. Laba Rugi (Profit & Loss) Calculations
        // ==========================================
        $monthlyFoodSales = DB::table('detail_pesanan')
            ->join('menu', 'detail_pesanan.id_menu', '=', 'menu.id_menu')
            ->join('pesanan', 'detail_pesanan.id_pesanan', '=', 'pesanan.id_pesanan')
            ->whereBetween('pesanan.created_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->whereIn('menu.id_kategori', [3, 4]) // Bakery, Dessert (Makanan)
            ->whereNull('detail_pesanan.deleted_at')
            ->whereNull('pesanan.deleted_at')
            ->sum('detail_pesanan.subtotal');

        $monthlyDrinkSales = DB::table('detail_pesanan')
            ->join('menu', 'detail_pesanan.id_menu', '=', 'menu.id_menu')
            ->join('pesanan', 'detail_pesanan.id_pesanan', '=', 'pesanan.id_pesanan')
            ->whereBetween('pesanan.created_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->whereIn('menu.id_kategori', [1, 2]) // Coffee, Non-Coffee (Minuman)
            ->whereNull('detail_pesanan.deleted_at')
            ->whereNull('pesanan.deleted_at')
            ->sum('detail_pesanan.subtotal');

        // Sum ledger credits/debits grouped by accounts for the selected month
        $ledgerMonthly = DB::table('keuangan_transaksi')
            ->select('kode_akun', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(kredit) as total_kredit'))
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->whereNull('deleted_at')
            ->groupBy('kode_akun')
            ->get()
            ->keyBy('kode_akun');

        // P&L elements
        $revFood = $monthlyFoodSales;
        $revDrink = $monthlyDrinkSales;
        $revMerch = isset($ledgerMonthly[4300]) ? $ledgerMonthly[4300]->total_debit : 0;
        $revPartnership = isset($ledgerMonthly[4400]) ? $ledgerMonthly[4400]->total_debit : 0;
        $revOthers = isset($ledgerMonthly[4500]) ? $ledgerMonthly[4500]->total_debit : 0;
        $grossSales = $revFood + $revDrink + $revMerch + $revPartnership + $revOthers;

        $posDiscounts = DB::table('pesanan')
            ->whereBetween('created_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->whereNull('deleted_at')
            ->sum('diskon');
        $discount = (isset($ledgerMonthly[6304]) ? $ledgerMonthly[6304]->total_kredit : 0) + $posDiscounts; // 6304: Diskon
        $netRevenue = $grossSales - $discount;

        // HPP (COGS)
        $hppFood = isset($ledgerMonthly[5100]) ? $ledgerMonthly[5100]->total_kredit : 0;
        $hppDrink = isset($ledgerMonthly[5200]) ? $ledgerMonthly[5200]->total_kredit : 0;
        $hppPackaging = isset($ledgerMonthly[5300]) ? $ledgerMonthly[5300]->total_kredit : 0;
        $totalHpp = $hppFood + $hppDrink + $hppPackaging;

        $grossProfit = $netRevenue - $totalHpp;

        // OPEX
        $opexStaff = (isset($ledgerMonthly[6101]) ? $ledgerMonthly[6101]->total_kredit : 0) +
                     (isset($ledgerMonthly[6102]) ? $ledgerMonthly[6102]->total_kredit : 0) +
                     (isset($ledgerMonthly[6103]) ? $ledgerMonthly[6103]->total_kredit : 0);

        $opexUtility = (isset($ledgerMonthly[6201]) ? $ledgerMonthly[6201]->total_kredit : 0) +
                       (isset($ledgerMonthly[6202]) ? $ledgerMonthly[6202]->total_kredit : 0) +
                       (isset($ledgerMonthly[6203]) ? $ledgerMonthly[6203]->total_kredit : 0) +
                       (isset($ledgerMonthly[6204]) ? $ledgerMonthly[6204]->total_kredit : 0) +
                       (isset($ledgerMonthly[6205]) ? $ledgerMonthly[6205]->total_kredit : 0);

        $opexMarketing = (isset($ledgerMonthly[6301]) ? $ledgerMonthly[6301]->total_kredit : 0) +
                         (isset($ledgerMonthly[6302]) ? $ledgerMonthly[6302]->total_kredit : 0) +
                         (isset($ledgerMonthly[6303]) ? $ledgerMonthly[6303]->total_kredit : 0) +
                         (isset($ledgerMonthly[6305]) ? $ledgerMonthly[6305]->total_kredit : 0); // 6305: Ojol

        $opexMaintenance = (isset($ledgerMonthly[6401]) ? $ledgerMonthly[6401]->total_kredit : 0) +
                            (isset($ledgerMonthly[6402]) ? $ledgerMonthly[6402]->total_kredit : 0) +
                            (isset($ledgerMonthly[6403]) ? $ledgerMonthly[6403]->total_kredit : 0) +
                            (isset($ledgerMonthly[6404]) ? $ledgerMonthly[6404]->total_kredit : 0);

        $opexLegalAdmin = (isset($ledgerMonthly[6501]) ? $ledgerMonthly[6501]->total_kredit : 0) +
                          (isset($ledgerMonthly[6503]) ? $ledgerMonthly[6503]->total_kredit : 0) +
                          (isset($ledgerMonthly[6504]) ? $ledgerMonthly[6504]->total_kredit : 0); // 6502 (tax) goes to balance sheet liability

        $totalOpex = $opexStaff + $opexUtility + $opexMarketing + $opexMaintenance + $opexLegalAdmin;

        $ebitda = $grossProfit - $totalOpex;
        
        $depreciation = 500000; // Estimated monthly asset depreciation (Mesin kopi, renovation, custom interior)
        $taxFinal = round(0.005 * $netRevenue); // UMKM Final Tax (0.5% of net revenue)
        $netProfit = $ebitda - $depreciation - $taxFinal;

        // ==========================================
        // 3. Neraca (Balance Sheet) Calculations
        // ==========================================
        // Current Assets
        $pettyCashVal = 1000000; // Standar Kas Kecil Laci Kasir
        $inventoryCostVal = BahanAlat::where('tipe', 'bahan')->get()->sum(function($item) {
            return $item->stok * $item->harga_estimasi;
        });

        // Dynamic Saldo Bank = 150.000.000 (Modal Awal) + total POS cash - total OPEX/HPP cash
        $totalReceivedPOS = DB::table('pesanan')->whereNull('deleted_at')->sum('total_bayar');
        $totalPaidLedger = DB::table('keuangan_transaksi')->whereNull('deleted_at')->sum('kredit');
        $totalReceivedLedger = DB::table('keuangan_transaksi')->whereNull('deleted_at')->sum('debit');
        
        $bankBalanceVal = 150000000 + $totalReceivedPOS + $totalReceivedLedger - $totalPaidLedger;
        $ojolPendingBalanceVal = round($qrisCount * 0.15); // Simulated pending GoFood/GrabFood cash settlement
        $totalCurrentAssets = $pettyCashVal + $inventoryCostVal + $bankBalanceVal + $ojolPendingBalanceVal;

        // Fixed Assets
        $fixedAssetsCostVal = BahanAlat::where('tipe', 'alat')->get()->sum(function($item) {
            return $item->stok * $item->harga_estimasi;
        });
        $fixedAssetsDepreciationVal = 4000000; // Simulated accumulated depreciation over months
        $netFixedAssets = $fixedAssetsCostVal - $fixedAssetsDepreciationVal;
        
        $totalAssets = $totalCurrentAssets + $netFixedAssets;

        // Liabilities
        $supplierLiabilitiesVal = 2500000; // Estimated outstanding raw material invoices
        $salaryLiabilitiesVal = 0; // Outstandings
        $taxLiabilityPB1Val = DB::table('pesanan')->whereNull('deleted_at')->sum('pajak'); // Total PB1 tax collected, not yet paid to state
        $totalLiabilities = $supplierLiabilitiesVal + $salaryLiabilitiesVal + $taxLiabilityPB1Val;

        // Equity
        $initialEquityVal = 150000000;
        $retainedEarningsVal = $netProfit; // Current month's net profit
        $totalEquity = $initialEquityVal + $retainedEarningsVal;

        // Balance Sheet check (Assets = Liabilities + Equity)
        // If there is any difference, we adjust capital/retained earnings to keep it balanced
        $difference = $totalAssets - ($totalLiabilities + $totalEquity);
        if ($difference != 0) {
            $totalEquity += $difference;
            $retainedEarningsVal += $difference;
        }

        // ==========================================
        // 4. Arus Kas (Cash Flow) Calculations
        // ==========================================
        $cashInOps = $totalOmset;
        $cashOutOps = $totalHpp + $totalOpex;
        $cashFlowOps = $cashInOps - $cashOutOps;

        // Cash flow from investments (Buying assets / tools)
        $cashFlowInvest = -2200000; // Estimated microwave purchase
        $cashFlowFinance = 0; // Simulated Prives/suntikan

        $netCashFlow = $cashFlowOps + $cashFlowInvest + $cashFlowFinance;

        // ==========================================
        // 5. Daily Ledger (Buku Kas Harian) Items
        // ==========================================
        $ledgerItems = [];

        // 5.1 Add transactions from keuangan_transaksi table
        $dbLedger = DB::table('keuangan_transaksi')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->whereNull('deleted_at')
            ->get();

        foreach ($dbLedger as $item) {
            $ledgerItems[] = [
                'tanggal' => $item->tanggal,
                'kode_akun' => $item->kode_akun,
                'deskripsi' => $item->deskripsi,
                'metode' => $item->metode,
                'debit' => $item->debit,
                'kredit' => $item->kredit,
                'is_sale' => false,
                'id_transaksi' => $item->id_transaksi,
            ];
        }

        // 5.2 Add daily sales aggregates from POS orders
        $dailySales = DB::table('pesanan')
            ->select(
                DB::raw("DATE(created_at) as tanggal"),
                DB::raw("SUM(total_harga) as net_sales"),
                DB::raw("SUM(pajak) as tax_sales"),
                DB::raw("SUM(diskon) as discount_sales")
            )
            ->whereBetween('created_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->whereNull('deleted_at')
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get();

        foreach ($dailySales as $sale) {
            // Find portion of food/beverages for this specific date
            $dayFood = DB::table('detail_pesanan')
                ->join('menu', 'detail_pesanan.id_menu', '=', 'menu.id_menu')
                ->join('pesanan', 'detail_pesanan.id_pesanan', '=', 'pesanan.id_pesanan')
                ->whereDate('pesanan.created_at', $sale->tanggal)
                ->whereIn('menu.id_kategori', [3, 4])
                ->whereNull('detail_pesanan.deleted_at')
                ->whereNull('pesanan.deleted_at')
                ->sum('detail_pesanan.subtotal');

            $dayDrink = $sale->net_sales - $dayFood;

            if ($dayFood > 0) {
                $ledgerItems[] = [
                    'tanggal' => $sale->tanggal,
                    'kode_akun' => 4100,
                    'deskripsi' => 'Penjualan Makanan Harian (Dine-in / Takeaway)',
                    'metode' => 'Tunai/QRIS',
                    'debit' => $dayFood,
                    'kredit' => 0,
                    'is_sale' => true,
                ];
            }

            if ($dayDrink > 0) {
                $ledgerItems[] = [
                    'tanggal' => $sale->tanggal,
                    'kode_akun' => 4200,
                    'deskripsi' => 'Penjualan Minuman Harian (Bar)',
                    'metode' => 'Tunai/QRIS',
                    'debit' => $dayDrink,
                    'kredit' => 0,
                    'is_sale' => true,
                ];
            }

            if ($sale->tax_sales > 0) {
                // PB1 collected is saved as current liability
                $ledgerItems[] = [
                    'tanggal' => $sale->tanggal,
                    'kode_akun' => 6502,
                    'deskripsi' => 'Titipan Pajak PB1 Konsumen Harian',
                    'metode' => 'Tunai/QRIS',
                    'debit' => $sale->tax_sales,
                    'kredit' => 0,
                    'is_sale' => true,
                ];
            }

            if ($sale->discount_sales > 0) {
                $ledgerItems[] = [
                    'tanggal' => $sale->tanggal,
                    'kode_akun' => 6304,
                    'deskripsi' => 'Diskon & Promo Penjualan POS Harian',
                    'metode' => 'Potong',
                    'debit' => 0,
                    'kredit' => $sale->discount_sales,
                    'is_sale' => true,
                ];
            }
        }

        // 5.3 Sort by date ascending, then code_akun ascending
        usort($ledgerItems, function($a, $b) {
            $tA = strtotime($a['tanggal']);
            $tB = strtotime($b['tanggal']);
            if ($tA == $tB) {
                return $a['kode_akun'] - $b['kode_akun'];
            }
            return $tA - $tB;
        });

        // 5.4 Compute running balance
        $currentBalance = 0;
        foreach ($ledgerItems as &$item) {
            $currentBalance += ($item['debit'] - $item['kredit']);
            $item['saldo'] = $currentBalance;
        }
        unset($item);

        // 5.5 Paginate the ledger items array
        $perPage = 20;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $currentItems = array_slice($ledgerItems, ($currentPage - 1) * $perPage, $perPage);
        $ledgerItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems, 
            count($ledgerItems), 
            $perPage, 
            $currentPage, 
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('laporan.index', compact(
            'type', 'dateStr', 'cashierId', 'paymentMethod', 'reportTitle', 
            'labels', 'data', 'transactions', 'totalOmset', 'totalPajak', 
            'totalBersih', 'cashCount', 'qrisCount', 'cashiers', 'tab',
            'revFood', 'revDrink', 'revMerch', 'revPartnership', 'revOthers', 'grossSales',
            'discount', 'netRevenue', 'hppFood', 'hppDrink', 'hppPackaging', 'totalHpp',
            'grossProfit', 'opexStaff', 'opexUtility', 'opexMarketing', 'opexMaintenance', 'opexLegalAdmin',
            'totalOpex', 'ebitda', 'depreciation', 'taxFinal', 'netProfit',
            'pettyCashVal', 'inventoryCostVal', 'bankBalanceVal', 'ojolPendingBalanceVal', 'totalCurrentAssets',
            'fixedAssetsCostVal', 'fixedAssetsDepreciationVal', 'netFixedAssets', 'totalAssets',
            'supplierLiabilitiesVal', 'salaryLiabilitiesVal', 'taxLiabilityPB1Val', 'totalLiabilities',
            'initialEquityVal', 'retainedEarningsVal', 'totalEquity',
            'cashInOps', 'cashOutOps', 'cashFlowOps', 'cashFlowInvest', 'cashFlowFinance', 'netCashFlow',
            'ledgerItems'
        ));
    }

    public function storeLedger(Request $request)
    {
        $user = $this->getActiveUser();
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kode_akun' => 'required|integer',
            'deskripsi' => 'required|string|max:255',
            'metode' => 'required|in:Tunai,Transfer,Potong',
            'tipe_transaksi' => 'required|in:debit,kredit',
            'nominal' => 'required|numeric|min:0',
        ]);

        $debit = 0;
        $kredit = 0;

        if ($validated['tipe_transaksi'] === 'debit') {
            $debit = $validated['nominal'];
        } else {
            $kredit = $validated['nominal'];
        }

        $tx = KeuanganTransaksi::create([
            'tanggal' => $validated['tanggal'],
            'kode_akun' => $validated['kode_akun'],
            'deskripsi' => $validated['deskripsi'],
            'metode' => $validated['metode'],
            'debit' => $debit,
            'kredit' => $kredit,
            'id_user' => $user ? $user->id_user : null,
        ]);

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'POST_LEDGER_TRANSACTION',
            'detail_aktivitas' => "Added ledger entry {$tx->nama_akun}: Rp {$validated['nominal']} ({$validated['tipe_transaksi']})",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Transaksi keuangan berhasil dibukukan.');
    }

    public function deleteLedger(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $tx = KeuanganTransaksi::findOrFail($id);
        $name = $tx->nama_akun;
        $tx->delete();

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'DELETE_LEDGER_TRANSACTION',
            'detail_aktivitas' => "Deleted ledger entry: {$name}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Transaksi dibatalkan & dihapus dari buku kas.');
    }
}
