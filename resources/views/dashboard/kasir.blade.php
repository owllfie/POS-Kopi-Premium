@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('page_title', 'Dashboard Kasir')

@section('content')
<div class="space-y-6" x-data="{ endShiftModal: false }">

    <!-- Active Shift Banner -->
    <div class="bg-amber-950 text-amber-50 rounded-2xl p-6 border border-amber-900 shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-amber-900/40 blur-xl"></div>
        <div class="z-10">
            <span class="text-[10px] uppercase font-bold text-coffee-gold bg-amber-900 px-2.5 py-1 rounded-full border border-amber-800">Shift Aktif</span>
            <h2 class="text-xl font-bold mt-2 text-white">Petugas: {{ $activeShift->user->username }}</h2>
            <p class="text-xs text-amber-200 mt-1">Mulai Kerja: <strong>{{ $activeShift->jam_mulai->format('d M Y - H:i') }}</strong> ({{ $activeShift->jam_mulai->diffForHumans() }})</p>
        </div>
        <div class="z-10 flex gap-3">
            <a href="{{ route('pesanan') }}" class="px-4 py-2.5 bg-coffee-gold text-coffee-dark rounded-xl font-bold text-sm hover:bg-yellow-500 hover:shadow-md transition">
                Proses Antrean Pesanan
            </a>
            <button @click="endShiftModal = true" class="px-4 py-2.5 bg-red-800 text-white rounded-xl font-bold text-sm hover:bg-red-700 transition cursor-pointer">
                Tutup Shift Kerja
            </button>
        </div>
    </div>

    <!-- Live Performance Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Transactions -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Total Transaksi</span>
                <h3 class="text-2xl font-bold text-coffee-dark">{{ $totalTransaksi }} Transaksi</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Total Omset</span>
                <h3 class="text-2xl font-bold text-coffee-dark">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Cash Sales -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Omset Tunai (CASH)</span>
                <h3 class="text-2xl font-bold text-coffee-dark">Rp {{ number_format($cashMasuk, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <!-- QRIS Sales -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 flex items-center justify-between coffee-card">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-coffee-light uppercase tracking-wider">Omset Non-Tunai (QRIS)</span>
                <h3 class="text-2xl font-bold text-coffee-dark">Rp {{ number_format($qrisMasuk, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-coffee-medium border border-amber-100">
                <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 8h16M4 16h16M4 20h16"/></svg>
            </div>
        </div>
    </div>

    <!-- Live Cash Box Expected Info -->
    @php
        $kasAwal = session('kas_awal_' . $activeShift->id_user, 0);
        $expectedCash = $kasAwal + $cashMasuk;
    @endphp
    <div class="bg-amber-50 rounded-2xl p-6 border border-amber-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-coffee-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h4 class="font-bold text-coffee-dark text-sm">Status Laci Uang (Live Cash Drawer Check)</h4>
                <p class="text-xs text-coffee-light font-medium mt-0.5">Modal Kas Awal: <strong>Rp {{ number_format($kasAwal, 0, ',', '.') }}</strong> | Cash Penjualan: <strong>Rp {{ number_format($cashMasuk, 0, ',', '.') }}</strong></p>
            </div>
        </div>
        <div class="text-right">
            <span class="text-xs text-coffee-light font-medium block">Estimasi Kas Tunai Seharusnya:</span>
            <strong class="text-lg text-coffee-dark font-extrabold">Rp {{ number_format($expectedCash, 0, ',', '.') }}</strong>
        </div>
    </div>

    <!-- Recent Shift Orders processed -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4">
        <h4 class="font-bold text-coffee-dark">Pesanan Terakhir Diproses (Shift Ini)</h4>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                        <th class="pb-3">Struk</th>
                        <th class="pb-3">Meja</th>
                        <th class="pb-3">Item Belanja</th>
                        <th class="pb-3">Metode</th>
                        <th class="pb-3">Total Bayar</th>
                        <th class="pb-3">Waktu Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                    @forelse($recentOrders as $tx)
                        <tr>
                            <td class="py-3.5 font-bold text-xs tracking-wide text-coffee-light">{{ $tx->kode_struk }}</td>
                            <td class="py-3.5">Meja {{ $tx->meja->nomor_meja }}</td>
                            <td class="py-3.5 text-xs text-coffee-medium">
                                @foreach($tx->details as $d)
                                    <div class="truncate max-w-xs">{{ $d->menu->nama_menu }} (x{{ $d->jumlah }})</div>
                                @endforeach
                            </td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $tx->metode_pembayaran === 'cash' ? 'bg-amber-50 border border-amber-100 text-coffee-light' : 'bg-blue-50 border border-blue-100 text-blue-600' }}">
                                    {{ $tx->metode_pembayaran }}
                                </span>
                            </td>
                            <td class="py-3.5 font-bold">Rp {{ number_format($tx->total_bayar, 0, ',', '.') }}</td>
                            <td class="py-3.5 text-xs text-coffee-light font-medium">{{ $tx->created_at->format('H:i') }} ({{ $tx->created_at->diffForHumans() }})</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-coffee-light font-medium">Belum ada transaksi di shift ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Close Shift Modal (End Shift Dialog) -->
    <template x-teleport="body">
        <div 
            x-show="endShiftModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition
            style="display: none;"
        >
            <div 
                @click.away="endShiftModal = false" 
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-8 max-w-md w-full space-y-6 coffee-card"
            >
                <div class="text-center">
                    <h3 class="text-lg font-bold text-coffee-dark">Konfirmasi Tutup Shift</h3>
                    <p class="text-xs text-coffee-light font-medium mt-1">Pastikan laci kas sudah dihitung fisiknya untuk menghitung selisih pembukuan.</p>
                </div>
    
                <!-- Precalculates shift details -->
                <div class="bg-coffee-cream rounded-xl p-4 text-xs font-semibold text-coffee-text space-y-2 border border-coffee-latte">
                    <div class="flex justify-between">
                        <span>Modal Kas Awal:</span>
                        <span>Rp {{ number_format($kasAwal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Omset Tunai Masuk:</span>
                        <span>Rp {{ number_format($cashMasuk, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-coffee-latte pt-2 font-bold text-sm text-coffee-dark">
                        <span>Kas Tunai Seharusnya:</span>
                        <span>Rp {{ number_format($expectedCash, 0, ',', '.') }}</span>
                    </div>
                </div>
    
                <form action="{{ route('shift.end') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="kas_di_tangan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kas Fisik di Tangan (Dihitung Manual)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-sm font-semibold text-coffee-light">Rp</span>
                            <input 
                                type="number" 
                                name="kas_di_tangan" 
                                id="kas_di_tangan" 
                                required 
                                min="0" 
                                value="{{ $expectedCash }}"
                                class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-bold text-coffee-dark transition"
                            >
                        </div>
                    </div>
    
                    <div>
                        <label for="note" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Catatan Perbedaan Kas (Opsional)</label>
                        <textarea 
                            name="note" 
                            id="note" 
                            rows="2"
                            class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-semibold text-coffee-dark transition"
                            placeholder="Contoh: Selisih Rp 5.000 karena uang kembalian dibulatkan."
                        ></textarea>
                    </div>
    
                    <div class="flex gap-3 pt-2">
                        <button 
                            type="button" 
                            @click="endShiftModal = false" 
                            class="w-1/2 py-3 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="w-1/2 py-3 bg-red-800 text-white rounded-xl font-semibold hover:bg-red-700 transition"
                        >
                            Tutup Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
