@extends('layouts.app')

@section('title', 'Antrean Dapur')
@section('page_title', 'Antrean Dapur (Chef Queue)')

@section('styles')
    <!-- Auto refresh every 30 seconds -->
    <meta http-equiv="refresh" content="30">
@endsection

@section('content')
<div class="space-y-6">

    <!-- Auto-refresh Indicator -->
    <div class="flex items-center justify-between bg-amber-50 border border-amber-100 p-4 rounded-xl text-xs font-semibold">
        <div class="flex items-center gap-2">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Halaman auto-refresh setiap 30 detik untuk memantau pesanan masuk.</span>
        </div>
        <a href="{{ route('pesanan') }}" class="px-3 py-1.5 bg-coffee-dark text-white rounded-lg hover:bg-coffee-medium transition">
            Segarkan Manual (Refresh)
        </a>
    </div>

    <!-- Active Orders Grouped by Table -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($kitchenItems as $mejaId => $details)
            @php $meja = $details->first()->mejaTemp; @endphp
            <div class="bg-white rounded-2xl border border-coffee-latte shadow-md overflow-hidden coffee-card">
                <!-- Header of Table -->
                <div class="bg-coffee-dark text-white p-4 flex justify-between items-center">
                    <h3 class="font-bold text-sm">Meja {{ $meja ? $meja->nomor_meja : '?' }}</h3>
                    <span class="text-[10px] bg-coffee-medium text-coffee-gold px-2 py-0.5 rounded font-extrabold uppercase">
                        {{ $details->count() }} Item Masakan
                    </span>
                </div>
                
                <!-- Items list -->
                <div class="divide-y divide-coffee-latte p-4">
                    @foreach($details as $item)
                        <div class="py-3.5 first:pt-0 last:pb-0 flex items-start justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-coffee-dark">{{ $item->menu->nama_menu }}</span>
                                    <span class="text-xs font-extrabold text-coffee-light">x{{ $item->jumlah }}</span>
                                </div>
                                @if($item->catatan)
                                    <p class="text-[11px] bg-red-50 border border-red-100 text-red-700 px-2 py-1 rounded-lg inline-block font-semibold">
                                        Catatan: {{ $item->catatan }}
                                    </p>
                                @endif
                                <p class="text-[10px] text-coffee-light font-medium">Dipesan: {{ $item->created_at->diffForHumans() }}</p>
                            </div>

                            <!-- Actions based on status -->
                            <div>
                                @if($item->status === 'menunggu')
                                    <form action="{{ route('pesanan.updateStatus', $item->id_detail) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="dimasak">
                                        <button type="submit" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition shadow cursor-pointer">
                                            Mulai Masak
                                        </button>
                                    </form>
                                @elseif($item->status === 'dimasak')
                                    <form action="{{ route('pesanan.updateStatus', $item->id_detail) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="selesai">
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow cursor-pointer">
                                            Selesai
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-20 bg-white rounded-3xl border border-coffee-latte coffee-card">
                <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center text-coffee-medium mx-auto mb-4 border border-amber-100">
                    <svg class="w-8 h-8 text-coffee-light" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h3 class="font-bold text-coffee-dark">Dapur Bersih!</h3>
                <p class="text-xs text-coffee-light font-medium mt-1">Tidak ada hidangan yang menunggu atau sedang dimasak.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
