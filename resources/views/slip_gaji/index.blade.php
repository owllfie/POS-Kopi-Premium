@extends('layouts.app')

@section('title', 'Slip Gaji Karyawan')
@section('page_title', 'Kelola Slip Gaji Karyawan')

@section('styles')
<style>
    @media print {
        /* Hide all UI elements except the slip area */
        body * {
            visibility: hidden;
        }
        #printable-slip-area, #printable-slip-area * {
            visibility: visible;
        }
        #printable-slip-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none !important;
            padding: 20px !important;
            margin: 0 !important;
            background: white !important;
            color: black !important;
        }
        /* Hide print modal action buttons & close triggers */
        .print-hidden {
            display: none !important;
        }
        /* Format page setup */
        @page {
            size: auto;
            margin: 10mm;
        }
    }
</style>
@endsection

@section('content')
<div class="space-y-6" x-data="slipGajiManager()">

    <!-- Stats Cards (Top) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 no-print">
        <!-- Stat 1: Total Gaji Terbayar Bulan Ini -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 shadow-sm flex items-center justify-between coffee-card">
            <div class="space-y-2">
                <p class="text-xs font-bold text-coffee-medium uppercase tracking-wider">Total Beban Gaji ({{ date('F Y') }})</p>
                <h3 class="text-2xl font-black text-coffee-dark">
                    Rp {{ number_format(\App\Models\SlipGaji::where('bulan', date('n'))->where('tahun', date('Y'))->sum('total_gaji'), 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-coffee-gold/15 border border-coffee-gold/20 flex items-center justify-center text-coffee-gold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Stat 2: Slip Diterbitkan -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 shadow-sm flex items-center justify-between coffee-card">
            <div class="space-y-2">
                <p class="text-xs font-bold text-coffee-medium uppercase tracking-wider">Slip Gaji Diterbitkan</p>
                <h3 class="text-2xl font-black text-coffee-dark">
                    {{ \App\Models\SlipGaji::where('bulan', date('n'))->where('tahun', date('Y'))->count() }} Karyawan
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Stat 3: Rata-Rata Gaji Bersih -->
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 shadow-sm flex items-center justify-between coffee-card">
            <div class="space-y-2">
                <p class="text-xs font-bold text-coffee-medium uppercase tracking-wider">Rata-Rata Take Home Pay</p>
                <h3 class="text-2xl font-black text-coffee-dark">
                    @php
                        $avgGaji = \App\Models\SlipGaji::where('bulan', date('n'))->where('tahun', date('Y'))->avg('total_gaji');
                    @endphp
                    Rp {{ number_format($avgGaji ?? 0, 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Header Actions & Filters -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
        <!-- Title and Filter -->
        <div class="flex items-center gap-4">
            <h2 class="text-lg font-bold text-coffee-dark">Daftar Payroll Bulanan</h2>
        </div>

        <!-- Add Button -->
        <button @click="addModal = true" class="px-4 py-2.5 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow flex items-center gap-1.5 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>Buat Slip Gaji</span>
        </button>
    </div>

    <!-- Main Data Table Card -->
    <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card no-print">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-coffee-latte text-xs font-bold text-coffee-light uppercase tracking-wider">
                        <th class="pb-3">Karyawan</th>
                        <th class="pb-3">Periode</th>
                        <th class="pb-3">Gaji Pokok</th>
                        <th class="pb-3">Tunjangan (+)</th>
                        <th class="pb-3">Potongan (-)</th>
                        <th class="pb-3">Total Bersih</th>
                        <th class="pb-3">Pembayaran</th>
                        <th class="pb-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-latte font-medium text-coffee-dark">
                    @php
                        $indoMonths = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                    @endphp
                    @forelse($slips as $s)
                        <tr class="hover:bg-coffee-cream/10 transition-colors">
                            <td class="py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-coffee-latte flex items-center justify-center overflow-hidden border border-coffee-latte/30 shadow-inner">
                                        @if($s->karyawan && $s->karyawan->foto)
                                            <img src="{{ asset($s->karyawan->foto) }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-4 h-4 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-coffee-dark text-xs sm:text-sm">{{ $s->karyawan ? $s->karyawan->nama_karyawan : 'Karyawan Dihapus' }}</p>
                                        <p class="text-[10px] text-coffee-medium tracking-wide uppercase font-semibold">{{ $s->karyawan && $s->karyawan->jabatan ? $s->karyawan->jabatan->nama_jabatan : ($s->karyawan ? $s->karyawan->pekerjaan : '-') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 text-xs text-coffee-dark font-bold">
                                {{ $indoMonths[$s->bulan] ?? '' }} {{ $s->tahun }}
                            </td>
                            <td class="py-3.5 text-xs text-coffee-medium">Rp {{ number_format($s->gaji_pokok, 0, ',', '.') }}</td>
                            <td class="py-3.5 text-xs text-emerald-600 font-semibold">+ Rp {{ number_format($s->tunjangan, 0, ',', '.') }}</td>
                            <td class="py-3.5 text-xs text-rose-500 font-semibold">- Rp {{ number_format($s->potongan, 0, ',', '.') }}</td>
                            <td class="py-3.5 text-xs text-coffee-dark font-black">
                                <span class="bg-coffee-gold/10 px-2.5 py-1.5 rounded-lg border border-coffee-gold/10 text-coffee-gold">
                                    Rp {{ number_format($s->total_gaji, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="py-3.5">
                                <p class="text-xs text-coffee-dark font-bold">{{ $s->tanggal_pembayaran ? \Carbon\Carbon::parse($s->tanggal_pembayaran)->format('d M Y') : '-' }}</p>
                                <p class="text-[10px] text-coffee-light font-extrabold tracking-wider uppercase">{{ $s->metode_pembayaran ?? '-' }}</p>
                            </td>
                            <td class="py-3.5 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="openPrintSlip({{ json_encode($s) }})" class="p-1.5 rounded-lg bg-coffee-cream hover:bg-coffee-latte text-coffee-dark transition cursor-pointer" title="Cetak Slip Gaji">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </button>
                                    <form action="{{ route('slip-gaji.delete', $s->id_slip) }}" method="POST" onsubmit="return confirm('Hapus slip gaji & batalkan pembukuan kas transaksi keuangan ini?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-500 hover:text-rose-700 transition cursor-pointer" title="Hapus Slip Gaji">
                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-coffee-light font-medium">Belum ada slip gaji yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="mt-6 no-print">
        {{ $slips->links() }}
    </div>

    <!-- CREATE NEW SLIP GAJI MODAL -->
    <template x-teleport="body">
        <div x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-transition style="display: none;">
            <div @click.away="addModal = false" class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-6 max-w-lg w-full space-y-4 coffee-card overflow-y-auto max-h-[95vh]">
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark">Buat Slip Gaji Karyawan</h3>
                </div>

                <form action="{{ route('slip-gaji.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Karyawan Select -->
                    <div>
                        <label for="id_karyawan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Pilih Karyawan</label>
                        <select name="id_karyawan" id="id_karyawan" required @change="onEmployeeSelect($event.target.value)" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($karyawans as $k)
                                <option value="{{ $k->id_karyawan }}">{{ $k->nama_karyawan }} ({{ $k->jabatan ? $k->jabatan->nama_jabatan : $k->pekerjaan }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Periode: Bulan & Tahun -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="bulan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Bulan</label>
                            <select name="bulan" id="bulan" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $i == date('n') ? 'selected' : '' }}>{{ $indoMonths[$i] }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label for="tahun" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Tahun</label>
                            <select name="tahun" id="tahun" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Rincian Gaji Pokok -->
                    <div>
                        <label for="gaji_pokok_disp" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Gaji Pokok</label>
                        <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                            <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                            <input type="text" x-model="gajiPokokFormatted" @input="gajiPokokRaw = parseInt(gajiPokokFormatted.replace(/[^0-9]/g, '')) || 0; gajiPokokFormatted = formatRupiah(gajiPokokRaw); calculateTotal();" required class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white" placeholder="0">
                            <input type="hidden" name="gaji_pokok" :value="gajiPokokRaw">
                        </div>
                    </div>

                    <!-- Tunjangan & Potongan -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="tunjangan_disp" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Tunjangan</label>
                            <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                                <span class="px-3 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                                <input type="text" x-model="tunjanganFormatted" @input="tunjanganRaw = parseInt(tunjanganFormatted.replace(/[^0-9]/g, '')) || 0; tunjanganFormatted = formatRupiah(tunjanganRaw); calculateTotal();" required class="w-full px-3 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white">
                                <input type="hidden" name="tunjangan" :value="tunjanganRaw">
                            </div>
                        </div>
                        <div>
                            <label for="potongan_disp" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Potongan</label>
                            <div class="flex rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                                <span class="px-3 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-r border-coffee-latte select-none flex items-center">Rp</span>
                                <input type="text" x-model="potonganFormatted" @input="potonganRaw = parseInt(potonganFormatted.replace(/[^0-9]/g, '')) || 0; potonganFormatted = formatRupiah(potonganRaw); calculateTotal();" required class="w-full px-3 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white">
                                <input type="hidden" name="potongan" :value="potonganRaw">
                            </div>
                        </div>
                    </div>

                    <!-- Total Net Salary (Readonly Display) -->
                    <div class="bg-coffee-cream/40 p-4 rounded-2xl border border-coffee-latte">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-[10px] font-bold text-coffee-medium uppercase tracking-wider">Total Gaji Diterima (Take Home Pay)</p>
                                <h4 class="text-lg font-black text-coffee-dark" x-text="'Rp ' + formatRupiah(totalGajiRaw)">Rp 0</h4>
                            </div>
                            <span class="px-2.5 py-1 bg-coffee-gold/10 border border-coffee-gold/20 text-coffee-gold rounded-lg text-[9px] font-black uppercase tracking-widest">AUTO CALC</span>
                        </div>
                    </div>

                    <!-- Pembayaran: Tanggal & Metode -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_pembayaran" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Tanggal Pembayaran</label>
                            <input type="date" name="tanggal_pembayaran" id="tanggal_pembayaran" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                        </div>
                        <div>
                            <label for="metode_pembayaran" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Metode Pembayaran</label>
                            <select name="metode_pembayaran" id="metode_pembayaran" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer" selected>Transfer</option>
                            </select>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label for="catatan" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Catatan / Keterangan</label>
                        <input type="text" name="catatan" id="catatan" class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white" placeholder="Contoh: Bonus target bulanan, Potongan terlambat 3x">
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="addModal = false" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs">Simpan & Bukukan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- PRINT SLIP MODAL -->
    <template x-teleport="body">
        <div x-show="showPrintModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm print:p-0 print:bg-white" x-transition style="display: none;">
            <div @click.away="showPrintModal = false" class="bg-white rounded-3xl border border-coffee-latte shadow-2xl p-8 max-w-2xl w-full space-y-6 coffee-card print:border-none print:shadow-none print:max-w-none print:w-full print:p-0">
                
                <!-- The printable payroll slip -->
                <div id="printable-slip-area" class="bg-white text-coffee-dark font-sans p-6 border border-coffee-latte rounded-2xl print:border-none print:p-0">
                    <!-- Header -->
                    <div class="flex justify-between items-start border-b-2 border-coffee-dark pb-4">
                        <div>
                            <h2 class="text-xl font-black text-coffee-dark tracking-wide uppercase">Kopi Premium</h2>
                            <p class="text-xs text-coffee-medium font-semibold">Premium Coffee Shop & Eatery</p>
                            <p class="text-xs text-coffee-light">Kawasan Bisnis, Kota Jakarta</p>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 bg-coffee-dark text-white rounded-lg text-[10px] font-black uppercase tracking-wider print:border print:border-coffee-dark print:text-coffee-dark">SLIP GAJI KARYAWAN</span>
                            <p class="text-[11px] font-bold text-coffee-medium mt-2">No: SG/{{ date('Ym') }}/<span x-text="printSlip.id_slip"></span></p>
                        </div>
                    </div>

                    <!-- Employee Details -->
                    <div class="grid grid-cols-2 gap-4 py-4 text-xs">
                        <div class="space-y-1">
                            <div class="flex"><span class="w-24 text-coffee-light font-bold">NAMA:</span><span class="font-extrabold text-coffee-dark" x-text="printSlip.karyawan?.nama_karyawan || 'N/A'"></span></div>
                            <div class="flex"><span class="w-24 text-coffee-light font-bold">JABATAN:</span><span class="font-bold text-coffee-medium" x-text="printSlip.karyawan?.jabatan?.nama_jabatan || printSlip.karyawan?.pekerjaan || 'N/A'"></span></div>
                        </div>
                        <div class="space-y-1 text-right sm:text-left sm:pl-8">
                            <div class="flex sm:justify-start"><span class="w-28 text-coffee-light font-bold">PERIODE:</span><span class="font-bold text-coffee-dark" x-text="getIndoMonthName(printSlip.bulan) + ' ' + printSlip.tahun"></span></div>
                            <div class="flex sm:justify-start"><span class="w-28 text-coffee-light font-bold">TGL BAYAR:</span><span class="font-bold text-coffee-dark" x-text="formatDate(printSlip.tanggal_pembayaran)"></span></div>
                            <div class="flex sm:justify-start"><span class="w-28 text-coffee-light font-bold">METODE:</span><span class="font-bold text-coffee-medium" x-text="printSlip.metode_pembayaran || 'Transfer'"></span></div>
                        </div>
                    </div>

                    <!-- Financial Breakdown Table -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-b border-coffee-latte py-4 my-2">
                        <!-- Income (Penerimaan) -->
                        <div>
                            <h4 class="font-black text-coffee-dark uppercase text-[10px] tracking-wider mb-2 pb-1 border-b border-coffee-latte">Penerimaan</h4>
                            <div class="space-y-1.5 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-coffee-medium">Gaji Pokok</span>
                                    <span class="font-bold" x-text="'Rp ' + formatRupiah(printSlip.gaji_pokok)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-coffee-medium">Tunjangan</span>
                                    <span class="font-bold text-emerald-600" x-text="'+ Rp ' + formatRupiah(printSlip.tunjangan)"></span>
                                </div>
                                <div class="flex justify-between border-t border-dashed border-coffee-latte pt-1.5 font-extrabold text-coffee-dark">
                                    <span>Total Penerimaan</span>
                                    <span x-text="'Rp ' + formatRupiah(printSlip.gaji_pokok + printSlip.tunjangan)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Deductions (Potongan) -->
                        <div>
                            <h4 class="font-black text-coffee-dark uppercase text-[10px] tracking-wider mb-2 pb-1 border-b border-coffee-latte">Potongan</h4>
                            <div class="space-y-1.5 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-coffee-medium">Potongan Gaji</span>
                                    <span class="font-bold text-rose-500" x-text="'- Rp ' + formatRupiah(printSlip.potongan)"></span>
                                </div>
                                <div class="flex justify-between border-t border-dashed border-coffee-latte pt-1.5 font-extrabold text-rose-600">
                                    <span>Total Potongan</span>
                                    <span x-text="'Rp ' + formatRupiah(printSlip.potongan)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Take Home Pay -->
                    <div class="bg-coffee-cream/40 p-4 rounded-xl flex flex-col sm:flex-row justify-between items-center gap-2 my-4 print:bg-slate-100">
                        <div>
                            <p class="text-[10px] text-coffee-medium font-bold uppercase tracking-wider">GAJI BERSIH (TAKE HOME PAY)</p>
                            <h3 class="text-lg font-black text-coffee-dark" x-text="'Rp ' + formatRupiah(printSlip.total_gaji)"></h3>
                        </div>
                        <div class="text-right sm:max-w-xs">
                            <p class="text-[10px] text-coffee-light font-bold uppercase tracking-wider">Terbilang</p>
                            <p class="text-xs font-extrabold text-coffee-medium italic" x-text="getTerbilang(printSlip.total_gaji) + ' Rupiah'"></p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="text-xs my-3" x-show="printSlip.catatan">
                        <span class="text-coffee-light font-bold uppercase tracking-wider text-[9px] block">Catatan:</span>
                        <p class="text-coffee-medium italic" x-text="printSlip.catatan"></p>
                    </div>

                    <!-- Signatures -->
                    <div class="flex justify-between pt-10 text-xs">
                        <div class="text-center w-36">
                            <p class="text-coffee-light font-bold uppercase tracking-wider text-[9px] mb-12">Penerima Karyawan,</p>
                            <div class="border-t border-coffee-dark pt-1 font-extrabold text-coffee-dark" x-text="printSlip.karyawan?.nama_karyawan || 'N/A'"></div>
                        </div>
                        <div class="text-center w-36">
                            <p class="text-coffee-light font-bold uppercase tracking-wider text-[9px] mb-12">Dibuat Oleh,</p>
                            <div class="border-t border-coffee-dark pt-1 font-extrabold text-coffee-dark">Manajemen Cafe</div>
                        </div>
                    </div>
                </div>

                <!-- Print buttons inside modal -->
                <div class="flex gap-4 print-hidden">
                    <button type="button" @click="showPrintModal = false" class="w-1/2 py-2.5 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs cursor-pointer">Tutup</button>
                    <button type="button" @click="window.print()" class="w-1/2 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition text-xs flex items-center justify-center gap-1.5 cursor-pointer shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.844l-.248-1.5a1.5 1.5 0 011.488-1.744h9.08a1.5 1.5 0 011.488 1.744l-.248 1.5M19.5 18h-15a1.5 1.5 0 01-1.5-1.5v-3a1.5 1.5 0 011.5-1.5h15a1.5 1.5 0 011.5 1.5v3a1.5 1.5 0 01-1.5 1.5zm-3-12v-1.5A1.5 1.5 0 0015 3H9a1.5 1.5 0 00-1.5 1.5V6m10.5 0v3H6V6"/></svg>
                        <span>Cetak Slip Gaji</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
    // Indonesian Terbilang Word converter
    function terbilang(nominal) {
        nominal = Math.floor(nominal);
        if (nominal < 0) return "";
        let bilangan = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        let temp = "";
        if (nominal < 12) {
            temp = " " + bilangan[nominal];
        } else if (nominal < 20) {
            temp = terbilang(nominal - 10) + " Belas";
        } else if (nominal < 100) {
            temp = terbilang(Math.floor(nominal / 10)) + " Puluh" + terbilang(nominal % 10);
        } else if (nominal < 200) {
            temp = " Seratus" + terbilang(nominal - 100);
        } else if (nominal < 1000) {
            temp = terbilang(Math.floor(nominal / 100)) + " Ratus" + terbilang(nominal % 100);
        } else if (nominal < 2000) {
            temp = " Seribu" + terbilang(nominal - 1000);
        } else if (nominal < 1000000) {
            temp = terbilang(Math.floor(nominal / 1000)) + " Ribu" + terbilang(nominal % 1000);
        } else if (nominal < 1000000000) {
            temp = terbilang(Math.floor(nominal / 1000000)) + " Juta" + terbilang(nominal % 1000000);
        } else if (nominal < 1000000000000) {
            temp = terbilang(Math.floor(nominal / 1000000000)) + " Milyar" + terbilang(nominal % 1000000000);
        }
        return temp.trim();
    }

    function formatRupiah(angka) {
        if (!angka) return '0';
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function slipGajiManager() {
        return {
            addModal: false,
            showPrintModal: false,
            printSlip: {},
            
            // Raw and formatted values for real-time reactivity in forms
            gajiPokokRaw: 0,
            gajiPokokFormatted: '',
            tunjanganRaw: 0,
            tunjanganFormatted: '0',
            potonganRaw: 0,
            potonganFormatted: '0',
            totalGajiRaw: 0,
            
            karyawans: @json($karyawans),
            slips: @json($slips->items()),

            init() {
                // If there's an auto-print session, find that slip and open print modal
                @if(session()->has('print_slip_id'))
                    let autoPrintId = {{ session('print_slip_id') }};
                    // Since paginated items are in this.slips, look for it
                    let target = this.slips.find(s => s.id_slip == autoPrintId);
                    if (target) {
                        this.openPrintSlip(target);
                    }
                @endif
            },

            onEmployeeSelect(karyawanId) {
                let emp = this.karyawans.find(k => k.id_karyawan == karyawanId);
                if (emp) {
                    this.gajiPokokRaw = emp.gaji;
                    this.gajiPokokFormatted = formatRupiah(emp.gaji);
                } else {
                    this.gajiPokokRaw = 0;
                    this.gajiPokokFormatted = '';
                }
                this.calculateTotal();
            },

            calculateTotal() {
                this.totalGajiRaw = this.gajiPokokRaw + this.tunjanganRaw - this.potonganRaw;
            },

            openPrintSlip(slip) {
                this.printSlip = slip;
                this.showPrintModal = true;
            },

            getIndoMonthName(monthNumber) {
                const months = {
                    1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
                    5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
                    9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
                };
                return months[monthNumber] || '';
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                // Try converting "YYYY-MM-DD" to nicer format
                try {
                    let d = new Date(dateString);
                    if (isNaN(d.getTime())) return dateString;
                    let day = d.getDate().toString().padStart(2, '0');
                    let month = this.getIndoMonthName(d.getMonth() + 1);
                    let year = d.getFullYear();
                    return `${day} ${month} ${year}`;
                } catch(e) {
                    return dateString;
                }
            },

            getTerbilang(nominal) {
                return terbilang(nominal || 0);
            },

            formatRupiah(val) {
                return formatRupiah(val);
            }
        }
    }
</script>
@endsection
