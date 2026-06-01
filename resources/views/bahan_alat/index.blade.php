@extends('layouts.app')

@section('title', 'Bahan')
@section('page_title', 'Bahan Baku Inventaris')

@section('content')
<div class="space-y-6" x-data="inventoryManager()">

    <!-- Filters & Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-coffee-latte coffee-card no-print">
        <form action="{{ route('bahan-alat.index') }}" method="GET" class="flex flex-wrap items-center gap-4 flex-grow">
            <div>
                <label for="kategori" class="block text-[10px] font-bold text-coffee-medium uppercase tracking-wider mb-1.5">Kategori</label>
                <select name="kategori" id="kategori" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none bg-white">
                    <option value="semua" {{ $kategori === 'semua' ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $kategori === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <button @click="addModal = true" class="px-4 py-2.5 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
            <svg class="w-4 h-4 text-coffee-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>Tambah Bahan Baru</span>
        </button>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 text-xs font-semibold shadow-sm no-print">
            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Inventory Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($items as $item)
            <div class="bg-white rounded-2xl border border-coffee-latte p-5 flex flex-col justify-between coffee-card relative overflow-hidden transition-all duration-200 hover:-translate-y-1 hover:shadow-md">
                <!-- Top Badge Row -->
                <div class="flex items-center justify-between gap-2 border-b border-coffee-latte/50 pb-3 mb-3">
                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wide border bg-amber-50 border-amber-200 text-coffee-light">
                        Bahan Baku
                    </span>
                    <span class="text-[10px] text-coffee-light font-bold uppercase tracking-wider">{{ $item->kategori }}</span>
                </div>

                <!-- Content -->
                <div class="space-y-2 flex-grow">
                    <h4 class="text-sm font-bold text-coffee-dark leading-tight">{{ $item->nama_item }}</h4>
                    
                    <div class="flex items-baseline justify-between pt-1">
                        <span class="text-xs font-medium text-coffee-light">Stok Tersedia:</span>
                        <strong class="text-base font-black text-coffee-dark">
                            {{ number_format($item->stok, 1, ',', '.') }} 
                            <span class="text-xs font-bold text-coffee-medium">{{ $item->satuan }}</span>
                        </strong>
                    </div>

                    <div class="flex items-center justify-between pt-1 border-t border-coffee-latte/40">
                        <span class="text-[10px] font-bold text-coffee-light uppercase tracking-wider">Estimasi Harga</span>
                        <span class="text-xs font-bold text-coffee-medium">Rp {{ number_format($item->harga_estimasi, 0, ',', '.') }}<span class="text-[10px] text-coffee-light font-medium">/{{ $item->satuan }}</span></span>
                    </div>

                    @if($item->keterangan)
                        <p class="text-[11px] text-coffee-light bg-coffee-cream/40 p-2.5 rounded-xl border border-coffee-latte/30 font-medium italic mt-2">
                            "{{ $item->keterangan }}"
                        </p>
                    @endif
                </div>

                <!-- Footer Actions -->
                <div class="flex justify-end gap-1.5 pt-4 mt-3 border-t border-coffee-latte/50 no-print">
                    <button @click="openEdit({{ json_encode($item) }})" class="p-2 rounded-xl hover:bg-amber-50 text-coffee-light hover:text-coffee-dark transition cursor-pointer" title="Ubah Item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <form action="{{ route('bahan-alat.delete', $item->id_item) }}" method="POST" onsubmit="return confirm('Hapus item ini dari inventaris?')">
                        @csrf
                        <button type="submit" class="p-2 rounded-xl hover:bg-rose-50 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus Item">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-20 bg-white rounded-3xl border border-coffee-latte coffee-card">
                <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center text-coffee-medium mx-auto mb-4 border border-amber-100">
                    <svg class="w-8 h-8 text-coffee-light" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="font-bold text-coffee-dark">Inventaris Kosong</h3>
                <p class="text-xs text-coffee-light font-medium mt-1">Belum ada item bahan yang terdaftar.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination Links -->
    <div class="mt-6 no-print">
        {{ $items->links() }}
    </div>

    <!-- ADD ITEM MODAL -->
    <template x-teleport="body">
        <div 
            x-show="addModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition
            style="display: none;"
        >
            <div 
                @click.away="addModal = false" 
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-md w-full space-y-4 coffee-card"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark">Tambah Bahan Baru</h3>
                    <button @click="addModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>
    
                <form action="{{ route('bahan-alat.store') }}" method="POST" class="space-y-4">
                     @csrf
                    <div>
                        <label for="nama_item" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Bahan</label>
                        <input type="text" name="nama_item" id="nama_item" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Contoh: Susu UHT Greenfield 1L">
                    </div>
                    
                    <div x-data="{ selectedKategori: '' }">
                        <label for="kategori_form_select" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kategori</label>
                        <select 
                            :name="selectedKategori !== 'NEW_CATEGORY' ? 'kategori' : 'kategori_select'"
                            id="kategori_form_select"
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" 
                            x-model="selectedKategori"
                        >
                            <option value="" disabled selected>Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                            <option value="NEW_CATEGORY">+ Tambah Kategori Baru</option>
                        </select>
                        
                        <div x-show="selectedKategori === 'NEW_CATEGORY'" class="mt-2" x-transition>
                            <input 
                                type="text" 
                                :name="selectedKategori === 'NEW_CATEGORY' ? 'kategori' : 'kategori_new'"
                                :required="selectedKategori === 'NEW_CATEGORY'"
                                class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" 
                                placeholder="Ketik Kategori Baru..."
                            >
                        </div>
                    </div>
    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="stok_form" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Stok Awal</label>
                            <input type="number" step="0.01" name="stok" id="stok_form" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="10">
                        </div>
                        <div>
                            <label for="satuan_form" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Satuan</label>
                            <select name="satuan" id="satuan_form" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="" disabled selected>Pilih Satuan</option>
                                <option value="kg">kg</option>
                                <option value="gr">gr</option>
                                <option value="liter">liter</option>
                                <option value="ml">ml</option>
                                <option value="pcs">pcs</option>
                                <option value="pack">pack</option>
                                <option value="botol">botol</option>
                                <option value="box">box</option>
                                <option value="karung">karung</option>
                                <option value="ikat">ikat</option>
                                <option value="lusin">lusin</option>
                                <option value="unit">unit</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="harga_estimasi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Estimasi Harga Beli</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="number" name="harga_estimasi" id="harga_estimasi" required class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white" placeholder="25000">
                        </div>
                    </div>

                    <div>
                        <label for="keterangan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Keterangan / Supplier</label>
                        <textarea name="keterangan" id="keterangan" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Wajib disimpan di suhu dingin / Beli di Toko X"></textarea>
                    </div>
    
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="addModal = false" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- EDIT ITEM MODAL -->
    <template x-teleport="body">
        <div 
            x-show="editModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition
            style="display: none;"
        >
            <div 
                @click.away="editModal = false" 
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-md w-full space-y-4 coffee-card"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark">Ubah Detail Bahan</h3>
                    <button @click="editModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>
    
                <form :action="`{{ url('/bahan-alat/update') }}/${editItem.id_item}`" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="edit_nama_item" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Bahan</label>
                        <input type="text" name="nama_item" id="edit_nama_item" x-model="editItem.nama_item" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
                    
                    <div x-data="{ editNewKategori: '' }">
                        <label for="edit_kategori_select" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kategori</label>
                        <select 
                            :name="editItem.kategori !== 'NEW_CATEGORY' ? 'kategori' : 'kategori_select'"
                            id="edit_kategori_select"
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" 
                            x-model="editItem.kategori"
                        >
                            <option value="" disabled>Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                            <option value="NEW_CATEGORY">+ Ubah / Kategori Baru</option>
                        </select>
                        
                        <div x-show="editItem.kategori === 'NEW_CATEGORY'" class="mt-2" x-transition>
                            <input 
                                type="text" 
                                :name="editItem.kategori === 'NEW_CATEGORY' ? 'kategori' : 'kategori_new'"
                                :required="editItem.kategori === 'NEW_CATEGORY'"
                                class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" 
                                placeholder="Ketik Kategori Baru..."
                                x-model="editNewKategori"
                            >
                        </div>
                    </div>
    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_stok" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Jumlah Stok</label>
                            <input type="number" step="0.01" name="stok" id="edit_stok" x-model="editItem.stok" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                        </div>
                        <div>
                            <label for="edit_satuan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Satuan</label>
                            <select name="satuan" id="edit_satuan" x-model="editItem.satuan" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="" disabled>Pilih Satuan</option>
                                <option value="kg">kg</option>
                                <option value="gr">gr</option>
                                <option value="liter">liter</option>
                                <option value="ml">ml</option>
                                <option value="pcs">pcs</option>
                                <option value="pack">pack</option>
                                <option value="botol">botol</option>
                                <option value="box">box</option>
                                <option value="karung">karung</option>
                                <option value="ikat">ikat</option>
                                <option value="lusin">lusin</option>
                                <option value="unit">unit</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="edit_harga_estimasi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Estimasi Harga Beli</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="number" name="harga_estimasi" id="edit_harga_estimasi" x-model="editItem.harga_estimasi" required class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white">
                        </div>
                    </div>

                    <div>
                        <label for="edit_keterangan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Keterangan / Supplier</label>
                        <textarea name="keterangan" id="edit_keterangan" x-model="editItem.keterangan" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"></textarea>
                    </div>
    
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="editModal = false" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>

<script>
    function inventoryManager() {
        return {
            addModal: false,
            editModal: false,
            editItem: {},
            
            openEdit(item) {
                this.editItem = {...item};
                this.editModal = true;
            }
        }
    }
</script>
@endsection
