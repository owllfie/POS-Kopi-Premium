@extends('layouts.app')

@section('title', 'Kelola Jabatan')
@section('page_title', 'Kelola Jabatan Karyawan')

@section('content')
<div class="space-y-6" x-data="jabatanManager()">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <!-- Tabs -->
        <div class="flex border-b border-coffee-latte">
            <a href="{{ route('jabatan.index', ['tab' => 'active']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'active' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Jabatan Aktif
            </a>
            <a href="{{ route('jabatan.index', ['tab' => 'trash']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'trash' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Tong Sampah (Trash)
            </a>
            <a href="{{ route('jabatan.index', ['tab' => 'history']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'history' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Riwayat Perubahan
            </a>
        </div>

        @if($tab === 'active')
            <button @click="addModal = true" class="px-4 py-2 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
                <span>Tambah Jabatan Baru</span>
            </button>
        @endif
    </div>

    <!-- Active Tab -->
    @if($tab === 'active' || $tab === 'trash')
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                            <th class="pb-3">Nama Jabatan</th>
                            <th class="pb-3">Gaji Standar</th>
                            <th class="pb-3">Deskripsi</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                        @forelse($jabatans as $j)
                            <tr>
                                <td class="py-3.5 font-bold text-coffee-dark">{{ $j->nama_jabatan }}</td>
                                <td class="py-3.5 text-xs text-coffee-medium font-bold">Rp {{ number_format($j->gaji_standar, 0, ',', '.') }}</td>
                                <td class="py-3.5 text-xs text-coffee-light">{{ $j->deskripsi ?? '-' }}</td>
                                <td class="py-3.5 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        @if($tab === 'active')
                                            <button @click="openEdit({{ json_encode($j) }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-coffee-light hover:text-coffee-dark transition cursor-pointer" title="Edit Jabatan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form action="{{ route('jabatan.delete', $j->id_jabatan) }}" method="POST" onsubmit="return confirm('Nonaktifkan jabatan ini?')">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus (Nonaktifkan)">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('jabatan.restore', $j->id_jabatan) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 hover:text-emerald-700 transition cursor-pointer" title="Aktifkan Kembali">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('jabatan.forceDelete', $j->id_jabatan) }}" method="POST" onsubmit="return confirm('Hapus PERMANEN jabatan ini? Semua karyawan dengan jabatan ini akan kehilangan relasi jabatan.')">
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
                                <td colspan="4" class="py-8 text-center text-coffee-light font-medium">Tidak ada data jabatan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">
            {{ $jabatans->links() }}
        </div>
    @endif

    <!-- History Tab -->
    @if($tab === 'history')
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
        <div class="mt-6">
            {{ $historyUpdates->links() }}
        </div>
    @endif

    <!-- ADD JABATAN MODAL -->
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
                    <h3 class="font-extrabold text-coffee-dark">Registrasi Jabatan Baru</h3>
                </div>

                <form action="{{ route('jabatan.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="nama_jabatan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Jabatan</label>
                        <input type="text" name="nama_jabatan" id="nama_jabatan" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>

                    <div>
                        <label for="gaji_standar" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Gaji Standar Bulanan</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="text" x-model="addGajiFormatted" @input="addGajiRaw = addGajiFormatted.replace(/[^0-9]/g, ''); addGajiFormatted = formatRupiahHelper(addGajiRaw)" required class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white" placeholder="0">
                            <input type="hidden" name="gaji_standar" :value="addGajiRaw">
                        </div>
                    </div>

                    <div>
                        <label for="deskripsi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Deskripsi Tugas</label>
                        <textarea name="deskripsi" id="deskripsi" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Penjelasan tugas pokok jabatan..."></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="addModal = false" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- EDIT JABATAN MODAL -->
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
                    <h3 class="font-extrabold text-coffee-dark">Ubah Data Jabatan</h3>
                    <button @click="editModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>

                <form :action="`{{ url('/jabatan/update') }}/${editJabatan.id_jabatan}`" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="edit_nama_jabatan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Jabatan</label>
                        <input type="text" name="nama_jabatan" id="edit_nama_jabatan" x-model="editJabatan.nama_jabatan" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>

                    <div>
                        <label for="edit_gaji_standar" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Gaji Standar Bulanan</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="text" x-model="editGajiFormatted" @input="editGajiRaw = editGajiFormatted.replace(/[^0-9]/g, ''); editGajiFormatted = formatRupiahHelper(editGajiRaw)" required class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white">
                            <input type="hidden" name="gaji_standar" :value="editGajiRaw">
                        </div>
                    </div>

                    <div>
                        <label for="edit_deskripsi" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Deskripsi Tugas</label>
                        <textarea name="deskripsi" id="edit_deskripsi" x-model="editJabatan.deskripsi" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>

<script>
    function formatRupiahHelper(angka) {
        if (!angka) return '0';
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function jabatanManager() {
        return {
            addModal: false,
            editModal: false,
            editJabatan: {},
            addGajiFormatted: '',
            addGajiRaw: '0',
            editGajiFormatted: '0',
            editGajiRaw: '0',

            openEdit(jabatan) {
                this.editJabatan = {...jabatan};
                this.editGajiRaw = jabatan.gaji_standar;
                this.editGajiFormatted = formatRupiahHelper(jabatan.gaji_standar);
                this.editModal = true;
            }
        }
    }
</script>
@endsection
