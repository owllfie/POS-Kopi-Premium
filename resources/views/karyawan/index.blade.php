@extends('layouts.app')

@section('title', 'Kelola Karyawan')
@section('page_title', 'Kelola Karyawan')

@section('content')
<div class="space-y-6" x-data="karyawanManager()">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <!-- Tabs -->
        <div class="flex border-b border-coffee-latte">
            <a href="{{ route('karyawan.index', ['trash' => '0']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ !$viewTrash ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Karyawan Aktif
            </a>
            <a href="{{ route('karyawan.index', ['trash' => '1']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $viewTrash ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Tong Sampah (Trash)
            </a>
        </div>

        @if(!$viewTrash)
            <button @click="addModal = true" class="px-4 py-2 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
                <span>Tambah Karyawan Baru</span>
            </button>
        @endif
    </div>

    <!-- Karyawan Table Card -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                        <th class="pb-3">Nama Karyawan</th>
                        <th class="pb-3">Pekerjaan</th>
                        <th class="pb-3">Gaji Bulanan</th>
                        <th class="pb-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                    @forelse($karyawans as $k)
                        <tr>
                            <td class="py-3.5 font-bold text-coffee-dark">{{ $k->nama_karyawan }}</td>
                            <td class="py-3.5 text-xs">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider bg-amber-50 border-amber-200 text-coffee-light">
                                    {{ $k->pekerjaan }}
                                </span>
                            </td>
                            <td class="py-3.5 text-xs text-coffee-medium font-bold">Rp {{ number_format($k->gaji, 0, ',', '.') }}</td>
                            <td class="py-3.5 text-right">
                                <div class="flex justify-end gap-1.5">
                                    @if(!$viewTrash)
                                        <button @click="openEdit({{ json_encode($k) }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-coffee-light hover:text-coffee-dark transition cursor-pointer" title="Edit Karyawan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form action="{{ route('karyawan.delete', $k->id_karyawan) }}" method="POST" onsubmit="return confirm('Nonaktifkan karyawan ini?')">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus (Nonaktifkan)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('karyawan.restore', $k->id_karyawan) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 hover:text-emerald-700 transition cursor-pointer" title="Aktifkan Kembali">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('karyawan.forceDelete', $k->id_karyawan) }}" method="POST" onsubmit="return confirm('Hapus PERMANEN karyawan ini? Tindakan tidak dapat dibatalkan.')">
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
                            <td colspan="5" class="py-8 text-center text-coffee-light font-medium">Tidak ada data karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Links -->
    <div class="mt-6 no-print">
        {{ $karyawans->links() }}
    </div>

    <!-- ADD KARYAWAN MODAL -->
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
                    <h3 class="font-extrabold text-coffee-dark">Registrasi Karyawan Baru</h3>
                </div>

                <form action="{{ route('karyawan.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="nama_karyawan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Karyawan</label>
                        <input type="text" name="nama_karyawan" id="nama_karyawan" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
                    
                    <div>
                        <label for="pekerjaan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Pekerjaan</label>
                        <select name="pekerjaan" id="pekerjaan" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                            @foreach($pekerjaanList as $pekerjaan)
                                <option value="{{ $pekerjaan }}">{{ $pekerjaan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="gaji" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Gaji Bulanan</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="text" x-model="addGajiFormatted" @input="addGajiRaw = addGajiFormatted.replace(/[^0-9]/g, ''); addGajiFormatted = formatRupiahHelper(addGajiRaw)" required class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white" placeholder="0">
                            <input type="hidden" name="gaji" :value="addGajiRaw">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="addModal = false" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- EDIT KARYAWAN MODAL -->
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
                    <h3 class="font-extrabold text-coffee-dark">Ubah Data Karyawan</h3>
                    <button @click="editModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>

                <form :action="`{{ url('/karyawan/update') }}/${editKaryawan.id_karyawan}`" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="edit_nama_karyawan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Karyawan</label>
                        <input type="text" name="nama_karyawan" id="edit_nama_karyawan" x-model="editKaryawan.nama_karyawan" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
                    
                    <div>
                        <label for="edit_pekerjaan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Pekerjaan</label>
                        <select name="pekerjaan" id="edit_pekerjaan" x-model="editKaryawan.pekerjaan" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                            @foreach($pekerjaanList as $pekerjaan)
                                <option value="{{ $pekerjaan }}">{{ $pekerjaan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="edit_gaji" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Gaji Bulanan</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="text" x-model="editGajiFormatted" @input="editGajiRaw = editGajiFormatted.replace(/[^0-9]/g, ''); editGajiFormatted = formatRupiahHelper(editGajiRaw)" required class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white">
                            <input type="hidden" name="gaji" :value="editGajiRaw">
                        </div>
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
    function karyawanManager() {
        return {
            addModal: false,
            editModal: false,
            editKaryawan: {},
            addGajiFormatted: '',
            addGajiRaw: '',
            editGajiFormatted: '',
            editGajiRaw: '',
            
            openEdit(karyawan) {
                this.editKaryawan = {...karyawan};
                this.editGajiRaw = karyawan.gaji;
                this.editGajiFormatted = formatRupiahHelper(karyawan.gaji);
                this.editModal = true;
            }
        }
    }
</script>
@endsection
