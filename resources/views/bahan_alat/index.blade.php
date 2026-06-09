@extends('layouts.app')

@section('title', 'Bahan')
@section('page_title', 'Bahan Baku Inventaris')

@section('content')
<div class="space-y-6" x-data="inventoryManager()">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <!-- Tabs -->
        <div class="flex border-b border-coffee-latte">
            <a href="{{ route('bahan-alat.index', ['tab' => 'active']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'active' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Bahan Aktif
            </a>
            <a href="{{ route('bahan-alat.index', ['tab' => 'trash']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'trash' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Tong Sampah (Trash)
            </a>
            <a href="{{ route('bahan-alat.index', ['tab' => 'history']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'history' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Riwayat Perubahan
            </a>
        </div>

        @if($tab === 'active')
            <button @click="addModal = true" class="px-4 py-2.5 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4 text-coffee-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Bahan Baru</span>
            </button>
        @endif
    </div>

    @if($tab === 'active' || $tab === 'trash')
        <!-- Filters & Action Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-coffee-latte coffee-card no-print">
            <form action="{{ route('bahan-alat.index') }}" method="GET" class="flex flex-wrap items-center gap-4 flex-grow">
                <input type="hidden" name="tab" value="{{ $tab }}">
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
        </div>

        <!-- Alert Success / Error -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 text-xs font-semibold shadow-sm no-print">
                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Inventory Table -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-medium uppercase tracking-wider">
                            <th class="pb-3 px-4">Nama Bahan</th>
                            <th class="pb-3 px-4">Kategori</th>
                            <th class="pb-3 px-4 text-center">Stok</th>
                            <th class="pb-3 px-4">Estimasi Harga</th>
                            <th class="pb-3 px-4">Keterangan</th>
                            <th class="pb-3 px-4 text-right no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-coffee-cream">
                        @forelse($items as $item)
                            <tr class="hover:bg-coffee-cream/20 transition group">
                                <td class="py-3.5 px-4 font-bold text-coffee-dark">
                                    {{ $item->nama_item }}
                                    @if($tab === 'trash')
                                        <span class="ml-2 px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wide border border-rose-100 bg-rose-50 text-rose-600">Terhapus</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs text-coffee-light font-bold">{{ $item->kategori }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-center gap-2.5 max-w-[150px] mx-auto">
                                        @if($tab === 'active')
                                            <button 
                                                type="button"
                                                @click="updateStok({{ $item->id_item }}, 'minus')"
                                                class="w-7 h-7 flex items-center justify-center bg-white border border-coffee-latte rounded-lg text-coffee-medium hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition font-black cursor-pointer select-none text-xs"
                                            >
                                                -
                                            </button>
                                        @endif
                                        <div class="text-center min-w-[50px]">
                                            <span class="text-sm font-black text-coffee-dark" id="stok-val-{{ $item->id_item }}">
                                                {{ number_format($item->stok, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[10px] font-bold text-coffee-light block -mt-1">{{ $item->satuan }}</span>
                                        </div>
                                        @if($tab === 'active')
                                            <button 
                                                type="button"
                                                @click="updateStok({{ $item->id_item }}, 'plus')"
                                                class="w-7 h-7 flex items-center justify-center bg-white border border-coffee-latte rounded-lg text-coffee-medium hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition font-black cursor-pointer select-none text-xs"
                                            >
                                                +
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-bold text-coffee-medium">
                                    Rp {{ number_format($item->harga_estimasi, 0, ',', '.') }}<span class="text-[9px] text-coffee-light font-medium">/{{ $item->satuan }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-coffee-light max-w-[200px] truncate" title="{{ $item->keterangan }}">
                                    {{ $item->keterangan ?: '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right no-print">
                                    <div class="flex justify-end items-center gap-1">
                                        @if($tab === 'active')
                                            <button @click="openEdit({{ json_encode($item) }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-coffee-light hover:text-coffee-dark transition cursor-pointer" title="Ubah Item">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form action="{{ route('bahan-alat.delete', $item->id_item) }}" method="POST" onsubmit="return confirm('Hapus item ini dari inventaris?')">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus Item">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('bahan-alat.restore', $item->id_item) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 hover:text-emerald-700 transition cursor-pointer" title="Pulihkan Item">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('bahan-alat.force-delete', $item->id_item) }}" method="POST" onsubmit="return confirm('Hapus PERMANEN item ini?')">
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
                                <td colspan="6" class="text-center py-20 bg-white">
                                    <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center text-coffee-medium mx-auto mb-4 border border-amber-100">
                                        <svg class="w-8 h-8 text-coffee-light" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <h3 class="font-bold text-coffee-dark text-sm">Inventaris Kosong</h3>
                                    <p class="text-xs text-coffee-light font-medium mt-1">Belum ada item bahan yang terdaftar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Links -->
        <div class="mt-6 no-print">
            {{ $items->links() }}
        </div>
    @endif

    @if($tab === 'history')
        <!-- History Table -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                            <th class="pb-3">Waktu</th>
                            <th class="pb-3">ID Record</th>
                            <th class="pb-3">Perubahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                        @forelse($historyUpdates as $h)
                            <tr>
                                <td class="py-3.5 text-xs">{{ $h->created_at->format('d M Y H:i:s') }}</td>
                                <td class="py-3.5 text-xs font-bold">#{{ $h->record_id }}</td>
                                <td class="py-3.5 text-xs space-y-1">
                                    @php
                                        $oldData = json_decode($h->data_lama, true) ?? [];
                                        $newData = json_decode($h->data_baru, true) ?? [];
                                    @endphp
                                    @foreach($newData as $key => $newVal)
                                        @php $oldVal = $oldData[$key] ?? 'N/A'; @endphp
                                        <div>
                                            <span class="font-bold text-coffee-medium">{{ $key }}:</span> 
                                            <span class="text-rose-500 line-through">{{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}</span>
                                            <span class="text-coffee-light mx-1">&rarr;</span>
                                            <span class="text-emerald-600">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                                        </div>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-coffee-light font-medium">Tidak ada riwayat perubahan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Links for History -->
        <div class="mt-6 no-print">
            {{ $historyUpdates->links() }}
        </div>
    @endif

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
                            <input type="number" step="1" name="stok" id="stok_form" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="10">
                        </div>
                        <div>
                            <label for="satuan_form" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Satuan</label>
                            <select name="satuan" id="satuan_form" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="" disabled selected>Pilih Satuan</option>
                                <option value="bungkus">bungkus</option>
                                <option value="kotak">kotak</option>
                                <option value="botol">botol</option>
                                <option value="pack">pack</option>
                                <option value="karung">karung</option>
                                <option value="ikat">ikat</option>
                                <option value="lusin">lusin</option>
                                <option value="pcs">pcs</option>
                                <option value="kaleng">kaleng</option>
                                <option value="sachet">sachet</option>
                                <option value="butir">butir</option>
                                <option value="cup">cup</option>
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
                            <input type="number" step="1" name="stok" id="edit_stok" x-model="editItem.stok" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                        </div>
                        <div>
                            <label for="edit_satuan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Satuan</label>
                            <select name="satuan" id="edit_satuan" x-model="editItem.satuan" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="" disabled>Pilih Satuan</option>
                                <option value="bungkus">bungkus</option>
                                <option value="kotak">kotak</option>
                                <option value="botol">botol</option>
                                <option value="pack">pack</option>
                                <option value="karung">karung</option>
                                <option value="ikat">ikat</option>
                                <option value="lusin">lusin</option>
                                <option value="pcs">pcs</option>
                                <option value="kaleng">kaleng</option>
                                <option value="sachet">sachet</option>
                                <option value="butir">butir</option>
                                <option value="cup">cup</option>
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
            },

            updateStok(itemId, action) {
                fetch(`/bahan-alat/update-stok/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ action: action })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const valEl = document.getElementById(`stok-val-${itemId}`);
                        if (valEl) {
                            valEl.textContent = data.new_stok.toLocaleString('id-ID');
                        }
                    }
                })
                .catch(err => {
                    console.error('Error updating stock:', err);
                });
            }
        }
    }
</script>
@endsection
