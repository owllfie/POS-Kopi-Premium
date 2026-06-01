@extends('layouts.app')

@section('title', 'Dashboard Manager')
@section('page_title', 'Dashboard Manajerial')

@section('content')
<div class="space-y-6">

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Pendapatan Hari Ini</span>
                <h3 class="text-2xl font-bold text-coffee-dark">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Pendapatan Minggu Ini</span>
                <h3 class="text-2xl font-bold text-coffee-dark">Rp {{ number_format($pendapatanMingguIni, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/></svg>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Pendapatan Bulan Ini</span>
                <h3 class="text-2xl font-bold text-coffee-dark">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Transaksi Bulan Ini</span>
                <h3 class="text-2xl font-bold text-coffee-dark">{{ $totalTransaksiBulanIni }} Transaksi</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>
    </div>

    <!-- Chart & Quick Nav Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- SVG Line Trend Chart -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-coffee-dark">Tren Omset (Harian)</h4>
                <div class="flex items-center gap-1.5 text-xs font-bold text-coffee-light">
                    <span class="w-2 h-2 rounded-full bg-coffee-gold"></span>
                    <span>Bulan Berjalan</span>
                </div>
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
                $colW = $chartW / ($count > 1 ? $count - 1 : 1);
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

                    <!-- Line path -->
                    @php $points = ''; @endphp
                    @foreach($chartData as $index => $val)
                        @php
                            $barHeight = $maxVal > 0 ? ($val / $maxVal) * $chartH : 0;
                            $x = $padX + ($index * $colW);
                            $y = $svgHeight - $padY - $barHeight;
                            $points .= "$x,$y ";
                        @endphp
                    @endforeach
                    <polyline fill="none" stroke="#8D6E63" stroke-width="3" points="{{ trim($points) }}" />

                    <!-- Points -->
                    @foreach($chartData as $index => $val)
                        @php
                            $barHeight = $maxVal > 0 ? ($val / $maxVal) * $chartH : 0;
                            $x = $padX + ($index * $colW);
                            $y = $svgHeight - $padY - $barHeight;
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="5" fill="#3E2723" stroke="#D4AF37" stroke-width="1.5" cursor="pointer">
                            <title>Rp {{ number_format($val, 0, ',', '.') }}</title>
                        </circle>
                        <text x="{{ $x }}" y="{{ $svgHeight - $padY + 14 }}" font-size="9" fill="#3E2723" font-weight="bold" text-anchor="middle">
                            {{ $chartLabels[$index] }}
                        </text>
                    @endforeach
                </svg>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4 flex flex-col justify-between">
            <div>
                <h4 class="font-bold text-coffee-dark mb-1">Akses Cepat Manajer</h4>
                <p class="text-xs text-coffee-light font-medium mb-6">Kelola dan analisa performa bisnis restoran.</p>
                
                <div class="space-y-3">
                    <a href="{{ route('laporan') }}" class="flex items-center gap-3 p-3.5 rounded-xl bg-coffee-cream hover:bg-coffee-latte border border-coffee-latte text-coffee-text transition">
                        <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center text-coffee-light border border-amber-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold">Laporan Omset</p>
                            <p class="text-[10px] text-coffee-light">Analisa penjualan harian/mingguan/bulanan</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('transaksi') }}" class="flex items-center gap-3 p-3.5 rounded-xl bg-coffee-cream hover:bg-coffee-latte border border-coffee-latte text-coffee-text transition">
                        <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center text-coffee-light border border-amber-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold">Riwayat Transaksi</p>
                            <p class="text-[10px] text-coffee-light">Cari & audit struk belanja lengkap</p>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="text-center text-[10px] text-coffee-light font-medium mt-4">
                POS Kopi Premium v1.0
            </div>
        </div>
    </div>

</div>
@endsection
