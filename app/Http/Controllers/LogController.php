<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\User;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->input('user_id', 'semua');
        $search = $request->input('search');

        $query = ActivityLog::with('user');

        if ($userId !== 'semua') {
            $query->where('id_user', $userId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('aktivitas', 'like', "%{$search}%")
                  ->orWhere('detail_aktivitas', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $users = User::all();

        return view('log.index', compact('logs', 'users', 'userId', 'search'));
    }
}
