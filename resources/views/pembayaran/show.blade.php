@extends('layouts.app')

@section('title', 'Proses Pembayaran')
@section('page_title', 'Proses Pembayaran')



@section('content')
@php
    $simUser = null;
    if (session()->has('simulated_user_id')) {
        $simUser = \App\Models\User::find(session('simulated_user_id'));
    }
    if (!$simUser && auth()->check()) {
        $simUser = auth()->user();
    }
    $isKasir = $simUser && $simUser->role->role === 'kasir';
@endphp

@if($isKasir && !$activeShift)
    <!-- Buka Shift Baru -->
    <div class="max-w-md mx-auto my-12 bg-white rounded-3xl border border-coffee-latte shadow-xl p-8 coffee-card animate-fade-in">
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
                        class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-bold text-coffee-dark transition bg-white"
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
@else
    <div x-data="{ 
        endShiftModal: false, 
        searchQuery: '', 
        selectedCategory: 'all',
        filterMenus() {
            return {{ json_encode($menus) }}.filter(menu => {
                const matchesSearch = menu.nama_menu.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                     menu.kode_menu.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesCategory = this.selectedCategory === 'all' || menu.id_kategori == this.selectedCategory;
                return matchesSearch && matchesCategory;
            });
        },
        addByClick(kode) {
            const input = document.getElementById('barcode_input');
            input.value = kode;
            input.closest('form').submit();
        }
    }">
        <div class="max-w-full mx-auto px-2 grid grid-cols-1 lg:grid-cols-12 gap-3" x-data="paymentProcessor({{ $subtotal }}, {{ $pajakPersen }}, {{ json_encode($activePromos) }}, {{ json_encode($pendingItems) }}, {{ $meja->id_meja }})">
            
            <!-- Left Column: Menus & Filters (3/12) -->
            <div class="lg:col-span-4 space-y-3">
                <div class="bg-white rounded-xl border border-coffee-latte p-3 coffee-card shadow-sm">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input 
                            type="text" 
                            x-model="searchQuery" 
                            placeholder="Cari menu..." 
                            class="flex-1 px-3 py-1.5 rounded-lg border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"
                        >
                        
                        <select 
                            x-model="selectedCategory"
                            class="w-full sm:w-40 px-3 py-1.5 rounded-lg border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"
                        >
                            <option value="all">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id_kategori }}">{{ $cat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-coffee-latte p-2 coffee-card h-[calc(100vh-200px)] overflow-y-auto shadow-sm">
                    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 gap-2">
                        <template x-for="menu in filterMenus()" :key="menu.id_menu">
                            <div 
                                @click="addByClick(menu.kode_menu)"
                                class="p-1.5 border border-coffee-latte rounded-lg hover:bg-coffee-cream cursor-pointer transition text-center group bg-white"
                            >
                                <div class="w-full aspect-square bg-coffee-cream rounded-md overflow-hidden mb-1 border border-coffee-latte/50">
                                    <template x-if="menu.foto">
                                        <img :src="'{{ asset("") }}'.replace(/\/$/, '') + '/' + menu.foto.replace(/^\//, '')" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    </template>
                                    <template x-if="!menu.foto">
                                        <div class="w-full h-full flex items-center justify-center text-coffee-light">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    </template>
                                </div>
                                <p class="text-[9px] font-extrabold text-coffee-dark leading-tight truncate" x-text="menu.nama_menu"></p>
                                <p class="text-[9px] font-bold text-coffee-medium" x-text="'Rp ' + Number(menu.harga).toLocaleString('id-ID')"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Middle Column: Table, Barcode, Orders (4/12) -->
            <div class="lg:col-span-4 space-y-3">
                <!-- Table & Barcode Row -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-white rounded-xl border border-coffee-latte p-3 coffee-card shadow-sm flex flex-col justify-center">
                        <label class="text-[9px] font-bold text-coffee-medium uppercase mb-1">Meja</label>
                        <select 
                            onchange="window.location.href = `/pesanan/${this.value}/bayar`"
                            class="w-full px-2 py-1 rounded-lg border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"
                        >
                            @foreach($allTables as $t)
                                <option value="{{ $t->id_meja }}" {{ $t->id_meja == $meja->id_meja ? 'selected' : '' }} {{ ($t->status === 'kosong' && $t->nomor_meja != 99) ? 'disabled' : '' }}>
                                    {{ $t->nomor_meja == 99 ? 'Takeaway' : 'Meja ' . $t->nomor_meja }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-white rounded-xl border border-coffee-latte p-3 coffee-card shadow-sm">
                        <label class="text-[9px] font-bold text-coffee-medium uppercase mb-1">Scan Menu</label>
                        <form action="{{ route('pesanan.scan-barcode', $meja->id_meja) }}" method="POST" class="relative">
                            @csrf
                            <input 
                                type="text" 
                                name="barcode" 
                                id="barcode_input" 
                                placeholder="Ketik/Scan..." 
                                class="w-full px-2 py-1 rounded-lg border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 text-xs font-semibold text-coffee-dark bg-white"
                                autofocus
                                required
                            >
                        </form>
                    </div>
                </div>

                <!-- Order list -->
                <div class="bg-white rounded-xl border border-coffee-latte p-4 coffee-card h-[calc(100vh-200px)] flex flex-col shadow-sm">
                    <div class="flex items-center justify-between border-b border-coffee-latte pb-2 mb-2">
                        <h3 class="font-extrabold text-xs text-coffee-dark uppercase">Pesanan: {{ $meja->nomor_meja == 99 ? 'Takeaway' : 'Meja ' . $meja->nomor_meja }}</h3>
                        <span class="text-[9px] text-coffee-light font-bold">Items: {{ count($pendingItems) }}</span>
                    </div>

                    <div class="flex-1 divide-y divide-coffee-latte overflow-y-auto pr-1">
                        @forelse($pendingItems as $item)
                            <div class="py-2 first:pt-0 last:pb-0 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-bold text-xs text-coffee-dark truncate">{{ $item->menu->nama_menu }}</p>
                                    <p class="text-[10px] text-coffee-light font-medium">
                                        {{ $item->jumlah }}x @ Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}
                                    </p>
                                    @if($item->catatan)
                                        <p class="text-[9px] text-red-600 font-bold italic truncate">"{{ $item->catatan }}"</p>
                                    @endif
                                </div>
                                <span class="font-bold text-xs text-coffee-medium flex-shrink-0">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center text-coffee-light opacity-50 space-y-2">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                <p class="text-[10px] font-bold">Keranjang Kosong</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Payment Details (4/12) -->
            <div class="lg:col-span-4">
                <form action="{{ route('pesanan.bayar', $meja->id_meja) }}" method="POST" class="bg-white rounded-xl border border-coffee-latte p-4 coffee-card h-[calc(100vh-120px)] flex flex-col shadow-sm" id="payment-form">
                    @csrf
                    <h3 class="font-extrabold text-xs text-coffee-dark border-b border-coffee-latte pb-2 mb-3 uppercase tracking-wider">Konfirmasi Bayar</h3>
                    
                    <div class="space-y-2 mb-4 bg-coffee-cream/30 p-3 rounded-lg border border-coffee-latte">
                        <div class="flex justify-between text-[10px] font-bold text-coffee-medium">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold text-rose-600" x-show="discount > 0">
                            <span>Promo</span>
                            <span x-text="'- ' + formatRupiah(discount)"></span>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold text-coffee-medium">
                            <span>Pajak ({{ $pajakPersen }}%)</span>
                            <span x-text="formatRupiah(tax)"></span>
                        </div>
                        <div class="flex justify-between border-t border-coffee-latte pt-2 items-center">
                            <span class="text-xs font-black text-coffee-dark uppercase">Total</span>
                            <span class="text-lg font-black text-coffee-light" x-text="formatRupiah(total)"></span>
                        </div>
                    </div>

                    <div class="flex-1 space-y-4">
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer group">
                                <input type="radio" name="metode_pembayaran" value="cash" x-model="method" class="sr-only">
                                <div 
                                    class="py-3 rounded-lg border text-center font-black text-[10px] uppercase transition"
                                    :class="method === 'cash' ? 'bg-coffee-dark text-white border-coffee-dark shadow-md' : 'bg-coffee-cream border-coffee-latte text-coffee-light hover:bg-coffee-latte/30'"
                                >
                                    Tunai
                                </div>
                            </label>
                            
                            <label class="cursor-pointer group">
                                <input type="radio" name="metode_pembayaran" value="qris" x-model="method" class="sr-only">
                                <div 
                                    class="py-3 rounded-lg border text-center font-black text-[10px] uppercase transition"
                                    :class="method === 'qris' ? 'bg-coffee-dark text-white border-coffee-dark shadow-md' : 'bg-coffee-cream border-coffee-latte text-coffee-light hover:bg-coffee-latte/30'"
                                >
                                    QRIS
                                </div>
                            </label>
                        </div>

                        <div x-show="method === 'cash'" class="space-y-3 pt-2" x-transition>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-xs font-black text-coffee-light">Rp</span>
                                <input 
                                    type="number" 
                                    name="nominal_bayar" 
                                    id="nominal_bayar" 
                                    x-model.number="nominal"
                                    x-on:input="calculateChange"
                                    value="{{ $totalBayar }}"
                                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-coffee-latte focus:ring-2 focus:ring-coffee-light/30 text-sm font-black text-coffee-dark bg-white"
                                    placeholder="0"
                                >
                            </div>

                            <div class="grid grid-cols-3 gap-1.5">
                                <button type="button" @click="setNominal(50000)" class="py-1.5 bg-coffee-cream hover:bg-coffee-latte text-coffee-dark border border-coffee-latte rounded-lg text-[9px] font-black transition">50K</button>
                                <button type="button" @click="setNominal(100000)" class="py-1.5 bg-coffee-cream hover:bg-coffee-latte text-coffee-dark border border-coffee-latte rounded-lg text-[9px] font-black transition">100K</button>
                                <button type="button" @click="setNominal(200000)" class="py-1.5 bg-coffee-cream hover:bg-coffee-latte text-coffee-dark border border-coffee-latte rounded-lg text-[9px] font-black transition">200K</button>
                            </div>

                            <div class="bg-amber-50 rounded-lg p-3 border border-amber-100 flex items-center justify-between">
                                <span class="text-[10px] font-black text-coffee-medium uppercase">Kembali</span>
                                <strong class="text-sm font-black" :class="kembalian >= 0 ? 'text-emerald-700' : 'text-rose-600'" x-text="formatRupiah(kembalian)"></strong>
                            </div>
                        </div>

                        <div x-show="method === 'qris'" class="p-4 bg-blue-50 border border-blue-100 rounded-lg text-center" x-transition>
                            <div class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full mb-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            </div>
                            <p class="text-[10px] text-blue-800 font-bold leading-tight">Otomatis generate QRIS Midtrans setelah konfirmasi.</p>
                        </div>
                    </div>

                    <div class="mt-auto pt-3 border-t border-coffee-latte space-y-2">
                        <button 
                            type="button"
                            @click="confirmPayment"
                            class="w-full py-3 bg-coffee-dark text-white rounded-lg font-black text-xs uppercase hover:bg-coffee-medium transition shadow-md flex items-center justify-center space-x-2"
                            :disabled="(method === 'cash' && kembalian < 0) || loading || subtotal === 0"
                            :class="((method === 'cash' && kembalian < 0) || loading || subtotal === 0) ? 'opacity-50 cursor-not-allowed' : ''"
                        >
                            <span x-text="loading ? 'Proses...' : 'Bayar Lunas'"></span>
                        </button>
                        <a href="{{ route('pesanan') }}" class="block text-center w-full py-2 text-[10px] font-bold text-coffee-medium hover:text-coffee-dark transition underline">
                            Batal Transaksi
                        </a>
                    </div>
                </form>
            </div>

        <!-- Second Confirmation Modal -->
        <template x-teleport="body">
            <div 
                x-show="showConfirmModal" 
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                x-transition
                style="display: none;"
            >
                <div 
                    @click.away="showConfirmModal = false" 
                    class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-sm w-full space-y-6 coffee-card"
                >
                    <div class="text-center space-y-2">
                        <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-coffee-medium border border-amber-100 mx-auto">
                            <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-coffee-dark">Konfirmasi Ulang Pembayaran</h3>
                        <p class="text-xs text-coffee-light font-medium">Apakah Anda yakin ingin menyelesaikan transaksi ini?</p>
                    </div>
        
                    <div class="bg-coffee-cream/50 rounded-xl p-4 border border-coffee-latte space-y-2 text-xs text-coffee-medium font-semibold">
                        <div class="flex justify-between">
                            <span>Tipe Pesanan:</span>
                            <span class="text-coffee-dark font-bold">
                                {{ $meja->nomor_meja == 99 ? 'Takeaway' : 'Dine-in (Meja ' . $meja->nomor_meja . ')' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Metode Pembayaran:</span>
                            <span class="uppercase text-coffee-dark font-bold" x-text="method === 'cash' ? 'Uang Tunai (Cash)' : 'QRIS / Midtrans'"></span>
                        </div>
                        <div class="flex justify-between border-t border-coffee-latte/50 pt-2 font-bold text-sm text-coffee-dark">
                            <span>Total Tagihan:</span>
                            <span class="text-coffee-light font-black" x-text="formatRupiah(total)"></span>
                        </div>
                        <template x-if="method === 'cash'">
                            <div class="space-y-2 pt-2 border-t border-dashed border-coffee-latte/50">
                                <div class="flex justify-between">
                                    <span>Uang Diterima:</span>
                                    <span class="text-coffee-dark" x-text="formatRupiah(nominal)"></span>
                                </div>
                                <div class="flex justify-between font-bold text-emerald-800">
                                    <span>Uang Kembalian:</span>
                                    <span x-text="formatRupiah(kembalian)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
        
                    <div class="flex gap-3 pt-2">
                        <button 
                            type="button" 
                            @click="showConfirmModal = false" 
                            class="w-1/2 py-3 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs"
                        >
                            Batal
                        </button>
                        <button 
                            type="button" 
                            @click="executePayment" 
                            class="w-1/2 py-3 bg-coffee-dark text-white rounded-xl font-semibold hover:bg-coffee-medium transition text-xs flex items-center justify-center space-x-1"
                        >
                            <span>Ya, Konfirmasi</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
        </div>

        <!-- Close Shift Modal -->
        @if($isKasir && $activeShift)
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
            
                        @php
                            $kasAwal = session('kas_awal_' . $activeShift->id_user, 0);
                            $expectedCash = $kasAwal + $activeShift->cash_masuk;
                        @endphp
                        <div class="bg-coffee-cream rounded-xl p-4 text-xs font-semibold text-coffee-text space-y-2 border border-coffee-latte">
                            <div class="flex justify-between">
                                <span>Modal Kas Awal:</span>
                                <span>Rp {{ number_format($kasAwal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Omset Tunai Masuk:</span>
                                <span>Rp {{ number_format($activeShift->cash_masuk, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between border-t border-coffee-latte pt-2 font-bold text-sm text-coffee-dark">
                                <span>Kas Tunai Seharusnya:</span>
                                <span>Rp {{ number_format($expectedCash, 0, ',', '.') }}</span>
                            </div>
                        </div>
            
                        <form action="{{ route('shift.end') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="kas_di_tangan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kas Fisik di Tangan</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-sm font-semibold text-coffee-light">Rp</span>
                                    <input 
                                        type="number" 
                                        name="kas_di_tangan" 
                                        id="kas_di_tangan" 
                                        required 
                                        min="0" 
                                        value="{{ $expectedCash }}"
                                        class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-bold text-coffee-dark transition bg-white"
                                    >
                                </div>
                            </div>
            
                            <div>
                                <label for="note" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Catatan (Opsional)</label>
                                <textarea 
                                    name="note" 
                                    id="note" 
                                    rows="2"
                                    class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-semibold text-coffee-dark transition bg-white"
                                    placeholder="Contoh: Selisih Rp 5.000 karena pembulatan."
                                ></textarea>
                            </div>
            
                            <div class="flex gap-3 pt-2">
                                <button 
                                    type="button" 
                                    @click="endShiftModal = false" 
                                    class="w-1/2 py-3 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit" 
                                    class="w-1/2 py-3 bg-red-800 text-white rounded-xl font-semibold hover:bg-red-700 transition text-xs"
                                >
                                    Tutup Shift
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        @endif
    </div>
@endif
@endsection

@section('scripts')
<script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    function paymentProcessor(subtotal, pajakPersen, promos, items, mejaId) {
        return {
            method: 'cash',
            subtotal: subtotal,
            pajakPersen: pajakPersen,
            promos: promos,
            items: items,
            selectedPromoId: '',
            discount: 0,
            tax: 0,
            total: 0,
            nominal: 0,
            kembalian: 0,
            mejaId: mejaId,
            loading: false,
            showConfirmModal: false,
            
            init() {
                this.recalculate();
                this.nominal = this.total;
                this.calculateChange();
            },
            
            recalculate() {
                let promo = this.promos.find(p => p.id_promo == this.selectedPromoId);
                if (promo) {
                    let eligibleSubtotal = this.subtotal;
                    if (promo.menu_ids && promo.menu_ids.length > 0) {
                        eligibleSubtotal = this.items
                            .filter(item => promo.menu_ids.includes(String(item.id_menu)) || promo.menu_ids.includes(Number(item.id_menu)))
                            .reduce((sum, item) => sum + Number(item.subtotal), 0);
                    }

                    this.discount = Number(promo.nominal_potongan);
                    if (this.discount > eligibleSubtotal) {
                        this.discount = eligibleSubtotal;
                    }
                } else {
                    this.discount = 0;
                }
                
                let taxableAmount = this.subtotal - this.discount;
                this.tax = Math.round((taxableAmount * Number(this.pajakPersen)) / 100);
                this.total = taxableAmount + this.tax;
                
                this.calculateChange();
            },
            
            setNominal(amount) {
                this.nominal = amount;
                this.calculateChange();
            },
            
            calculateChange() {
                let nominalVal = Number(this.nominal);
                if (isNaN(nominalVal) || this.nominal === '') {
                    this.kembalian = -this.total;
                } else {
                    this.kembalian = nominalVal - this.total;
                }
            },
            
            formatRupiah(val) {
                if (val < 0) {
                    return '- Rp ' + Math.abs(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
                return 'Rp ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            },

            confirmPayment() {
                if (this.method === 'cash' && this.kembalian < 0) return;
                if (this.subtotal === 0) return;
                this.showConfirmModal = true;
            },

            async executePayment() {
                this.showConfirmModal = false;
                if (this.method === 'cash') {
                    if (this.kembalian < 0) return;
                    this.loading = true;
                    document.getElementById('payment-form').submit();
                    return;
                }

                if (this.method === 'qris') {
                    this.loading = true;
                    try {
                        const response = await fetch("{{ route('pesanan.bayar', $meja->id_meja) }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({
                                metode_pembayaran: "qris",
                                id_promo: this.selectedPromoId
                            })
                        });

                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            if (typeof snap === 'undefined') {
                                alert("Midtrans Snap SDK tidak berhasil dimuat. Periksa koneksi internet Anda atau konfigurasi server.");
                                this.loading = false;
                                return;
                            }
                            snap.pay(data.snap_token, {
                                onSuccess: (result) => {
                                    this.finalizeMidtrans(data.order_id);
                                },
                                onPending: (result) => {
                                    alert("Pembayaran tertunda. Harap selesaikan pembayaran Anda.");
                                    this.loading = false;
                                },
                                onError: (result) => {
                                    alert("Pembayaran gagal!");
                                    this.loading = false;
                                },
                                onClose: () => {
                                    alert("Anda menutup popup pembayaran sebelum menyelesaikannya.");
                                    this.loading = false;
                                }
                            });
                        } else {
                            alert("Gagal mendapatkan token pembayaran: " + (data.message || "Unknown error"));
                            this.loading = false;
                        }
                    } catch (error) {
                        console.error("Error:", error);
                        alert("Terjadi kesalahan sistem.");
                        this.loading = false;
                    }
                }
            },

            async finalizeMidtrans(orderId) {
                try {
                    const response = await fetch("{{ route('pembayaran.finish') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            meja_id: this.mejaId,
                            id_promo: this.selectedPromoId
                        })
                    });

                    const data = await response.json();
                    if (data.status === 'success') {
                        const finalUrl = data.redirect;
                        window.location.href = finalUrl + "?success=Pembayaran berhasil";
                    } else {
                        alert("Gagal memproses pesanan: " + data.message);
                        this.loading = false;
                    }
                } catch (error) {
                    console.error("Error:", error);
                    alert("Terjadi kesalahan saat finalisasi pesanan.");
                    this.loading = false;
                }
            }
        }
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const barcodeInput = document.getElementById('barcode_input');
        if (barcodeInput) {
            // Keep focus on the barcode input
            document.addEventListener('click', (e) => {
                const target = e.target;
                if (!['INPUT', 'SELECT', 'TEXTAREA', 'BUTTON', 'A'].includes(target.tagName) && !target.closest('a') && !target.closest('button')) {
                    barcodeInput.focus();
                }
            });
        }
    });
</script>
@endsection
