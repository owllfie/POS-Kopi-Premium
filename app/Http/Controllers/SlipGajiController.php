<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SlipGaji;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\KeuanganTransaksi;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SlipGajiController extends Controller
{
    private function getActiveUser()
    {
        if (session()->has('simulated_user_id')) {
            return User::find(session('simulated_user_id'));
        }
        return Auth::user();
    }

    private function getMonthName($monthNumber)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $months[$monthNumber] ?? '';
    }

    public function index(Request $request)
    {
        $user = $this->getActiveUser();
        $slips = SlipGaji::with('karyawan.jabatan')->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->paginate(15);
        $karyawans = Karyawan::with('jabatan')->orderBy('nama_karyawan', 'asc')->get();

        return view('slip_gaji.index', compact('slips', 'karyawans'));
    }

    public function store(Request $request)
    {
        $user = $this->getActiveUser();

        $validated = $request->validate([
            'id_karyawan' => 'required|exists:karyawan,id_karyawan',
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'gaji_pokok' => 'required|integer|min:0',
            'tunjangan' => 'required|integer|min:0',
            'potongan' => 'required|integer|min:0',
            'catatan' => 'nullable|string|max:255',
            'tanggal_pembayaran' => 'required|date',
            'metode_pembayaran' => 'required|in:Tunai,Transfer',
        ]);

        // Check if slip already exists for this employee, month, and year
        $exists = SlipGaji::where('id_karyawan', $validated['id_karyawan'])
            ->where('bulan', $validated['bulan'])
            ->where('tahun', $validated['tahun'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Slip gaji untuk karyawan ini pada bulan dan tahun terpilih sudah ada.');
        }

        $totalGaji = $validated['gaji_pokok'] + $validated['tunjangan'] - $validated['potongan'];
        $validated['total_gaji'] = $totalGaji;

        $slip = SlipGaji::create($validated);
        $karyawan = Karyawan::find($validated['id_karyawan']);
        $bulanName = $this->getMonthName($validated['bulan']);

        // Post ledger entry (salary expense: credit)
        $tx = KeuanganTransaksi::create([
            'tanggal' => $validated['tanggal_pembayaran'],
            'kode_akun' => 6101, // OPEX - Gaji Pokok Karyawan
            'deskripsi' => "Pembayaran Gaji Karyawan: {$karyawan->nama_karyawan} ({$bulanName} {$validated['tahun']})",
            'metode' => $validated['metode_pembayaran'],
            'debit' => 0,
            'kredit' => $totalGaji,
            'id_user' => $user ? $user->id_user : null,
        ]);

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'GENERATE_SALARY_SLIP',
            'detail_aktivitas' => "Generated salary slip for {$karyawan->nama_karyawan} for {$bulanName} {$validated['tahun']}. Posted ledger transaction ID {$tx->id_transaksi}.",
            'ip_address' => $request->ip(),
        ]);

        // Put generated slip ID in session for auto-print trigger
        return back()->with('success', 'Slip gaji berhasil dibuat & dibukukan.')->with('print_slip_id', $slip->id_slip);
    }

    public function delete(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $slip = SlipGaji::findOrFail($id);
        $name = $slip->karyawan->nama_karyawan;
        $bulanName = $this->getMonthName($slip->bulan);
        $tahun = $slip->tahun;
        
        $slip->delete();

        ActivityLog::create([
            'id_user' => $user ? $user->id_user : null,
            'aktivitas' => 'DELETE_SALARY_SLIP',
            'detail_aktivitas' => "Deleted salary slip of {$name} for {$bulanName} {$tahun}.",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Slip gaji berhasil dihapus.');
    }
}
