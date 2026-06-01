@extends('layouts.app')

@section('title', 'Kelola Menu')
@section('page_title', 'Kelola Menu Hidangan')

@section('content')
<div class="space-y-6" x-data="menuManager()">

    <!-- Filters & Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <!-- Tabs & Filters -->
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex border-b border-coffee-latte">
                <a href="{{ route('menu.index', ['trash' => '0', 'kategori_id' => $categoryId]) }}" class="px-5 py-2.5 font-bold text-xs border-b-2 transition {{ !$viewTrash ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                    Menu Aktif
                </a>
                <a href="{{ route('menu.index', ['trash' => '1', 'kategori_id' => $categoryId]) }}" class="px-5 py-2.5 font-bold text-xs border-b-2 transition {{ $viewTrash ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                    Tong Sampah (Trash)
                </a>
            </div>

            <!-- Category Filter dropdown -->
            <form action="{{ route('menu.index') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="trash" value="{{ $viewTrash ? '1' : '0' }}">
                <select name="kategori_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none bg-white">
                    <option value="semua" {{ $categoryId === 'semua' ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id_kategori }}" {{ $categoryId == $cat->id_kategori ? 'selected' : '' }}>{{ $cat->kategori }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if(!$viewTrash)
            <button @click="addModal = true" class="px-4 py-2 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
                <span>Tambah Menu Baru</span>
            </button>
        @endif
    </div>

    <!-- Menus Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($menus as $menu)
            <div class="bg-white rounded-2xl border border-coffee-latte p-4 flex gap-4 coffee-card relative {{ $menu->status === 'habis' ? 'opacity-65' : '' }}">
                <!-- Menu Icon/Photo -->
                <div class="w-20 h-20 rounded-xl bg-coffee-latte flex items-center justify-center text-coffee-medium border border-coffee-latte flex-shrink-0">
                    @if($menu->foto)
                        <img src="{{ str_starts_with($menu->foto, 'http') ? $menu->foto : asset($menu->foto) }}" alt="{{ $menu->nama_menu }}" class="w-full h-full object-cover rounded-xl">
                    @else
                        <!-- Fork knife SVG -->
                        <svg class="w-10 h-10 text-coffee-light" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    @endif
                </div>

                <div class="flex-grow flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-1">
                            <h4 class="text-sm font-bold text-coffee-dark leading-tight">{{ $menu->nama_menu }}</h4>
                            
                            <!-- Toggle availability button -->
                            @if(!$viewTrash)
                                <form action="{{ route('menu.toggleStatus', $menu->id_menu) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wide border transition cursor-pointer
                                        {{ $menu->status === 'tersedia' ? 'bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100' : 'bg-red-50 border-red-200 text-red-700 hover:bg-red-100' }}"
                                        title="Klik untuk mengubah status"
                                    >
                                        {{ $menu->status }}
                                    </button>
                                </form>
                            @endif
                        </div>
                        <span class="text-[10px] text-coffee-light font-bold uppercase tracking-wider block mt-0.5">{{ $menu->kategori ? $menu->kategori->kategori : 'Tanpa Kategori' }}</span>
                    </div>

                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-coffee-latte/50">
                        <strong class="text-xs font-bold text-coffee-medium">Rp {{ number_format($menu->harga, 0, ',', '.') }}</strong>
                        
                        <!-- Actions -->
                        <div class="flex gap-1">
                            @if(!$viewTrash)
                                <button @click="openEdit({{ json_encode($menu) }})" class="p-1 rounded hover:bg-amber-50 text-coffee-light hover:text-coffee-dark transition cursor-pointer" title="Edit Menu">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form action="{{ route('menu.delete', $menu->id_menu) }}" method="POST" onsubmit="return confirm('Hapus menu ini?')">
                                    @csrf
                                    <button type="submit" class="p-1 rounded hover:bg-rose-50 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus Menu">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('menu.restore', $menu->id_menu) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-1 rounded hover:bg-emerald-50 text-emerald-600 hover:text-emerald-700 transition cursor-pointer" title="Aktifkan Kembali">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    </button>
                                </form>
                                <form action="{{ route('menu.forceDelete', $menu->id_menu) }}" method="POST" onsubmit="return confirm('Hapus PERMANEN item menu ini?')">
                                    @csrf
                                    <button type="submit" class="p-1 rounded hover:bg-red-100 text-red-600 hover:text-red-700 transition cursor-pointer" title="Hapus Permanen">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-1 sm:col-span-2 lg:col-span-3 text-center py-20 bg-white rounded-3xl border border-coffee-latte coffee-card">
                <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center text-coffee-medium mx-auto mb-4 border border-amber-100">
                    <svg class="w-8 h-8 text-coffee-light" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="font-bold text-coffee-dark">Menu Kosong</h3>
                <p class="text-xs text-coffee-light font-medium mt-1">Belum ada item hidangan terdaftar sesuai filter.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination Links -->
    <div class="mt-6 no-print">
        {{ $menus->links() }}
    </div>

    <!-- ADD MENU MODAL -->
    <template x-teleport="body">
        <div 
            x-show="addModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition
            style="display: none;"
        >
            <div 
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-md w-full space-y-4 coffee-card"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark">Tambah Menu Hidangan</h3>
                    <button @click="addModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>
    
                <form action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="nama_menu" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Hidangan</label>
                        <input type="text" name="nama_menu" id="nama_menu" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
                    
                    <div>
                        <label for="id_kategori" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kategori</label>
                        <select name="id_kategori" id="id_kategori" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id_kategori }}">{{ $cat->kategori }}</option>
                            @endforeach
                        </select>
                    </div>
    
                    <div>
                        <label for="harga" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Harga</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="text" x-model="addHargaFormatted" @input="addHargaRaw = addHargaFormatted.replace(/[^0-9]/g, ''); addHargaFormatted = formatRupiahHelper(addHargaRaw)" required class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white" placeholder="0">
                            <input type="hidden" name="harga" :value="addHargaRaw">
                        </div>
                    </div>
    
                    <div>
                        <label for="status" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Status Ketersediaan</label>
                        <select name="status" id="status" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                            <option value="tersedia">Tersedia</option>
                            <option value="habis">Habis</option>
                        </select>
                    </div>

                    <div>
                        <label for="foto" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Foto Menu</label>
                        <input type="file" name="foto" id="foto" accept="image/*" class="w-full px-4 py-2 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
    
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="addModal = false" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- EDIT MENU MODAL -->
    <template x-teleport="body">
        <div 
            x-show="editModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition
            style="display: none;"
        >
            <div 
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-md w-full space-y-4 coffee-card"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark">Ubah Menu Hidangan</h3>
                    <button @click="editModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>
    
                <form :action="`{{ url('/menu/update') }}/${editMenu.id_menu}`" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="edit_nama_menu" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Hidangan</label>
                        <input type="text" name="nama_menu" id="edit_nama_menu" x-model="editMenu.nama_menu" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
                    
                    <div>
                        <label for="edit_kategori" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kategori</label>
                        <select name="id_kategori" id="edit_kategori" x-model="editMenu.id_kategori" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id_kategori }}">{{ $cat->kategori }}</option>
                            @endforeach
                        </select>
                    </div>
    
                    <div>
                        <label for="edit_harga" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Harga</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="text" x-model="editHargaFormatted" @input="editHargaRaw = editHargaFormatted.replace(/[^0-9]/g, ''); editHargaFormatted = formatRupiahHelper(editHargaRaw)" required class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white">
                            <input type="hidden" name="harga" :value="editHargaRaw">
                        </div>
                    </div>
    
                    <div>
                        <label class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Status Ketersediaan</label>
                        <div class="flex items-center gap-6 mt-2">
                            <label class="flex items-center gap-2 text-xs font-bold text-coffee-dark cursor-pointer">
                                <input type="radio" name="status" value="tersedia" x-model="editMenu.status" class="w-4 h-4 text-coffee-medium accent-amber-950">
                                Tersedia
                            </label>
                            <label class="flex items-center gap-2 text-xs font-bold text-coffee-dark cursor-pointer">
                                <input type="radio" name="status" value="habis" x-model="editMenu.status" class="w-4 h-4 text-coffee-medium accent-amber-950">
                                Habis
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="edit_foto" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Foto Menu (Biarkan kosong jika tidak diubah)</label>
                        <input type="file" name="foto" id="edit_foto" accept="image/*" class="w-full px-4 py-2 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
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

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addFotoInput = document.getElementById('foto');
        if (addFotoInput) initImageCropper(addFotoInput, 'cropped_image', null, 1);

        const editFotoInput = document.getElementById('edit_foto');
        if (editFotoInput) initImageCropper(editFotoInput, 'cropped_image', null, 1);
    });

    function menuManager() {
        return {
            addModal: false,
            editModal: false,
            editMenu: {},
            addHargaFormatted: '',
            addHargaRaw: '',
            editHargaFormatted: '',
            editHargaRaw: '',
            
            openEdit(menu) {
                this.editMenu = {...menu};
                this.editHargaRaw = menu.harga;
                this.editHargaFormatted = formatRupiahHelper(menu.harga);
                this.editModal = true;
            }
        }
    }
</script>
@endsection

