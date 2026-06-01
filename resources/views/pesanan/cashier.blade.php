@extends('layouts.app')

@section('title', 'Antrean Pesanan')
@section('page_title', 'Antrean Pesanan')

@section('content')
<div class="space-y-6">

    <!-- Filters header -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-4 flex flex-col sm:flex-row items-center justify-between gap-4 coffee-card">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-coffee-medium uppercase tracking-wider">Filter Status Masakan:</span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('pesanan', ['status' => 'semua']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $statusFilter === 'semua' ? 'bg-coffee-dark text-white shadow' : 'bg-coffee-cream hover:bg-coffee-latte/50 text-coffee-light' }}">
                Semua
            </a>
            <a href="{{ route('pesanan', ['status' => 'menunggu']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $statusFilter === 'menunggu' ? 'bg-coffee-dark text-white shadow' : 'bg-coffee-cream hover:bg-coffee-latte/50 text-coffee-light' }}">
                Menunggu
            </a>
            <a href="{{ route('pesanan', ['status' => 'dimasak']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $statusFilter === 'dimasak' ? 'bg-coffee-dark text-white shadow' : 'bg-coffee-cream hover:bg-coffee-latte/50 text-coffee-light' }}">
                Dimasak
            </a>
            <a href="{{ route('pesanan', ['status' => 'selesai']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $statusFilter === 'selesai' ? 'bg-coffee-dark text-white shadow' : 'bg-coffee-cream hover:bg-coffee-latte/50 text-coffee-light' }}">
                Selesai
            </a>
        </div>
    </div>

    <!-- Grid of Active Orders -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($activeOrders as $order)
            <div class="bg-white rounded-2xl border border-coffee-latte shadow-md overflow-hidden flex flex-col justify-between coffee-card">
                <div>
                    <!-- Order header -->
                    <div class="bg-coffee-cream border-b border-coffee-latte p-4 flex justify-between items-center">
                        <div>
                            <h3 class="font-extrabold text-coffee-dark">Meja {{ $order['meja']->nomor_meja }}</h3>
                            <span class="text-[10px] text-coffee-light font-medium block">Dipesan: {{ $order['created_at']->format('H:i') }} ({{ $order['created_at']->diffForHumans() }})</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider 
                            @if($order['status'] === 'menunggu') bg-slate-100 border-slate-200 text-slate-600
                            @elseif($order['status'] === 'dimasak') bg-amber-100 border-amber-200 text-coffee-light
                            @else bg-emerald-100 border-emerald-200 text-emerald-800
                            @endif"
                        >
                            @if($order['status'] === 'menunggu') Dapur: Menunggu
                            @elseif($order['status'] === 'dimasak') Dapur: Memasak
                            @else Dapur: Siap Saji
                            @endif
                        </span>
                    </div>

                    <!-- Items list -->
                    <div class="divide-y divide-coffee-latte p-4">
                        @foreach($order['details'] as $item)
                            <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between text-xs">
                                <div class="space-y-0.5">
                                    <div class="font-bold text-coffee-dark">
                                        {{ $item->menu->nama_menu }}
                                        <span class="text-coffee-light ml-1 font-semibold">x{{ $item->jumlah }}</span>
                                    </div>
                                    @if($item->catatan)
                                        <p class="text-[10px] text-red-600 font-semibold italic">Catatan: "{{ $item->catatan }}"</p>
                                    @endif
                                </div>
                                <span class="font-bold text-coffee-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Footer Summary / Action -->
                <div class="bg-coffee-cream/40 border-t border-coffee-latte p-4 flex items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] text-coffee-light uppercase font-bold tracking-wider block">Total Subtotal</span>
                        <strong class="text-base text-coffee-dark font-extrabold">Rp {{ number_format($order['total'], 0, ',', '.') }}</strong>
                    </div>
                    
                    @php
                        // Check if cashier has active shift to check out
                        $hasActiveShift = \App\Models\Shift::whereNull('jam_selesai')->exists();
                    @endphp

                    @if($hasActiveShift)
                        <a href="{{ route('pesanan.bayar', $order['meja']->id_meja) }}" class="px-4 py-2.5 bg-coffee-dark hover:bg-coffee-medium text-white rounded-xl text-xs font-bold transition shadow">
                            Proses Pembayaran
                        </a>
                    @else
                        <button disabled class="px-4 py-2.5 bg-gray-200 text-gray-400 rounded-xl text-xs font-bold cursor-not-allowed" title="Buka shift kasir terlebih dahulu di dashboard">
                            Shift Tutup
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-20 bg-white rounded-3xl border border-coffee-latte coffee-card">
                <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center text-coffee-medium mx-auto mb-4 border border-amber-100">
                    <svg class="w-8 h-8 text-coffee-light" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h3 class="font-bold text-coffee-dark">Antrean Bersih</h3>
                <p class="text-xs text-coffee-light font-medium mt-1">Tidak ada pesanan aktif dari meja manapun.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
