<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Redirect if already authenticated (or simulated)
        if (session()->has('simulated_user_id')) {
            return redirect()->route('dashboard');
        }
        if (Auth::check()) {
            session(['simulated_user_id' => Auth::user()->id_user]);
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();
            
            // Set simulated session to match logged in user
            session(['simulated_user_id' => $user->id_user]);

            // Log activity
            ActivityLog::create([
                'id_user' => $user->id_user,
                'aktivitas' => 'LOGIN',
                'detail_aktivitas' => 'User logged in successfully via credentials.',
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('dashboard')->with('success', 'Selamat datang kembali, ' . $user->username . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $userId = session('simulated_user_id') ?: (Auth::check() ? Auth::user()->id_user : null);
        
        if ($userId) {
            ActivityLog::create([
                'id_user' => $userId,
                'aktivitas' => 'LOGOUT',
                'detail_aktivitas' => 'User logged out.',
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    // Role simulator logic - sets active simulated user in session
    public function simulateRole(Request $request, $id_role)
    {
        $user = User::where('id_role', $id_role)->first();
        if ($user) {
            session(['simulated_user_id' => $user->id_user]);
            
            ActivityLog::create([
                'id_user' => $user->id_user,
                'aktivitas' => 'SIMULATE_ROLE',
                'detail_aktivitas' => 'Switched active simulated role to: ' . strtoupper($user->role->role),
                'ip_address' => $request->ip(),
            ]);

            // Chef goes directly to Daftar Pesanan per workflow.md, others go to Dashboard
            if ($user->role->role === 'chef') {
                return redirect()->route('pesanan')->with('success', 'Simulasi: Masuk sebagai CHEF');
            }
            
            return redirect()->route('dashboard')->with('success', 'Simulasi: Masuk sebagai ' . strtoupper($user->role->role));
        }

        return redirect()->route('dashboard')->with('error', 'Role tidak ditemukan.');
    }
}
