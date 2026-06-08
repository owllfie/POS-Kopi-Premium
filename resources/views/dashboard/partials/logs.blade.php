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
                <tr class="transition hover:bg-coffee-latte/10">
                    <td class="py-3 font-bold text-coffee-dark">
                        {{ $log->user ? $log->user->username : 'Guest / System' }}
                    </td>
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
