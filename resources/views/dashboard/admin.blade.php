@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard Operasional')

@section('content')
<div class="space-y-6">

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Pendapatan Hari Ini</span>
                <h3 class="text-2xl font-bold text-coffee-dark">Rp {{ number_format($totalPendapatanHariIni, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Transaksi Hari Ini</span>
                <h3 class="text-2xl font-bold text-coffee-dark">{{ $totalTransaksiHariIni }} Transaksi</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Meja Terisi</span>
                <h3 class="text-2xl font-bold text-coffee-dark">{{ $jumlahMejaAktif }} Meja</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Menu Tersedia</span>
                <h3 class="text-2xl font-bold text-coffee-dark">{{ $totalMenuTersedia }} Item</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
        </div>
    </div>

    <!-- Chart & Top Menu Rows -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- SVG Chart -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-coffee-dark">Tren Pendapatan (7 Hari Terakhir)</h4>
            </div>

            @php
                $maxVal = max($chartData) ?: 50000;
                $svgHeight = 160;
                $svgWidth = 500;
                $padX = 40;
                $padY = 20;
                $chartH = $svgHeight - (2 * $padY);
                $chartW = $svgWidth - (2 * $padX);
                $count = count($chartData);
                $colW = $chartW / $count;
            @endphp
            <div class="w-full">
                <svg viewBox="0 0 500 180" class="w-full overflow-visible">
                    <!-- Grid lines -->
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

                    <!-- Bars -->
                    @foreach($chartData as $index => $val)
                        @php
                            $barHeight = $maxVal > 0 ? ($val / $maxVal) * $chartH : 0;
                            $x = $padX + ($index * $colW) + ($colW - 24) / 2;
                            $y = $svgHeight - $padY - $barHeight;
                        @endphp
                        <!-- Bar background glow on hover -->
                        <rect x="{{ $x }}" y="{{ $y }}" width="24" height="{{ $barHeight }}" rx="4" fill="url(#coffeeGradient)" class="transition-all duration-300 hover:fill-amber-900 cursor-pointer">
                            <title>Rp {{ number_format($val, 0, ',', '.') }}</title>
                        </rect>
                        <!-- Label below bar -->
                        <text x="{{ $x + 12 }}" y="{{ $svgHeight - $padY + 14 }}" font-size="9" fill="#3E2723" font-weight="bold" text-anchor="middle">
                            {{ $chartLabels[$index] }}
                        </text>
                    @endforeach

                    <!-- Gradients -->
                    <defs>
                        <linearGradient id="coffeeGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#8D6E63" />
                            <stop offset="100%" stop-color="#4A3531" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <!-- Top Menu Items -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4">
            <h4 class="font-bold text-coffee-dark">Menu Terlaris Hari Ini</h4>
            <div class="divide-y divide-coffee-latte">
                @forelse($topMenuItems as $index => $item)
                    @php $menuItem = \App\Models\Menu::find($item->id_menu); @endphp
                    @if($menuItem)
                        <div class="py-3 flex items-center justify-between first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-lg bg-coffee-latte text-coffee-text font-bold text-xs flex items-center justify-center">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-coffee-dark">{{ $menuItem->nama_menu }}</p>
                                    <p class="text-xs text-coffee-light font-medium">{{ $menuItem->kategori ? $menuItem->kategori->kategori : 'Tanpa Kategori' }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold bg-amber-50 text-coffee-light px-2.5 py-1 rounded-lg border border-amber-100">
                                {{ $item->total_qty }} Terjual
                            </span>
                        </div>
                    @endif
                @empty
                    <div class="py-12 text-center text-coffee-light text-sm font-medium">
                        Belum ada penjualan tercatat.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Transactions Row -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="font-bold text-coffee-dark">Transaksi Terakhir</h4>
            <a href="{{ route('transaksi') }}" class="text-xs font-bold text-coffee-light hover:text-coffee-dark flex items-center gap-1">
                <span>Lihat Semua</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                        <th class="pb-3">Struk</th>
                        <th class="pb-3">Meja</th>
                        <th class="pb-3">Kasir</th>
                        <th class="pb-3">Metode</th>
                        <th class="pb-3">Total Bayar</th>
                        <th class="pb-3">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                    @forelse($recentTransactions as $tx)
                        <tr>
                            <td class="py-3.5 font-bold text-xs tracking-wide text-coffee-light">{{ $tx->kode_struk }}</td>
                            <td class="py-3.5">{{ $tx->meja->nomor_meja == 99 ? 'Takeaway' : 'Meja ' . $tx->meja->nomor_meja }}</td>
                            <td class="py-3.5">{{ $tx->user ? $tx->user->username : 'System' }}</td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $tx->metode_pembayaran === 'cash' ? 'bg-amber-50 border border-amber-100 text-coffee-light' : 'bg-blue-50 border border-blue-100 text-blue-600' }}">
                                    {{ $tx->metode_pembayaran }}
                                </span>
                            </td>
                            <td class="py-3.5 font-bold">Rp {{ number_format($tx->total_bayar, 0, ',', '.') }}</td>
                            <td class="py-3.5 text-xs text-coffee-light font-medium">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-coffee-light font-medium">Belum ada transaksi tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lazy Loaded Audit Logs Row (Demonstrates Database Fetch Lazy Loading) -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="font-bold text-coffee-dark">Log Aktivitas Terbaru</h4>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100/50 text-coffee-light border border-amber-200/50 uppercase tracking-wider">Dynamic DB Fetch</span>
        </div>
        
        <!-- Lazy Load Target Element -->
        <div 
            class="lazy-fade min-h-[150px]"
            data-lazy-url="{{ route('dashboard.logs-lazy') }}"
            data-skeleton-type="table"
        >
            <!-- Content will be auto-injected here by LazyLoadManager when in viewport -->
        </div>
    </div>

</div>
@endsection

