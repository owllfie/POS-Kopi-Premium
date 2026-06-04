@extends('layouts.app')

@section('title', 'Proses Pembayaran')
@section('page_title', 'Proses Pembayaran')

@section('styles')
<script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
@endsection

@section('content')
<div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="paymentProcessor({{ $subtotal }}, {{ $pajakPersen }}, {{ json_encode($activePromos) }}, {{ json_encode($pendingItems) }}, {{ $meja->id_meja }})">

    <!-- Order Items Summary Column -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4">
            <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                <h3 class="font-extrabold text-coffee-dark">Rincian Belanja Meja {{ $meja->nomor_meja }}</h3>
                <span class="text-xs text-coffee-light font-bold">POS Kopi Premium</span>
            </div>

            <!-- Items -->
            <div class="divide-y divide-coffee-latte">
                @foreach($pendingItems as $item)
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
                @endforeach
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
            
            <!-- Promo Selection Dropdown -->
            <div class="space-y-1.5">
                <label for="promo_select" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider">Pilih Promo</label>
                <select 
                    name="id_promo" 
                    id="promo_select" 
                    x-model="selectedPromoId" 
                    @change="recalculate"
                    class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"
                >
                    <option value="">-- Tanpa Promo --</option>
                    @foreach($activePromos as $promo)
                        <option value="{{ $promo->id_promo }}">
                            {{ $promo->nama_promo }} 
                            ({{ $promo->tipe_potongan === 'persen' ? $promo->nominal_potongan . '%' : 'Rp ' . number_format($promo->nominal_potongan, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <input type="hidden" name="diskon" :value="discount">
            
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
                    @click="submitPayment"
                    class="w-full py-3.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition shadow-md cursor-pointer flex items-center justify-center space-x-2"
                    :disabled="(method === 'cash' && change < 0) || loading"
                    :class="((method === 'cash' && change < 0) || loading) ? 'opacity-50 cursor-not-allowed' : ''"
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

            async submitPayment() {
                if (this.method === 'cash') {
                    if (this.change < 0) return;
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
@endsection
