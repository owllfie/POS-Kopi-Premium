@extends('layouts.app')

@section('title', 'Laporan Keuangan')
@section('page_title', 'Laporan Keuangan & Akuntansi')

@section('content')
<div class="space-y-6" x-data="reportManager()">

    <!-- Tab Selection Bar -->
    <div class="flex border-b border-coffee-latte bg-white px-6 py-2.5 rounded-2xl border border-coffee-latte coffee-card no-print overflow-x-auto scrollbar-none">
        <div class="flex gap-2">
            <a href="{{ route('laporan', ['tab' => 'pos', 'date' => $dateStr, 'type' => $type]) }}" class="px-4 py-2 text-xs font-bold rounded-xl transition cursor-pointer {{ $tab === 'pos' ? 'bg-coffee-dark text-white shadow-md' : 'text-coffee-light hover:text-coffee-dark hover:bg-coffee-cream/30' }}">
                Performa Penjualan POS
            </a>
            <a href="{{ route('laporan', ['tab' => 'laba-rugi', 'date' => $dateStr, 'type' => $type]) }}" class="px-4 py-2 text-xs font-bold rounded-xl transition cursor-pointer {{ $tab === 'laba-rugi' ? 'bg-coffee-dark text-white shadow-md' : 'text-coffee-light hover:text-coffee-dark hover:bg-coffee-cream/30' }}">
                Laba Rugi (P&L)
            </a>
            <a href="{{ route('laporan', ['tab' => 'neraca', 'date' => $dateStr, 'type' => $type]) }}" class="px-4 py-2 text-xs font-bold rounded-xl transition cursor-pointer {{ $tab === 'neraca' ? 'bg-coffee-dark text-white shadow-md' : 'text-coffee-light hover:text-coffee-dark hover:bg-coffee-cream/30' }}">
                Neraca Keuangan
            </a>
            <a href="{{ route('laporan', ['tab' => 'arus-kas', 'date' => $dateStr, 'type' => $type]) }}" class="px-4 py-2 text-xs font-bold rounded-xl transition cursor-pointer {{ $tab === 'arus-kas' ? 'bg-coffee-dark text-white shadow-md' : 'text-coffee-light hover:text-coffee-dark hover:bg-coffee-cream/30' }}">
                Arus Kas (Cash Flow)
            </a>
            <a href="{{ route('laporan', ['tab' => 'ledger', 'date' => $dateStr, 'type' => $type]) }}" class="px-4 py-2 text-xs font-bold rounded-xl transition cursor-pointer {{ $tab === 'ledger' ? 'bg-coffee-dark text-white shadow-md' : 'text-coffee-light hover:text-coffee-dark hover:bg-coffee-cream/30' }}">
                Buku Kas Harian
            </a>
        </div>
    </div>

    <!-- Filters Panel (Visible for POS Tab and Ledger Tab) -->
    @if($tab === 'pos')
        <form action="{{ route('laporan') }}" method="GET" class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end no-print">
            <input type="hidden" name="tab" value="pos">
            <div>
                <label for="type" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Tipe Laporan</label>
                <select name="type" id="type" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    <option value="harian" {{ $type === 'harian' ? 'selected' : '' }}>Laporan Harian</option>
                    <option value="mingguan" {{ $type === 'mingguan' ? 'selected' : '' }}>Laporan Mingguan</option>
                    <option value="bulanan" {{ $type === 'bulanan' ? 'selected' : '' }}>Laporan Bulanan</option>
                </select>
            </div>

            <div>
                <label for="date" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Tanggal Acuan</label>
                <input 
                    type="date" 
                    name="date" 
                    id="date" 
                    value="{{ $dateStr }}"
                    onchange="this.form.submit()"
                    class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"
                >
            </div>

            <div>
                <label for="kasir_id" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kasir Petugas</label>
                <select name="kasir_id" id="kasir_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    <option value="semua" {{ $cashierId === 'semua' ? 'selected' : '' }}>Semua Kasir</option>
                    @foreach($cashiers as $kasir)
                        <option value="{{ $kasir->id_user }}" {{ $cashierId == $kasir->id_user ? 'selected' : '' }}>{{ $kasir->username }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="metode" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Metode Pembayaran</label>
                <select name="metode" id="metode" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    <option value="semua" {{ $paymentMethod === 'semua' ? 'selected' : '' }}>Semua Metode</option>
                    <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>CASH (Tunai)</option>
                    <option value="qris" {{ $paymentMethod === 'qris' ? 'selected' : '' }}>QRIS (Non-Tunai)</option>
                </select>
            </div>
        </form>
    @else
        <!-- Standard Date Filter for accounting reports -->
        <form action="{{ route('laporan') }}" method="GET" class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card flex items-end gap-4 no-print max-w-sm">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-grow">
                <label for="date_acuan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Pilih Bulan & Tahun</label>
                <input 
                    type="date" 
                    name="date" 
                    id="date_acuan" 
                    value="{{ $dateStr }}"
                    onchange="this.form.submit()"
                    class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"
                >
            </div>
        </form>
    @endif

    <!-- Report Header Info -->
    <div class="flex items-center justify-between border-b border-coffee-latte pb-4">
        <div>
            <h3 class="font-extrabold text-coffee-dark text-lg">
                @if($tab === 'pos') Performa Penjualan POS @elseif($tab === 'laba-rugi') Laporan Laba Rugi bulanan @elseif($tab === 'neraca') Laporan Neraca Keuangan @elseif($tab === 'arus-kas') Laporan Arus Kas (Cash Flow) @else Buku Kas Harian (Ledger) @endif
            </h3>
            <span class="text-xs text-coffee-light font-bold block mt-0.5 uppercase tracking-wider">
                @if($tab === 'pos') {{ $reportTitle }} @else Periode Bulanan — {{ \Carbon\Carbon::parse($dateStr)->format('F Y') }} @endif
            </span>
        </div>
        <div class="flex gap-2 no-print">
            @if($tab === 'ledger')
                <button @click="ledgerModal = true" class="px-4 py-2 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-coffee-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Input Transaksi</span>
                </button>
            @endif
            <button onclick="window.print()" class="px-4 py-2 border border-coffee-medium text-coffee-dark rounded-xl text-xs font-bold hover:bg-coffee-latte transition shadow flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: POS PERFORMANCE -->
    @if($tab === 'pos')
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card text-center space-y-1">
                <span class="text-[10px] font-bold text-coffee-light uppercase tracking-wider block">Total Omset Kotor</span>
                <strong class="text-2xl text-coffee-dark font-extrabold block">Rp {{ number_format($totalOmset, 0, ',', '.') }}</strong>
                <div class="text-[10px] text-coffee-light font-medium pt-1 border-t border-coffee-latte/50 flex justify-around">
                    <span>Tunai: Rp {{ number_format($cashCount, 0, ',', '.') }}</span>
                    <span>QRIS: Rp {{ number_format($qrisCount, 0, ',', '.') }}</span>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card text-center space-y-1">
                <span class="text-[10px] font-bold text-coffee-light uppercase tracking-wider block">Total Pajak Terkumpul</span>
                <strong class="text-2xl text-coffee-light font-extrabold block">Rp {{ number_format($totalPajak, 0, ',', '.') }}</strong>
                <span class="text-[9px] text-coffee-light font-medium block">Pajak Restoran PB1</span>
            </div>

            <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card text-center space-y-1">
                <span class="text-[10px] font-bold text-coffee-light uppercase tracking-wider block">Total Pendapatan Bersih</span>
                <strong class="text-2xl text-emerald-800 font-extrabold block">Rp {{ number_format($totalBersih, 0, ',', '.') }}</strong>
                <span class="text-[9px] text-emerald-600 font-medium block">Omset kotor dikurangi Pajak PB1</span>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4 no-print">
            <h4 class="font-bold text-coffee-dark">Grafik Performa Penjualan</h4>
            @php
                $maxVal = max($data) ?: 100000;
                $svgHeight = 160;
                $svgWidth = 500;
                $padX = 45;
                $padY = 20;
                $chartH = $svgHeight - (2 * $padY);
                $chartW = $svgWidth - (2 * $padX);
                $count = count($data);
                $colW = $chartW / ($count > 1 ? $count - 1 : 1);
            @endphp
            <div class="w-full">
                <svg viewBox="0 0 500 180" class="w-full overflow-visible">
                    @for($g = 0; $g <= 4; $g++)
                        @php
                            $yPos = $padY + ($chartH * ($g / 4));
                            $gridVal = $maxVal * (1 - ($g / 4));
                        @endphp
                        <line x1="{{ $padX }}" y1="{{ $yPos }}" x2="{{ $svgWidth - $padX }}" y2="{{ $yPos }}" stroke="#EFEBE9" stroke-width="1" stroke-dasharray="4" />
                        <text x="{{ $padX - 8 }}" y="{{ $yPos + 4 }}" font-size="9" fill="#8D6E63" font-weight="600" text-anchor="end">
                            {{ $gridVal >= 1000 ? number_format($gridVal / 1000, 0) . 'k' : number_format($gridVal, 0) }}
                        </text>
                    @endfor
                    @php $points = ''; @endphp
                    @foreach($data as $index => $val)
                        @php
                            $barHeight = $maxVal > 0 ? ($val / $maxVal) * $chartH : 0;
                            $x = $padX + ($index * $colW);
                            $y = $svgHeight - $padY - $barHeight;
                            $points .= "$x,$y ";
                        @endphp
                    @endforeach
                    <polyline fill="none" stroke="#8D6E63" stroke-width="2.5" points="{{ trim($points) }}" />
                    @foreach($data as $index => $val)
                        @php
                            $barHeight = $maxVal > 0 ? ($val / $maxVal) * $chartH : 0;
                            $x = $padX + ($index * $colW);
                            $y = $svgHeight - $padY - $barHeight;
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="#3E2723" stroke="#D4AF37" stroke-width="1.5" cursor="pointer">
                            <title>Rp {{ number_format($val, 0, ',', '.') }}</title>
                        </circle>
                        <text x="{{ $x }}" y="{{ $svgHeight - $padY + 14 }}" font-size="8" fill="#3E2723" font-weight="bold" text-anchor="middle">
                            {{ $labels[$index] }}
                        </text>
                    @endforeach
                </svg>
            </div>
        </div>

        <!-- Invoices List -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4">
            <h4 class="font-bold text-coffee-dark border-b border-coffee-latte pb-3">Daftar Struk Transaksi</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                            <th class="pb-3">Struk</th>
                            <th class="pb-3">Meja</th>
                            <th class="pb-3">Petugas Kasir</th>
                            <th class="pb-3">Metode</th>
                            <th class="pb-3">Subtotal (Net)</th>
                            <th class="pb-3">Pajak</th>
                            <th class="pb-3">Total Bayar</th>
                            <th class="pb-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                        @forelse($transactions as $tx)
                            <tr>
                                <td class="py-3.5 font-bold text-xs tracking-wide text-coffee-light">{{ $tx->kode_struk }}</td>
                                <td class="py-3.5">Meja {{ $tx->meja->nomor_meja }}</td>
                                <td class="py-3.5">{{ $tx->user ? $tx->user->username : 'System' }}</td>
                                <td class="py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $tx->metode_pembayaran === 'cash' ? 'bg-amber-50 border border-amber-100 text-coffee-light' : 'bg-blue-50 border border-blue-100 text-blue-600' }}">
                                        {{ $tx->metode_pembayaran }}
                                    </span>
                                </td>
                                <td class="py-3.5">Rp {{ number_format($tx->total_harga, 0, ',', '.') }}</td>
                                <td class="py-3.5 text-xs text-coffee-light">Rp {{ number_format($tx->pajak, 0, ',', '.') }}</td>
                                <td class="py-3.5 font-bold text-coffee-dark">Rp {{ number_format($tx->total_bayar, 0, ',', '.') }}</td>
                                <td class="py-3.5 text-xs text-coffee-light font-medium">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-coffee-light font-medium">Belum ada transaksi sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links for POS Transactions -->
            <div class="mt-6 no-print">
                {{ $transactions->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 2: LABA RUGI (PROFIT & LOSS) -->
    @if($tab === 'laba-rugi')
        <div class="bg-white rounded-3xl border border-coffee-latte p-8 coffee-card max-w-3xl mx-auto space-y-6">
            <div class="text-center space-y-1 pb-4 border-b border-coffee-latte">
                <h2 class="text-xl font-black text-coffee-dark tracking-wide uppercase">Laporan Laba Rugi</h2>
                <p class="text-xs text-coffee-medium font-bold">Periode: {{ \Carbon\Carbon::parse($dateStr)->format('F Y') }}</p>
                <p class="text-[10px] text-coffee-light font-medium uppercase tracking-wider">Mata Uang: IDR (Rupiah)</p>
            </div>

            <!-- Financial Table Structure -->
            <table class="w-full text-sm font-semibold text-coffee-dark">
                <tbody>
                    <!-- REVENUES -->
                    <tr class="text-xs font-bold text-coffee-light uppercase tracking-wider">
                        <td class="py-2" colspan="2">1. Pendapatan (Revenue)</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Penjualan Makanan (POS)</td>
                        <td class="py-2 text-right">Rp {{ number_format($revFood, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Penjualan Minuman (Bar POS)</td>
                        <td class="py-2 text-right">Rp {{ number_format($revDrink, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Penjualan Merchandise & Kemasan</td>
                        <td class="py-2 text-right">Rp {{ number_format($revMerch, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Pendapatan Kemitraan (Promo Subsidi)</td>
                        <td class="py-2 text-right">Rp {{ number_format($revPartnership, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Pendapatan Lain-lain (Jelantah/Space)</td>
                        <td class="py-2 text-right">Rp {{ number_format($revOthers, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-coffee-cream/20">
                        <td class="py-2.5 font-bold">TOTAL PENDAPATAN KOTOR</td>
                        <td class="py-2.5 text-right font-bold text-coffee-dark border-t border-coffee-latte">Rp {{ number_format($grossSales, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Diskon & Promo Penjualan (6304)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($discount, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="bg-coffee-cream/35">
                        <td class="py-2.5 font-extrabold uppercase">PENDAPATAN BERSIH (Net Revenue)</td>
                        <td class="py-2.5 text-right font-extrabold text-coffee-dark border-t border-coffee-latte">Rp {{ number_format($netRevenue, 0, ',', '.') }}</td>
                    </tr>

                    <!-- COGS / HPP -->
                    <tr class="text-xs font-bold text-coffee-light uppercase tracking-wider pt-4 block">
                        <td class="py-2" colspan="2">2. Harga Pokok Penjualan (HPP / COGS)</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">HPP Bahan Baku Makanan (5100)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($hppFood, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">HPP Bahan Baku Minuman (5200)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($hppDrink, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">HPP Kemasan & Konsumabel (5300)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($hppPackaging, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="bg-coffee-cream/20">
                        <td class="py-2.5 font-bold">TOTAL HARGA POKOK PENJUALAN (HPP)</td>
                        <td class="py-2.5 text-right font-bold text-rose-700 border-t border-coffee-latte">(Rp {{ number_format($totalHpp, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="bg-amber-100/20 border-y border-coffee-latte">
                        <td class="py-3 font-extrabold uppercase">LABA KOTOR (Gross Profit)</td>
                        <td class="py-3 text-right font-extrabold text-coffee-dark">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
                    </tr>

                    <!-- OPEX -->
                    <tr class="text-xs font-bold text-coffee-light uppercase tracking-wider pt-4 block">
                        <td class="py-2" colspan="2">3. Beban Operasional (OPEX)</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Biaya Karyawan (6100 - Gaji, Lembur, THR)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($opexStaff, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Biaya Utilitas & Tempat (6200 - Listrik, Air, Gas, Sewa)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($opexUtility, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Biaya Pemasaran (6300 - Ads, Influencer, Komisi Ojol)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($opexMarketing, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Biaya Pemeliharaan & Alat Pecah (6400)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($opexMaintenance, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Biaya Administrasi & Legalitas (6500)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($opexLegalAdmin, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="bg-coffee-cream/20 border-b border-coffee-latte">
                        <td class="py-2.5 font-bold">TOTAL BEBAN OPERASIONAL (OPEX)</td>
                        <td class="py-2.5 text-right font-bold text-rose-700">(Rp {{ number_format($totalOpex, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="bg-coffee-cream/35">
                        <td class="py-2.5 font-extrabold uppercase">EBITDA</td>
                        <td class="py-2.5 text-right font-extrabold text-coffee-dark">Rp {{ number_format($ebitda, 0, ',', '.') }}</td>
                    </tr>

                    <!-- DEPRECIATION & TAX -->
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Penyusutan Peralatan & Interior (Amortisasi)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($depreciation, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Pajak Penghasilan Resto (PPh Final UMKM 0.5%)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($taxFinal, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="bg-coffee-dark text-white rounded-xl border-t-2 border-double border-coffee-gold">
                        <td class="py-3.5 px-4 font-black text-xs uppercase tracking-wider text-coffee-gold rounded-l-xl">LABA BERSIH (Net Profit / Loss)</td>
                        <td class="py-3.5 px-4 text-right font-black text-sm text-white rounded-r-xl">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    <!-- TAB 3: NERACA (BALANCE SHEET) -->
    @if($tab === 'neraca')
        <div class="bg-white rounded-3xl border border-coffee-latte p-8 coffee-card max-w-3xl mx-auto space-y-6">
            <div class="text-center space-y-1 pb-4 border-b border-coffee-latte">
                <h2 class="text-xl font-black text-coffee-dark tracking-wide uppercase">Neraca Keuangan (Balance Sheet)</h2>
                <p class="text-xs text-coffee-medium font-bold">Per Tanggal: {{ \Carbon\Carbon::parse($dateStr)->format('d F Y') }}</p>
                <p class="text-[10px] text-coffee-light font-medium uppercase tracking-wider">Mata Uang: IDR (Rupiah)</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm font-semibold text-coffee-dark">
                <!-- LEFTSIDE: ASSETS -->
                <div class="space-y-4">
                    <h4 class="font-extrabold text-coffee-medium border-b border-coffee-latte pb-2 uppercase text-xs tracking-wider">1. ASET (ASSETS)</h4>
                    
                    <div class="space-y-2">
                        <h5 class="text-xs font-bold text-coffee-light uppercase tracking-wider">Aset Lancar (Current Assets)</h5>
                        <div class="flex justify-between border-b border-coffee-cream py-1.5 pl-2 text-xs font-medium">
                            <span>Kas Kecil (Petty Cash Laci)</span>
                            <span>Rp {{ number_format($pettyCashVal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-coffee-cream py-1.5 pl-2 text-xs font-medium">
                            <span>Kas Bank (Saldo Rekening)</span>
                            <span>Rp {{ number_format($bankBalanceVal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-coffee-cream py-1.5 pl-2 text-xs font-medium">
                            <span>Dana Ojol Terendap (Aggregator)</span>
                            <span>Rp {{ number_format($ojolPendingBalanceVal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-coffee-cream py-1.5 pl-2 text-xs font-medium" title="Dihitung otomatis dari inventaris bahan baku">
                            <span>Persediaan Bahan Baku (Stock)</span>
                            <span>Rp {{ number_format($inventoryCostVal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between bg-coffee-cream/20 py-1.5 font-bold text-xs">
                            <span>Total Aset Lancar</span>
                            <span>Rp {{ number_format($totalCurrentAssets, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2">
                        <h5 class="text-xs font-bold text-coffee-light uppercase tracking-wider">Aset Tetap (Fixed Assets)</h5>
                        <div class="flex justify-between border-b border-coffee-cream py-1.5 pl-2 text-xs font-medium" title="Dihitung otomatis dari peralatan inventaris">
                            <span>Peralatan & Mesin Bar/Dapur</span>
                            <span>Rp {{ number_format($fixedAssetsCostVal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-coffee-cream py-1.5 pl-2 text-xs font-medium">
                            <span>Akumulasi Penyusutan Alat</span>
                            <span class="text-rose-700">(Rp {{ number_format($fixedAssetsDepreciationVal, 0, ',', '.') }})</span>
                        </div>
                        <div class="flex justify-between bg-coffee-cream/20 py-1.5 font-bold text-xs">
                            <span>Total Aset Tetap (Netto)</span>
                            <span>Rp {{ number_format($netFixedAssets, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between bg-coffee-dark text-white p-3.5 rounded-xl border border-coffee-gold mt-4 font-black">
                        <span>TOTAL ASET</span>
                        <span class="text-coffee-gold">Rp {{ number_format($totalAssets, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- RIGHTSIDE: LIABILITIES & EQUITY -->
                <div class="space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <h4 class="font-extrabold text-coffee-medium border-b border-coffee-latte pb-2 uppercase text-xs tracking-wider">2. KEWAJIBAN & MODAL</h4>
                        
                        <div class="space-y-2">
                            <h5 class="text-xs font-bold text-coffee-light uppercase tracking-wider">Kewajiban Lancar (Liabilities)</h5>
                            <div class="flex justify-between border-b border-coffee-cream py-1.5 pl-2 text-xs font-medium">
                                <span>Utang Dagang Supplier</span>
                                <span>Rp {{ number_format($supplierLiabilitiesVal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between border-b border-coffee-cream py-1.5 pl-2 text-xs font-medium">
                                <span>Utang Gaji Karyawan</span>
                                <span>Rp {{ number_format($salaryLiabilitiesVal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between border-b border-coffee-cream py-1.5 pl-2 text-xs font-medium" title="Koleksi Pajak PB1/PBJT POS yang belum disetor">
                                <span>Titipan Pajak PB1 (10%)</span>
                                <span>Rp {{ number_format($taxLiabilityPB1Val, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between bg-coffee-cream/20 py-1.5 font-bold text-xs">
                                <span>Total Kewajiban</span>
                                <span>Rp {{ number_format($totalLiabilities, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="space-y-2 pt-2">
                            <h5 class="text-xs font-bold text-coffee-light uppercase tracking-wider">Ekuitas & Modal (Equity)</h5>
                            <div class="flex justify-between border-b border-coffee-cream py-1.5 pl-2 text-xs font-medium">
                                <span>Modal Disetor Awal</span>
                                <span>Rp {{ number_format($initialEquityVal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between border-b border-coffee-cream py-1.5 pl-2 text-xs font-medium">
                                <span>Laba Ditahan (Retained Earnings)</span>
                                <span>Rp {{ number_format($retainedEarningsVal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between bg-coffee-cream/20 py-1.5 font-bold text-xs">
                                <span>Total Ekuitas</span>
                                <span>Rp {{ number_format($totalEquity, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between bg-coffee-medium text-white p-3.5 rounded-xl border border-coffee-latte mt-4 font-black">
                        <span>TOTAL PASSIVA</span>
                        <span>Rp {{ number_format($totalLiabilities + $totalEquity, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Balanced Indicator -->
            <div class="p-3 text-center rounded-2xl text-xs font-bold mt-4 {{ round($totalAssets) === round($totalLiabilities + $totalEquity) ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                <span>Status Neraca: <strong>{{ round($totalAssets) === round($totalLiabilities + $totalEquity) ? 'BALANCED (SEIMBANG)' : 'UNBALANCED (TIDAK SEIMBANG)' }}</strong></span>
            </div>
        </div>
    @endif

    <!-- TAB 4: ARUS KAS (CASH FLOW) -->
    @if($tab === 'arus-kas')
        <div class="bg-white rounded-3xl border border-coffee-latte p-8 coffee-card max-w-3xl mx-auto space-y-6">
            <div class="text-center space-y-1 pb-4 border-b border-coffee-latte">
                <h2 class="text-xl font-black text-coffee-dark tracking-wide uppercase">Laporan Arus Kas (Cash Flow)</h2>
                <p class="text-xs text-coffee-medium font-bold">Periode: {{ \Carbon\Carbon::parse($dateStr)->format('F Y') }}</p>
                <p class="text-[10px] text-coffee-light font-medium uppercase tracking-wider">Metode Langsung (Direct Method)</p>
            </div>

            <table class="w-full text-sm font-semibold text-coffee-dark">
                <tbody>
                    <!-- OPERATIONAL -->
                    <tr class="text-xs font-bold text-coffee-light uppercase tracking-wider">
                        <td class="py-2" colspan="2">1. Arus Kas dari Aktivitas Operasional</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Penerimaan Kas dari Konsumen (POS)</td>
                        <td class="py-2 text-right text-emerald-700">Rp {{ number_format($cashInOps, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Pengeluaran Kas Belanja Harian & HPP (Ledger)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($totalHpp, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Pengeluaran Kas Pembayaran Operasional & Gaji (Ledger)</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format($totalOpex, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="bg-coffee-cream/20">
                        <td class="py-2.5 font-bold">Arus Kas Bersih dari Aktivitas Operasional</td>
                        <td class="py-2.5 text-right font-bold {{ $cashFlowOps >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($cashFlowOps, 0, ',', '.') }}</td>
                    </tr>

                    <!-- INVESTING -->
                    <tr class="text-xs font-bold text-coffee-light uppercase tracking-wider pt-4 block">
                        <td class="py-2" colspan="2">2. Arus Kas dari Aktivitas Investasi</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Pembelian Peralatan Bar & Aset Cafe</td>
                        <td class="py-2 text-right text-rose-700">(Rp {{ number_format(abs($cashFlowInvest), 0, ',', '.') }})</td>
                    </tr>
                    <tr class="bg-coffee-cream/20">
                        <td class="py-2.5 font-bold">Arus Kas Bersih dari Aktivitas Investasi</td>
                        <td class="py-2.5 text-right font-bold text-rose-700">(Rp {{ number_format(abs($cashFlowInvest), 0, ',', '.') }})</td>
                    </tr>

                    <!-- FINANCING -->
                    <tr class="text-xs font-bold text-coffee-light uppercase tracking-wider pt-4 block">
                        <td class="py-2" colspan="2">3. Arus Kas dari Aktivitas Pendanaan</td>
                    </tr>
                    <tr class="border-b border-coffee-cream">
                        <td class="py-2 pl-4 text-xs font-medium">Suntikan Modal Baru / Prive Pemilik</td>
                        <td class="py-2 text-right">Rp {{ number_format($cashFlowFinance, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-coffee-cream/20">
                        <td class="py-2.5 font-bold">Arus Kas Bersih dari Aktivitas Pendanaan</td>
                        <td class="py-2.5 text-right font-bold">Rp {{ number_format($cashFlowFinance, 0, ',', '.') }}</td>
                    </tr>

                    <!-- SUMMARY -->
                    <tr class="bg-coffee-dark text-white rounded-xl border-t-2 border-double border-coffee-gold mt-6">
                        <td class="py-3 px-4 font-black text-xs uppercase tracking-wider text-coffee-gold rounded-l-xl">KENAIKAN (PENURUNAN) BERSIH KAS</td>
                        <td class="py-3 px-4 text-right font-black text-sm text-white rounded-r-xl">Rp {{ number_format($netCashFlow, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    <!-- TAB 5: DAILY LEDGER (BUKU KAS HARIAN) -->
    @if($tab === 'ledger')
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4">
            <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                <h4 class="font-bold text-coffee-dark">Ledger Buku Kas Bulanan</h4>
                <span class="text-[10px] text-coffee-light bg-coffee-cream border border-coffee-latte px-2 py-0.5 rounded font-bold">Urut Kronologis</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                            <th class="pb-3">Tanggal</th>
                            <th class="pb-3">Akun COA</th>
                            <th class="pb-3">Nama Akun</th>
                            <th class="pb-3">Deskripsi</th>
                            <th class="pb-3">Metode</th>
                            <th class="pb-3 text-right">Debit (+)</th>
                            <th class="pb-3 text-right">Kredit (-)</th>
                            <th class="pb-3 text-right">Saldo Kas</th>
                            <th class="pb-3 text-right no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark text-xs font-mono">
                        @forelse($ledgerItems as $item)
                            <tr class="{{ $item['is_sale'] ? 'bg-amber-50/20' : '' }}">
                                <td class="py-3.5">{{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y') }}</td>
                                <td class="py-3.5 font-bold text-coffee-medium">{{ $item['kode_akun'] }}</td>
                                <td class="py-3.5 text-coffee-light">
                                    @php
                                        $coa = [
                                            4100 => 'Penjualan Makanan',
                                            4200 => 'Penjualan Minuman',
                                            4300 => 'Penjualan Merchandise',
                                            4400 => 'Pendapatan Kemitraan',
                                            4500 => 'Pendapatan Lain-lain',
                                            5100 => 'HPP Makanan',
                                            5200 => 'HPP Minuman',
                                            5300 => 'HPP Kemasan',
                                            6100 => 'Biaya Karyawan',
                                            6101 => 'Gaji Pokok Karyawan',
                                            6102 => 'Uang Lembur Karyawan',
                                            6103 => 'THR & BPJS Karyawan',
                                            6201 => 'Sewa Tempat Ruko',
                                            6202 => 'Listrik PLN',
                                            6203 => 'Air PDAM',
                                            6204 => 'Gas LPG dapur',
                                            6205 => 'Langganan WiFi Internet',
                                            6301 => 'Digital Promo Ads',
                                            6302 => 'Influencer Endorsement',
                                            6303 => 'Cetak Buku Menu',
                                            6304 => 'Diskon & Promo',
                                            6305 => 'Komisi Ojol (Bagi Hasil)',
                                            6401 => 'Servis Mesin Kopi',
                                            6402 => 'Servis Gedung / AC',
                                            6403 => 'Bahan Kimia & Sabun',
                                            6404 => 'Alat Makan Pecah',
                                            6501 => 'Sampah & Keamanan',
                                            6502 => 'Titipan Pajak PB1',
                                            6503 => 'Sertifikasi Halal NIB',
                                            6504 => 'Admin Bank QRIS',
                                        ];
                                        echo $coa[$item['kode_akun']] ?? 'Beban Akun';
                                    @endphp
                                </td>
                                <td class="py-3.5 text-coffee-dark font-sans">{{ $item['deskripsi'] }}</td>
                                <td class="py-3.5 text-[10px] uppercase font-bold text-coffee-light">{{ $item['metode'] }}</td>
                                <td class="py-3.5 text-right font-bold text-emerald-800">
                                    {{ $item['debit'] > 0 ? 'Rp ' . number_format($item['debit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-3.5 text-right text-rose-700">
                                    {{ $item['kredit'] > 0 ? 'Rp ' . number_format($item['kredit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-3.5 text-right font-bold text-coffee-dark">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                                <td class="py-3.5 text-right no-print">
                                    @if(!$item['is_sale'])
                                        <form action="{{ route('laporan.ledger.delete', $item['id_transaksi']) }}" method="POST" onsubmit="return confirm('Batalkan transaksi ini?')">
                                            @csrf
                                            <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 cursor-pointer" title="Batalkan/Hapus Transaksi">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[9px] font-sans font-bold text-coffee-light uppercase select-none">Sistem</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-coffee-light font-sans font-semibold">Tidak ada pencatatan ledger bulan ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links for Ledger Items -->
            <div class="mt-6 no-print">
                {{ $ledgerItems->links() }}
            </div>
        </div>
    @endif

    <!-- ADD LEDGER ENTRY MODAL -->
    <template x-teleport="body">
        <div 
            x-show="ledgerModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition
            style="display: none;"
        >
            <div 
                @click.away="ledgerModal = false" 
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-md w-full space-y-4 coffee-card"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark">Input Transaksi Buku Kas</h3>
                    <button @click="ledgerModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>
    
                <form action="{{ route('laporan.ledger.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                        </div>
                        <div>
                            <label for="metode_ledger" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Metode Kas</label>
                            <select name="metode" id="metode_ledger" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="Tunai">Tunai (Petty Cash)</option>
                                <option value="Transfer">Transfer Bank</option>
                                <option value="Potong">Potong saldo</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="kode_akun" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kode Akun (COA)</label>
                        <select name="kode_akun" id="kode_akun" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                            <!-- Revenue -->
                            <optgroup label="4000 - Pendapatan (Revenue)">
                                <option value="4300">4300 - Penjualan Produk Kemasan / Merchandise</option>
                                <option value="4400">4400 - Pendapatan Kemitraan (Promo Aggregator)</option>
                                <option value="4500">4500 - Pendapatan Lain-lain</option>
                            </optgroup>
                            <!-- HPP -->
                            <optgroup label="5000 - Harga Pokok Penjualan (HPP)">
                                <option value="5100">5100 - HPP Bahan Baku Makanan</option>
                                <option value="5200">5200 - HPP Bahan Baku Minuman</option>
                                <option value="5300">5300 - HPP Kemasan & Konsumabel</option>
                            </optgroup>
                            <!-- OPEX -->
                            <optgroup label="6000 - Beban Operasional (OPEX)">
                                <option value="6101">6101 - OPEX Gaji Pokok Karyawan</option>
                                <option value="6102">6102 - OPEX Lembur Karyawan</option>
                                <option value="6103">6103 - OPEX THR & BPJS</option>
                                <option value="6201">6201 - OPEX Sewa Bulanan Tempat</option>
                                <option value="6202">6202 - OPEX Listrik Toko</option>
                                <option value="6203">6203 - OPEX Air Toko</option>
                                <option value="6204">6204 - OPEX Gas LPG dapur</option>
                                <option value="6205">6205 - OPEX Internet Wi-Fi</option>
                                <option value="6301">6301 - OPEX Digital Ads</option>
                                <option value="6302">6302 - OPEX Influencer / Endorsement</option>
                                <option value="6303">6303 - OPEX Cetak Menu / Banner</option>
                                <option value="6304">6304 - OPEX Diskon / Promo</option>
                                <option value="6305">6305 - OPEX Komisi Aplikasi Ojol</option>
                                <option value="6401">6401 - OPEX Servis Alat Dapur/Mesin Kopi</option>
                                <option value="6402">6402 - OPEX Servis Gedung / AC</option>
                                <option value="6403">6403 - OPEX Sabun / Alat Kebersihan</option>
                                <option value="6404">6404 - OPEX Alat Makan Pecah (Breakage)</option>
                                <option value="6501">6501 - OPEX Iuran Sampah & Keamanan</option>
                                <option value="6503">6503 - OPEX Sertifikasi Halal / NIB</option>
                                <option value="6504">6504 - OPEX Admin Bank / QRIS</option>
                            </optgroup>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="tipe_transaksi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Jenis Arus</label>
                            <select name="tipe_transaksi" id="tipe_transaksi" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="kredit">Kredit / Kas Keluar (-)</option>
                                <option value="debit">Debit / Kas Masuk (+)</option>
                            </select>
                        </div>
                        <div>
                            <label for="nominal" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Jumlah Nominal</label>
                            <input type="number" name="nominal" id="nominal" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="150000">
                        </div>
                    </div>

                    <div>
                        <label for="deskripsi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Deskripsi Transaksi</label>
                        <input type="text" name="deskripsi" id="deskripsi" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Contoh: Belanja kopi robusta robusta 5 kg">
                    </div>
    
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="ledgerModal = false" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Posting Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>

<script>
    function reportManager() {
        return {
            ledgerModal: false,
        }
    }
</script>

<style>
    @media print {
        header, aside, .no-print, form, .bg-amber-900 {
            display: none !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
        }
        body {
            background: white !important;
            color: black !important;
        }
        .coffee-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
        /* Custom print sizing */
        .max-w-3xl {
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
        }
    }
</style>
@endsection
