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
        <!-- Chart -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card lg:col-span-2 space-y-4" x-data="{ chartType: 'line' }">
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-coffee-dark">Tren Pendapatan (7 Hari Terakhir)</h4>
                
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
                    label: 'Pendapatan',
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

