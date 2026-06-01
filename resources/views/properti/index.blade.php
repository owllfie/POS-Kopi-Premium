@extends('layouts.app')

@section('title', 'Properti Cafe')
@section('page_title', 'Daftar Properti & Biaya Bulanan')

@section('content')
<div class="space-y-6" x-data="propertyManager()">

    <!-- Filters & Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-coffee-latte coffee-card no-print">
        <form action="{{ route('properti.index') }}" method="GET" class="flex flex-wrap items-center gap-4 flex-grow">
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

        <button @click="addModal = true; addType = 'properti'" class="px-4 py-2.5 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
            <svg class="w-4 h-4 text-coffee-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>Tambah Properti / Peralatan</span>
        </button>
    </div>

    <!-- Properti Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($items as $item)
            <div class="bg-white rounded-2xl border border-coffee-latte p-5 flex flex-col justify-between coffee-card relative overflow-hidden transition-all duration-200 hover:-translate-y-1 hover:shadow-md">
                <!-- Top Badge Row -->
                <div class="flex items-center justify-between gap-2 border-b border-coffee-latte/50 pb-3 mb-3">
                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wide border 
                        {{ $item->tipe === 'properti' ? 'bg-amber-50 border-amber-200 text-coffee-light' : 'bg-blue-50 border-blue-200 text-blue-800' }}">
                        {{ $item->tipe === 'properti' ? 'Properti / Biaya' : 'Peralatan / Aset' }}
                    </span>
                    <span class="text-[10px] text-coffee-light font-bold uppercase tracking-wider">{{ $item->kategori }}</span>
                </div>

                <!-- Content -->
                <div class="space-y-2 flex-grow">
                    <h4 class="text-sm font-bold text-coffee-dark leading-tight">{{ $item->nama_item }}</h4>
                    
                    @if($item->tipe === 'alat')
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-xs font-medium text-coffee-light font-bold">Stok Peralatan:</span>
                        <strong class="text-xs font-black text-coffee-dark">
                            {{ number_format($item->stok, 0) }} <span class="text-[10px] font-bold text-coffee-medium">{{ $item->satuan }}</span>
                        </strong>
                    </div>
                    @endif

                    @if($item->harga_estimasi !== null)
                    <div class="flex items-center justify-between pt-2 mt-2 border-t border-coffee-latte/40">
                        <span class="text-[10px] font-bold text-coffee-light uppercase tracking-wider">Biaya Bulanan</span>
                        <span class="text-sm font-black text-coffee-dark">Rp {{ number_format($item->harga_estimasi, 0, ',', '.') }}<span class="text-[10px] text-coffee-light font-medium">/bulan</span></span>
                    </div>
                    @endif

                    @if($item->keterangan)
                        <p class="text-[11px] text-coffee-light bg-coffee-cream/40 p-2.5 rounded-xl border border-coffee-latte/30 font-medium italic mt-2.5">
                            "{{ $item->keterangan }}"
                        </p>
                    @endif
                </div>

                <!-- Footer Actions -->
                <div class="flex justify-end gap-1.5 pt-4 mt-3 border-t border-coffee-latte/50 no-print">
                    <button @click="openEdit({{ json_encode($item) }})" class="p-2 rounded-xl hover:bg-amber-50 text-coffee-light hover:text-coffee-dark transition cursor-pointer" title="Ubah Properti">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <form action="{{ route('properti.delete', $item->id_item) }}" method="POST" onsubmit="return confirm('Hapus properti ini dari daftar?')">
                        @csrf
                        <button type="submit" class="p-2 rounded-xl hover:bg-rose-50 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus Properti">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-20 bg-white rounded-3xl border border-coffee-latte coffee-card">
                <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center text-coffee-medium mx-auto mb-4 border border-amber-100">
                    <svg class="w-8 h-8 text-coffee-light" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h3 class="font-bold text-coffee-dark">Daftar Properti Kosong</h3>
                <p class="text-xs text-coffee-light font-medium mt-1">Belum ada properti atau peralatan yang terdaftar.</p>
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
                    <h3 class="font-extrabold text-coffee-dark">Tambah Properti / Peralatan Baru</h3>
                    <button @click="addModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>
    
                <form action="{{ route('properti.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="tipe_form" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Tipe</label>
                            <select name="tipe" id="tipe_form" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" x-model="addType">
                                <option value="properti">Properti / Utilitas</option>
                                <option value="alat">Peralatan / Aset</option>
                            </select>
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
                    </div>

                    <div>
                        <label for="nama_item" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Properti / Alat</label>
                        <input type="text" name="nama_item" id="nama_item" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Contoh: Mesin Espresso / Tagihan Listrik">
                    </div>

                    <div class="grid grid-cols-2 gap-4" x-show="addType === 'alat'">
                        <div>
                            <label for="stok_form" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Jumlah Unit</label>
                            <input type="number" name="stok" id="stok_form" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="1">
                        </div>
                        <div>
                            <label for="satuan_form" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Satuan</label>
                            <select name="satuan" id="satuan_form" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="" disabled selected>Pilih Satuan</option>
                                <option value="bulan">bulan</option>
                                <option value="unit">unit</option>
                                <option value="tahun">tahun</option>
                                <option value="pcs">pcs</option>
                                <option value="set">set</option>
                                <option value="box">box</option>
                                <option value="kg">kg</option>
                                <option value="liter">liter</option>
                            </select>
                        </div>
                    </div>
    
                    <div>
                        <label for="harga_estimasi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Biaya Bulanan (Opsional)</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="number" name="harga_estimasi" id="harga_estimasi" class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white" placeholder="5000000">
                        </div>
                    </div>
    
                    <div>
                        <label for="keterangan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Keterangan / Supplier</label>
                        <textarea name="keterangan" id="keterangan" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Contoh: Jatuh tempo setiap tanggal 5 bulan berjalan."></textarea>
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
                    <h3 class="font-extrabold text-coffee-dark">Ubah Detail Properti / Peralatan</h3>
                    <button @click="editModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>
    
                <form :action="`{{ url('/properti/update') }}/${editItem.id_item}`" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_tipe" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Tipe</label>
                            <select name="tipe" id="edit_tipe" x-model="editItem.tipe" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="properti">Properti / Utilitas</option>
                                <option value="alat">Peralatan / Aset</option>
                            </select>
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
                    </div>

                    <div>
                        <label for="edit_nama_item" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Properti / Alat</label>
                        <input type="text" name="nama_item" id="edit_nama_item" x-model="editItem.nama_item" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>

                    <div class="grid grid-cols-2 gap-4" x-show="editItem.tipe === 'alat'">
                        <div>
                            <label for="edit_stok" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Jumlah Unit</label>
                            <input type="number" name="stok" id="edit_stok" x-model="editItem.stok" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                        </div>
                        <div>
                            <label for="edit_satuan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Satuan</label>
                            <select name="satuan" id="edit_satuan" x-model="editItem.satuan" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="" disabled>Pilih Satuan</option>
                                <option value="bulan">bulan</option>
                                <option value="unit">unit</option>
                                <option value="tahun">tahun</option>
                                <option value="pcs">pcs</option>
                                <option value="set">set</option>
                                <option value="box">box</option>
                                <option value="kg">kg</option>
                                <option value="liter">liter</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="edit_harga_estimasi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Biaya Bulanan (Opsional)</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="number" name="harga_estimasi" id="edit_harga_estimasi" x-model="editItem.harga_estimasi" class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white">
                        </div>
                    </div>

                    <div>
                        <label for="edit_keterangan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Keterangan / Supplier</label>
                        <textarea name="keterangan" id="edit_keterangan" x-model="editItem.keterangan" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"></textarea>
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
    function propertyManager() {
        return {
            addModal: false,
            editModal: false,
            editItem: {},
            addType: 'properti',
            
            openEdit(item) {
                this.editItem = {...item};
                this.editModal = true;
            }
        }
    }
</script>
@endsection
