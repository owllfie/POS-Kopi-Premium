@extends('layouts.app')

@section('title', 'Mulai Shift Kasir')
@section('page_title', 'Mulai Shift Kerja')

@section('content')
<div class="max-w-md mx-auto my-12 bg-white rounded-3xl border border-coffee-latte shadow-xl p-8 coffee-card">
    <div class="text-center mb-6">
        <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center text-coffee-medium border border-amber-100 mx-auto mb-4">
            <svg class="w-8 h-8 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="text-xl font-bold text-coffee-dark">Buka Shift Baru</h3>
        <p class="text-xs text-coffee-light font-medium mt-1">Anda harus menginput modal kas awal sebelum dapat memproses transaksi.</p>
    </div>

    <form action="{{ route('shift.start') }}" method="POST" class="space-y-5">
        @csrf
        <div>
            <label for="kas_awal" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Modal Kas Awal (Rupiah)</label>
            <div class="relative">
                <span class="absolute left-4 top-3.5 text-sm font-semibold text-coffee-light">Rp</span>
                <input 
                    type="number" 
                    name="kas_awal" 
                    id="kas_awal" 
                    required 
                    min="0" 
                    value="100000"
                    class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-bold text-coffee-dark transition"
                    placeholder="Masukkan jumlah kas awal"
                >
            </div>
            <span class="text-[10px] text-coffee-light font-medium block mt-1.5">* Uang cash pecahan kecil untuk kembalian pelanggan.</span>
        </div>

        <button 
            type="submit" 
            class="w-full py-4 px-4 bg-coffee-dark text-white rounded-xl font-semibold hover:bg-coffee-medium transition duration-200 shadow-md shadow-coffee-medium/10 hover:shadow-lg cursor-pointer"
        >
            Mulai Shift Kerja
        </button>
    </form>
</div>
@endsection
