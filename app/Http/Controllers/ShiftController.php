<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShiftController extends Controller
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
        $tab = $request->input('tab', 'active');
        if ($request->input('trash') === '1') {
            $tab = 'trash';
        }

        $query = Shift::with('user');

        if ($tab === 'trash') {
            $query->onlyTrashed();
        }

        $shifts = $query->orderBy('jam_mulai', 'desc')->paginate(15)->withQueryString();

        $historyUpdates = \App\Models\HistoryUpdate::where('table', 'shift')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('shift.index', compact('shifts', 'tab', 'historyUpdates'));
    }

    public function update(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $shift = Shift::findOrFail($id);

        $validated = $request->validate([
            'cash_masuk' => 'required|numeric|min:0',
            'qris_masuk' => 'required|numeric|min:0',
        ]);

        $validated['total_masuk'] = $validated['cash_masuk'] + $validated['qris_masuk'];

        $shift->update($validated);

        return back()->with('success', 'Catatan kas shift berhasil diperbarui.');
    }

    public function delete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $shift = Shift::findOrFail($id);
        $shift->delete();

        return back()->with('success', 'Catatan shift diarsipkan (dipindahkan ke Trash).');
    }

    public function restore(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $shift = Shift::onlyTrashed()->findOrFail($id);
        $shift->restore();

        return back()->with('success', 'Catatan shift berhasil dipulihkan.');
    }

    public function forceDelete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $shift = Shift::onlyTrashed()->findOrFail($id);
        $shift->forceDelete();

        return back()->with('success', 'Catatan shift dihapus secara permanen.');
    }
}
