@extends('layouts.app')

@section('title', 'Database Backup')
@section('page_title', 'Backup Database & Disaster Recovery')

@section('content')
<div class="space-y-6">

    <!-- Header Panel -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-extrabold text-coffee-dark">Disaster Recovery Center</h3>
            <p class="text-xs text-coffee-light font-medium mt-0.5">Amankan data transaksi POS Anda dengan mencadangkan database SQL berkala.</p>
        </div>
        <form action="{{ route('backup.create') }}" method="POST">
            @csrf
            <button type="submit" class="px-5 py-2.5 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-2 cursor-pointer">
                <svg class="w-4.5 h-4.5 text-coffee-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Backup Database Sekarang</span>
            </button>
        </form>
    </div>

    <!-- Backups Table List -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card space-y-4">
        <h4 class="font-bold text-coffee-dark border-b border-coffee-latte pb-3">Daftar Cadangan Backup SQL</h4>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                        <th class="pb-3">Nama File Backup</th>
                        <th class="pb-3">Ukuran File</th>
                        <th class="pb-3">Waktu Pencadangan</th>
                        <th class="pb-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                    @forelse($files as $file)
                        <tr>
                            <td class="py-3.5 font-mono text-xs text-coffee-dark font-semibold">{{ $file['filename'] }}</td>
                            <td class="py-3.5 text-xs text-coffee-medium">{{ $file['size'] }} KB</td>
                            <td class="py-3.5 text-xs text-coffee-light font-semibold">{{ $file['created_at'] }}</td>
                            <td class="py-3.5 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('backup.download', $file['filename']) }}" class="p-1.5 rounded-lg hover:bg-amber-50 text-coffee-light hover:text-coffee-dark transition flex items-center justify-center" title="Unduh File Backup">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                    <form action="{{ route('backup.delete', $file['filename']) }}" method="POST" onsubmit="return confirm('Hapus file backup cadangan ini?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus Backup">
                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-coffee-light font-medium">Belum ada file backup cadangan database.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links for Backup Files -->
        <div class="mt-6 no-print">
            {{ $files->links() }}
        </div>
    </div>

</div>
@endsection
