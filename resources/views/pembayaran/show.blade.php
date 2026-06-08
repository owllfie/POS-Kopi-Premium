@extends('layouts.app')

@section('title', 'Proses Pembayaran')
@section('page_title', 'Proses Pembayaran')

@section('styles')
<script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
@endsection

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
    <!-- Buka Shift Baru (Show full page start shift card instead of other options) -->
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
    <div x-data="{ endShiftModal: false }">
                <!-- Table Switcher at the top -->
        <div class="max-w-4xl mx-auto mb-6 bg-white rounded-2xl border border-coffee-latte p-4 flex flex-col sm:flex-row items-center justify-between gap-4 coffee-card">
            <div>
                <h3 class="font-extrabold text-coffee-dark text-sm">Pilih Meja Transaksi</h3>
                <p class="text-[10px] text-coffee-light font-medium mt-0.5">Pilih meja untuk melakukan input pembayaran.</p>
            </div>
            <div class="flex gap-3 w-full sm:w-auto">
                <select 
                    onchange="window.location.href = `/pesanan/${this.value}/bayar`"
                    class="w-full sm:w-64 px-4 py-2 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"
                >
                    @foreach($allTables as $t)
                        <option value="{{ $t->id_meja }}" {{ $t->id_meja == $meja->id_meja ? 'selected' : '' }} {{ ($t->status === 'kosong' && $t->nomor_meja != 99) ? 'disabled' : '' }}>
                            {{ $t->nomor_meja == 99 ? 'Takeaway' : 'Meja ' . $t->nomor_meja }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="paymentProcessor({{ $subtotal }}, {{ $pajakPersen }}, {{ json_encode($activePromos) }}, {{ json_encode($pendingItems) }}, {{ $meja->id_meja }})">

    <!-- Order Items Summary Column -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Barcode Scanner Card -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card">
            <h4 class="text-xs font-bold text-coffee-medium uppercase tracking-wider mb-3">Scan Barcode Menu</h4>
            <form action="{{ route('pesanan.scan-barcode', $meja->id_meja) }}" method="POST" class="relative flex items-center">
                @csrf
                <div class="relative w-full">
                    <span class="absolute left-4 top-3 text-coffee-light">
                        <svg class="w-5 h-5 text-coffee-medium" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5v14M7 5v14M11 5v14M15 5v14M19 5v14M21 5v14M3 5h18M3 19h18"/></svg>
                    </span>
                    <input 
                        type="text" 
                        name="barcode" 
                        id="barcode_input" 
                        placeholder="Scan atau ketik barcode item..." 
                        class="w-full pl-12 pr-24 py-2.5 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-semibold text-coffee-dark transition bg-white"
                        autofocus
                        required
                    >
                    <button type="submit" class="absolute right-2 top-1.5 px-4 py-1.5 bg-coffee-dark text-white rounded-lg text-xs font-bold hover:bg-coffee-medium transition shadow-sm cursor-pointer">
                        Tambah
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4">
            <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                <h3 class="font-extrabold text-coffee-dark">Rincian Belanja {{ $meja->nomor_meja == 99 ? 'Takeaway' : 'Meja ' . $meja->nomor_meja }}</h3>
                <span class="text-xs text-coffee-light font-bold">POS Kopi Premium</span>
            </div>

            <!-- Items -->
            <div class="divide-y divide-coffee-latte">
                @forelse($pendingItems as $item)
                    <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between text-sm">
                        <div>
                            <p class="font-bold text-coffee-dark">{{ $item->menu->nama_menu }}</p>
                            <p class="text-xs text-coffee-light font-medium">
                                Rp {{ number_format($item->harga_satuan, 0, ',', '.') }} x {{ $item->jumlah }}
                                @if($item->catatan)
                                    <span class="text-red-600 block text-[10px] font-semibold italic">Catatan: "{{ $item->catatan }}"</span>
                                @endif
                            </p>
                        </div>
                        <span class="font-bold text-coffee-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <div class="py-6 text-center text-coffee-light font-medium text-xs">
                        Belum ada item belanja. Silakan scan barcode di atas.
                    </div>
                @endforelse
            </div>

            <!-- Totals calculation -->
            <div class="border-t border-coffee-latte pt-4 space-y-2.5 text-xs font-semibold text-coffee-medium">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-rose-600" x-show="discount > 0" x-transition>
                    <span>Diskon Promo:</span>
                    <span x-text="'- ' + formatRupiah(discount)"></span>
                </div>
                <div class="flex justify-between">
                    <span>Pajak ({{ $pajakPersen }}%):</span>
                    <span x-text="formatRupiah(tax)"></span>
                </div>
                <div class="flex justify-between border-t border-coffee-latte pt-2.5 font-bold text-sm text-coffee-dark">
                    <span>Total Bayar:</span>
                    <span class="text-base text-coffee-light font-black" x-text="formatRupiah(total)"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Actions Column -->
    <div class="space-y-6">
        <form action="{{ route('pesanan.bayar', $meja->id_meja) }}" method="POST" class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-5" id="payment-form">
            @csrf
            <h3 class="font-extrabold text-coffee-dark border-b border-coffee-latte pb-3">Konfirmasi Transaksi</h3>
                        
            <h4 class="font-bold text-coffee-medium text-xs uppercase tracking-wider pt-2 border-t border-coffee-latte/50">Metode Pembayaran</h4>
            
            <!-- Method Selectors -->
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="metode_pembayaran" value="cash" x-model="method" class="sr-only">
                    <div 
                        class="p-4 rounded-xl border text-center font-bold text-xs transition duration-150"
                        :class="method === 'cash' ? 'bg-coffee-dark text-white border-coffee-dark shadow' : 'bg-coffee-cream border-coffee-latte text-coffee-light hover:bg-coffee-latte/50'"
                    >
                        Uang Tunai (CASH)
                    </div>
                </label>
                
                <label class="cursor-pointer">
                    <input type="radio" name="metode_pembayaran" value="qris" x-model="method" class="sr-only">
                    <div 
                        class="p-4 rounded-xl border text-center font-bold text-xs transition duration-150"
                        :class="method === 'qris' ? 'bg-coffee-dark text-white border-coffee-dark shadow' : 'bg-coffee-cream border-coffee-latte text-coffee-light hover:bg-coffee-latte/50'"
                    >
                        QRIS Digital
                    </div>
                </label>
            </div>

            <!-- CASH Calculation Area -->
            <div x-show="method === 'cash'" class="space-y-4" x-transition>
                <div>
                    <label for="nominal_bayar" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Uang Diterima (Nominal)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-sm font-semibold text-coffee-light">Rp</span>
                        <input 
                            type="number" 
                            name="nominal_bayar" 
                            id="nominal_bayar" 
                            x-model.number="nominal"
                            x-on:input="calculateChange"
                            class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-bold text-coffee-dark transition"
                            placeholder="Contoh: 100000"
                        >
                    </div>
                </div>

                <!-- Quick amount shortcuts -->
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" @click="setNominal(50000)" class="py-1.5 bg-coffee-cream hover:bg-coffee-latte text-coffee-text border border-coffee-latte rounded-lg text-[10px] font-bold transition">50.000</button>
                    <button type="button" @click="setNominal(100000)" class="py-1.5 bg-coffee-cream hover:bg-coffee-latte text-coffee-text border border-coffee-latte rounded-lg text-[10px] font-bold transition">100.000</button>
                    <button type="button" @click="setNominal(200000)" class="py-1.5 bg-coffee-cream hover:bg-coffee-latte text-coffee-text border border-coffee-latte rounded-lg text-[10px] font-bold transition">200.000</button>
                </div>

                <!-- Change Calculation Result -->
                <div class="bg-amber-50 rounded-xl p-4 border border-amber-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-coffee-medium">Uang Kembalian:</span>
                    <strong class="text-base font-extrabold" :class="change >= 0 ? 'text-emerald-800' : 'text-rose-600'" x-text="formatRupiah(change)"></strong>
                </div>
            </div>

            <!-- QRIS Info Area (Removed) -->
            <div x-show="method === 'qris'" class="p-4 bg-blue-50 border border-blue-100 rounded-2xl text-center" x-transition>
                <p class="text-xs text-blue-800 font-medium">Pembayaran akan diproses melalui jendela aman Midtrans setelah Anda menekan konfirmasi.</p>
            </div>

            <!-- Submit buttons -->
            <div class="space-y-2 pt-2 border-t border-coffee-latte">
                <button 
                    type="button"
                    @click="confirmPayment"
                    class="w-full py-3.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition shadow-md cursor-pointer flex items-center justify-center space-x-2"
                    :disabled="(method === 'cash' && change < 0) || loading || subtotal === 0"
                    :class="((method === 'cash' && change < 0) || loading || subtotal === 0) ? 'opacity-50 cursor-not-allowed' : ''"
                >
                    <template x-if="loading">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="loading ? 'Memproses...' : 'Konfirmasi Pembayaran Lunas'"></span>
                </button>
                <a href="{{ route('pesanan') }}" class="block text-center w-full py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <!-- Second Confirmation Modal (Confirm Payment Dialog) -->
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
                    <p class="text-xs text-coffee-light font-medium">Apakah Anda yakin ingin menyelesaikan transaksi ini? Harap periksa kembali detail pembayaran berikut:</p>
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
                                <span x-text="formatRupiah(change)"></span>
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
@endsection

@section('scripts')
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
            change: 0,
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

                    if (promo.tipe_potongan === 'persen') {
                        this.discount = Math.round((eligibleSubtotal * promo.nominal_potongan) / 100);
                    } else {
                        this.discount = promo.nominal_potongan;
                    }
                    if (this.discount > eligibleSubtotal) {
                        this.discount = eligibleSubtotal;
                    }
                } else {
                    this.discount = 0;
                }
                
                let taxableAmount = this.subtotal - this.discount;
                this.tax = Math.round((taxableAmount * this.pajakPersen) / 100);
                this.total = taxableAmount + this.tax;
                
                this.calculateChange();
            },
            
            setNominal(amount) {
                this.nominal = amount;
                this.calculateChange();
            },
            
            calculateChange() {
                if (this.nominal === '') {
                    this.change = -this.total;
                } else {
                    this.change = this.nominal - this.total;
                }
            },
            
            formatRupiah(val) {
                if (val < 0) {
                    return '- Rp ' + Math.abs(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
                return 'Rp ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            },

            confirmPayment() {
                if (this.method === 'cash' && this.change < 0) return;
                if (this.subtotal === 0) return;
                this.showConfirmModal = true;
            },

            async executePayment() {
                this.showConfirmModal = false;
                if (this.method === 'cash') {
                    if (this.change < 0) return;
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



        <!-- Close Shift Modal (End Shift Dialog) -->
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
            
                        <!-- Precalculates shift details -->
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
                                        class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-bold text-coffee-dark transition bg-white"
                                    >
                                </div>
                            </div>
            
                            <div>
                                <label for="note" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Catatan Perbedaan Kas (Opsional)</label>
                                <textarea 
                                    name="note" 
                                    id="note" 
                                    rows="2"
                                    class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-semibold text-coffee-dark transition bg-white"
                                    placeholder="Contoh: Selisih Rp 5.000 karena uang kembalian dibulatkan."
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
