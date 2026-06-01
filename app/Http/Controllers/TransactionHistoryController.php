<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\ActivityLog;
use Carbon\Carbon;

class TransactionHistoryController extends Controller
{
    private function getActiveUser()
    {
        if (session()->has('simulated_user_id')) {
            return User::find(session('simulated_user_id'));
        }
        return Auth::user();
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $cashierId = $request->input('kasir_id', 'semua');
        $paymentMethod = $request->input('metode', 'semua');
        $viewTrash = $request->input('trash', '0') === '1';

        $query = Pesanan::with(['meja', 'user', 'details.menu']);

        if ($viewTrash) {
            $query->onlyTrashed();
        }

        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_struk', 'like', "%{$search}%")
                  ->orWhereHas('meja', function($qm) use ($search) {
                      $qm->where('nomor_meja', $search);
                  });
            });
        }

        if ($cashierId !== 'semua') {
            $query->where('id_user', $cashierId);
        }

        if ($paymentMethod !== 'semua') {
            $query->where('metode_pembayaran', $paymentMethod);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $cashiers = User::whereHas('role', function($q) {
            $q->where('role', 'kasir');
        })->get();

        return view('transaksi.index', compact('transactions', 'search', 'cashierId', 'paymentMethod', 'viewTrash', 'cashiers'));
    }

    public function show($id)
    {
        $transaction = Pesanan::withTrashed()->with(['meja', 'user', 'details.menu'])->find($id);
        if (!$transaction) {
            return response()->json(['error' => 'Transaksi tidak ditemukan.'], 404);
        }
        return response()->json($transaction);
    }

    // Soft delete transaction
    public function delete(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $transaction = Pesanan::find($id);

        if (!$transaction) {
            return back()->with('error', 'Transaksi tidak ditemukan.');
        }

        $transaction->delete();

        ActivityLog::create([
            'id_user' => $user->id_user,
            'aktivitas' => 'DELETE_TRANSACTION',
            'detail_aktivitas' => "Soft-deleted transaction: {$transaction->kode_struk}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Transaksi berhasil dihapus (dipindahkan ke Trash).');
    }

    // Restore soft-deleted transaction
    public function restore(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $transaction = Pesanan::onlyTrashed()->find($id);

        if (!$transaction) {
            return back()->with('error', 'Transaksi tidak ditemukan.');
        }

        $transaction->restore();

        ActivityLog::create([
            'id_user' => $user->id_user,
            'aktivitas' => 'RESTORE_TRANSACTION',
            'detail_aktivitas' => "Restored soft-deleted transaction: {$transaction->kode_struk}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Transaksi berhasil dikembalikan.');
    }

    // Permanent hard delete
    public function forceDelete(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $transaction = Pesanan::onlyTrashed()->find($id);

        if (!$transaction) {
            return back()->with('error', 'Transaksi tidak ditemukan.');
        }

        $kode = $transaction->kode_struk;
        $transaction->forceDelete();

        ActivityLog::create([
            'id_user' => $user->id_user,
            'aktivitas' => 'PERMANENT_DELETE_TRANSACTION',
            'detail_aktivitas' => "Permanently deleted transaction: {$kode}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Transaksi berhasil dihapus secara permanen.');
    }
}
