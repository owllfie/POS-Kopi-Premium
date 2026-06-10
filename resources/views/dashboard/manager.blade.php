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
        <!-- Chart -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card lg:col-span-2 space-y-4" x-data="{ chartType: 'line' }">
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-coffee-dark">Tren Omset (Harian)</h4>
                
                <!-- Chart Type Selector -->
                <div class="flex items-center gap-1 bg-coffee-cream/40 p-1 rounded-xl border border-coffee-latte no-print">
                    <button type="button" @click="chartType = 'line'; changeChartType('line')" :class="chartType === 'line' ? 'bg-coffee-dark text-white shadow-sm' : 'text-coffee-light hover:bg-coffee-cream'" class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider transition">Line</button>
                    <button type="button" @click="chartType = 'bar'; changeChartType('bar')" :class="chartType === 'bar' ? 'bg-coffee-dark text-white shadow-sm' : 'text-coffee-light hover:bg-coffee-cream'" class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider transition">Bar</button>
                    <button type="button" @click="chartType = 'pie'; changeChartType('pie')" :class="chartType === 'pie' ? 'bg-coffee-dark text-white shadow-sm' : 'text-coffee-light hover:bg-coffee-cream'" class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider transition">Pie</button>
                </div>
            </div>

            <div class="w-full relative min-h-[220px] flex items-center justify-center">
                <canvas id="revenue-chart" class="w-full" style="max-height: 220px;"></canvas>
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

    <!-- Penjualan Harian & Shift Kasir Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Live Performance/Daily Sales Breakdown (CASH vs QRIS) -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-5 lg:col-span-1">
            <div>
                <h4 class="font-bold text-coffee-dark mb-1">Breakdown Omset Harian</h4>
                <p class="text-xs text-coffee-light font-medium">Distribusi metode pembayaran penjualan hari ini.</p>
            </div>
            
            <div class="space-y-4">
                <!-- Tunai -->
                <div class="p-4 rounded-xl bg-amber-50/50 border border-amber-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-coffee-medium">
                            <svg class="w-5 h-5 text-coffee-medium" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Tunai (CASH)</span>
                            <h5 class="font-extrabold text-coffee-dark">Rp {{ number_format($todaySales['cash_masuk'], 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>

                <!-- QRIS -->
                <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 8h16M4 16h16M4 20h16"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Non-Tunai (QRIS)</span>
                            <h5 class="font-extrabold text-coffee-dark">Rp {{ number_format($todaySales['qris_masuk'], 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="pt-3 border-t border-coffee-latte text-xs font-semibold text-coffee-medium flex justify-between">
                <span>Total Transaksi Hari Ini:</span>
                <span class="font-bold text-coffee-dark">{{ $todaySales['total_transaksi'] }} Transaksi</span>
            </div>
        </div>

        <!-- Shift Kerja Active & History Today -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card lg:col-span-2 space-y-4">
            <div>
                <h4 class="font-bold text-coffee-dark mb-1">Shift Kerja & Status Kasir Harian</h4>
                <p class="text-xs text-coffee-light font-medium">Pantau jam operasional kasir dan laci kas hari ini.</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                            <th class="pb-3">Kasir</th>
                            <th class="pb-3">Jam Kerja</th>
                            <th class="pb-3">Omset Shift</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                        @forelse($todayShifts as $shift)
                            <tr>
                                <td class="py-3.5 font-bold text-coffee-dark text-xs">{{ $shift->user->username }}</td>
                                <td class="py-3.5">
                                    <span class="block">Mulai: {{ $shift->jam_mulai->format('H:i') }}</span>
                                    <span class="text-[10px] text-coffee-light">
                                        Selesai: {{ $shift->jam_selesai ? $shift->jam_selesai->format('H:i') : '--:-- (Aktif)' }}
                                    </span>
                                </td>
                                <td class="py-3.5">
                                    <span class="block font-bold">Rp {{ number_format($shift->total_masuk, 0, ',', '.') }}</span>
                                    <span class="text-[10px] text-coffee-light">
                                        Cash: Rp {{ number_format($shift->cash_masuk, 0, ',', '.') }} | QRIS: Rp {{ number_format($shift->qris_masuk, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3.5">
                                    @if($shift->jam_selesai)
                                        <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-gray-100 border border-gray-200 text-gray-500">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-amber-100 border border-amber-200 text-coffee-light animate-pulse">
                                            Aktif
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-coffee-light font-medium">Belum ada aktivitas shift hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Shift Logs / Activity Feed -->
    @if(count($todayShiftLogs) > 0)
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4">
            <h4 class="font-bold text-coffee-dark">Log Pembukaan & Penutupan Shift Hari Ini</h4>
            <div class="divide-y divide-coffee-latte">
                @foreach($todayShiftLogs as $log)
                    <div class="py-3 flex items-start justify-between gap-4 text-xs border-b border-coffee-latte last:border-b-0">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[9px] uppercase font-bold {{ $log->aktivitas === 'START_SHIFT' ? 'bg-emerald-100 border border-emerald-200 text-emerald-800' : 'bg-red-100 border border-red-200 text-red-800' }}">
                                    {{ $log->aktivitas === 'START_SHIFT' ? 'Mulai Shift' : 'Tutup Shift' }}
                                </span>
                                <span class="font-extrabold text-coffee-dark">Kasir: {{ $log->user->username }}</span>
                            </div>
                            <p class="text-coffee-medium font-semibold">{{ $log->detail_aktivitas }}</p>
                        </div>
                        <span class="text-[10px] text-coffee-light font-medium flex-shrink-0">{{ $log->created_at->format('H:i:s') }} ({{ $log->created_at->diffForHumans() }})</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('revenue-chart').getContext('2d');
    const labels = @json($chartLabels);
    const dataValues = @json($chartData);

    // Coffee theme color palette
    const coffeeColors = [
        '#4A3531', '#5D4037', '#6D4C41', '#7D5748', 
        '#8D6E63', '#A1887F', '#BCAAA4', '#D7CCC8'
    ];

    // Chart.js instance variable
    let revenueChart = null;

    window.changeChartType = function(type) {
        if (revenueChart) {
            revenueChart.destroy();
        }

        const isPie = type === 'pie';

        // Set configuration based on chart type
        let chartConfig = {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Omset',
                    data: dataValues,
                    backgroundColor: isPie 
                        ? coffeeColors.slice(0, dataValues.length) 
                        : (type === 'bar' ? '#8D6E63' : 'rgba(141, 110, 99, 0.15)'),
                    borderColor: isPie ? '#FFFFFF' : '#4A3531',
                    borderWidth: isPie ? 2 : 3,
                    tension: 0.4, // Curvy line like Google currency chart
                    fill: type === 'line', // Fill under line chart
                    pointBackgroundColor: '#4A3531',
                    pointBorderColor: '#D4AF37',
                    pointBorderWidth: 2,
                    pointRadius: type === 'line' ? 5 : 0,
                    pointHoverRadius: type === 'line' ? 7 : 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: isPie, // Only show legend for Pie chart
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Outfit',
                                size: 10,
                                weight: 'bold'
                            },
                            color: '#3E2723'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== undefined) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                } else if (context.parsed !== undefined) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: isPie ? {} : {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#EFEBE9',
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 100000,
                            font: {
                                family: 'Outfit',
                                size: 9,
                                weight: '600'
                            },
                            color: '#8D6E63',
                            callback: function(value) {
                                return value >= 1000 ? (value / 1000) + 'k' : value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Outfit',
                                size: 9,
                                weight: 'bold'
                            },
                            color: '#3E2723'
                        }
                    }
                }
            }
        };

        revenueChart = new Chart(ctx, chartConfig);
    };

    // Initialize with default chart type 'line'
    changeChartType('line');
});
</script>
@endsection
