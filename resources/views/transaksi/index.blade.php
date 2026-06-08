@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page_title', 'Riwayat Transaksi')

@section('content')
<div class="space-y-6" x-data="transactionManager()">

    <!-- Search & Filter Options -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4">
        <form action="{{ route('transaksi') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <input type="hidden" name="trash" value="{{ $viewTrash ? '1' : '0' }}">
            
            <div>
                <label for="search" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Cari Struk / No. Meja</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    value="{{ $search }}"
                    placeholder="Contoh: STR-20260101-001"
                    class="w-full px-4 py-2 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"
                >
            </div>

            <div>
                <label for="kasir_id" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kasir</label>
                <select name="kasir_id" id="kasir_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    <option value="semua" {{ $cashierId === 'semua' ? 'selected' : '' }}>Semua Kasir</option>
                    @foreach($cashiers as $kasir)
                        <option value="{{ $kasir->id_user }}" {{ $cashierId == $kasir->id_user ? 'selected' : '' }}>{{ $kasir->username }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="metode" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Metode</label>
                <select name="metode" id="metode" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    <option value="semua" {{ $paymentMethod === 'semua' ? 'selected' : '' }}>Semua</option>
                    <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>CASH</option>
                    <option value="qris" {{ $paymentMethod === 'qris' ? 'selected' : '' }}>QRIS</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow cursor-pointer">
                    Cari
                </button>
                <a href="{{ route('transaksi') }}" class="w-1/2 text-center py-2.5 border border-coffee-light text-coffee-dark rounded-xl text-xs font-bold hover:bg-coffee-latte transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Active vs Trash Tabs -->
    @php
        $simUser = null;
        if (session()->has('simulated_user_id')) {
            $simUser = \App\Models\User::find(session('simulated_user_id'));
        }
        if (!$simUser && auth()->check()) {
            $simUser = auth()->user();
        }
        $isAdminOrSA = $simUser && ($simUser->role->role === 'admin' || $simUser->role->role === 'superadmin');
    @endphp
    
    @if($isAdminOrSA)
        <div class="flex border-b border-coffee-latte">
            <a href="{{ route('transaksi', ['trash' => '0']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ !$viewTrash ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Struk Aktif
            </a>
            <a href="{{ route('transaksi', ['trash' => '1']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $viewTrash ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Tong Sampah (Trash)
            </a>
        </div>
    @endif

    <!-- Transactions List Card -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                        <th class="pb-3">Struk</th>
                        <th class="pb-3">Meja</th>
                        <th class="pb-3">Kasir</th>
                        <th class="pb-3">Metode</th>
                        <th class="pb-3">Item Belanja</th>
                        <th class="pb-3">Subtotal</th>
                        <th class="pb-3">Total Bayar</th>
                        <th class="pb-3">Waktu</th>
                        <th class="pb-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                    @forelse($transactions as $tx)
                        <!-- Row Click details viewer -->
                        <tr class="hover:bg-coffee-latte/20 transition">
                            <td class="py-3.5 font-bold text-xs tracking-wide text-coffee-light">{{ $tx->kode_struk }}</td>
                            <td class="py-3.5">{{ $tx->meja->nomor_meja == 99 ? 'Takeaway' : 'Meja ' . $tx->meja->nomor_meja }}</td>
                            <td class="py-3.5">{{ $tx->user ? $tx->user->username : 'System' }}</td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $tx->metode_pembayaran === 'cash' ? 'bg-amber-50 border border-amber-100 text-coffee-light' : 'bg-blue-50 border border-blue-100 text-blue-600' }}">
                                    {{ $tx->metode_pembayaran }}
                                </span>
                            </td>
                            <td class="py-3.5 text-xs text-coffee-medium truncate max-w-[150px]">
                                {{ $tx->details->count() }} item
                            </td>
                            <td class="py-3.5">Rp. {{ number_format($tx->total_harga, 0, ',', '.') }}</td>
                            <td class="py-3.5 font-bold">Rp. {{ number_format($tx->total_bayar, 0, ',', '.') }}</td>
                            <td class="py-3.5 text-xs text-coffee-light font-medium">{{ $tx->created_at->format('d/m H:i') }}</td>
                            <td class="py-3.5 text-right">
                                <div class="flex justify-end gap-1.5" onclick="event.stopPropagation()">
                                    <button @click="viewDetails({{ json_encode($tx) }})" class="p-1.5 rounded-lg hover:bg-coffee-latte text-coffee-medium hover:text-coffee-dark transition cursor-pointer" title="Detail Struk">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    @if(!$viewTrash)
                                        @if($isAdminOrSA)
                                            <form action="{{ route('transaksi.delete', $tx->id_pesanan) }}" method="POST" onsubmit="return confirm('Pindahkan struk ini ke Trash?')">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus Struk">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <!-- Trash Action Buttons -->
                                        <form action="{{ route('transaksi.restore', $tx->id_pesanan) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 hover:text-emerald-700 transition cursor-pointer" title="Kembalikan Struk">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('transaksi.forceDelete', $tx->id_pesanan) }}" method="POST" onsubmit="return confirm('Hapus PERMANEN struk ini? Tindakan tidak dapat dibatalkan.')">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-red-100 text-red-600 hover:text-red-700 transition cursor-pointer" title="Hapus Permanen">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-coffee-light font-medium">Tidak ada riwayat transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Links -->
    <div class="mt-6 no-print">
        {{ $transactions->links() }}
    </div>

    <!-- Transaction Detail Modal Dialog -->
    <template x-teleport="body">
        <div 
            x-show="modalOpen" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition
            style="display: none;"
        >
            <div 
                @click.away="modalOpen = false" 
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-md w-full space-y-4 coffee-card"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark text-sm" x-text="`Struk ${activeTx.kode_struk}`"></h3>
                    <button @click="modalOpen = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>
    
                <!-- Receipt Detail body -->
                <div class="space-y-4 font-mono text-xs text-coffee-text bg-coffee-cream/50 p-4 rounded-xl border border-coffee-latte">
                    <div class="text-center space-y-0.5">
                        <strong class="text-sm font-bold uppercase tracking-wider block">Kopi Premium</strong>
                        <span class="text-[10px] text-coffee-light block" x-text="activeTx.meja && activeTx.meja.nomor_meja == 99 ? 'Tipe: Takeaway' : 'Meja: ' + (activeTx.meja ? activeTx.meja.nomor_meja : '?')"></span>
                        <span class="text-[10px] text-coffee-light block" x-text="`Tgl: ${formatDate(activeTx.created_at)}`"></span>
                    </div>
                    <div class="border-b border-dashed border-coffee-latte my-2"></div>
    
                    <!-- Items list -->
                    <div class="space-y-3">
                        <template x-for="item in activeTx.details" :key="item.id_detail">
                            <div class="space-y-0.5">
                                <div class="flex justify-between">
                                    <span class="font-bold text-coffee-dark" x-text="item.menu ? item.menu.nama_menu : 'Unknown Item'"></span>
                                    <span x-text="formatRupiah(item.subtotal)"></span>
                                </div>
                                <div class="text-[10px] text-coffee-light font-semibold">
                                    <span x-text="`${item.jumlah} x ${formatRupiah(item.harga_satuan)}`"></span>
                                    <span x-show="item.catatan" x-text="` — Note: '${item.catatan}'`" class="text-red-500 font-bold"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="border-b border-dashed border-coffee-latte my-2"></div>
    
                    <!-- Pricing -->
                    <div class="space-y-1">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span x-text="formatRupiah(activeTx.total_harga)"></span>
                        </div>
                        <div class="flex justify-between text-rose-600" x-show="activeTx.diskon > 0">
                            <span>Diskon Promo:</span>
                            <span x-text="`- ${formatRupiah(activeTx.diskon)}`"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pajak ({{ $webSettings['pajak'] }}%):</span>
                            <span x-text="formatRupiah(activeTx.pajak)"></span>
                        </div>
                        <div class="flex justify-between font-bold text-sm border-t border-dotted border-coffee-latte pt-2 mt-1">
                            <span>TOTAL BAYAR:</span>
                            <span x-text="formatRupiah(activeTx.total_bayar)"></span>
                        </div>
                    </div>
                    <div class="border-b border-dashed border-coffee-latte my-2"></div>
                    <div class="text-center font-bold text-[10px] text-coffee-light uppercase" x-text="`Metode: ${activeTx.metode_pembayaran}`"></div>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
    function transactionManager() {
        return {
            modalOpen: false,
            activeTx: {},
            
            viewDetails(tx) {
                this.activeTx = tx;
                this.modalOpen = true;
            },
            
            formatRupiah(val) {
                if (!val) return 'Rp 0';
                return 'Rp ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            },
            
            formatDate(datetime) {
                if (!datetime) return '';
                const date = new Date(datetime);
                return date.toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });
            }
        }
    }
</script>
@endsection
