@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('page_title', 'Audit Log Aktivitas')

@section('content')
<div class="space-y-6">

    <!-- Filters Panel -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card">
        <form action="{{ route('log') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label for="user_id" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Filter User</label>
                <select name="user_id" id="user_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                    <option value="semua" {{ $userId === 'semua' ? 'selected' : '' }}>Semua User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id_user }}" {{ $userId == $user->id_user ? 'selected' : '' }}>{{ $user->username }} ({{ $user->role->role }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="search" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Cari Aktivitas / Detail</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    value="{{ $search }}"
                    placeholder="Contoh: LOGIN, UPDATE_MENU, dll"
                    class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"
                >
            </div>

            <div class="flex gap-2">
                <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow cursor-pointer">
                    Cari
                </button>
                <a href="{{ route('log') }}" class="w-1/2 text-center py-2.5 border border-coffee-light text-coffee-dark rounded-xl text-xs font-bold hover:bg-coffee-latte transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                        <th class="pb-3">User</th>
                        <th class="pb-3">Aktivitas</th>
                        <th class="pb-3">Detail Deskripsi</th>
                        <th class="pb-3">IP Address</th>
                        <th class="pb-3">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark text-xs">
                    @forelse($logs as $log)
                        <tr>
                            <td class="py-3 font-bold text-coffee-dark">{{ $log->user ? $log->user->username : 'Guest / System' }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider bg-slate-50 border-slate-200 text-slate-700">
                                    {{ $log->aktivitas }}
                                </span>
                            </td>
                            <td class="py-3 text-coffee-medium font-medium">{{ $log->detail_aktivitas }}</td>
                            <td class="py-3 text-coffee-light tracking-wide">{{ $log->ip_address }}</td>
                            <td class="py-3 text-coffee-light font-medium">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-coffee-light font-medium text-sm">Belum ada catatan log aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Links -->
    <div class="mt-6 no-print">
        {{ $logs->links() }}
    </div>

</div>
@endsection
