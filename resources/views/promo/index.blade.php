@extends('layouts.app')

@section('title', 'Kelola Promo')
@section('page_title', 'Kelola Promo Restoran')

@section('content')
<div class="space-y-6" x-data="promoManager()">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <!-- Tabs -->
        <div class="flex border-b border-coffee-latte">
            <a href="{{ route('promo.index', ['tab' => 'active']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'active' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Promo Aktif
            </a>
            <a href="{{ route('promo.index', ['tab' => 'trash']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'trash' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Tong Sampah
            </a>
            <a href="{{ route('promo.index', ['tab' => 'history']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'history' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Riwayat Perubahan
            </a>
        </div>

        @if($tab === 'active')
            <button @click="addModal = true" class="px-4 py-2 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
                <span>Tambah Promo Baru</span>
            </button>
        @endif
    </div>

    @if($tab === 'active' || $tab === 'trash')
        <!-- Promo Grid/Table -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                            <th class="pb-3">Nama Promo</th>
                            <th class="pb-3">Potongan</th>
                            <th class="pb-3">Periode</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                        @forelse($promos as $p)
                            <tr>
                                <td class="py-3.5">
                                    <div class="font-bold text-coffee-dark">{{ $p->nama_promo }}</div>
                                    <div class="text-xs text-coffee-light">{{ $p->deskripsi ?? '-' }}</div>
                                    @if($p->menu_ids && count($p->menu_ids) > 0)
                                        <div class="flex flex-wrap gap-1 mt-1.5">
                                            <span class="text-[9px] font-bold text-coffee-light uppercase tracking-wider block mr-1 self-center">Berlaku:</span>
                                            @foreach($p->menu_ids as $mId)
                                                @php $menuItem = $menus->firstWhere('id_menu', $mId); @endphp
                                                @if($menuItem)
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 border border-amber-200 text-coffee-light">{{ $menuItem->nama_menu }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5">
                                    <span class="font-bold text-coffee-dark">
                                        Rp {{ number_format($p->nominal_potongan, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3.5 text-xs">
                                    <div>Mulai: {{ $p->start_time ? $p->start_time->format('d M Y H:i') : 'Selamanya' }}</div>
                                    <div>Selesai: {{ $p->end_time ? $p->end_time->format('d M Y H:i') : 'Selamanya' }}</div>
                                </td>
                                <td class="py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider
                                        {{ $p->status === 'Aktif' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}"
                                    >
                                        {{ $p->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        @if($tab === 'active')
                                            <button @click="openEdit({{ json_encode($p) }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-coffee-light hover:text-coffee-dark transition cursor-pointer" title="Ubah Promo">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form action="{{ route('promo.delete', $p->id_promo) }}" method="POST" onsubmit="return confirm('Pindahkan promo ini ke tong sampah?')">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus Promo">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('promo.restore', $p->id_promo) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 hover:text-emerald-700 transition cursor-pointer" title="Aktifkan Kembali">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('promo.force-delete', $p->id_promo) }}" method="POST" onsubmit="return confirm('Hapus PERMANEN promo ini?')">
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
                                <td colspan="5" class="py-8 text-center text-coffee-light font-medium">Tidak ada data promo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Links -->
        <div class="mt-6 no-print">
            {{ $promos->links() }}
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
                                            <span class="text-rose-500 line-through">
                                                @if($key === 'menu_ids' && is_array($oldVal))
                                                    @php
                                                        $names = collect($oldVal)->map(fn($id) => optional($menus->firstWhere('id_menu', $id))->nama_menu ?? $id)->implode(', ');
                                                    @endphp
                                                    [{{ $names ?: 'Semua Menu' }}]
                                                @else
                                                    {{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}
                                                @endif
                                            </span>
                                            <span class="text-coffee-light mx-1">&rarr;</span>
                                            <span class="text-emerald-600">
                                                @if($key === 'menu_ids' && is_array($newVal))
                                                    @php
                                                        $names = collect($newVal)->map(fn($id) => optional($menus->firstWhere('id_menu', $id))->nama_menu ?? $id)->implode(', ');
                                                    @endphp
                                                    [{{ $names ?: 'Semua Menu' }}]
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

    <!-- ADD PROMO MODAL -->
    <template x-teleport="body">
        <div 
            x-show="addModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition
            style="display: none;"
        >
            <div 
                @click.away="addModal = false" 
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-lg w-full space-y-4 coffee-card overflow-y-auto max-h-[90vh]"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark">Registrasi Promo Baru</h3>
                </div>

                <form action="{{ route('promo.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nama_promo" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Promo</label>
                            <input type="text" name="nama_promo" id="nama_promo" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Contoh: Diskon Kopi Senja">
                        </div>

                        <div>
                            <label for="tipe_promo" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Tipe Promo</label>
                            <select name="tipe_promo" id="tipe_promo" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="Harian">Harian</option>
                                <option value="Mingguan">Mingguan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Sekali Pakai">Sekali Pakai</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nominal_potongan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Besar Potongan (Rp)</label>
                            <input type="number" name="nominal_potongan" id="nominal_potongan" required min="0" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Contoh: 10000">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_time" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Waktu Mulai</label>
                            <input type="datetime-local" name="start_time" id="start_time" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                        </div>

                        <div>
                            <label for="end_time" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Waktu Selesai</label>
                            <input type="datetime-local" name="end_time" id="end_time" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                        </div>
                    </div>

                    <!-- Menu Search and Suggestion Select -->
                    <div class="space-y-2 relative" x-data="{ open: false }" @click.outside="open = false">
                        <label class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-1">Berlaku untuk Menu Tertentu (Kosongkan jika berlaku untuk semua menu)</label>
                        <div class="relative">
                            <input type="text" x-model="menuQuery" @focus="open = true" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Cari makanan atau minuman...">
                        </div>
                        
                        <!-- Suggestions list -->
                        <div x-show="open && filteredMenus().length > 0" class="absolute z-10 w-full mt-1 bg-white border border-coffee-latte rounded-xl shadow-lg max-h-48 overflow-y-auto" style="display: none;">
                            <template x-for="menu in filteredMenus()" :key="menu.id_menu">
                                <button type="button" @click="selectMenu(menu.id_menu); open = false" class="w-full text-left px-4 py-2 hover:bg-coffee-cream text-xs font-bold text-coffee-dark transition border-b border-coffee-cream/50 last:border-b-0 flex justify-between items-center">
                                    <span x-text="menu.nama_menu"></span>
                                    <span class="text-coffee-medium text-[10px]" x-text="'Rp ' + menu.harga.toLocaleString('id-ID')"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Selected items badges -->
                        <div class="flex flex-wrap gap-2 mt-2">
                            <template x-for="menuId in selectedMenuIds" :key="menuId">
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-coffee-cream border border-coffee-latte rounded-xl text-[11px] font-bold text-coffee-dark select-none">
                                    <span x-text="getMenuName(menuId)"></span>
                                    <input type="hidden" name="menu_ids[]" :value="menuId">
                                    <button type="button" @click="removeMenu(menuId)" class="text-rose-500 hover:text-rose-700 font-extrabold focus:outline-none">&times;</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Status</label>
                        <select name="status" id="status" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>

                    <div>
                        <label for="deskripsi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Deskripsi promo..."></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="addModal = false; selectedMenuIds = []; menuQuery = ''" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- EDIT PROMO MODAL -->
    <template x-teleport="body">
        <div 
            x-show="editModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition
            style="display: none;"
        >
            <div 
                @click.away="editModal = false" 
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-lg w-full space-y-4 coffee-card overflow-y-auto max-h-[90vh]"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark">Ubah Detail Promo</h3>
                </div>

                <form :action="`{{ url('/promo/update') }}/${editPromo.id_promo}`" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_nama_promo" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Promo</label>
                            <input type="text" name="nama_promo" id="edit_nama_promo" x-model="editPromo.nama_promo" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                        </div>

                        <div>
                            <label for="edit_tipe_promo" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Tipe Promo</label>
                            <select name="tipe_promo" id="edit_tipe_promo" x-model="editPromo.tipe_promo" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="Harian">Harian</option>
                                <option value="Mingguan">Mingguan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Sekali Pakai">Sekali Pakai</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_nominal_potongan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Besar Potongan (Rp)</label>
                            <input type="number" name="nominal_potongan" id="edit_nominal_potongan" x-model="editPromo.nominal_potongan" required min="0" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_start_time" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Waktu Mulai</label>
                            <input type="datetime-local" name="start_time" id="edit_start_time" x-model="editPromo.start_time" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                        </div>

                        <div>
                            <label for="edit_end_time" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Waktu Selesai</label>
                            <input type="datetime-local" name="end_time" id="edit_end_time" x-model="editPromo.end_time" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                        </div>
                    </div>

                    <!-- Menu Search and Suggestion Select (Edit) -->
                    <div class="space-y-2 relative" x-data="{ open: false }" @click.outside="open = false">
                        <label class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-1">Berlaku untuk Menu Tertentu (Kosongkan jika berlaku untuk semua menu)</label>
                        <div class="relative">
                            <input type="text" x-model="editMenuQuery" @focus="open = true" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Cari makanan atau minuman...">
                        </div>
                        
                        <!-- Suggestions list -->
                        <div x-show="open && filteredEditMenus().length > 0" class="absolute z-10 w-full mt-1 bg-white border border-coffee-latte rounded-xl shadow-lg max-h-48 overflow-y-auto" style="display: none;">
                            <template x-for="menu in filteredEditMenus()" :key="menu.id_menu">
                                <button type="button" @click="selectEditMenu(menu.id_menu); open = false" class="w-full text-left px-4 py-2 hover:bg-coffee-cream text-xs font-bold text-coffee-dark transition border-b border-coffee-cream/50 last:border-b-0 flex justify-between items-center">
                                    <span x-text="menu.nama_menu"></span>
                                    <span class="text-coffee-medium text-[10px]" x-text="'Rp ' + menu.harga.toLocaleString('id-ID')"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Selected items badges -->
                        <div class="flex flex-wrap gap-2 mt-2">
                            <template x-for="menuId in editPromo.menu_ids || []" :key="menuId">
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-coffee-cream border border-coffee-latte rounded-xl text-[11px] font-bold text-coffee-dark select-none">
                                    <span x-text="getMenuName(menuId)"></span>
                                    <input type="hidden" name="menu_ids[]" :value="menuId">
                                    <button type="button" @click="removeEditMenu(menuId)" class="text-rose-500 hover:text-rose-700 font-extrabold focus:outline-none">&times;</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label for="edit_status" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Status</label>
                        <select name="status" id="edit_status" x-model="editPromo.status" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>

                    <div>
                        <label for="edit_deskripsi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" x-model="editPromo.deskripsi" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="editModal = false; editMenuQuery = ''" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>

<script>
    function promoManager() {
        return {
            addModal: false,
            editModal: false,
            editPromo: {},
            
            allMenus: @json($menus),
            selectedMenuIds: [],
            menuQuery: '',
            editMenuQuery: '',

            filteredMenus() {
                if (!this.menuQuery) return [];
                const query = this.menuQuery.toLowerCase();
                return this.allMenus.filter(m => m.nama_menu.toLowerCase().includes(query) && !this.selectedMenuIds.includes(String(m.id_menu)) && !this.selectedMenuIds.includes(Number(m.id_menu)));
            },
            
            filteredEditMenus() {
                if (!this.editMenuQuery) return [];
                const query = this.editMenuQuery.toLowerCase();
                const selected = this.editPromo.menu_ids || [];
                return this.allMenus.filter(m => m.nama_menu.toLowerCase().includes(query) && !selected.includes(String(m.id_menu)) && !selected.includes(Number(m.id_menu)));
            },

            selectMenu(menuId) {
                this.selectedMenuIds.push(menuId);
                this.menuQuery = '';
            },

            removeMenu(menuId) {
                this.selectedMenuIds = this.selectedMenuIds.filter(id => id != menuId);
            },
            
            selectEditMenu(menuId) {
                if (!this.editPromo.menu_ids) {
                    this.editPromo.menu_ids = [];
                }
                this.editPromo.menu_ids.push(menuId);
                this.editMenuQuery = '';
            },

            removeEditMenu(menuId) {
                this.editPromo.menu_ids = this.editPromo.menu_ids.filter(id => id != menuId);
            },

            getMenuName(menuId) {
                const m = this.allMenus.find(item => item.id_menu == menuId);
                return m ? m.nama_menu : 'Unknown';
            },

            openEdit(promo) {
                let start = promo.start_time ? promo.start_time.replace(' ', 'T').substring(0, 16) : '';
                let end = promo.end_time ? promo.end_time.replace(' ', 'T').substring(0, 16) : '';
                
                this.editPromo = {
                    ...promo,
                    menu_ids: promo.menu_ids ? [...promo.menu_ids] : [],
                    start_time: start,
                    end_time: end
                };
                this.editModal = true;
            }
        }
    }
</script>
@endsection
