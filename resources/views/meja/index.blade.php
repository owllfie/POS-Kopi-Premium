@extends('layouts.app')

@section('title', 'Kelola Meja')
@section('page_title', 'Kelola Meja Restoran')

@section('content')
<div class="space-y-6" x-data="mejaManager()">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <!-- Tabs -->
        <div class="flex border-b border-coffee-latte">
            <a href="{{ route('meja.index', ['trash' => '0']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ !$viewTrash ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Meja Aktif
            </a>
            <a href="{{ route('meja.index', ['trash' => '1']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $viewTrash ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Tong Sampah
            </a>
        </div>

        @if(!$viewTrash)
            <button @click="addModal = true" class="px-4 py-2 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
                <span>Tambah Meja Baru</span>
            </button>
        @endif
    </div>

    <!-- Meja Grid/Table -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                        <th class="pb-3">No. Meja</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                    @forelse($mejas as $m)
                        @php
                            $qrUrl = request()->getSchemeAndHttpHost() . '/menu/' . $m->qrcode_token;
                        @endphp
                        <tr>
                            <td class="py-3.5 font-bold text-base text-coffee-dark">Meja {{ $m->nomor_meja }}</td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider
                                    {{ $m->status === 'kosong' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-coffee-light' }}"
                                >
                                    {{ $m->status }}
                                </span>
                            </td>
                            <td class="py-3.5 text-right">
                                <div class="flex justify-end gap-1.5">
                                    @if(!$viewTrash)
                                        <button @click="openQr('{{ $qrUrl }}', '{{ $m->nomor_meja }}')" class="p-1.5 rounded-lg hover:bg-slate-100 text-coffee-light hover:text-coffee-dark transition cursor-pointer" title="Tampilkan QR Code">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12h.008v.008H15V12Zm2.25 0h.008v.008H17.25V12Zm-2.25 2.25h.008v.008H15v-.008Zm2.25 0h.008v.008H17.25v-.008Zm0 2.25h.008v.008H17.25V16.5Zm-2.25 0h.008v.008H15V16.5Zm-2.25-2.25h.008v.008H12.75v-.008Zm0 2.25h.008v.008H12.75V16.5Z" />
                                            </svg>
                                        </button>
                                        <button @click="openEdit({{ json_encode($m) }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-coffee-light hover:text-coffee-dark transition cursor-pointer" title="Ubah Meja">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form action="{{ route('meja.delete', $m->id_meja) }}" method="POST" onsubmit="return confirm('Hapus meja ini?')">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus Meja">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('meja.restore', $m->id_meja) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 hover:text-emerald-700 transition cursor-pointer" title="Aktifkan Kembali">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('meja.forceDelete', $m->id_meja) }}" method="POST" onsubmit="return confirm('Hapus PERMANEN meja ini?')">
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
                            <td colspan="3" class="py-8 text-center text-coffee-light font-medium">Tidak ada data meja.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Links -->
    <div class="mt-6 no-print">
        {{ $mejas->links() }}
    </div>

    <!-- ADD MEJA MODAL -->
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
                    <h3 class="font-extrabold text-coffee-dark">Registrasi Meja Baru</h3>
                </div>

                <form action="{{ route('meja.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="nomor_meja" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nomor Meja</label>
                        <input type="number" name="nomor_meja" id="nomor_meja" required min="1" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Contoh: 9">
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="addModal = false" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- EDIT MEJA MODAL -->
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
                    <h3 class="font-extrabold text-coffee-dark">Ubah Nomor Meja</h3>
                </div>

                <form :action="`{{ url('/meja/update') }}/${editMeja.id_meja}`" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="edit_nomor_meja" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nomor Meja</label>
                        <input type="number" name="nomor_meja" id="edit_nomor_meja" x-model="editMeja.nomor_meja" required min="1" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="editModal = false" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- QR CODE MODAL -->
    <template x-teleport="body">
        <div 
            x-show="qrModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition
            style="display: none;"
        >
            <div 
                @click.away="qrModal = false" 
                class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-sm w-full space-y-4 coffee-card text-center"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark">QR Code Meja <span x-text="qrTableNum"></span></h3>
                    <button @click="qrModal = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs">Tutup</button>
                </div>

                <div class="flex flex-col items-center justify-center py-4 bg-amber-50/30 rounded-2xl border border-coffee-latte/50">
                    <!-- Dynamic scannable QR Code image -->
                    <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(qrCodeUrl)" alt="QR Code Meja" class="w-48 h-48 bg-white p-2 rounded-xl shadow-md border border-coffee-latte">
                    
                    <p class="text-[10px] text-coffee-light font-bold uppercase tracking-wider mt-3">Scan untuk memesan makanan</p>
                    <a :href="qrCodeUrl" target="_blank" class="text-xs font-bold text-blue-600 hover:text-blue-800 underline mt-1 break-all" x-text="qrCodeUrl"></a>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
    function mejaManager() {
        return {
            addModal: false,
            editModal: false,
            qrModal: false,
            editMeja: {},
            qrCodeUrl: '',
            qrTableNum: '',
            
            openEdit(meja) {
                this.editMeja = {...meja};
                this.editModal = true;
            },
            openQr(url, number) {
                this.qrCodeUrl = url;
                this.qrTableNum = number;
                this.qrModal = true;
            }
        }
    }
</script>
@endsection
