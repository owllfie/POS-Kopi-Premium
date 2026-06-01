<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Meja;
use App\Models\DetailPesanan;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
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
        $user = $this->getActiveUser();
        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->role->role;

        if ($role === 'chef') {
            // Chef view: only pending/cooking detail lines, grouped by table
            $kitchenItems = DetailPesanan::whereNull('id_pesanan')
                ->whereIn('status', ['menunggu', 'dimasak'])
                ->with(['menu', 'mejaTemp'])
                ->orderBy('created_at', 'asc')
                ->get()
                ->groupBy('id_meja_temp');

            return view('pesanan.chef', compact('kitchenItems'));
        }

        // Cashier/Admin view: active orders grouped by table
        $statusFilter = $request->input('status', 'semua');
        
        $query = DetailPesanan::whereNull('id_pesanan')
            ->whereNotNull('id_meja_temp')
            ->with(['menu', 'mejaTemp']);

        if ($statusFilter !== 'semua') {
            $query->where('status', $statusFilter);
        }

        $items = $query->orderBy('created_at', 'asc')->get();
        
        // Group by table to present active orders
        $activeOrders = [];
        $grouped = $items->groupBy('id_meja_temp');
        
        foreach ($grouped as $mejaId => $details) {
            $meja = Meja::find($mejaId);
            if (!$meja) continue;

            $total = $details->sum('subtotal');
            $earliestTime = $details->min('created_at');
            
            // Determine order-level status: if any is 'menunggu' -> 'menunggu', if all are 'selesai' -> 'selesai', else 'dimasak'
            $statuses = $details->pluck('status')->unique()->toArray();
            $orderStatus = 'dimasak';
            if (count($statuses) === 1 && $statuses[0] === 'selesai') {
                $orderStatus = 'selesai';
            } elseif (in_array('menunggu', $statuses) && !in_array('dimasak', $statuses)) {
                $orderStatus = 'menunggu';
            }

            $activeOrders[] = [
                'meja' => $meja,
                'details' => $details,
                'total' => $total,
                'created_at' => $earliestTime,
                'status' => $orderStatus,
            ];
        }

        return view('pesanan.cashier', compact('activeOrders', 'statusFilter'));
    }

    // Chef updates item status (menunggu -> dimasak -> selesai)
    public function updateStatus(Request $request, $id)
    {
        $user = $this->getActiveUser();
        $item = DetailPesanan::find($id);

        if (!$item) {
            return back()->with('error', 'Item pesanan tidak ditemukan.');
        }

        $status = $request->input('status');
        if (!in_array($status, ['dimasak', 'selesai'])) {
            return back()->with('error', 'Status tidak valid.');
        }

        $oldStatus = $item->status;
        $item->status = $status;
        $item->save();

        // Log kitchen action
        ActivityLog::create([
            'id_user' => $user->id_user,
            'aktivitas' => 'KITCHEN_STATUS_UPDATE',
            'detail_aktivitas' => 'Chef updated detail ID ' . $item->id_detail . ' (' . $item->menu->nama_menu . ') status from ' . $oldStatus . ' to ' . $status,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Status menu ' . $item->menu->nama_menu . ' berhasil diperbarui.');
    }
}
