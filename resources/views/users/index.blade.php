@extends('layouts.app')

@section('title', 'Kelola Users')
@section('page_title', 'Kelola Akun')

@section('content')
<div class="space-y-6" x-data="userManager()">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <!-- Tabs -->
        <div class="flex border-b border-coffee-latte">
            <a href="{{ route('users.index', ['tab' => 'active']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'active' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                User Aktif
            </a>
            <a href="{{ route('users.index', ['tab' => 'trash']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'trash' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Tong Sampah (Trash)
            </a>
            <a href="{{ route('users.index', ['tab' => 'history']) }}" class="px-6 py-2.5 font-bold text-xs border-b-2 transition {{ $tab === 'history' ? 'border-coffee-dark text-coffee-dark' : 'border-transparent text-coffee-light hover:text-coffee-dark' }}">
                Riwayat Perubahan
            </a>
        </div>

        @if($tab === 'active')
            <button @click="addModal = true" class="px-4 py-2 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
                <span>Tambah Akun Baru</span>
            </button>
        @endif
    </div>

    @if($tab === 'active' || $tab === 'trash')
        <!-- Users Table Card -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                            <th class="pb-3">Username</th>
                            <th class="pb-3">Email</th>
                            <th class="pb-3">Role</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                        @forelse($users as $u)
                            <tr>
                                <td class="py-3.5 font-bold text-coffee-dark">{{ $u->username }}</td>
                                <td class="py-3.5 text-xs text-coffee-light">{{ $u->email }}</td>
                                <td class="py-3.5 text-xs uppercase font-bold text-coffee-medium">{{ $u->role->role }}</td>
                                <td class="py-3.5 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        @if($tab === 'active')
                                            <button @click="openEdit({{ json_encode($u) }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-coffee-light hover:text-coffee-dark transition cursor-pointer" title="Edit Akun">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form action="{{ route('users.delete', $u->id_user) }}" method="POST" onsubmit="return confirm('Nonaktifkan staff ini?')">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus (Nonaktifkan)">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('users.restore', $u->id_user) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 hover:text-emerald-700 transition cursor-pointer" title="Aktifkan Kembali">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('users.forceDelete', $u->id_user) }}" method="POST" onsubmit="return confirm('Hapus PERMANEN akun staff ini? Tindakan tidak dapat dibatalkan.')">
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
                                <td colspan="4" class="py-8 text-center text-coffee-light font-medium">Tidak ada data staff.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Links -->
        <div class="mt-6 no-print">
            {{ $users->links() }}
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
                                                @if($key === 'id_role')
                                                    @php $oldRole = \App\Models\Role::find($oldVal); @endphp
                                                    {{ $oldRole ? $oldRole->role : $oldVal }}
                                                @elseif($key === 'password')
                                                    [PROTECTED]
                                                @else
                                                    {{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}
                                                @endif
                                            </span>
                                            <span class="text-coffee-light mx-1">&rarr;</span>
                                            <span class="text-emerald-600">
                                                @if($key === 'id_role')
                                                    @php $newRole = \App\Models\Role::find($newVal); @endphp
                                                    {{ $newRole ? $newRole->role : $newVal }}
                                                @elseif($key === 'password')
                                                    [PROTECTED]
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

    <!-- ADD USER MODAL -->
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
                    <h3 class="font-extrabold text-coffee-dark">Registrasi Staff Baru</h3>
                </div>

                <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="username" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Username / Nama</label>
                        <input type="text" name="username" id="username" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Email Login</label>
                        <input type="email" name="email" id="email" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
                    <div>
                        <label for="password" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Password</label>
                        <input type="password" name="password" id="password" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Min. 6 karakter">
                    </div>
                    <div>
                        <label for="id_role" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Role</label>
                        <select name="id_role" id="id_role" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                            @foreach($roles as $r)
                                <option value="{{ $r->id_role }}">{{ strtoupper($r->role) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="addModal = false" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- EDIT USER MODAL -->
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
                    <h3 class="font-extrabold text-coffee-dark">Ubah Akun Staff</h3>
                </div>

                <form :action="`{{ url('/users/update') }}/${editUser.id_user}`" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="edit_username" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Username / Nama</label>
                        <input type="text" name="username" id="edit_username" x-model="editUser.username" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
                    <div>
                        <label for="edit_email" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Email Login</label>
                        <input type="email" name="email" id="edit_email" x-model="editUser.email" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    </div>
                    <div>
                        <label for="edit_password" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Password Baru (Biarkan kosong jika tidak diubah)</label>
                        <input type="password" name="password" id="edit_password" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Min. 6 karakter">
                    </div>
                    <div>
                        <label for="edit_role" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Role</label>
                        <select name="id_role" id="edit_role" x-model="editUser.id_role" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                            @foreach($roles as $r)
                                <option value="{{ $r->id_role }}">{{ strtoupper($r->role) }}</option>
                            @endforeach
                        </select>
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
    function userManager() {
        return {
            addModal: false,
            editModal: false,
            editUser: {},
            
            openEdit(user) {
                this.editUser = {...user};
                this.editModal = true;
            }
        }
    }
</script>
@endsection

