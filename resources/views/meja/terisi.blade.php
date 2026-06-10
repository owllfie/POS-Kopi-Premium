@extends('layouts.app')

@section('title', 'Meja yang Terisi')
@section('page_title', 'Status Keterisian Meja')

@section('content')
<div class="space-y-8">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Total Meja Card -->
        <div class="bg-white border border-coffee-latte rounded-3xl p-6 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md">
            <div>
                <p class="text-xs font-bold text-coffee-medium uppercase tracking-wider">Total Meja</p>
                <h3 class="text-3xl font-extrabold text-coffee-dark mt-1">{{ $mejas->count() }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-coffee-cream border border-coffee-latte flex items-center justify-center text-coffee-medium shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                </svg>
            </div>
        </div>

        <!-- Meja Kosong (White) -->
        <div class="bg-white border-2 border-dashed border-coffee-light/20 rounded-3xl p-6 shadow-sm flex items-center justify-between transition-all duration-300 hover:shadow-md">
            <div>
                <p class="text-xs font-bold text-coffee-light uppercase tracking-wider">Meja Kosong (Putih)</p>
                <h3 class="text-3xl font-extrabold text-coffee-medium mt-1">{{ $mejas->where('status', 'kosong')->count() }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white border border-coffee-light/30 flex items-center justify-center text-coffee-light shadow-sm">
                <div class="w-5 h-5 rounded bg-white border-2 border-coffee-medium/40"></div>
            </div>
        </div>

        <!-- Meja Terisi (Chocolate) -->
        <div class="bg-coffee-dark rounded-3xl p-6 shadow-md flex items-center justify-between transition-all duration-300 hover:shadow-lg">
            <div>
                <p class="text-xs font-bold text-coffee-gold uppercase tracking-wider">Meja Terisi (Cokelat)</p>
                <h3 class="text-3xl font-extrabold text-white mt-1">{{ $mejas->where('status', 'terisi')->count() }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-coffee-medium border border-coffee-medium/55 flex items-center justify-center text-coffee-gold shadow-md">
                <div class="w-5 h-5 rounded bg-coffee-medium border-2 border-coffee-gold"></div>
            </div>
        </div>
    </div>

    <!-- Live Status Badge Info -->
    <div class="bg-coffee-latte/50 border border-coffee-latte rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs font-semibold text-coffee-medium">
        <div class="flex items-center gap-2">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-coffee-light opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-coffee-medium"></span>
            </span>
            <span>Menampilkan denah tata letak meja cafe secara real-time.</span>
        </div>
        <div class="flex gap-4">
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded bg-white border border-coffee-medium/30 inline-block"></span>
                <span>Kosong (White)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded bg-coffee-dark inline-block"></span>
                <span>Terisi (Chocolate)</span>
            </div>
        </div>
    </div>

    <!-- Single Cafe Map Card containing the 3x3 Grid Layout -->
    <div class="bg-white border border-coffee-latte rounded-3xl p-8 shadow-sm coffee-card">
        <!-- Card Header -->
        <div class="border-b border-coffee-latte pb-4 mb-6 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-coffee-dark text-base">Denah Tata Letak Meja</h3>
            </div>
            <span class="px-3 py-1 bg-coffee-cream border border-coffee-latte rounded-xl text-[10px] font-extrabold text-coffee-medium tracking-wide uppercase">
                Floor Map
            </span>
        </div>

        <!-- Layout Grid Container -->
        <div class="flex justify-center py-6">
            <div class="grid grid-cols-3 gap-8 sm:gap-12 md:gap-16 max-w-3xl w-full p-8 sm:p-12 bg-coffee-cream/40 rounded-3xl border border-coffee-latte/60 shadow-inner relative">
                @forelse($mejas as $m)
                    @php
                        $isKosong = $m->status === 'kosong';
                    @endphp
                    <!-- Table Grid Cell -->
                    <div class="flex flex-col items-center justify-center relative group">
                        
                        <!-- Visual representation of table + chairs -->
                        <div class="relative w-24 h-24 sm:w-28 sm:h-28 flex items-center justify-center">
                            
                            <!-- Chairs around the table -->
                            <!-- Top Chair -->
                            <div class="absolute -top-1 w-6 h-4 rounded-t-lg transition-all duration-300
                                {{ $isKosong ? 'bg-white border border-coffee-light/40' : 'bg-coffee-dark border border-coffee-medium/40' }}"></div>
                            <!-- Bottom Chair -->
                            <div class="absolute -bottom-1 w-6 h-4 rounded-b-lg transition-all duration-300
                                {{ $isKosong ? 'bg-white border border-coffee-light/40' : 'bg-coffee-dark border border-coffee-medium/40' }}"></div>
                            <!-- Left Chair -->
                            <div class="absolute -left-1 w-4 h-6 rounded-l-lg transition-all duration-300
                                {{ $isKosong ? 'bg-white border border-coffee-light/40' : 'bg-coffee-dark border border-coffee-medium/40' }}"></div>
                            <!-- Right Chair -->
                            <div class="absolute -right-1 w-4 h-6 rounded-r-lg transition-all duration-300
                                {{ $isKosong ? 'bg-white border border-coffee-light/40' : 'bg-coffee-dark border border-coffee-medium/40' }}"></div>

                            <!-- Center Circular Table Top -->
                            <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full border-2 shadow-sm transition-all duration-300 flex flex-col items-center justify-center font-extrabold text-base relative z-10
                                {{ $isKosong 
                                    ? 'bg-white border-coffee-medium/40 text-coffee-dark group-hover:border-coffee-medium group-hover:scale-105' 
                                    : 'bg-coffee-dark border-coffee-medium text-white group-hover:scale-105 shadow-md' }}">
                                <span class="text-xs font-semibold {{ $isKosong ? 'text-coffee-medium' : 'text-coffee-gold' }}">No.</span>
                                <span class="text-lg leading-tight">{{ $m->nomor_meja }}</span>
                            </div>
                        </div>

                        <!-- Status Badge Label under the table -->
                        <div class="mt-2 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[9px] font-bold uppercase tracking-wider border transition-colors duration-300
                                {{ $isKosong 
                                    ? 'bg-emerald-50 border-emerald-100 text-emerald-700' 
                                    : 'bg-coffee-cream border-coffee-light/20 text-coffee-medium' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isKosong ? 'bg-emerald-500 animate-pulse' : 'bg-coffee-medium' }}"></span>
                                {{ $isKosong ? 'Kosong' : 'Terisi' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 text-coffee-light">
                        Tidak ada data meja.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
