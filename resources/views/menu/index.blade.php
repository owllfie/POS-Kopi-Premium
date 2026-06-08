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
                <a href="{{ route('menu.index', ['tab' => 'makanan', 'kategori_id' => 'semua']) }}" class="px-5 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'makanan' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                    Menu Makanan
                </a>
                <a href="{{ route('menu.index', ['tab' => 'minuman', 'kategori_id' => 'semua']) }}" class="px-5 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'minuman' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                    Menu Minuman
                </a>
                <a href="{{ route('menu.index', ['tab' => 'paket']) }}" class="px-5 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'paket' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                    Menu Paket
                </a>
                <a href="{{ route('menu.index', ['tab' => 'trash', 'kategori_id' => 'semua']) }}" class="px-5 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'trash' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                    Tong Sampah (Trash)
                </a>
                <a href="{{ route('menu.index', ['tab' => 'history']) }}" class="px-5 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'history' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                    Riwayat Perubahan
                </a>
            </div>

            @if($tab !== 'paket' && $tab !== 'history')
                <!-- Category Filter dropdown -->
                <form action="{{ route('menu.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <select name="kategori_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none bg-white">
                        <option value="semua" {{ $categoryId === 'semua' ? 'selected' : '' }}>Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id_kategori }}" {{ $categoryId == $cat->id_kategori ? 'selected' : '' }}>{{ $cat->kategori }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        @if($tab === 'makanan' || $tab === 'minuman' || $tab === 'paket')
            <button @click="addModal = true" class="px-4 py-2 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
                <span>{{ $tab === 'paket' ? 'Tambah Paket Baru' : 'Tambah Menu Baru' }}</span>
            </button>
        @endif
    </div>

    @if($tab !== 'history')
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
                            
                            @if($menu->kategori && $menu->kategori->kategori === 'Paket')
                                <div class="mt-2 text-[10px] text-coffee-medium space-y-0.5 border-t border-coffee-latte/30 pt-1.5">
                                    @php
                                        $foodNames = $menu->getPaketMakananNames();
                                        $drinkNames = $menu->getPaketMinumanNames();
                                    @endphp
                                    @if(!empty($foodNames))
                                        <div><span class="font-bold">Makanan:</span> {{ implode(', ', $foodNames) }}</div>
                                    @endif
                                    @if(!empty($drinkNames))
                                        <div><span class="font-bold">Minuman:</span> {{ implode(', ', $drinkNames) }}</div>
                                    @endif
                                    @if(!empty($menu->paket_addons))
                                        <div><span class="font-bold">Add-on:</span> {{ $menu->paket_addons }}</div>
                                    @endif
                                </div>
                            @endif
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
                                    <!-- RESTORE/DELETE ACTIONS HIDDEN OR INACTIVE PER USER GUIDELINE TO PREVENT RESTORING/DELETING TRASH ITEMS -->
                                    <button type="button" onclick="alert('Peringatan: Mengambil kembali data dari trash dilarang pada langkah ini.')" class="p-1 rounded hover:bg-emerald-50 text-emerald-600/50 transition cursor-not-allowed" title="Restore Dinonaktifkan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    </button>
                                    <button type="button" onclick="alert('Peringatan: Menghapus permanen data dari trash dilarang pada langkah ini.')" class="p-1 rounded hover:bg-red-100 text-red-600/50 transition cursor-not-allowed" title="Hapus Permanen Dinonaktifkan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
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
    @else
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
                                            <span class="text-rose-500 line-through">
                                                @if($key === 'id_kategori')
                                                    @php $oldCat = \App\Models\Kategori::find($oldVal); @endphp
                                                    {{ $oldCat ? $oldCat->kategori : $oldVal }}
                                                @else
                                                    {{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}
                                                @endif
                                            </span>
                                            <span class="text-coffee-light mx-1">&rarr;</span>
                                            <span class="text-emerald-600">
                                                @if($key === 'id_kategori')
                                                    @php $newCat = \App\Models\Kategori::find($newVal); @endphp
                                                    {{ $newCat ? $newCat->kategori : $newVal }}
                                                @else
                                                    {{ is_array($newVal) ? json_encode($newVal) : $newVal }}
                                                @endif
                                            </span>
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

    <!-- ADD MENU MODAL -->
    <template x-teleport="body">
        <div 
            x-show="addModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition
            style="display: none;"
        >
            <div 
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-md w-full space-y-4 coffee-card max-h-[90vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark">{{ $tab === 'paket' ? 'Tambah Paket Baru' : 'Tambah Menu Hidangan' }}</h3>
                    <button @click="addModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>
    
                <form action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="nama_menu" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Hidangan</label>
                        <input type="text" name="nama_menu" id="nama_menu" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
                    
                    @if($tab === 'paket')
                        <input type="hidden" name="id_kategori" value="{{ $categories->first()->id_kategori ?? '' }}">
                        <div>
                            <label class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kategori</label>
                            <input type="text" value="Paket" disabled class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-light bg-coffee-cream/40 cursor-not-allowed">
                        </div>
                    @else
                        <div>
                            <label for="id_kategori" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kategori</label>
                            <select name="id_kategori" id="id_kategori" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id_kategori }}">{{ $cat->kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
    
                    <div>
                        <label for="harga" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Harga</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="text" x-model="addHargaFormatted" @input="addHargaRaw = addHargaFormatted.replace(/[^0-9]/g, ''); addHargaFormatted = formatRupiahHelper(addHargaRaw)" required class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white" placeholder="0">
                            <input type="hidden" name="harga" :value="addHargaRaw">
                        </div>
                    </div>
    
                    <div>
                        <label for="kode_menu" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kode Barcode (Opsional)</label>
                        <input type="text" name="kode_menu" id="kode_menu" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Scan atau ketik kode barcode...">
                    </div>
    
                    <div>
                        <label for="deskripsi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" id="deskripsi" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Ketik deskripsi menu hidangan..."></textarea>
                    </div>
    
                    @if($tab === 'paket')
                        <!-- Makanan Search and Suggestion Select -->
                        <div class="space-y-2 relative" x-data="{ open: false }" @click.outside="open = false">
                            <label class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-1">Pilih Makanan (Bisa > 1)</label>
                            <div class="relative">
                                <input type="text" x-model="addFoodQuery" @focus="open = true" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Cari makanan">
                            </div>
                            
                            <!-- Suggestions list -->
                            <div x-show="open && filteredAddFoods().length > 0" class="absolute z-10 w-full mt-1 bg-white border border-coffee-latte rounded-xl shadow-lg max-h-48 overflow-y-auto" style="display: none;">
                                <template x-for="food in filteredAddFoods()" :key="food.id_menu">
                                    <button type="button" @click="selectAddFood(food.id_menu); open = false" class="w-full text-left px-4 py-2 hover:bg-coffee-cream text-xs font-bold text-coffee-dark transition border-b border-coffee-cream/50 last:border-b-0 flex justify-between items-center">
                                        <span x-text="food.nama_menu"></span>
                                        <span class="text-coffee-medium text-[10px]" x-text="'Rp ' + formatRupiahHelper(food.harga)"></span>
                                    </button>
                                </template>
                            </div>

                            <!-- Selected items badges with quantity adjusters -->
                            <div class="flex flex-wrap gap-2 mt-2">
                                <template x-for="foodId in Object.keys(addSelectedFoods)" :key="foodId">
                                    <div class="flex items-center gap-2 px-3 py-1 bg-coffee-cream border border-coffee-latte rounded-xl text-[11px] font-bold text-coffee-dark select-none">
                                        <span x-text="getMenuName(foodId)"></span>
                                        <div class="flex items-center gap-1 bg-white border border-coffee-latte rounded-lg px-1.5 py-0.5">
                                            <button type="button" @click="if(addSelectedFoods[foodId] > 1) addSelectedFoods[foodId]--" class="text-coffee-medium hover:text-coffee-dark font-extrabold focus:outline-none">-</button>
                                            <input type="number" :name="`paket_makanan[${foodId}]`" x-model.number="addSelectedFoods[foodId]" class="w-8 text-center text-xs font-bold text-coffee-dark focus:outline-none border-0 p-0 bg-transparent" min="1">
                                            <button type="button" @click="addSelectedFoods[foodId]++" class="text-coffee-medium hover:text-coffee-dark font-extrabold focus:outline-none">+</button>
                                        </div>
                                        <button type="button" @click="removeAddFood(foodId)" class="text-rose-500 hover:text-rose-700 font-extrabold focus:outline-none">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Minuman Search and Suggestion Select -->
                        <div class="space-y-2 relative" x-data="{ open: false }" @click.outside="open = false">
                            <label class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-1">Pilih Minuman (Bisa > 1)</label>
                            <div class="relative">
                                <input type="text" x-model="addDrinkQuery" @focus="open = true" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Cari minuman...">
                            </div>
                            
                            <!-- Suggestions list -->
                            <div x-show="open && filteredAddDrinks().length > 0" class="absolute z-10 w-full mt-1 bg-white border border-coffee-latte rounded-xl shadow-lg max-h-48 overflow-y-auto" style="display: none;">
                                <template x-for="drink in filteredAddDrinks()" :key="drink.id_menu">
                                    <button type="button" @click="selectAddDrink(drink.id_menu); open = false" class="w-full text-left px-4 py-2 hover:bg-coffee-cream text-xs font-bold text-coffee-dark transition border-b border-coffee-cream/50 last:border-b-0 flex justify-between items-center">
                                        <span x-text="drink.nama_menu"></span>
                                        <span class="text-coffee-medium text-[10px]" x-text="'Rp ' + formatRupiahHelper(drink.harga)"></span>
                                    </button>
                                </template>
                            </div>

                            <!-- Selected items badges with quantity adjusters -->
                            <div class="flex flex-wrap gap-2 mt-2">
                                <template x-for="drinkId in Object.keys(addSelectedDrinks)" :key="drinkId">
                                    <div class="flex items-center gap-2 px-3 py-1 bg-coffee-cream border border-coffee-latte rounded-xl text-[11px] font-bold text-coffee-dark select-none">
                                        <span x-text="getMenuName(drinkId)"></span>
                                        <div class="flex items-center gap-1 bg-white border border-coffee-latte rounded-lg px-1.5 py-0.5">
                                            <button type="button" @click="if(addSelectedDrinks[drinkId] > 1) addSelectedDrinks[drinkId]--" class="text-coffee-medium hover:text-coffee-dark font-extrabold focus:outline-none">-</button>
                                            <input type="number" :name="`paket_minuman[${drinkId}]`" x-model.number="addSelectedDrinks[drinkId]" class="w-8 text-center text-xs font-bold text-coffee-dark focus:outline-none border-0 p-0 bg-transparent" min="1">
                                            <button type="button" @click="addSelectedDrinks[drinkId]++" class="text-coffee-medium hover:text-coffee-dark font-extrabold focus:outline-none">+</button>
                                        </div>
                                        <button type="button" @click="removeAddDrink(drinkId)" class="text-rose-500 hover:text-rose-700 font-extrabold focus:outline-none">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label for="paket_addons" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Add-on</label>
                            <input type="text" name="paket_addons" id="paket_addons" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Contoh: Ekstra Espresso, Whipped Cream">
                        </div>
                    @endif

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
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-md w-full space-y-4 coffee-card max-h-[90vh] overflow-y-auto"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark">{{ $tab === 'paket' ? 'Ubah Paket Baru' : 'Ubah Menu Hidangan' }}</h3>
                    <button @click="editModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>
    
                <form :action="`{{ url('/menu/update') }}/${editMenu.id_menu}`" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="edit_nama_menu" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Hidangan</label>
                        <input type="text" name="nama_menu" id="edit_nama_menu" x-model="editMenu.nama_menu" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
                    
                    @if($tab === 'paket')
                        <input type="hidden" name="id_kategori" :value="editMenu.id_kategori">
                        <div>
                            <label class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kategori</label>
                            <input type="text" value="Paket" disabled class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-light bg-coffee-cream/40 cursor-not-allowed">
                        </div>
                    @else
                        <div>
                            <label for="edit_kategori" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kategori</label>
                            <select name="id_kategori" id="edit_kategori" x-model="editMenu.id_kategori" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id_kategori }}">{{ $cat->kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
    
                    <div>
                        <label for="edit_harga" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Harga</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="text" x-model="editHargaFormatted" @input="editHargaRaw = editHargaFormatted.replace(/[^0-9]/g, ''); editHargaFormatted = formatRupiahHelper(editHargaRaw)" required class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white">
                            <input type="hidden" name="harga" :value="editHargaRaw">
                        </div>
                    </div>
    
                    <div>
                        <label for="edit_kode_menu" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Kode Barcode (Opsional)</label>
                        <input type="text" name="kode_menu" id="edit_kode_menu" x-model="editMenu.kode_menu" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Scan atau ketik kode barcode...">
                    </div>
    
                    <div>
                        <label for="edit_deskripsi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" id="edit_deskripsi" x-model="editMenu.deskripsi" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Ketik deskripsi menu hidangan..."></textarea>
                    </div>
    
                    @if($tab === 'paket')
                        <!-- Makanan Search and Suggestion Select -->
                        <div class="space-y-2 relative" x-data="{ open: false }" @click.outside="open = false">
                            <label class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-1">Pilih Makanan (Bisa > 1)</label>
                            <div class="relative">
                                <input type="text" x-model="editFoodQuery" @focus="open = true" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Cari makanan (misal: almond)...">
                            </div>
                            
                            <!-- Suggestions list -->
                            <div x-show="open && filteredEditFoods().length > 0" class="absolute z-10 w-full mt-1 bg-white border border-coffee-latte rounded-xl shadow-lg max-h-48 overflow-y-auto" style="display: none;">
                                <template x-for="food in filteredEditFoods()" :key="food.id_menu">
                                    <button type="button" @click="selectEditFood(food.id_menu); open = false" class="w-full text-left px-4 py-2 hover:bg-coffee-cream text-xs font-bold text-coffee-dark transition border-b border-coffee-cream/50 last:border-b-0 flex justify-between items-center">
                                        <span x-text="food.nama_menu"></span>
                                        <span class="text-coffee-medium text-[10px]" x-text="'Rp ' + formatRupiahHelper(food.harga)"></span>
                                    </button>
                                </template>
                            </div>

                            <!-- Selected items badges with quantity adjusters -->
                            <div class="flex flex-wrap gap-2 mt-2">
                                <template x-for="foodId in Object.keys(editMenu.paket_makanan || {})" :key="foodId">
                                    <div class="flex items-center gap-2 px-3 py-1 bg-coffee-cream border border-coffee-latte rounded-xl text-[11px] font-bold text-coffee-dark select-none">
                                        <span x-text="getMenuName(foodId)"></span>
                                        <div class="flex items-center gap-1 bg-white border border-coffee-latte rounded-lg px-1.5 py-0.5">
                                            <button type="button" @click="if(editMenu.paket_makanan[foodId] > 1) editMenu.paket_makanan[foodId]--" class="text-coffee-medium hover:text-coffee-dark font-extrabold focus:outline-none">-</button>
                                            <input type="number" :name="`paket_makanan[${foodId}]`" x-model.number="editMenu.paket_makanan[foodId]" class="w-8 text-center text-xs font-bold text-coffee-dark focus:outline-none border-0 p-0 bg-transparent" min="1">
                                            <button type="button" @click="editMenu.paket_makanan[foodId]++" class="text-coffee-medium hover:text-coffee-dark font-extrabold focus:outline-none">+</button>
                                        </div>
                                        <button type="button" @click="removeEditFood(foodId)" class="text-rose-500 hover:text-rose-700 font-extrabold focus:outline-none">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Minuman Search and Suggestion Select -->
                        <div class="space-y-2 relative" x-data="{ open: false }" @click.outside="open = false">
                            <label class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-1">Pilih Minuman (Bisa > 1)</label>
                            <div class="relative">
                                <input type="text" x-model="editDrinkQuery" @focus="open = true" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Cari minuman...">
                            </div>
                            
                            <!-- Suggestions list -->
                            <div x-show="open && filteredEditDrinks().length > 0" class="absolute z-10 w-full mt-1 bg-white border border-coffee-latte rounded-xl shadow-lg max-h-48 overflow-y-auto" style="display: none;">
                                <template x-for="drink in filteredEditDrinks()" :key="drink.id_menu">
                                    <button type="button" @click="selectEditDrink(drink.id_menu); open = false" class="w-full text-left px-4 py-2 hover:bg-coffee-cream text-xs font-bold text-coffee-dark transition border-b border-coffee-cream/50 last:border-b-0 flex justify-between items-center">
                                        <span x-text="drink.nama_menu"></span>
                                        <span class="text-coffee-medium text-[10px]" x-text="'Rp ' + formatRupiahHelper(drink.harga)"></span>
                                    </button>
                                </template>
                            </div>

                            <!-- Selected items badges with quantity adjusters -->
                            <div class="flex flex-wrap gap-2 mt-2">
                                <template x-for="drinkId in Object.keys(editMenu.paket_minuman || {})" :key="drinkId">
                                    <div class="flex items-center gap-2 px-3 py-1 bg-coffee-cream border border-coffee-latte rounded-xl text-[11px] font-bold text-coffee-dark select-none">
                                        <span x-text="getMenuName(drinkId)"></span>
                                        <div class="flex items-center gap-1 bg-white border border-coffee-latte rounded-lg px-1.5 py-0.5">
                                            <button type="button" @click="if(editMenu.paket_minuman[drinkId] > 1) editMenu.paket_minuman[drinkId]--" class="text-coffee-medium hover:text-coffee-dark font-extrabold focus:outline-none">-</button>
                                            <input type="number" :name="`paket_minuman[${drinkId}]`" x-model.number="editMenu.paket_minuman[drinkId]" class="w-8 text-center text-xs font-bold text-coffee-dark focus:outline-none border-0 p-0 bg-transparent" min="1">
                                            <button type="button" @click="editMenu.paket_minuman[drinkId]++" class="text-coffee-medium hover:text-coffee-dark font-extrabold focus:outline-none">+</button>
                                        </div>
                                        <button type="button" @click="removeEditDrink(drinkId)" class="text-rose-500 hover:text-rose-700 font-extrabold focus:outline-none">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label for="edit_paket_addons" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Add-on</label>
                            <input type="text" name="paket_addons" id="edit_paket_addons" x-model="editMenu.paket_addons" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Contoh: Ekstra Espresso, Whipped Cream">
                        </div>
                    @endif

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
            
            allFoods: @json($allFoods),
            allDrinks: @json($allDrinks),

            addSelectedFoods: {},
            addSelectedDrinks: {},
            addFoodQuery: '',
            addDrinkQuery: '',

            editFoodQuery: '',
            editDrinkQuery: '',

            filteredAddFoods() {
                if (!this.addFoodQuery) return [];
                const query = this.addFoodQuery.toLowerCase();
                const selectedIds = Object.keys(this.addSelectedFoods);
                return this.allFoods.filter(f => f.nama_menu.toLowerCase().includes(query) && !selectedIds.includes(String(f.id_menu)));
            },
            filteredAddDrinks() {
                if (!this.addDrinkQuery) return [];
                const query = this.addDrinkQuery.toLowerCase();
                const selectedIds = Object.keys(this.addSelectedDrinks);
                return this.allDrinks.filter(d => d.nama_menu.toLowerCase().includes(query) && !selectedIds.includes(String(d.id_menu)));
            },
            filteredEditFoods() {
                if (!this.editFoodQuery) return [];
                const query = this.editFoodQuery.toLowerCase();
                const selectedIds = Object.keys(this.editMenu.paket_makanan || {});
                return this.allFoods.filter(f => f.nama_menu.toLowerCase().includes(query) && !selectedIds.includes(String(f.id_menu)));
            },
            filteredEditDrinks() {
                if (!this.editDrinkQuery) return [];
                const query = this.editDrinkQuery.toLowerCase();
                const selectedIds = Object.keys(this.editMenu.paket_minuman || {});
                return this.allDrinks.filter(d => d.nama_menu.toLowerCase().includes(query) && !selectedIds.includes(String(d.id_menu)));
            },

            selectAddFood(foodId) {
                this.addSelectedFoods = {...this.addSelectedFoods, [foodId]: 1};
                this.addFoodQuery = '';
            },
            removeAddFood(foodId) {
                const copy = {...this.addSelectedFoods};
                delete copy[foodId];
                this.addSelectedFoods = copy;
            },
            selectAddDrink(drinkId) {
                this.addSelectedDrinks = {...this.addSelectedDrinks, [drinkId]: 1};
                this.addDrinkQuery = '';
            },
            removeAddDrink(drinkId) {
                const copy = {...this.addSelectedDrinks};
                delete copy[drinkId];
                this.addSelectedDrinks = copy;
            },

            selectEditFood(foodId) {
                if (!this.editMenu.paket_makanan) {
                    this.editMenu.paket_makanan = {};
                }
                this.editMenu.paket_makanan = {...this.editMenu.paket_makanan, [foodId]: 1};
                this.editFoodQuery = '';
            },
            removeEditFood(foodId) {
                const copy = {...this.editMenu.paket_makanan};
                delete copy[foodId];
                this.editMenu.paket_makanan = copy;
            },
            selectEditDrink(drinkId) {
                if (!this.editMenu.paket_minuman) {
                    this.editMenu.paket_minuman = {};
                }
                this.editMenu.paket_minuman = {...this.editMenu.paket_minuman, [drinkId]: 1};
                this.editDrinkQuery = '';
            },
            removeEditDrink(drinkId) {
                const copy = {...this.editMenu.paket_minuman};
                delete copy[drinkId];
                this.editMenu.paket_minuman = copy;
            },

            getMenuName(id) {
                const food = this.allFoods.find(f => f.id_menu == id);
                if (food) return food.nama_menu;
                const drink = this.allDrinks.find(d => d.id_menu == id);
                if (drink) return drink.nama_menu;
                return '';
            },
            
            openEdit(menu) {
                this.editMenu = {...menu};
                
                // Initialize paket_makanan object with quantities
                let rawMakanan = {};
                if (menu.paket_makanan) {
                    if (Array.isArray(menu.paket_makanan)) {
                        menu.paket_makanan.forEach(id => {
                            rawMakanan[id] = 1;
                        });
                    } else if (typeof menu.paket_makanan === 'object') {
                        rawMakanan = {...menu.paket_makanan};
                    } else if (typeof menu.paket_makanan === 'string') {
                        try {
                            const parsed = JSON.parse(menu.paket_makanan);
                            if (Array.isArray(parsed)) {
                                parsed.forEach(id => {
                                    rawMakanan[id] = 1;
                                });
                            } else {
                                rawMakanan = parsed;
                            }
                        } catch(e) {
                            rawMakanan = {};
                        }
                    }
                }
                this.editMenu.paket_makanan = rawMakanan;

                // Initialize paket_minuman object with quantities
                let rawMinuman = {};
                if (menu.paket_minuman) {
                    if (Array.isArray(menu.paket_minuman)) {
                        menu.paket_minuman.forEach(id => {
                            rawMinuman[id] = 1;
                        });
                    } else if (typeof menu.paket_minuman === 'object') {
                        rawMinuman = {...menu.paket_minuman};
                    } else if (typeof menu.paket_minuman === 'string') {
                        try {
                            const parsed = JSON.parse(menu.paket_minuman);
                            if (Array.isArray(parsed)) {
                                parsed.forEach(id => {
                                    rawMinuman[id] = 1;
                                });
                            } else {
                                rawMinuman = parsed;
                            }
                        } catch(e) {
                            rawMinuman = {};
                        }
                    }
                }
                this.editMenu.paket_minuman = rawMinuman;

                this.editFoodQuery = '';
                this.editDrinkQuery = '';

                this.editHargaRaw = menu.harga;
                this.editHargaFormatted = formatRupiahHelper(menu.harga);
                this.editModal = true;
            }
        }
    }
</script>
@endsection

